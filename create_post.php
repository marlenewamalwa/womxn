<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$content = trim($_POST['content']);
$user_id = $_SESSION['user_id'];
$imagePath = null;

// Handle image upload
if (!empty($_FILES['image']['name'])) {
  $uploadDir = 'post_uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir);

  $filename = uniqid() . '_' . basename($_FILES['image']['name']);
  $targetPath = $uploadDir . $filename;

  if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    $imagePath = $targetPath;
  }
}

// Prepare and execute the insert
$stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $content, $imagePath);
$stmt->execute();
$stmt->close();

header('Location: feed.php');
exit;
?>


<!DOCTYPE html>
<html>
<head>
  <title>Create Post</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f3f3f3;
      margin: 0;
      padding: 0;
    }

    .post-container {
      max-width: 500px;
      margin: 50px auto;
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    .post-container h2 {
      margin-bottom: 20px;
      color: #333;
    }

    textarea {
      width: 100%;
      height: 100px;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      resize: vertical;
      margin-bottom: 15px;
    }

    input[type="file"] {
      margin-bottom: 15px;
    }

    button {
      padding: 12px 20px;
      background: #d33b79;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background: #b42e66;
    }
  </style>
</head>
<body>
  <div class="post-container">
    <h2>Create a Post</h2>
    <form action="create_post.php" method="POST" enctype="multipart/form-data">
  <textarea name="content" placeholder="What's on your mind?" required></textarea><br>
  <input type="file" name="image" accept="image/*"><br>
  <button type="submit">Post</button>
</form>

  </div>
</body>
</html>
