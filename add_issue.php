<?php
session_start();
require(__DIR__ . '/database/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $priority = $_POST['priority'];
    $open_date = $_POST['open_date'];
    $close_date = $_POST['close_date'];
    
    // Handle file upload
    $pdf_path = ''; // Default empty string instead of null
    if (isset($_FILES['pdf_attachment']) && $_FILES['pdf_attachment']['error'] === UPLOAD_ERR_OK) {
        $file_name = uniqid() . '_' . basename($_FILES['pdf_attachment']['name']);
        $target_path = $upload_dir . $file_name;
        
        // Verify file is PDF
        $file_type = $_FILES['pdf_attachment']['type'];
        if ($file_type == 'application/pdf') {
            if (move_uploaded_file($_FILES['pdf_attachment']['tmp_name'], $target_path)) {
                $pdf_path = $target_path;
            } else {
                $error_message = "Failed to upload file.";
            }
        } else {
            $error_message = "Only PDF files are allowed.";
        }
    }

    try {
        $conn = Database::connect();
        // Modified to handle cases where pdf_attachment might be empty
        $stmt = $conn->prepare("INSERT INTO iss_issues 
                              (short_description, long_description, priority, open_date, close_date, pdf_attachment) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $short_description, 
            $long_description, 
            $priority, 
            $open_date, 
            $close_date, 
            $pdf_path ? $pdf_path : '' // Send empty string if no file
        ]);

        header("Location: issues_list.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Issue - DSR</title>
    <style>
        /* Style the form */
        label, input, textarea {
            margin-bottom: 10px;
            padding: 5px;
            width: 100%;
            max-width: 500px;
            display: block;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <h2>Add New Issue</h2>

    <?php
    // Display error message if there was an issue inserting the data
    if (isset($error_message)) {
        echo "<p style='color: red;'>$error_message</p>";
    }
    ?>

   <!-- Add Issue Form -->
<form method="POST" action="add_issue.php" enctype="multipart/form-data">
    <label for="short_description">Short Description:</label>
    <input type="text" id="short_description" name="short_description" required>

    <label for="long_description">Long Description:</label>
    <textarea id="long_description" name="long_description" required></textarea>

    <label for="priority">Priority:</label>
    <input type="text" id="priority" name="priority" required>

    <label for="open_date">Open Date:</label>
    <input type="date" id="open_date" name="open_date" required>

    <label for="close_date">Close Date:</label>
    <input type="date" id="close_date" name="close_date">

    <label for="pdf_attachment">PDF Attachment:</label>
    <input type="file" id="pdf_attachment" name="pdf_attachment" class="form_control mb-2" accept="application/pdf">

    <button type="submit">Add Issue</button>
</form>

    <br>
    <a href="issues_list.php"><button>Back to Issues List</button></a>

</body>
</html>
