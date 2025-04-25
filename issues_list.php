    <?php
require(__DIR__ . '/database/database.php');
ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    session_start();
    
    // Validate user session
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    // Optional: Check admin status
    $admin = isset($_SESSION['admin']) && $_SESSION['admin'];

    // Handle PDF upload (admin only)
    if ($admin && isset($_FILES['pdf_attachment'])) {
        $fileTemppath = $_FILES['pdf_attachment']['tmp_name'];
        $fileName = $_FILES['pdf_attachment']['name'];
        $fileType = $_FILES['pdf_attachment']['type'];
        $fileSize = $_FILES['pdf_attachment']['size'];

        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        if ($fileExtension !== 'pdf') {
            die('Only PDF files are allowed.');
        }

        if ($fileSize > 2 * 1024 * 1024) {
            die('File size exceeds 2MB limit.');
        }

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = './upload/';
        $dest_path = $uploadFileDir . $newFileName;

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        if (move_uploaded_file($fileTemppath, $dest_path)) {
            $attachmentPath = $dest_path;
            // Optional: Save $attachmentPath to DB here
        } else {
            die('Error moving file.');
        }
    }

    // Handle issue deletion (admin only)
    if ($admin && isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];

        try {
            $conn = Database::connect();
            $stmt = $conn->prepare("DELETE FROM iss_issues WHERE id = ?");
            $stmt->execute([$delete_id]);
            header("Location: issues_list.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error deleting issue: " . $e->getMessage();
        }
    }

    // Fetch all issues from the database
    try {
        $conn = Database::connect();
        $stmt = $conn->query("SELECT * FROM iss_issues");
        $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "Error fetching issues: " . $e->getMessage();
    }
    ?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Issues List - DSR</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }

            h2 {
                text-align: center;
                padding: 20px;
                background-color: #333;
                color: white;
            }

            table {
                width: 80%;
                margin: 20px auto;
                border-collapse: collapse;
                background-color: white;
            }

            th, td {
                padding: 10px;
                text-align: center;
                border: 1px solid #ddd;
            }

            th {
                background-color: #333;
                color: white;
            }

            button {
                padding: 8px 12px;
                border: none;
                cursor: pointer;
                font-size: 14px;
                transition: background-color 0.3s ease;
            }

            button:hover {
                background-color: #007BFF;
                color: white;
            }

            .delete-btn {
                background-color: #dc3545;
                color: white;
            }

            .delete-btn:hover {
                background-color: #c82333;
            }

            .comments-btn {
                background-color: #17a2b8;
                color: white;
            }

            .comments-btn:hover {
                background-color: #138496;
            }

            /* Modal */
            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0, 0, 0, 0.5);
                padding-top: 60px;
            }

            .modal-content {
                background-color: #fff;
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 400px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .modal h3 {
                margin-bottom: 20px;
                font-size: 18px;
            }

            .modal button {
                font-size: 16px;
                padding: 10px 20px;
                margin: 5px;
            }

            .modal .btn-yes {
                background-color: #28a745;
                color: white;
            }

            .modal .btn-no {
                background-color: #6c757d;
                color: white;
            }

            .close {
                color: #aaa;
                font-size: 28px;
                font-weight: bold;
                position: absolute;
                top: 10px;
                right: 20px;
            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
            }

            /* Responsive Design */
            @media screen and (max-width: 600px) {
                table {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>

        <h2>Issues List</h2>

        <?php
        // Display any error message
        if (isset($error_message)) {
            echo "<p style='color: red; text-align: center;'>$error_message</p>";
        }
        ?>

        <a href="add_issue.php"><button>Add New Issue</button></a>
        <a href="logout.php"><button type="button" style="float: right; margin: 10px;">Log Out</button></a>



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
                        <td><?php echo $issue['id']; ?></td>
                        <td><?php echo $issue['short_description']; ?></td>
                        <td><?php echo $issue['priority']; ?></td>
                        <td><?php echo $issue['open_date']; ?></td>
                        <td><?php echo $issue['close_date']; ?></td>
                        <td>
                        <a href="read_issue.php?id=<?php echo $issue['id']; ?>"><button>Read</button></a>
<a href="issue_comments.php?issue_id=<?php echo $issue['id']; ?>"><button class="comments-btn">Comments</button></a>
<?php if ($_SESSION['user_id'] == $issue['per_id'] || $_SESSION['admin']): ?>
    <a href="update_issue.php?id=<?php echo $issue['id']; ?>"><button>Update</button></a>
    <button class="delete-btn" onclick="showDeleteModal(<?php echo $issue['id']; ?>)">Delete</button>
<?php endif; ?>


                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Modal for Deleting -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3>Are you sure you want to delete this issue?</h3>
                <a href="" id="deleteLink"><button class="btn-yes">Yes</button></a>
                <button class="btn-no" onclick="closeModal()">No</button>
            </div>
        </div>

        <script>
            function showDeleteModal(issueId) {
                // Show the modal
                document.getElementById("deleteModal").style.display = "block";
                // Set the URL for the Yes button to delete the issue
                document.getElementById("deleteLink").href = "issues_list.php?delete_id=" + issueId;
            }

            function closeModal() {
                // Close the modal
                document.getElementById("deleteModal").style.display = "none";
            }

            // Close modal if the user clicks anywhere outside of the modal
            window.onclick = function(event) {
                if (event.target == document.getElementById("deleteModal")) {
                    closeModal();
                }
            }
        </script>

    </body>
    </html>