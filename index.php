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
// Sample data for keyword search
$items = [
    "Lesbian fashion tips",
    "Queer women events",
    "Rainbow accessories",
    "WOMXN blog updates",
    "LGBTQ+ stories",
];

// Handle search
$searchResults = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $query = strtolower($_GET['q']);
    foreach ($items as $item) {
        if (strpos(strtolower($item), $query) !== false) {
            $searchResults[] = $item;
        }
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    /* Global styles with lesbian flag colors */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

:root {
  --lesbian-orange: #D62900;
  --lesbian-light-orange: #FF9B56;
  --lesbian-white: #ffe6f0;
  --lesbian-light-pink: #D462A6;
  --lesbian-dark-pink: #A50062;
  --text-dark: #1a1a1a;
  --text-gray: #6b7280;
  --bg-light: #fef7ff;
  --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);     
  --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.12);
  --border-radius: 16px;
}

body {
  font-family: 'Inter', sans-serif;
  display: flex;
  background: var(--lesbian-white);
  color: var(--text-dark);
  line-height: 1.6;
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
  min-height: 100vh;
}



.logo {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--lesbian-dark-pink);
}

.topbar {
  display: flex;
  justify-content: center;
  padding: 20px;
  background: #fde6ef; /* soft pink background */
  margin-bottom: 20px;
}



.search input[type="text"] {
  border: 2px solid #b03a6f; /* pink border */
    border-radius: 25px;
  padding: 12px 18px;
  font-size: 16px;
  outline: none;
  width: 300px;
  transition: all 0.3s ease;
}

.search input[type="text"]:focus {
  border-color: #a0185c;
  box-shadow: 0 0 6px rgba(160, 24, 92, 0.4);
}

.search-btn {
  padding: 12px 25px;
    background: linear-gradient(135deg, #872657 0%, #b8336a 100%);
    color: white;
    border: none;
    border-radius: 25px;
    margin-left: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.search-btn:hover {
  background: #8d2a59;
}

/* Search results */
.results {
    margin: 1rem 0 2rem;
}

.results ul {
    list-style: none;
    padding: 0;
    display: grid;
    gap: 0.75rem;
}

.results li {
    background: white;
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-soft);
    border-left: 4px solid var(--lesbian-light-orange);
    transition: all 0.3s ease;
}

.results li:hover {
    transform: translateX(8px);
    box-shadow: var(--shadow-medium);
}

/* Hero Section */
.hero {
    background: white;
    border-radius: 24px;
    padding: 4rem;
    margin-bottom: 4rem;
    text-align: center;
    box-shadow: var(--shadow-soft);
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, var(--lesbian-orange), var(--lesbian-light-orange), var(--lesbian-white), var(--lesbian-light-pink), var(--lesbian-dark-pink));
}

.hero h1 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 3.5rem;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.hero p {
    font-size: 1.25rem;
    color: var(--text-gray);
    max-width: 600px;
    margin: 0 auto 2rem;
}

/* Call to Action */
.cta {
    background: white;
    padding: 4rem;
    border-radius: 24px;
    text-align: center;
    box-shadow: var(--shadow-soft);
    margin-bottom: 4rem;
    position: relative;
}

.cta h2 {
    font-family: 'Space Grotesk', sans-serif;
    color: var(--lesbian-dark-pink);
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.cta p {
    font-size: 1.1rem;
    color: var(--text-gray);
    margin-bottom: 2.5rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

/* Buttons */
.btn {
    display: inline-block;
    background-color: var(--lesbian-light-pink);
    color: white;
    padding: 1rem 2rem;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    margin: 0.5rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background-color: var(--lesbian-dark-pink);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(165, 0, 98, 0.3);
}

.btn-secondary {
    background: transparent;
    color: var(--lesbian-dark-pink);
    border: 2px solid var(--lesbian-light-pink);
}

.btn-secondary:hover {
    background: var(--lesbian-light-pink);
    color: white;
    border-color: var(--lesbian-light-pink);
}

/* Quick Navigation */
.quick-nav {
    margin-bottom: 4rem;
}

.quick-nav h2 {
    font-family: 'Space Grotesk', sans-serif;
    text-align: center;
    margin-bottom: 3rem;
    color: var(--lesbian-dark-pink);
    font-size: 2.2rem;
    font-weight: 700;
}

.nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
}

.nav-card {
    background: white;
    padding: 2.5rem;
    border-radius: 20px;
    text-align: center;
    transition: all 0.4s ease;
    text-decoration: none;
    color: var(--text-dark);
    box-shadow: var(--shadow-soft);
    position: relative;
    overflow: hidden;
}

.nav-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--lesbian-orange);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.nav-card:hover::before {
    transform: scaleX(1);
}

.nav-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-medium);
}

.nav-icon {
    font-size: 3rem;
    margin-bottom: 1.5rem;
    filter: grayscale(0.3);
    transition: filter 0.3s ease;
}

.nav-card:hover .nav-icon {
    filter: grayscale(0);
}

.nav-card h3 {
    color: var(--lesbian-dark-pink);
    margin-bottom: 1rem;
    font-size: 1.4rem;
    font-weight: 600;
}

.nav-card p {
    color: var(--text-gray);
    font-size: 1rem;
}

/* Latest Events */
.latest-events {
    background: white;
    padding: 3rem;
    border-radius: 24px;
    box-shadow: var(--shadow-soft);
    margin-bottom: 4rem;
}

.latest-events h2 {
    font-family: 'Space Grotesk', sans-serif;
    color: var(--lesbian-dark-pink);
    font-size: 2.2rem;
    text-align: center;
    margin-bottom: 3rem;
    font-weight: 700;
}

.event-list {
    display: grid;
    gap: 2rem;
}

.event-card {
    background: var(--bg-light);
    display: flex;
    gap: 2rem;
    padding: 2rem;
    border-radius: 20px;
    align-items: flex-start;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.event-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-medium);
    border-color: var(--lesbian-light-orange);
}

.event-card img {
    width: 160px;
    height: 160px;
    object-fit: cover;
    border-radius: 16px;
    flex-shrink: 0;
}

.event-details h3 {
    margin-top: 0;
    color: var(--lesbian-dark-pink);
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.event-details p {
    color: var(--text-gray);
    margin-bottom: 0.5rem;
}

.event-details a {
    color: var(--lesbian-orange);
    text-decoration: none;
    font-weight: 500;
}

.event-details a:hover {
    text-decoration: underline;
}

/* Latest Posts */
.latest-posts {
    background: white;
    padding: 3rem;
    border-radius: 24px;
    box-shadow: var(--shadow-soft);
    margin-bottom: 4rem;
}

.latest-posts h2 {
    font-family: 'Space Grotesk', sans-serif;
    text-align: center;
    margin-bottom: 3rem;
    color: var(--lesbian-dark-pink);
    font-size: 2.2rem;
    font-weight: 700;
}

.post {
    border-bottom: 2px solid var(--bg-light);
    padding: 2rem 0;
    transition: all 0.3s ease;
}

.post:last-child {
    border-bottom: none;
}

.post:hover {
    background: var(--bg-light);
    border-radius: 16px;
    padding: 2rem 1.5rem;
    margin: 0 -1.5rem;
}

.post-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 3px solid var(--lesbian-light-pink);
    object-fit: cover;
}

.post-header strong {
    color: var(--lesbian-dark-pink);
    font-weight: 600;
}

.post p {
    color: var(--text-dark);
    margin-bottom: 1rem;
    font-size: 1.05rem;
}

.post-image {
    width: 100%;
    max-width: 500px;
    height: auto;
    margin: 1rem 0;
    border-radius: 16px;
    box-shadow: var(--shadow-soft);
}

.post small {
    color: var(--text-gray);
    font-size: 0.9rem;
}

/* Newsletter */
.newsletter {
    background: var(--lesbian-dark-pink);
    color: white;
    padding: 4rem;
    border-radius: 24px;
    text-align: center;
    margin-bottom: 4rem;
    position: relative;
    overflow: hidden;
}

.newsletter::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: url('https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=60') center/cover;
    opacity: 0.1;
    z-index: 0;
}

.newsletter > * {
    position: relative;
    z-index: 1;
}

.newsletter h2 {
    font-family: 'Space Grotesk', sans-serif;
    margin-bottom: 1rem;
    font-size: 2.2rem;
}

.newsletter p {
    margin-bottom: 2.5rem;
    opacity: 0.9;
    font-size: 1.1rem;
}

.newsletter-form {
    display: flex;
    justify-content: center;
    gap: 0;
    max-width: 500px;
    margin: 0 auto;
    background: white;
    border-radius: 50px;
    padding: 0.5rem;
    box-shadow: var(--shadow-medium);
}

.newsletter-form input {
    flex: 1;
    padding: 1rem 1.5rem;
    border: none;
    border-radius: 50px;
    outline: none;
    font-size: 1rem;
}

.newsletter-form button {
    padding: 1rem 2rem;
    background: var(--lesbian-orange);
    color: white;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.newsletter-form button:hover {
    background: var(--lesbian-light-orange);
    transform: scale(1.02);
}

/* Featured Images */
.featured-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 20px;
    box-shadow: var(--shadow-soft);
    margin: 2rem 0;
}

/* Community Stats */
.community-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin: 3rem 0;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    text-align: center;
    box-shadow: var(--shadow-soft);
    border-top: 4px solid var(--lesbian-orange);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--lesbian-dark-pink);
    display: block;
}

.stat-label {
    color: var(--text-gray);
    font-weight: 500;
    margin-top: 0.5rem;
}

/* Footer */
footer {
    text-align: center;
    font-size: 0.9rem;
    color: var(--text-gray);
    padding: 3rem 0;
    border-top: 2px solid var(--bg-light);
    margin-top: 3rem;
    background: white;
    border-radius: 24px 24px 0 0;
}

footer a {
    color: var(--lesbian-dark-pink);
    text-decoration: none;
    font-weight: 500;
}

footer a:hover {
    text-decoration: underline;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    main {
        margin-left: 0;
        padding: 1rem;
    }
    
    .hero {
        padding: 2.5rem 1.5rem;
    }
    
    .hero h1 {
        font-size: 2.5rem;
    }
    
    .nav-grid {
        grid-template-columns: 1fr;
    }
    
    .event-card {
        flex-direction: column;
        text-align: center;
    }
    
    .event-card img {
        width: 100%;
        height: 200px;
        align-self: center;
    }
    
    .newsletter-form {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }
    
    .newsletter-form input,
    .newsletter-form button {
        border-radius: 50px;
    }
    
    .search {
        width: 100%;
    }
    
    .search input {
        width: 100%;
    }
    
    .topbar {
        flex-direction: column;
        gap: 1rem;
    }
}

/* Accessibility improvements */
.btn:focus,
.search-btn:focus,
.newsletter-form button:focus {
    outline: 2px solid var(--lesbian-orange);
    outline-offset: 2px;
}

.nav-card:focus {
    outline: 2px solid var(--lesbian-orange);
    outline-offset: 4px;
}
</style>
</head>
<body>
  <div class="container">
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main>
      <!-- Topbar with search -->
      <div class="topbar">
        
        <form class="search" method="GET" action="">
            <input type="text" name="q" placeholder="Search the community..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            <button class="search-btn" type="submit">🔍 Search</button>
        </form>
      </div>

      <!-- Dynamic Search Results -->
      <div class="results">
          <?php if (!empty($searchResults)) : ?>
              <ul>
                  <?php foreach ($searchResults as $result) : ?>
                      <li><?php echo htmlspecialchars($result); ?></li>
                  <?php endforeach; ?>
              </ul>
          <?php elseif (isset($_GET['q'])): ?>
              <p style="text-align: center; color: var(--text-gray); font-style: italic;">No results found for "<?php echo htmlspecialchars($_GET['q']); ?>"</p>
          <?php endif; ?>
      </div>

      <!-- Hero Section -->
      <section class="hero">
        <h1>Welcome to WOMXN</h1>
        <p>A vibrant community celebrating queer women, non-binary folks, and allies. Connect, share stories, and build lasting friendships in a safe, supportive space.</p>
        <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Diverse group of women celebrating" class="featured-image">
      </section>

      <!-- Community Stats -->
      <div class="community-stats">
        <div class="stat-card">
          <span class="stat-number"><?php echo $userCount; ?>+</span>
          <div class="stat-label">Community Members</div>
        </div>
        <div class="stat-card">
          <span class="stat-number"><?php echo $postCount; ?>+</span>
          <div class="stat-label">Stories Shared</div>
        </div>
        <div class="stat-card">
          <span class="stat-number"><?php echo $eventCount; ?>+</span>
          <div class="stat-label">Events Hosted</div>
        </div>
      </div>

      <!-- Call to Action -->
      <section class="cta">
        <h2>Be Part of the Movement 💕</h2>
        <p>Join thousands of amazing queer women and allies building connections, sharing experiences, and creating positive change across Kenya and beyond.</p>
        <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Women supporting each other" class="featured-image">
        <div>
          <a href="signup.php" class="btn">Join the Community</a>
          <a href="about.php" class="btn btn-secondary">Learn More</a>
        </div>
      </section>

      <!-- Quick Navigation -->
      <section class="quick-nav">
        <h2>Quick Access</h2>
        <div class="nav-grid">
          <a href="feed.php" class="nav-card">
            <div class="nav-icon">💬</div>
            <h3>Community Feed</h3>
            <p>Join conversations, share your authentic self, and connect with like-minded individuals</p>
          </a>
          <a href="events.php" class="nav-card">
            <div class="nav-icon">📅</div>
            <h3>Events & Meetups</h3>
            <p>Discover local gatherings, workshops, pride celebrations, and community activities</p>
          </a>
          <a href="#resources" class="nav-card">
            <div class="nav-icon">🌈</div>
            <h3>Opportunities</h3>
            <p>Find job opportunities, collaborations, and partnerships within our community</p>
          </a>
        </div>
      </section>

      <!-- Latest Events -->
      <section class="latest-events">
        <h2>Upcoming Events</h2>
        <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Community gathering" class="featured-image">
        
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
                    <p><a href="<?= htmlspecialchars($event['ticket_link']) ?>" target="_blank">Get Tickets →</a></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div style="text-align: center; padding: 2rem; color: var(--text-gray);">
            <p>No events scheduled yet. Stay tuned for exciting community gatherings! 🌟</p>
          </div>
        <?php endif; ?>
        <div style="text-align: center; margin-top: 2rem;">
          <a href="events.php" class="btn">View All Events</a>
        </div>
      </section>

      <!-- Latest Posts -->
      <section class="latest-posts">
        <h2>💫 Latest Community Stories</h2>
        
        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Women sharing stories" class="featured-image">

        <?php if (empty($latestPosts)): ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-gray);">
                <p>No posts yet. Be the first to share your story! ✨</p>
            </div>
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

        <div style="text-align: center; margin-top: 2rem;">
          <a href="feed.php" class="btn">View Full Feed</a>
        </div>
      </section>

      <!-- Newsletter Signup -->
      <section class="newsletter">
        <h2>📬 Stay Connected</h2>
        <p>Get weekly updates on events, featured stories, community highlights, and opportunities to connect with amazing people.</p>
        <form class="newsletter-form" action="newsletter_signup.php" method="POST">
          <input type="email" name="email" placeholder="Enter your email address" required>
          <button type="submit">Subscribe</button>
        </form>
      </section>

      <footer>
        <p>Made with love in Kenya 🇰🇪🏳️‍🌈 | © 2025 WOMXN | 
        <a href="privacy.php">Privacy Policy</a> | 
        <a href="terms.php">Terms of Service</a></p>
      </footer>
    </main>
  </div>
</body>
</html>