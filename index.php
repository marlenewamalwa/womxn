<?php
session_start();
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$defaultPic = 'uploads/default.jpeg';
$pic = $isLoggedIn && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : $defaultPic;

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$searchResults = [];
$searching = false;

if (!empty($q)) {
    $searching = true;
    $sql = "SELECT posts.*, users.name, users.profile_pic 
            FROM posts 
            JOIN users ON posts.user_id = users.id
            WHERE posts.content LIKE '%$q%' 
            ORDER BY posts.created_at DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }
}

// Get latest posts
$sql = "SELECT posts.*, users.name, users.profile_pic 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC 
        LIMIT 5";
$result = $conn->query($sql);
$latestPosts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $latestPosts[] = $row;
    }
}

// Latest events
$eventQuery = "SELECT * FROM events ORDER BY event_date DESC LIMIT 3";
$eventResult = $conn->query($eventQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WOMXN | Queer Platform</title>
  <link rel="stylesheet" href="styles.css">
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
/* Main content */
main {
  margin-left: 240px;
  padding: 2rem;
  flex: 1;
}
.search-bar {
  display: flex;
  justify-content: center;
  margin: 20px 0;
}

.search-bar input {
  padding: 8px;
  width: 300px;
  border: 1px solid #ccc;
  border-radius: 5px 0 0 5px;
  outline: none;
}

.search-bar button {
  padding: 8px 15px;
  background: #872657;
  color: white;
  border: none;
  border-radius: 0 5px 5px 0;
  cursor: pointer;
}

.search-bar button:hover {
  background: #68212fff;
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
.latest-posts {
  max-width: 500px;
  margin: 40px auto;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

.latest-posts h2 {
  text-align: center;
  margin-bottom: 20px;
}

.post {
  border-bottom: 1px solid #eee;
  padding: 15px 0;
}

.post:last-child {
  border-bottom: none;
}

.post-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.post-image {
  max-width: 100%;
  margin-top: 10px;
  border-radius: 8px;
}
.latest-events {
  margin-left: 220px;
  padding: 20px;
}

.event-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.event-card {
  background: #222;
  color: #fff;
  display: flex;
  gap: 15px;
  padding: 15px;
  border-radius: 8px;
  align-items: flex-start;
}

.event-card img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
}

.event-details h3 {
  margin-top: 0;
  color: #ff4081;
}

.event-details a {
  color: #ffc107;
  text-decoration: underline;
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
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main>
  <form method="GET" action="" class="search-bar">
  <input type="text" name="q" placeholder="Search..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
  <button type="submit">Search</button>
    </form>


      <section id="hero" class="section hero">
        <h2 class="hero-title">Welcome to WOMXN 🌈</h2>
        <p>A safe space for queer women in Kenya to connect, share, and thrive.</p>
        <a href="#join" class="btn">Join the Movement</a>
      </section>

      <hr />

<section class="latest-posts">
  <?php if ($searching): ?>
    <h2>Search results for "<?= htmlspecialchars($q) ?>"</h2>
    <?php if (!empty($searchResults)): ?>
      <?php foreach ($searchResults as $post): ?>
        <div class="post">
          <div class="post-header">
            <img src="<?= htmlspecialchars($post['profile_pic']) ?>" alt="Profile" class="avatar">
            <strong><?= htmlspecialchars($post['name']) ?></strong>
          </div>
          <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
          <?php if (!empty($post['image'])): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post Image">
          <?php endif; ?>
          <small><?= date("F j, Y, g:i a", strtotime($post['created_at'])) ?></small>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No results found.</p>
    <?php endif; ?>
  <?php else: ?>
    <h2>Latest Posts</h2>
    <?php if (empty($latestPosts)): ?>
      <p>No posts yet.</p>
    <?php else: ?>
      <?php foreach ($latestPosts as $post): ?>
        <div class="post">
          <div class="post-header">
            <img src="<?= htmlspecialchars($post['profile_pic']) ?>" alt="Profile" class="avatar">
            <strong><?= htmlspecialchars($post['name']) ?></strong>
          </div>
          <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
          <?php if (!empty($post['image'])): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post Image">
          <?php endif; ?>
          <small><?= date("F j, Y, g:i a", strtotime($post['created_at'])) ?></small>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endif; ?>
</section>


      <hr />

<section class="latest-events">
  <h2>Upcoming Events</h2>

  <?php if ($eventResult && $eventResult->num_rows > 0): ?>
    <div class="event-list">
      <?php while($event = $eventResult->fetch_assoc()): ?>
        <div class="event-card">
          <img src="post_uploads/<?= htmlspecialchars($event['image']) ?>" alt="Event Image">
          <div class="event-details">
            <h3><?= htmlspecialchars($event['title']) ?></h3>
            <p><?= htmlspecialchars($event['description']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
            <?php if (!empty($event['ticket_link'])): ?>
              <p><a href="<?= htmlspecialchars($event['ticket_link']) ?>" target="_blank">Get Tickets</a></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>No events yet. Stay tuned!</p>
  <?php endif; ?>
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
