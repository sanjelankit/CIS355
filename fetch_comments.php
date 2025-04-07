<?php
require '../database/database.php';

$iss_id = $_GET['issue_id'];

$conn = Database::connect();
$stmt = $conn->prepare("
    SELECT c.short_comment, c.long_comment, c.posted_date, p.name AS person_name
    FROM iss_comments c
    JOIN iss_persons p ON c.per_id = p.id
    WHERE c.iss_id = ?
    ORDER BY c.posted_date DESC
");
$stmt->execute([$iss_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($comments);
?>
