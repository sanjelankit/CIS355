<?php
session_start();
require(__DIR__ . '/database/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$issue_id = $_GET['issue_id'] ?? null;
if (!$issue_id) {
    header("Location: issues_list.php");
    exit();
}

// Fetch issue details
try {
    $conn = Database::connect();
    $stmt = $conn->prepare("SELECT * FROM iss_issues WHERE id = ?");
    $stmt->execute([$issue_id]);
    $issue = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$issue) {
        header("Location: issues_list.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error fetching issue: " . $e->getMessage());
}

// Handle comment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_comment'])) {
        // Add new comment
        $comment = trim($_POST['comment']);
        if (!empty($comment)) {
            try {
                $short_comment = substr($comment, 0, 255);
                $stmt = $conn->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $issue_id, $short_comment, $comment]);
                header("Location: issue_comments.php?issue_id=$issue_id");
                exit();
            } catch (PDOException $e) {
                $error = "Error adding comment: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['update_comment'])) {
        // Update existing comment
        $comment_id = $_POST['comment_id'];
        $comment = trim($_POST['comment']);
        if (!empty($comment)) {
            try {
                $short_comment = substr($comment, 0, 255);
                $stmt = $conn->prepare("UPDATE iss_comments SET short_comment = ?, long_comment = ? WHERE id = ? AND per_id = ?");
                $stmt->execute([$short_comment, $comment, $comment_id, $_SESSION['user_id']]);
                header("Location: issue_comments.php?issue_id=$issue_id");
                exit();
            } catch (PDOException $e) {
                $error = "Error updating comment: " . $e->getMessage();
            }
        }
    }
} elseif (isset($_GET['delete_comment'])) {
    // Delete comment (admin or comment creator)
    $comment_id = $_GET['delete_comment'];
    try {
        if ($_SESSION['admin']) {
            $stmt = $conn->prepare("DELETE FROM iss_comments WHERE id = ?");
            $stmt->execute([$comment_id]);
        } else {
            $stmt = $conn->prepare("DELETE FROM iss_comments WHERE id = ? AND per_id = ?");
            $stmt->execute([$comment_id, $_SESSION['user_id']]);
        }
        header("Location: issue_comments.php?issue_id=$issue_id");
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting comment: " . $e->getMessage();
    }
}


// Fetch comments for this issue
try {
    $stmt = $conn->prepare("
     SELECT c.*, CONCAT(p.fname, ' ', p.lname) AS author 
FROM iss_comments c 
JOIN iss_persons p ON c.per_id = p.id 
WHERE c.iss_id = ? 
ORDER BY c.posted_date DESC

    ");
    $stmt->execute([$issue_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching comments: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments for Issue #<?= $issue_id ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .issue-info { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .comments-list { margin-top: 30px; }
        .comment { border-left: 3px solid #3498db; padding: 10px 15px; margin-bottom: 15px; background: #f9f9f9; border-radius: 4px; }
        .comment-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .comment-author { font-weight: bold; }
        .comment-date { color: #666; }
        .comment-actions a { margin-right: 10px; color: #17a2b8; text-decoration: none; }
        .comment-actions a:hover { text-decoration: underline; }
        .comment-form { margin-top: 30px; }
        textarea { width: 100%; height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .error { color: red; }
        button {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
            background-color: #17a2b8;
            color: white;
            border-radius: 4px;
        }
        button:hover {
            background-color: #138496;
        }
        .back-btn {
            background-color: #6c757d;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="issues_list.php" class="back-btn"><button>Back to Issues List</button></a>
        <h1>Comments for Issue #<?= htmlspecialchars($issue_id) ?></h1>
        
        <div class="issue-info">
            <h2><?= htmlspecialchars($issue['short_description']) ?></h2>
            <p>Priority: <?= htmlspecialchars($issue['priority']) ?> | 
               Opened: <?= htmlspecialchars($issue['open_date']) ?> | 
               <?= $issue['close_date'] ? 'Closed: ' . htmlspecialchars($issue['close_date']) : 'Status: Open' ?>
            </p>
        </div>

        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <!-- Add Comment Form -->
        <div class="comment-form">
            <h3>Add New Comment</h3>
            <form method="POST">
                <textarea name="comment" placeholder="Enter your comment..." required></textarea>
                <input type="hidden" name="add_comment" value="1">
                <button type="submit">Submit Comment</button>
            </form>
        </div>

        <!-- Comments List -->
        <div class="comments-list">
            <h3>Comments (<?= count($comments) ?>)</h3>
            
            <?php if (empty($comments)): ?>
                <p>No comments yet.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment" id="comment-<?= $comment['id'] ?>">
                        <div class="comment-header">
                            <span class="comment-author"><?= htmlspecialchars($comment['author']) ?></span>
                            <span class="comment-date"><?= date('M j, Y H:i', strtotime($comment['posted_date'])) ?></span>
                        </div>
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($comment['long_comment'])) ?>
                        </div>
                        <div class="comment-actions">
                            <?php if ($comment['per_id'] == $_SESSION['user_id']): ?>
                                <a href="#" onclick="editComment(<?= $comment['id'] ?>, `<?= str_replace('`', '\`', $comment['long_comment']) ?>`)">Edit</a>
                                <a href="issue_comments.php?issue_id=<?= $issue_id ?>&delete_comment=<?= $comment['id'] ?>" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function editComment(commentId, currentText) {
            const commentDiv = document.getElementById(`comment-${commentId}`);
            
            // Create edit form
            const editForm = document.createElement('form');
            editForm.method = 'POST';
            editForm.innerHTML = `
                <textarea name="comment" style="width:100%; height:100px;">${currentText}</textarea>
                <input type="hidden" name="comment_id" value="${commentId}">
                <input type="hidden" name="update_comment" value="1">
                <button type="submit">Update</button>
                <button type="button" onclick="cancelEdit(${commentId}, \`${currentText}\`)">Cancel</button>
            `;
            
            // Replace comment content with edit form
            commentDiv.querySelector('.comment-text').innerHTML = '';
            commentDiv.querySelector('.comment-text').appendChild(editForm);
        }
        
        function cancelEdit(commentId, originalText) {
            const commentDiv = document.getElementById(`comment-${commentId}`);
            commentDiv.querySelector('.comment-text').innerHTML = originalText.replace(/\n/g, '<br>');
        }
    </script>
</body>
</html>