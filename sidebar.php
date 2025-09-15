<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$defaultPic = 'uploads/default.jpeg';
$pic = $isLoggedIn && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : $defaultPic;
?>
<!-- sidebar.php -->

<!-- Burger Toggle -->
<button class="sidebar-toggle">☰</button>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">

<script>
const toggleBtn = document.querySelector('.sidebar-toggle');
const sidebar = document.querySelector('.sidebar');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});
</script>

  <div class="user-info">
   <img src="<?= htmlspecialchars(getProfilePic()) ?>" 
     alt="Profile Picture" class="profile-pic">


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

  <hr class="sidebar-separator" >
   <p class="sidebar-text">CONNECTING QUEER WOMEN IN KENYA</p>
    </hr>

</nav>

<!-- End of sidebar.php -->

