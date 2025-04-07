<?php
session_start();
require '../database/database.php'; // Ensure correct path

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $conn = Database::connect();

        // Get id, hash, salt, and admin status
        $stmt = $conn->prepare("SELECT id, pwd_hash, pwd_salt, admin FROM iss_persons WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && md5($password . $user['pwd_salt']) === $user['pwd_hash']) {
            $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $email;
                $_SESSION['admin'] = ($user['admin'] === 'Yes'); // boolean

                header("Location: issues_list.php");
                exit();
            } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DSR</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: #F8B195;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #6C5B7B;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(255, 108, 120, 0.5);
            width: 350px;
            text-align: center;
            color: white;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #355C7D;
        }

        .error-message {
            color: #F67280;
            font-size: 14px;
            margin-bottom: 10px;
        }

        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #C06C84;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #355C7D;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #F67280;
            box-shadow: 0 0 15px rgba(255, 108, 120, 0.5);
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Login</button>
    </form>
</div>

</body>
</html>
