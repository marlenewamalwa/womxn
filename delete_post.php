<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("No post selected.");

// Fetch post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) die("Post not found or not yours.");

// Delete on confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Optional: delete uploaded files too
    if ($post['image'] && file_exists($post['image'])) unlink($post['image']);
    if ($post['video_path'] && file_exists($post['video_path'])) unlink($post['video_path']);

    $stmt = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();

    header("Location: feed.php");
    exit;
}
?>

<h2>Delete Post</h2>
<p>Are you sure you want to delete this post? This action cannot be undone.</p>

<div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
    <?php if ($post['image']): ?>
        <img src="<?= $post['image'] ?>" width="150"><br>
    <?php endif; ?>
    <?php if ($post['video_path']): ?>
        <video src="<?= $post['video_path'] ?>" width="200" controls></video>
    <?php endif; ?>
</div>

<form method="POST">
    <button type="submit" style="background:red;color:#fff;padding:8px 12px;border:none;">Yes, Delete</button>
    <a href="feed.php" style="margin-left:10px;">Cancel</a>
</form>
