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
  <title>Queer Platform</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    /* Global styles with lesbian flag colors */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
/* On mobile: sidebar overlays, so main should be full width */
@media (max-width: 768px) {
  main {
    margin-left: 0;
  }
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
  font-family: 'poppins', sans-serif;
  display: flex;
  background: #ffffff;
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
  margin-top: 60px;  /* space for topbar */
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

/* Hero with background image */
.hero {
  background: url("uploads/paint.jpeg") no-repeat center center/cover;
  position: relative;
  border-radius: 24px 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.hero-overlay {
  background: rgba(0, 0, 0, 0.5); /* dark overlay for readability */
  width: 100%;
   border-radius: 24px 24px;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.hero-content {
  max-width: 600px;
  text-align: center;
}

.hero h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}
.hero h1 span {
  color:var(--lesbian-white);
  font-family:  macondo, sans-serif;
}
.hero p {
  margin: 1rem 0 2rem;
  font-size: 1.2rem;
}

.hero-ctas .cta-btn {
    display: inline-block;
    background-color: var(--lesbian-orange);
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
.hero-ctas .cta-btn.secondary {
    background: transparent;
    color: white;
    border: 2px solid var(--lesbian-orange);
}

.cta-btn secondary:hover {
    background: var(--lesbian-light-pink);
    color: white;
    border-color: var(--lesbian-light-pink);
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

/* Quick Links Cards */
.quick-links {
  padding: 3rem 2rem;
  display: grid;
  gap: 2rem;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.card {
  background: #f8f0f4ff;
  margin: 0 auto;
  padding: 1.5rem;
  border-radius: 15px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
  transition: transform 0.2s ease;
}
.card:hover {
  transform: translateY(-5px);
}
.cta-link {
  display: inline-block;
  margin-top: 1rem;
  color: #c2185b;
  font-weight: 500;
  text-decoration: none;
}

.latest-posts {
  padding: 3rem 1rem;
  background: #f8f0f4ff;
  border-radius: 24px;
  margin-bottom: 4rem;
}

.latest-posts h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: #d63384;
}

.no-posts {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.post-card {
  background: #fff;
  padding: 1.2rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.post-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 0.8rem;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.post-image {
  margin-top: 0.8rem;
  border-radius: 10px;
  width: 100%;
  max-height: 250px;
  object-fit: cover;
}

.view-more {
  text-align: center;
  margin-top: 2rem;
}

.btn {
  display: inline-block;
  background: #d63384;
  color: #fff;
  padding: 0.7rem 1.3rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: bold;
  transition: background 0.3s ease;
}

.btn:hover {
  background: #a62265;
}
/* Comments Section */
.comments {
  margin-top: 1rem;
  padding-top: 0.8rem;
  border-top: 1px solid #e0c9d9;
}

.comment {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  margin-bottom: 0.8rem;
}

.comment strong {
  color: #333;
  font-size: 0.95rem;
}

.comment-text {
  background: #fff;
  padding: 0.6rem 0.9rem;
  border-radius: 10px;
  font-size: 0.9rem;
  color: #444;
  max-width: 80%;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.comment small {
  display: block;
  font-size: 0.75rem;
  color: #888;
  margin-top: 0.3rem;
}

.add-comment {
  margin-top: 0.8rem;
  display: flex;
  gap: 0.6rem;
}

.add-comment input[type="text"] {
  flex: 1;
  padding: 0.6rem 0.9rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.9rem;
}

.add-comment button {
  background: #d63384;
  border: none;
  color: #fff;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.3s ease;
}

.add-comment button:hover {
  background: #a62265;
}

 
/* Responsive grid */
@media (max-width: 900px) {
  .posts-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .posts-grid {
    grid-template-columns: 1fr;
  }
}
.comments {
  margin-top: 1rem;
  padding-top: 0.8rem;
  border-top: 1px solid #eee;
}

.comments h4 {
  font-size: 1rem;
  margin-bottom: 0.6rem;
  color: #d63384;
}

.comment {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  margin-bottom: 0.8rem;
}

.comment-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
}

.comment p {
  margin: 0.2rem 0;
}

.no-comments {
  font-size: 0.9rem;
  color: #777;
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
    color: var(--lesbian-dark-pink);
    font-size: 2.2rem;
    text-align: center;
    margin-bottom: 3rem;
    font-weight: 700;
}

.event-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.event-card {
  background: #f8f0f4ff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 8px rgba(0,0,0,0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.event-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.12);
}

.event-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.event-details {
  padding: 1rem;
}

.event-details h3 {
  margin: 0 0 0.5rem;
  color: #872657;
}

.event-details p {
  margin: 0.3rem 0;
  color: #444;
}
  
.event-details a {
  display: inline-block;
  margin-top: 0.5rem;
  color: var    
}
/* Opportunities Section */
.opportunities {
  padding: 3rem 1rem;

}

.opportunity-container {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  max-width: 1100px;
  margin: 0 auto;
  gap: 2rem;
}

.opportunity-image {
  flex: 1 1 45%;
}

.opportunity-image img {
  width: 100%;
  border-radius: 100px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}

.opportunity-text {
  flex: 1 1 45%;
}

.opportunity-text h2 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #872657; /* WOMXN pink */
}

.opportunity-text p {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 1.5rem;
}

.btn {
  display: inline-block;
  background: #872657;
  color: #fff;
  padding: 0.7rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: bold;
  transition: background 0.3s ease;
}

.btn:hover {
  background: #a62265;
}

/* Mobile stacking */
@media (max-width: 768px) {
  .opportunity-container {
    flex-direction: column;
    text-align: center;
  }

  .opportunity-text {
    padding: 0 1rem;
  }
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
    background: radial-gradient(circle at center, rgba(255, 255, 255, 0.2), transparent 70%);
    opacity: 0.1;
    z-index: 0;
}

.newsletter > * {
    position: relative;
    z-index: 1;
}

.newsletter h2 {
    
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
    border-radius: 50%;
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
 /*mobile friendly layout */
/* Mobile friendly layout refinements */
@media (max-width: 768px) {
  main {
    padding: 1rem;
    margin-left: 0;
  }

  .hero h1 {
    font-size: 2rem;
    line-height: 1.3;
  }

  .hero p {
    font-size: 1rem;
  }

  .search input[type="text"] {
    width: 100%;
    max-width: 100%;
    margin-bottom: 0.5rem;
  }

  .search-btn {
    width: 100%;
    margin-left: 0;
  }

  .newsletter {
    padding: 2rem 1rem;
  }

  .newsletter-form {
    flex-direction: column;
    border-radius: 20px;
    padding: 0.8rem;
  }

  .newsletter-form input,
  .newsletter-form button {
    width: 100%;
    border-radius: 12px;
    margin: 0.3rem 0;
  }
}

@media (max-width: 600px) {
  .posts-grid,
  .event-list,
  .community-stats {
    grid-template-columns: 1fr;
  }

  .opportunity-container {
    flex-direction: column;
    text-align: center;
  }

  .opportunity-text {
    padding: 0 0.5rem;
  }

  .hero h1 {
    font-size: 1.8rem;
  }
}


</style>
</head>
<body>
  <div class="container">
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php'; ?>
<?php include 'topbar.php'; ?>

    <!-- Main Content -->
    <main>
           <!-- Hero Section -->
<section class="hero">
  <div class="hero-overlay">
    <div class="hero-content">
      <p>Connecting queer women in Kenya through community, events, and opportunities.</p>
      <div class="hero-ctas">
        <a href="community.php" class="cta-btn primary">See the Community</a>
        <a href="about.php" class="cta-btn secondary">Learn More</a>
      </div>
    </div>
  </div>
</section>


  <!-- Quick Links / Cards -->
  <section class="quick-links">
   
    <div class="card">
      <h2>Community</h2>
      <p>Find safe spaces, connect, and build friendships.</p>
      <a href="community.php" class="cta-link">Explore →</a>
    </div>
    <div class="card">
      <h2>Events</h2>
      <p>Stay updated on gatherings, workshops, and meetups.</p>
      <a href="events.php" class="cta-link">See Events →</a>
    </div>
    <div class="card">
      <h2>Opportunities</h2>
      <p>Access scholarships, jobs, and collaborations.</p>
      <a href="exchange.php" class="cta-link">Get Started →</a>
    </div>
  </section>
        <!-- Latest Posts -->
<section class="latest-posts">
  <h2> Latest Community Stories</h2>

  <?php if (empty($latestPosts)): ?>
      <div class="no-posts">
          <p>No posts yet. Be the first to share your story! ✨</p>
      </div>
  <?php else: ?>
      <div class="posts-grid">
          <?php foreach ($latestPosts as $post): ?>
              <div class="post-card">
                  <div class="post-header">
                      <img src="<?= htmlspecialchars($post['profile_pic']) ?>" alt="Profile" class="avatar">
                      <strong><?= htmlspecialchars($post['name']) ?></strong>
                  </div>

                  <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                  <?php if (!empty($post['image'])): ?>
                      <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post Image">
                  <?php elseif (!empty($post['video_path'])): ?>
                      <video controls class="post-image">
                          <source src="<?= htmlspecialchars($post['video_path']) ?>" type="video/mp4">
                          Your browser does not support the video tag.
                      </video>
                  <?php endif; ?>

                  <small><?= date("F j, Y, g:i a", strtotime($post['created_at'])) ?></small>

                  <!-- ✅ Comments Section -->
                  <div class="comments">
                    <?php
                      $post_id = $post['id'];
                      $stmt = $conn->prepare("SELECT comments.comment_text, comments.created_at, users.name 
                                               FROM comments 
                                               JOIN users ON comments.user_id = users.id 
                                               WHERE comments.post_id = ? 
                                               ORDER BY comments.created_at ASC");
                      $stmt->bind_param("i", $post_id);
                      $stmt->execute();
                      $comments_result = $stmt->get_result();

                      if ($comments_result->num_rows > 0):
                          while ($comment = $comments_result->fetch_assoc()): ?>
                              <div class="comment">
                                  <strong><?= htmlspecialchars($comment['name']) ?>:</strong>
                                  <?= nl2br(htmlspecialchars($comment['comment_text'])) ?>
                                  <small><?= date("F j, Y, g:i a", strtotime($comment['created_at'])) ?></small>
                              </div>
                          <?php endwhile;
                      else: ?>
                          <div class="no-comments">No comments yet.</div>
                      <?php endif;
                      $stmt->close();
                    ?>
                  </div>

                  <!-- ✅ Add Comment Form -->
                  <form action="submit_comment.php" method="POST" class="comment-form">
                      <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                      <textarea name="comment_text" placeholder="Write a comment..." required></textarea>
                      <button type="submit">Post</button>
                  </form>
              </div>
          <?php endforeach; ?>
      </div>
  <?php endif; ?>
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
    
        <!-- opportunities -->            
<section class="opportunities">
  <div class="opportunity-container">
    <!-- Image side -->
    <div class="opportunity-image">
      <img src="uploads/heads.jpg" alt="Opportunities at WOMXN">
    </div>

    <!-- Text side -->
    <div class="opportunity-text">
      <h2> Opportunities for the Community</h2>
      <p>
        Discover events, workshops, and programs created to uplift queer women in Kenya.
      </p>
      <a href="exchange.php" class="btn">Explore Opportunities</a>
    </div>
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