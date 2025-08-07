<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email, pronouns, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $pronouns, $profile_pic);
$stmt->fetch();
$stmt->close();

// Use default if no image
$profile_pic_url = $profile_pic && file_exists("uploads/$profile_pic") 
    ? "uploads/$profile_pic" 
    : "uploads/default.jpeg";
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
      max-width: 400px;
      margin: 0 auto;
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
  </style>
</head>
<body>
  <div class="profile-card">
    <img src="<?= htmlspecialchars($profile_pic_url) ?>" alt="Profile Picture">
    <h2><?= htmlspecialchars($name) ?></h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong>Pronouns:</strong> <?= htmlspecialchars($pronouns) ?></p>
    <p><a href="logout.php">Logout</a></p>
  </div>
</body>
</html>
