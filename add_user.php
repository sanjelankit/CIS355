<?php
require(__DIR__ . '/database/database.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['fname'], $data['lname'], $data['email'], $data['password'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$fname = $data['fname'];
$lname = $data['lname'];
$mobile = $data['mobile'] ?? '';
$email = $data['email'];
$password = $data['password'];
$admin = ($data['admin'] === 'Yes') ? 'Yes' : 'No';

// Hashing password with md5 + salt
$salt = bin2hex(random_bytes(4));
$hash = md5($password . $salt);

try {
    $conn = Database::connect();
    $stmt = $conn->prepare("INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, pwd_salt, admin)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fname, $lname, $mobile, $email, $hash, $salt, $admin]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            padding: 20px;
        }
        button {
            padding: 8px 12px;
            font-size: 14px;
            margin-right: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #222;
            color: white;
        }
        .btn-comments { background-color: #17a2b8; color: white; }
        .btn-update { background-color: #ccc; }
        .btn-delete { background-color: #dc3545; color: white; }
        .top-buttons {
            margin-bottom: 10px;
        }
        .logout-btn {
            float: right;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
        }
    </style>
</head>
<body>
    <header>
        <h2>Issues List</h2>
    </header>

    <div class="container">
        <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>

        <div class="top-buttons">
            <a href="add_issue.php"><button>Add New Issue</button></a>
            <?php if ($admin): ?>
                <a href="persons_list.php"><button>Manage Users</button></a>
            <?php endif; ?>
            <a href="logout.php"><button class="logout-btn">Log Out</button></a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Description</th>
                    <th>Priority</th>
                    <th>Open Date</th>
                    <th>Close Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues as $issue): ?>
                    <tr>
                        <td><?= $issue['id'] ?></td>
                        <td><?= htmlspecialchars($issue['short_description']) ?></td>
                        <td><?= $issue['priority'] ?></td>
                        <td><?= $issue['open_date'] ?></td>
                        <td><?= $issue['close_date'] ?></td>
                        <td>
                            <a href="read_issue.php?id=<?= $issue['id'] ?>"><button>Read</button></a>
                            <a href="issue_comments.php?issue_id=<?= $issue['id'] ?>"><button class="btn-comments">Comments</button></a>
                            <?php if ($_SESSION['user_id'] == $issue['per_id'] || $admin): ?>
                                <button class="btn-update" onclick="openEditModal(<?= $issue['id'] ?>)">Update</button>
                                <a href="issues_list.php?delete_id=<?= $issue['id'] ?>"><button class="btn-delete">Delete</button></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="edit_user.php?id=<?= $user['id'] ?>"><button>Edit</button></a>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span style="float:right;cursor:pointer" onclick="closeEditModal()">&times;</span>
            <p>Redirecting to update page...</p>
        </div>
    </div>

    <script>
        function openEditModal(id) {
            document.getElementById('editModal').style.display = 'block';
            setTimeout(function() {
                window.location.href = 'update_issue.php?id=' + id;
            }, 800);
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
