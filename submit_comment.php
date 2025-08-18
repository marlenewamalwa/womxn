<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to homepage or login
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'] ?? null;
    $comment_text = trim($_POST['comment_text'] ?? '');

    // Basic validation
    if (!$post_id || empty($comment_text)) {
        $_SESSION['error'] = "Comment cannot be empty.";
        header('Location: feed.php'); // Redirect back (you can adjust)
        exit;
    }

    // Optional: limit comment length
    if (strlen($comment_text) > 1000) {
        $_SESSION['error'] = "Comment too long.";
        header('Location: feed.php');
        exit;
    }

    // Insert comment safely
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $comment_text);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: feed.php"); // Back to feed (adjust if needed)
        exit;
    } else {
        $_SESSION['error'] = "Failed to post comment.";
        header('Location: feed.php');
        exit;
    }
} else {
    header('Location: feed.php');
    exit;
}
?>
