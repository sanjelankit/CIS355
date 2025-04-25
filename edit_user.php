<?php
require(__DIR__ . '/database/database.php');
session_start();

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Missing user ID.";
    exit();
}

$id = $_GET['id'];
$conn = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $admin = $_POST['admin'] === 'Yes' ? 'Yes' : 'No';

    $stmt = $conn->prepare("UPDATE iss_persons SET fname = ?, lname = ?, email = ?, mobile = ?, admin = ? WHERE id = ?");
    $stmt->execute([$fname, $lname, $email, $mobile, $admin, $id]);

    header("Location: persons_list.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM iss_persons WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit User</h2>
        <form method="POST">
            <label>First Name:</label>
            <input type="text" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" required>

            <label>Last Name:</label>
            <input type="text" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Mobile:</label>
            <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>">

            <label>Admin:</label>
            <select name="admin">
                <option value="Yes" <?= $user['admin'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
                <option value="No" <?= $user['admin'] === 'No' ? 'selected' : '' ?>>No</option>
            </select>

            <button type="submit">Update User</button>
        </form>
    </div>
</body>
</html>