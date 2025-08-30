<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Post not found.");

// Fetch post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) die("Post not found or not yours.");

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $content = $_POST['content'] ?? $post['content'];
    $imagePath = $post['image'];
    $videoPath = $post['video_path'];

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetImage = "uploads/$imageName";
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetImage)) {
            $imagePath = $targetImage;
        }
    }

    // Handle new video upload
    if (isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
        $videoName = time() . '_' . basename($_FILES['video']['name']);
        $targetVideo = "uploads/$videoName";
        if (move_uploaded_file($_FILES['video']['tmp_name'], $targetVideo)) {
            $videoPath = $targetVideo;
        }
    }

    // Update in database
    $stmt = $conn->prepare("UPDATE posts SET content=?, image=?, video_path=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sssii", $content, $imagePath, $videoPath, $id, $_SESSION['user_id']);
    $stmt->execute();

    header("Location: feed.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
     <style>
        body {
        background: #f0f2f5;
        font-family: 'Poppins', sans-serif;
        color: #333;
        margin: 0; padding: 0;
        }
        form {
    max-width: 500px;
    margin: 50px auto;
    padding: 25px;
    background-color: #f8f8f8;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
    }
    form h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
    }
    form textarea {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
    }
    form textarea:focus {
    border-color: #007bff;
    outline: none;
    }
    form button {
    background: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    }
    form button:hover {
    background: #0056b3;
    }
    form a {
        margin-left: 15px;
        color: #555;
        text-decoration: none;
        }
    form a:hover { text-decoration: underline; }
    </style>

<h2>Edit Post</h2>
<form method="POST" enctype="multipart/form-data">
    <textarea name="content" rows="4"><?= htmlspecialchars($post['content']) ?></textarea><br>
    
    <?php if ($post['image']): ?>
        <p>Current Image: <img src="<?= $post['image'] ?>" width="100"></p>
    <?php endif; ?>
    <input type="file" name="image" accept="image/*"><br><br>
    
    <?php if ($post['video_path']): ?>
        <p>Current Video: <video src="<?= $post['video_path'] ?>" width="200" controls></video></p>
    <?php endif; ?>
    <input type="file" name="video" accept="video/*"><br><br>
    
    <button type="submit">Update Post</button>
    <a href="feed.php">Cancel</a>
</form>
