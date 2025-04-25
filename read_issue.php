<?php
session_start();
require(__DIR__ . '/database/database.php');

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
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Issue - DSR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 60%;
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

        .content {
            padding: 20px;
        }

        p {
            font-size: 16px;
            padding: 10px 0;
        }

        strong {
            color: #007BFF;
        }

        .btn-container {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 15px;
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .pdf-preview {
            margin-top: 20px;
            border: 1px solid #ccc;
            height: 600px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Issue Details</h2>
    <div class="content">
        <p><strong>ID:</strong> <?php echo htmlspecialchars($issue['id']); ?></p>
        <p><strong>Full Description:</strong> <?php echo htmlspecialchars($issue['long_description']); ?></p>
        <p><strong>Priority:</strong> <?php echo htmlspecialchars($issue['priority']); ?></p>
        <p><strong>Open Date:</strong> <?php echo htmlspecialchars($issue['open_date']); ?></p>
        <p><strong>Close Date:</strong> <?php echo htmlspecialchars($issue['close_date'] ?: "N/A"); ?></p>

        <?php if (!empty($issue['pdf_attachment']) && file_exists($issue['pdf_attachment'])): ?>
            <h3>Attached PDF:</h3>
            <div class="pdf-preview">
                <embed src="<?php echo htmlspecialchars($issue['pdf_attachment']); ?>" type="application/pdf" width="100%" height="100%">
            </div>
            <p style="text-align:center; margin-top: 10px;">
                <a class="btn" href="<?php echo htmlspecialchars($issue['pdf_attachment']); ?>" target="_blank">Download PDF</a>
            </p>
        <?php else: ?>
            <p><strong>Attachment:</strong> No PDF attached.</p>
        <?php endif; ?>
    </div>

    <div class="btn-container">
        <a href="issues_list.php" class="btn">Back to Issues</a>
    </div>
</div>

</body>
</html>
