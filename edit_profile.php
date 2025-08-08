<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, pronouns, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $pronouns, $profile_pic);
$stmt->fetch();
$stmt->close();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_name = trim($_POST['name']);
  $new_email = trim($_POST['email']);
  $new_pronouns = trim($_POST['pronouns']);
  $new_profile_pic = $profile_pic; // default to current

  // Handle profile pic upload
  if (!empty($_FILES['profile_pic']['name'])) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir);

    $file_name = uniqid() . '_' . basename($_FILES['profile_pic']['name']);
    $target_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
      $new_profile_pic = $file_name;
    }
  }

  $update = $conn->prepare("UPDATE users SET name = ?, email = ?, pronouns = ?, profile_pic = ? WHERE id = ?");
  $update->bind_param("ssssi", $new_name, $new_email, $new_pronouns, $new_profile_pic, $user_id);
  $update->execute();
  $update->close();

  header("Location: profile.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f8f8;
      padding: 2rem;
    }
    .edit-form {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .edit-form h2 {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .edit-form label {
      display: block;
      margin-top: 1rem;
      font-weight: bold;
    }
    .edit-form input,
    .edit-form select {
      width: 100%;
      padding: 10px;
      margin-top: 0.5rem;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    .edit-form button {
      margin-top: 1.5rem;
      padding: 12px;
      background: #d33b79;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .edit-form button:hover {
      background: #b42e66;
    }
  </style>
</head>
<body>
  <form class="edit-form" action="edit_profile.php" method="POST" enctype="multipart/form-data">
    <h2>Edit Your Profile</h2>

    <label for="name">Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

    <label for="email">Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

    <label for="pronouns">Pronouns:</label>
    <select name="pronouns" required>
      <option value="">-- Select --</option>
      <option value="she/her" <?= $pronouns == 'she/her' ? 'selected' : '' ?>>She/Her</option>
      <option value="he/him" <?= $pronouns == 'he/him' ? 'selected' : '' ?>>He/Him</option>
      <option value="they/them" <?= $pronouns == 'they/them' ? 'selected' : '' ?>>They/Them</option>
      <option value="she/they" <?= $pronouns == 'she/they' ? 'selected' : '' ?>>She/They</option>
      <option value="he/they" <?= $pronouns == 'he/they' ? 'selected' : '' ?>>He/They</option>
      <option value="other" <?= $pronouns == 'other' ? 'selected' : '' ?>>Other</option>
    </select>

    <label for="profile_pic">Change Profile Picture:</label>
    <input type="file" name="profile_pic" accept="image/*">

    <button type="submit">Save Changes</button>
  </form>
</body>
</html>
