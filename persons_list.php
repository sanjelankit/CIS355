<?php
session_start();
require(__DIR__ . '/database/database.php');

if (!isset($_SESSION['user_id']) || !$_SESSION['admin']) {
    header("Location: login.php");
    exit();
}

$conn = Database::connect();
$users = $conn->query("
    SELECT p.id, p.fname, p.lname, p.mobile, p.email, p.admin, 
           (SELECT COUNT(*) FROM iss_issues i WHERE i.per_id = p.id) AS issue_count
    FROM iss_persons p
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List</title>
    <style>
        body { font-family: Arial, sans-serif; background: #F8B195; }
        .container { width: 80%; margin: auto; padding: 20px; background: #6C5B7B; border-radius: 10px; color: white; }
        table { width: 100%; border-collapse: collapse; background: #355C7D; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #fff; }
        th { background: #F67280; }
        .btn { padding: 5px 10px; border: none; cursor: pointer; border-radius: 5px; }
        .edit-btn { background: #F67280; color: white; }
        .delete-btn { background: #C06C84; color: white; }
    </style>
</head>
<body>

<div class="container">
<a href="issues_list.php">
    <button style="margin-bottom: 15px; background-color: #007BFF; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer;">
        ← Back to Issues
    </button>
</a>

    <h2>Users List</h2>
    <button onclick="document.getElementById('addUserModal').style.display='block'">+ Add User</button>
    
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Admin</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['fname'] . " " . $user['lname']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['mobile']) ?></td>
                <td><?= ($user['admin'] === 'Yes') ? "Yes" : "No" ?></td>
                <td>
                <a href="edit_user.php?id=<?= $user['id'] ?>"><button>Edit</button></a>
                <?php if ($_SESSION['user_id'] != $user['id'] && $user['admin'] !== 'Yes'): ?>
                        <button class="btn delete-btn" onclick="deleteUser(<?= $user['id'] ?>)">Delete</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Add User Modal -->
<div id="addUserModal" style="display:none; position:fixed; top:20%; left:30%; background:white; padding:20px; border-radius:10px;">
    <h3>Add User</h3>
    <input type="text" id="newFname" placeholder="First Name">
    <input type="text" id="newLname" placeholder="Last Name">
    <input type="text" id="newMobile" placeholder="Mobile">
    <input type="email" id="newEmail" placeholder="Email">
    <input type="password" id="newPassword" placeholder="Password">
    <label><input type="checkbox" id="newAdmin"> Admin</label>
    <button onclick="addUser()">Submit</button>
    <button onclick="document.getElementById('addUserModal').style.display='none'">Close</button>
</div>

<script>
function addUser() {
    let fname = document.getElementById("newFname").value;
    let lname = document.getElementById("newLname").value;
    let mobile = document.getElementById("newMobile").value;
    let email = document.getElementById("newEmail").value;
    let password = document.getElementById("newPassword").value;
    let admin = document.getElementById("newAdmin").checked ? 1 : 0;

    fetch("add_user.php", {
        method: "POST",
        body: JSON.stringify({ fname, lname, mobile, email, password, admin }),
        headers: { "Content-Type": "application/json" }
    }).then(res => res.json()).then(data => {
        if (data.success) location.reload();
        else alert(data.error);
    });
}

function editUser(id, fname, lname, mobile, email, admin) {
    let newFname = prompt("Enter new first name:", fname);
    let newLname = prompt("Enter new last name:", lname);
    let newMobile = prompt("Enter new mobile:", mobile);
    let newEmail = prompt("Enter new email:", email);
    let newAdmin = confirm("Should this user be an admin?") ? 1 : 0;

    if (newFname && newLname && newMobile && newEmail) {
        fetch("edit_user.php", {
            method: "POST",
            body: JSON.stringify({ id, fname: newFname, lname: newLname, mobile: newMobile, email: newEmail, admin: newAdmin }),
            headers: { "Content-Type": "application/json" }
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.error);
        });
    }
}

function deleteUser(id) {
    if (confirm("Are you sure you want to delete this user?")) {
        fetch("delete_user.php", {
            method: "POST",
            body: JSON.stringify({ id }),
            headers: { "Content-Type": "application/json" }
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.error);
        });
    }
}
</script>

</body>
</html>