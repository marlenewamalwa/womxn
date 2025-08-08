<?php
session_start();
require_once 'db.php';

// Check login state
$isLoggedIn = isset($_SESSION['user_id']);
$pic = $isLoggedIn ? $_SESSION['profile_pic'] ?? 'uploads/default.jpeg' : 'uploads/default.jpeg';

// Fetch all users
$sql = "SELECT name, pronouns, profile_pic FROM users ORDER BY name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>WOMXN Community</title>
  <link rel="stylesheet" href="styles.css"> <!-- Optional external CSS -->
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      display: flex;
      background-color: #fffafc;
      color: #2b2b2b;
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
      letter-spacing: -1px;
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
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 20px;
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
      text-align: center;
    }

    .logout-link,
    .login-link {
      color: white;
      font-size: 0.9rem;
      text-decoration: underline;
    }

    .community-container {
      margin-left: 240px;
      padding: 2rem;
      flex: 1;
    }

    .member-card {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 15px;
      background-color: #222;
      padding: 15px;
      border-radius: 10px;
      color: #fff;
    }

    .member-card img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
    }

    .member-info p {
      margin: 3px 0;
    }

    .member-info .pronouns {
      font-size: 14px;
      color: #aaa;
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

  <!-- Main content -->
  <div class="community-container">
    <h2>WOMXN Community</h2>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="member-card">
          <img src="<?= htmlspecialchars($row['profile_pic'] ?: 'uploads/default.jpeg') ?>" alt="Profile">
          <div class="member-info">
            <p><strong><?= htmlspecialchars($row['name']) ?></strong></p>
            <p class="pronouns"><?= htmlspecialchars($row['pronouns']) ?></p>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No members yet.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
