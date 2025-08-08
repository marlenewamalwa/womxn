<?php
session_start();
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$pic = 'uploads/default.jpeg';

// Get user profile pic if logged in
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($profilePic);
    if ($stmt->fetch()) {
        $pic = $profilePic ?: $pic;
    }
    $stmt->close();
}

// Fetch posts with user info
$sql = "SELECT posts.*, users.name, users.profile_pic 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC";
$result = $conn->query($sql);

$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Feed</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      display: flex;
      background-color: #fffafc;
      color: #2b2b2b;
      margin: 0;
    }
    .container {
      display: flex;
      width: 100%;
    }
    .sidebar {
      width: 220px;
      background-color: #3a0b2d;
      color: white;
      padding: 2rem 1rem;
      height: 100vh;
      position: fixed;
    }
    .sidebar h1 {
      font-size: 1.8rem;
      margin-bottom: 2rem;
    }
    .sidebar ul {
      list-style: none;
      padding: 0;
    }
    .sidebar ul li {
      margin: 1rem 0;
    }
    .sidebar ul li a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      display: block;
      padding: 0.5rem;
      border-radius: 5px;
      transition: background 0.3s ease;
    }
    .sidebar ul li a:hover {
      background-color: #872657;
    }
    .gradient-text {
      font-size: 3rem;
      font-weight: bold;
      background: linear-gradient(90deg, #d52d00, #ff9a56, #ffffff, #d362a4, #a30262);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .user-info {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 2rem;
    }
    .nav-profile-pic {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 0.5rem;
    }
    .username-link {
      color: white;
      text-decoration: none;
      margin-bottom: 0.3rem;
    }
    .username-link p {
      margin: 0;
      font-weight: bold;
    }
    .logout-link,
    .login-link {
      color: white;
      font-size: 0.9rem;
      text-decoration: underline;
    }
    main {
      margin-left: 240px;
      padding: 2rem;
      flex: 1;
    }
    .post-form, .post {
      background: white;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .post img {
      max-width: 80%;
      border-radius: 8px;
      margin-top: 10px;
    }
    .post .meta {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }
    .post .meta img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
    }
    textarea {
      width: 100%;
      padding: 10px;
      font-size: 15px;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <nav class="sidebar">
      <h1 class="gradient-text">WOMXN</h1>
      <div class="user-info">
        <img src="<?= htmlspecialchars($pic) ?>" alt="Profile" class="nav-profile-pic">
        <?php if ($isLoggedIn): ?>
          <a href="profile.php" class="username-link">
            <p><?= htmlspecialchars($_SESSION['user_name']) ?></p>
          </a>
          <a href="logout.php" class="logout-link">Logout</a>
        <?php else: ?>
          <a href="login.php" class="login-link">Login</a>
        <?php endif; ?>
      </div>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="community.php">Community</a></li>
        <li><a href="events.php">Events</a></li>
      </ul>
    </nav>

    <!-- Main Content -->
    <main>
      <?php if ($isLoggedIn): ?>
        <div class="post-form">
          <form action="create_post.php" method="POST" enctype="multipart/form-data">
            <textarea name="content" placeholder="What's on your mind?" required></textarea><br>
            <input type="file" name="image" accept="image/*"><br><br>
            <button type="submit">Post</button>
          </form>
        </div>
      <?php endif; ?>

      <?php foreach ($posts as $post): ?>
        <div class="post">
          <div class="meta">
            <img src="<?= htmlspecialchars($post['profile_pic'] ?? 'uploads/default.jpeg') ?>" alt="user">
            <strong><?= htmlspecialchars($post['name']) ?></strong>
            <span style="color: gray; font-size: 12px;"><?= htmlspecialchars($post['created_at']) ?></span>
          </div>
          <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
          <?php if (!empty($post['image'])): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Post Image">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </main>
  </div>
</body>
</html>
