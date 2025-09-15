<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$content = $_POST['content'] ?? '';
$imagePath = null;
$videoPath = null;

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $imageName = time() . '_' . basename($_FILES['image']['name']);
    $targetImage = "uploads/$imageName";
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetImage)) {
        $imagePath = $targetImage;
    }
}

// Handle video upload
if (isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
    $videoName = time() . '_' . basename($_FILES['video']['name']);
    $targetVideo = "uploads/$videoName";
    if (move_uploaded_file($_FILES['video']['tmp_name'], $targetVideo)) {
        $videoPath = $targetVideo;
    }
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, video_path, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("isss", $userId, $content, $imagePath, $videoPath);
$stmt->execute();
$stmt->close();

// Redirect back to feed
header('Location: feed.php');
exit;
