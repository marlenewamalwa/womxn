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
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet" />
  <style>
  * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  display: flex;
  background-color: #ffe6f0;
  color: #2b2b2b;
}

.container {
  display: flex;
  width: 100%;
}
 main {
      margin-left: 240px;
      padding: 2rem;
      flex: 1;
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
   <div class="container">
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>
  <main>
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


  <?php if (empty($posts)): ?>
    <p style="text-align:center;">You have not made any posts yet.</p>
  <?php endif; ?>

  <?php
// Fetch distinct chat partners (users who sent or received messages with logged in user)
$chat_partners = [];
$query = "
    SELECT DISTINCT u.id, u.name, u.profile_pic
    FROM users u
    INNER JOIN (
        SELECT sender_id AS user_id FROM messages WHERE receiver_id = ?
        UNION
        SELECT receiver_id AS user_id FROM messages WHERE sender_id = ?
    ) m ON u.id = m.user_id
    WHERE u.id != ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $chat_partners[] = $row;
}
$stmt->close();
?>

<h3 style="text-align:center; margin-top: 2rem;">Your Messages</h3>

<?php if (empty($chat_partners)): ?>
    <p style="text-align:center;">You have no messages yet.</p>
<?php else: ?>
    <div style="max-width: 500px; margin: 0 auto;">
        <?php foreach ($chat_partners as $partner): ?>
            <div style="display:flex; align-items:center; justify-content: space-between; background: white; padding: 10px; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.05);">
                <div style="display:flex; align-items:center; gap: 10px;">
     <?php 
$pic = (!empty($partner['profile_pic']) && file_exists("uploads/" . $partner['profile_pic'])) 
    ? "uploads/" . $partner['profile_pic'] 
    : "uploads/default.jpeg"; 
?>
<img src="<?= htmlspecialchars($pic) ?>" alt="Profile Pic" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">

                    <span><?= htmlspecialchars($partner['name']) ?></span>
                </div>
                <a href="chat.php?user=<?= $partner['id'] ?>" class="edit-btn" style="padding: 6px 12px; font-size: 0.9rem;">Message</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

  </main>
</body>
</html>
