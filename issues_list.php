<?php
session_start();
require '../database/database.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch issues from the database
$conn = Database::connect();
$stmt = $conn->prepare("SELECT id, short_description, long_description, priority, open_date, close_date FROM iss_issues");
$stmt->execute();
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to handle modal display for read, update, and delete actions
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List - DSR</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .add-issue-btn {
            margin: 20px 0;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .add-issue-btn:hover {
            background-color: #45a049;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
        .button-group button {
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Issues List</h2>

    <!-- Add New Issue Button -->
    <a href="add_issue.php"><button class="add-issue-btn">Add New Issue</button></a>

    <!-- Issues Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Short Description</th>
                <th>Long Description</th>
                <th>Priority</th>
                <th>Open Date</th>
                <th>Close Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($issues as $issue): ?>
                <tr>
                    <td><?php echo htmlspecialchars($issue['id']); ?></td>
                    <td><?php echo htmlspecialchars($issue['short_description']); ?></td>
                    <td><?php echo htmlspecialchars($issue['long_description']); ?></td>
                    <td><?php echo htmlspecialchars($issue['priority']); ?></td>
                    <td><?php echo htmlspecialchars($issue['open_date']); ?></td>
                    <td><?php echo htmlspecialchars($issue['close_date']); ?></td>
                    <td class="button-group">
                        <!-- Read Button -->
                        <button onclick="openModal('read', <?php echo $issue['id']; ?>)">R</button>
                        <!-- Update Button -->
                        <button onclick="openModal('update', <?php echo $issue['id']; ?>)">U</button>
                        <!-- Delete Button -->
                        <button onclick="openModal('delete', <?php echo $issue['id']; ?>)">D</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal for Read, Update, Delete -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modal-title">Read Issue</h3>
            <form id="issue-form" method="POST" action="">
                <label for="short_description">Short Description:</label>
                <input type="text" id="short_description" name="short_description" readonly><br><br>

                <label for="long_description">Long Description:</label>
                <textarea id="long_description" name="long_description" readonly></textarea><br><br>

                <label for="priority">Priority:</label>
                <input type="text" id="priority" name="priority" readonly><br><br>

                <label for="open_date">Open Date:</label>
                <input type="date" id="open_date" name="open_date" readonly><br><br>

                <label for="close_date">Close Date:</label>
                <input type="date" id="close_date" name="close_date" readonly><br><br>

                <!-- Buttons for Update and Delete -->
                <div id="modal-buttons">
                    <!-- Buttons dynamically shown based on action -->
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open the modal and populate data based on action type (read, update, delete)
        function openModal(action, issueId) {
            var modal = document.getElementById('modal');
            var form = document.getElementById('issue-form');
            var modalTitle = document.getElementById('modal-title');
            var modalButtons = document.getElementById('modal-buttons');

            // Set modal action title
            if (action === 'read') {
                modalTitle.innerHTML = 'Read Issue';
                form.elements['short_description'].readOnly = true;
                form.elements['long_description'].readOnly = true;
                form.elements['priority'].readOnly = true;
                form.elements['open_date'].readOnly = true;
                form.elements['close_date'].readOnly = true;
                modalButtons.innerHTML = ''; // No buttons for read
            } else if (action === 'update') {
                modalTitle.innerHTML = 'Update Issue';
                form.elements['short_description'].readOnly = false;
                form.elements['long_description'].readOnly = false;
                form.elements['priority'].readOnly = false;
                form.elements['open_date'].readOnly = false;
                form.elements['close_date'].readOnly = false;
                modalButtons.innerHTML = '<button type="submit" name="update" value="' + issueId + '">Update Issue</button>';
            } else if (action === 'delete') {
                modalTitle.innerHTML = 'Delete Issue';
                form.elements['short_description'].readOnly = true;
                form.elements['long_description'].readOnly = true;
                form.elements['priority'].readOnly = true;
                form.elements['open_date'].readOnly = true;
                form.elements['close_date'].readOnly = true;
                modalButtons.innerHTML = '<button type="submit" name="delete" value="' + issueId + '">Delete Issue</button>';
            }

            // Fetch issue data and populate the modal form fields
            fetch('get_issue_data.php?id=' + issueId)
                .then(response => response.json())
                .then(data => {
                    form.elements['short_description'].value = data.short_description;
                    form.elements['long_description'].value = data.long_description;
                    form.elements['priority'].value = data.priority;
                    form.elements['open_date'].value = data.open_date;
                    form.elements['close_date'].value = data.close_date;
                });

            modal.style.display = 'block';
        }

        // Close the modal
        function closeModal() {
            var modal = document.getElementById('modal');
            modal.style.display = 'none';
        }
    </script>

</body>
</html>
