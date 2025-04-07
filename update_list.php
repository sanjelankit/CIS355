<?php
session_start();
require '../database/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Error: Issue ID not provided.");
}

$issue_id = $_GET['id'];

try {
    $conn = Database::connect();
    $stmt = $conn->prepare("SELECT * FROM iss_issues WHERE id = ?");
    $stmt->execute([$issue_id]);
    $issue = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$issue) {
        die("Error: Issue not found.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $short_description = $_POST['short_description'];
        $priority = $_POST['priority'];
        $close_date = $_POST['close_date'] ?: null;

        $update_stmt = $conn->prepare("UPDATE iss_issues SET short_description = ?, priority = ?, close_date = ? WHERE id = ?");
        $update_stmt->execute([$short_description, $priority, $close_date, $issue_id]);

        header("Location: issues_list.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Issue - DSR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            background-color: #333;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }

        form {
            padding: 20px;
        }

        label {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-container {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 15px;
            text-decoration: none;
            color: white;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background-color: #218838;
        }

        .btn-back {
            background-color: #007BFF;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Update Issue</h2>
    <form method="POST">
        <label>Short Description:</label>
        <input type="text" name="short_description" value="<?php echo htmlspecialchars($issue['short_description']); ?>" required>

        <label>Priority:</label>
        <input type="text" name="priority" value="<?php echo htmlspecialchars($issue['priority']); ?>" required>

        <label>Close Date:</label>
        <input type="date" name="close_date" value="<?php echo htmlspecialchars($issue['close_date']); ?>">

        <div class="btn-container">
            <button type="submit" class="btn">Update Issue</button>
            <a href="issues_list.php" class="btn btn-back">Back to Issues</a>
        </div>
    </form>
</div>

</body>
</html>
