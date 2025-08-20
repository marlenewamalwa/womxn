<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$defaultPic = 'uploads/default.jpeg';
$pic = $isLoggedIn && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : $defaultPic;
?>

<nav class="sidebar" id="sidebar">
  <button class="sidebar-toggle">☰</button>

  
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
    <li><a href="feed.php">Feed</a></li>
    <li><a href="community.php">Community</a></li>
    <li><a href="events.php">Events</a></li>
    <li><a href="exchange.php">Opportunities</a></li>
  </ul>
  <script>
const toggleBtn = document.querySelector('.sidebar-toggle');
const sidebar = document.querySelector('.sidebar');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});


</script>
</nav>

