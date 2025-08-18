<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$defaultPic = 'uploads/default.jpeg';
$pic = $isLoggedIn && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : $defaultPic;

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

// Get community stats (optional - you can create these tables if needed)
$userCount = 0;
$postCount = 0;
$eventCount = 0;

// Try to get stats if tables exist
$statsQueries = [
    "SELECT COUNT(*) as count FROM users" => 'userCount',
    "SELECT COUNT(*) as count FROM posts" => 'postCount', 
    "SELECT COUNT(*) as count FROM events" => 'eventCount'
];

foreach ($statsQueries as $query => $var) {
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $$var = $row['count'];
    }
}
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
  background-color: #ffe6f0;
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



/* Quick navigation */
.quick-nav {
  background: white;
  padding: 2rem;
  border-radius: 15px;
  margin: 3rem 0;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.quick-nav h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: #872657;
}

.nav-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.nav-card {
  padding: 1.5rem;
  border: 2px solid #f0f0f0;
  border-radius: 12px;
  text-align: center;
  transition: all 0.3s ease;
  text-decoration: none;
  color: #2b2b2b;
}

.nav-card:hover {
  border-color: #872657;
  background: #fff0f7;
  transform: translateY(-3px);
}

.nav-icon {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.nav-card h3 {
  color: #872657;
  margin-bottom: 0.5rem;
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
  border-radius: 25px;
  font-weight: bold;
  margin-top: 1.5rem;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.btn:hover {
  background-color: #6b1d45;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-secondary {
  background: transparent;
  color: #872657;
  border: 2px solid #872657;
}

.btn-secondary:hover {
  background: #872657;
  color: white;
}

/* Latest posts */
.latest-posts {
  max-width: 100%;
  margin: 40px auto;
  background: #fff;
  padding: 25px;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.latest-posts h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #872657;
}

.post {
  border-bottom: 1px solid #eee;
  padding: 20px 0;
  transition: all 0.3s ease;
}

.post:last-child {
  border-bottom: none;
}

.post:hover {
  background: #fff8fc;
  border-radius: 8px;
  padding: 20px 15px;
}

.post-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  border: 2px solid #872657;
}

.post-image {
  height: auto;
  max-width: 90%;
  margin-top: 10px;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.latest-events {
  max-width: 100%;
  padding: 25px;
  background: white;
  text-align: center;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.latest-events h2 {
  color: #872657;
}

.event-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.event-card {
  background: linear-gradient(135deg, #2c2c2c, #1a1a1a);
  color: #fff;
  display: flex;
  gap: 15px;
  padding: 20px;
  border-radius: 12px;
  align-items: flex-start;
  transition: transform 0.3s ease;
}

.event-card:hover {
  transform: translateY(-3px);
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

/* Newsletter signup */
.newsletter {
  background: linear-gradient(135deg, #872657, #c44569);
  color: white;
  padding: 3rem;
  border-radius: 15px;
  text-align: center;
  margin: 3rem 0;
}

.newsletter h2 {
  margin-bottom: 1rem;
}

.newsletter p {
  margin-bottom: 2rem;
  opacity: 0.9;
}

.newsletter-form {
  display: flex;
  justify-content: center;
  gap: 0;
  max-width: 500px;
  margin: 0 auto;
}

.newsletter-form input {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 25px 0 0 25px;
  outline: none;
  font-size: 1rem;
}

.newsletter-form button {
  padding: 12px 25px;
  background: white;
  color: #872657;
  border: none;
  border-radius: 0 25px 25px 0;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s ease;
}

.newsletter-form button:hover {
  background: #f0f0f0;
  transform: scale(1.05);
}

/* CTA */
.cta {
  background: linear-gradient(45deg, #fff0f7, #ffe6f0);
  padding: 3rem;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.cta h2 {
  color: #872657;
}

/* Footer */
footer {
  text-align: center;
  font-size: 0.9rem;
  color: #555;
  padding: 3rem 0 0;
  border-top: 1px solid #e0e0e0;
  margin-top: 3rem;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  main {
    margin-left: 0;
    padding: 1rem;
  }
  
  .hero h1 {
    font-size: 2.5rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  
  .latest-events {
    margin-left: 0;
  }
  
  .newsletter-form {
    flex-direction: column;
    gap: 1rem;
  }
  
  .newsletter-form input,
  .newsletter-form button {
    border-radius: 25px;
  }
}
</style>
</head>
<body>
  <div class="container">
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main>
   

      
 <!-- Call to Action -->
      <section id="join" class="section cta">
        <h2>Be Part of the Movement 💕</h2>
        <p>We're building something powerful together. Join our community to connect, share, and grow with fellow WOMXN and queer individuals across Kenya and beyond.</p>
        <a href="signup.php" class="btn">Join the Community</a>
        <a href="about.php" class="btn btn-secondary">Learn More</a>
      </section>
    

      <!-- Quick Navigation -->
      <section class="quick-nav">
        <h2>Quick Access</h2>
        <div class="nav-grid">
          <a href="feed.php" class="nav-card">
            <div class="nav-icon">💬</div>
            <h3>Community Feed</h3>
            <p>Join the conversation and share your story</p>
          </a>
          <a href="events.php" class="nav-card">
            <div class="nav-icon">📅</div>
            <h3>Events</h3>
            <p>Discover meetups, workshops, and celebrations</p>
          </a>
          <a href="#resources" class="nav-card">
            <div class="nav-icon">📚</div>
            <h3>Opportunities</h3>
            <p>Work with other queer folk</p>
          </a>
        </div>
      </section>

      <!-- Latest Events -->
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
          <p>No events yet. Stay tuned for exciting community gatherings! 🌟</p>
        <?php endif; ?>
        <a href="events.php" class="btn">View All Events</a>
      </section>

 <!-- Latest Posts -->
      <section class="latest-posts">
    <h2>💫 Latest Community Posts</h2>

    <?php if (empty($latestPosts)): ?>
        <p>No posts yet. Be the first to share your story! ✨</p>
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

    <a href="feed.php" class="btn">View Full Feed</a>
</section>

      <!-- Newsletter Signup -->
      <section class="newsletter">
        <h2>📬 Stay Connected</h2>
        <p>Get weekly updates on events, featured stories, and community highlights</p>
        <form class="newsletter-form" action="newsletter_signup.php" method="POST">
          <input type="email" name="email" placeholder="Enter your email address" required>
          <button type="submit">Subscribe</button>
        </form>
      </section>

           <footer>
        <p>Made with love in Kenya 🇰🇪🏳️‍🌈 | © 2025 WOMXN | 
        <a href="privacy.php" style="color: #872657;">Privacy Policy</a> | 
        <a href="terms.php" style="color: #872657;">Terms of Service</a></p>
      </footer>
    </main>
  </div>
</body>
</html>