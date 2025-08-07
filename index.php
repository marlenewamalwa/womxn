<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$defaultPic = 'uploads/default.jpeg';
$pic = $isLoggedIn && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : $defaultPic;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WOMXN | Queer Platform</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet" />
  <style>
    /* Global styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

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

/* Sidebar */
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
  margin-bottom: 20px;
}

.nav-profile-pic {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  margin: 10px auto;
}
.user-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem 0;
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


/* Main content */
main {
  margin-left: 240px;
  padding: 2rem;
  flex: 1;
}

.section {
  margin-bottom: 4rem;
}

h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

p {
  font-size: 1.1rem;
  line-height: 1.7;
}

ul.mission-list {
  list-style: none;
  padding-left: 0;
}

ul.mission-list li {
  margin-bottom: 0.8rem;
  font-size: 1.1rem;
}

/* Buttons */
.btn {
  display: inline-block;
  background-color: #872657;
  color: white;
  padding: 0.75rem 1.5rem;
  text-decoration: none;
  border-radius: 8px;
  font-weight: bold;
  margin-top: 1.5rem;
  transition: transform 0.2s ease, background-color 0.3s ease;
}

.btn:hover {
  background-color: #6b1d45;
  transform: translateY(-2px);
}

/* Hero */
.hero {
  background-color: #ffe6f0;
  padding: 3rem 2rem;
  border-radius: 12px;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 800;
  color: #3a0b2d;
  margin-bottom: 1rem;
}

/* CTA */
.cta {
  background-color: #fff0f7;
  padding: 2.5rem;
  border-radius: 10px;
  text-align: center;
}

/* Footer */
footer {
  text-align: center;
  font-size: 0.9rem;
  color: #555;
  padding: 2rem 0 0;
}

  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar Navigation -->
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
      <section id="hero" class="section hero">
        <h2 class="hero-title">Welcome to WOMXN 🌈</h2>
        <p>A safe space for queer women in Kenya to connect, share, and thrive.</p>
        <a href="#join" class="btn">Join the Movement</a>
      </section>

      <hr />

      <section id="about" class="section">
        <h2>About Us </h2>
        <p>WOMXN is a digital community built by and for queer women across Kenya. Whether you’re out, questioning, or exploring, this platform is your space.</p>
      </section>

      <hr />

      <section id="mission" class="section">
        <h2>Our Mission </h2>
        <ul class="mission-list">
          <li>🏳️‍🌈 Celebrate and center queer Kenyan voices</li>
          <li>🗓️ Promote safe meetups, events, and discussions</li>
          <li>🎤 Share real stories and amplify queer experiences</li>
        </ul>
      </section>

      <hr />

      <section id="join" class="section cta">
        <h2>Be Part of the Movement 💕</h2>
        <p>We’re building something powerful together. Sign up to stay updated on upcoming events, new content, and how to get involved.</p>
        <a href="#" class="btn">Sign Up</a>
      </section>

      <footer>
        <p>Made with love in Kenya 🇰🇪🏳️‍🌈 | © 2025 WOMXN</p>
      </footer>
    </main>
  </div>
</body>
</html>
