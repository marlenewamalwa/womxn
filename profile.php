<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT name, email, pronouns, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $pronouns, $profile_pic);
$stmt->fetch();
$stmt->close();

$profile_pic_url = $profile_pic && file_exists("uploads/$profile_pic") 
    ? "uploads/$profile_pic" 
    : "uploads/default.jpeg";

// Get user posts
$posts = [];
$post_stmt = $conn->prepare("SELECT id, content, image, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$post_stmt->bind_param("i", $user_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();
while ($row = $post_result->fetch_assoc()) {
    $posts[] = $row;
}
$post_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      padding: 2rem;
    }
    .profile-card {
      background: white;
      padding: 2rem;
      max-width: 500px;
      margin: 0 auto 2rem;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .profile-card img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 1rem;
    }
    .edit-btn {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 16px;
      background: #d63384;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }
    .post {
      background: white;
      max-width: 500px;
      margin: 1rem auto;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 0 5px rgba(0,0,0,0.05);
    }
    .post img {
      max-width: 100%;
      margin-top: 0.5rem;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="profile-card">
    <img src="<?= htmlspecialchars($profile_pic_url) ?>" alt="Profile Picture">
    <h2><?= htmlspecialchars($name) ?></h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong>Pronouns:</strong> <?= htmlspecialchars($pronouns) ?></p>
    <a class="edit-btn" href="edit_profile.php">Edit Profile</a>
    <p><a href="logout.php">Logout</a></p>
  </div>

  <h3 style="text-align:center;">Your Posts</h3>
  <?php foreach ($posts as $post): ?>
    <div class="post">
      <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
      <?php if ($post['image']): ?>
        <img src="<?= htmlspecialchars($post['image']) ?>" alt="Post Image">
      <?php endif; ?>
      <small>Posted on <?= htmlspecialchars($post['created_at']) ?></small>
    </div>
  <?php endforeach; ?>
</body>
</html>
