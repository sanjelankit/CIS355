<?php
session_start();
require '../database/database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "User not logged in."]);
    exit();
}

$per_id = $_SESSION['user_id'];
$iss_id = $data['issue_id'];
$short_comment = trim(substr($data['comment'], 0, 255)); // First 255 chars
$long_comment = trim($data['comment']);

if (empty($long_comment)) {
    echo json_encode(["success" => false, "error" => "Comment cannot be empty."]);
    exit();
}

$conn = Database::connect();
$stmt = $conn->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment) VALUES (?, ?, ?, ?)");
$success = $stmt->execute([$per_id, $iss_id, $short_comment, $long_comment]);

echo json_encode(["success" => $success]);
?>
