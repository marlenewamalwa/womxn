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

// Get user events
$events = [];
$event_stmt = $conn->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC");
$event_stmt->bind_param("i", $user_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
while ($row = $event_result->fetch_assoc()) {
    $events[] = $row;
}
$event_stmt->close();

// Get user exchange listings
$listings = [];
$list_stmt = $conn->prepare("SELECT id, type, description, category, location, payment, created_at 
                             FROM exchange_listings 
                             WHERE user_id = ? 
                             ORDER BY created_at DESC");
$list_stmt->bind_param("i", $user_id);
$list_stmt->execute();
$list_result = $list_stmt->get_result();
while ($row = $list_result->fetch_assoc()) {
    $listings[] = $row;
}
$list_stmt->close();


// Fetch distinct chat partners
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Profile</title>
  <link rel="stylesheet" href="styles.css">
<link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #872657;
      --bg: #faf7fb;
      --text: #2d3748;
      --muted: #718096;
      --card-bg: #ffffffee;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #fff7fa, #f0e8f2);
      color: var(--text);
    }

    .container { display: flex; min-height: 100vh; }

    main { flex: 1; padding: 2rem; margin-left: 240px;
      margin-top: 60px; /* space for topbar */ 
    min-height: 100vh;
}

    /* Profile Header */
    .profile-header {
      background: var(--card-bg);
      border-radius: 20px;
      padding: 2rem;
      margin-top: 1rem;
      margin-bottom: 2rem;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      text-align: center;
    }

    .profile-avatar img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 4px solid #fff;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      margin-bottom: 1rem;
      transition: transform .3s;
    }
    .profile-avatar img:hover { transform: scale(1.05); }

    .profile-details h1 { font-size: 1.8rem; color: var(--primary); }
    .info-item { font-size: .9rem; color: var(--muted); margin-top: .25rem; }

    .profile-actions { margin-top: 1rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

    .btn {
      padding: .7rem 1.3rem; border-radius: 10px; text-decoration: none; font-weight: 500;
      display: inline-flex; align-items: center; gap: .5rem; transition: all .3s ease; cursor: pointer;
    }
    .btn-primary { background: var(--primary); color: white; margin-bottom: 6px; }
    .btn-primary:hover { background: #a23b6a; }
    .btn-outline { border: 2px solid var(--primary); color: var(--primary); }
    .btn-outline:hover { background: var(--primary); color: white; }

    /* Tabs */
    .tabs { display: flex; gap: 1rem; margin-bottom: 1rem; }
    .tab-btn {
      flex: 1; text-align: center; padding: .8rem; border-radius: 10px; cursor: pointer;
      background: #f2e6ef; color: var(--primary); font-weight: 500; transition: .3s;
    }
    .tab-btn.active, .tab-btn:hover { background: var(--primary); color: #fff; }

    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* Sections */
    .posts-section, .sidebar-panel, .messages-section {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .section-header { display: flex; align-items: center; gap: .5rem; margin-bottom: 1rem; }
    .section-header h2 { font-size: 1.3rem; }
    .section-header i { color: var(--primary); }

    .post {
      background: #fff;
      padding: 1rem;
      border-radius: 12px;
      margin-bottom: 1rem;
      border: 1px solid #eee;
      transition: .3s;
    }
    .post:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    .post img { width: 100%; border-radius: 10px; margin-top: .5rem; }

    .empty-state { text-align: center; color: var(--muted); padding: 2rem 1rem; }
    .empty-state i { font-size: 2rem; display: block; margin-bottom: .5rem; color: #ccc; }

    /* Sidebar */
    .member-item, .chat-item {
      display: flex; align-items: center; justify-content: space-between;
      padding: .6rem .8rem; border-radius: 10px; transition: background .3s;
    }
    .member-item:hover, .chat-item:hover { background: #f9f1f5; }
    .chat-avatar { display: flex; align-items: center; gap: .75rem; }
    .chat-avatar img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #eee; }
/* Responsive */
    @media (max-width: 900px) {
      main { margin-left: 0; padding: 1rem; }
      .container { flex-direction: column; }
      .profile-actions { flex-direction: column; }
    }
  </style>
</head>
<body>
<div class="container">
  <?php include 'sidebar.php'; ?>
  <?php include 'topbar.php'; ?>

  <main>
    <!-- Profile Header -->
    <div class="profile-header">
      <div class="profile-avatar">
        <img src="<?= htmlspecialchars($profile_pic_url) ?>" alt="Profile Picture">
      </div>
      <div class="profile-details">
        <h1><?= htmlspecialchars($name) ?></h1>
        <div class="info-item"><i class="fas fa-user"></i> <?= htmlspecialchars($pronouns) ?></div>
      </div>
      <div class="profile-actions">
        <a href="edit_profile.php" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Profile</a>
        <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <div class="tab-btn active" data-tab="posts">Posts</div>
      <div class="tab-btn" data-tab="events">Events</div>
      <div class="tab-btn" data-tab="listings">Listings</div>
    </div>

    <!-- Posts -->
    <div id="posts" class="tab-content active">
      <div class="posts-section">
        <div class="section-header"><i class="fas fa-newspaper"></i><h2>Your Posts</h2></div>
        <a href="create_post.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Post</a>
        <?php if (!empty($posts)): foreach ($posts as $post): ?>
          <div class="post">
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            <?php if ($post['image']): ?><img src="<?= htmlspecialchars($post['image']) ?>" alt="Post Image"><?php endif; ?>
                <div class="post-meta"><i class="fas fa-clock"></i> <?= htmlspecialchars($post['created_at']) ?></div>
            <a href="edit_post.php?id=<?= $post['id'] ?>">Edit</a> |
                <a href="delete_post.php?id=<?= $post['id'] ?>">Delete</a>
          
            </div>
        <?php endforeach; else: ?>
          <div class="empty-state"><i class="fas fa-pen-alt"></i>No posts yet.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Events -->
    <div id="events" class="tab-content">
      <div class="posts-section">
        <div class="section-header"><i class="fas fa-calendar-alt"></i><h2>Your Events</h2></div>
        <a href="create_event.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Event</a>
        <?php if (!empty($events)): foreach ($events as $event): ?>
          <div class="post">
            <h3><?= htmlspecialchars($event['title']) ?></h3>
            <p><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
            <a href="delete_event.php?id=<?= $event['id'] ?>">Delete</a>
            <a href="edit_event.php?id=<?= $event['id'] ?>">Edit</a>
               
          </div>
        <?php endforeach; else: ?>
          <div class="empty-state"><i class="fas fa-calendar-times"></i>No events yet.</div>
        <?php endif; ?>
        
      </div>
      
    </div>

    <!-- Listings -->
    <div id="listings" class="tab-content">
      <div class="posts-section">
        <div class="section-header"><i class="fas fa-exchange-alt"></i><h2>Your Listings</h2></div>
        <a href="post_exchange.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Listing</a>
        <?php if (!empty($listings)): foreach ($listings as $l): ?>
          <div class="post">
            <p><strong>Type:</strong> <?= htmlspecialchars($l['type']) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($l['description']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($l['category']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($l['location']) ?></p>
            <p><strong>Payment:</strong> <?= htmlspecialchars($l['payment']) ?></p>
            <div class="post-meta"><i class="fas fa-clock"></i> <?= htmlspecialchars($l['created_at']) ?></div>
            <a href="edit_listing.php?id=<?= $l['id'] ?>">Edit</a> 
            <a href="delete_listing.php?id=<?= $l['id'] ?>">Delete</a>
        </div>
        <?php endforeach; else: ?>
          <div class="empty-state"><i class="fas fa-exchange-alt"></i>No listings yet.</div>
        <?php endif; ?>
      </div>
    </div>

 

      <div class="messages-section">
        <div class="section-header"><i class="fas fa-comments"></i><h2>Your Messages</h2></div>
        <?php if (!empty($chat_partners)): foreach ($chat_partners as $partner): ?>
          <div class="chat-item">
            <div class="chat-avatar">
              <?php $pic = (!empty($partner['profile_pic']) && file_exists("uploads/" . $partner['profile_pic'])) ? "uploads/" . $partner['profile_pic'] : "uploads/default.jpeg"; ?>
              <img src="<?= htmlspecialchars($pic) ?>" alt="Profile Pic">
              <span><?= htmlspecialchars($partner['name']) ?></span>
            </div>
            <a href="chat.php?user=<?= $partner['id'] ?>" class="btn btn-primary" style="padding:.4rem .8rem;font-size:.8rem;">Chat</a>
          </div>
        <?php endforeach; else: ?>
          <div class="empty-state"><i class="fas fa-inbox"></i>No messages yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<script>
  // Tab switching
  document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
      document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
      btn.classList.add("active");
      document.getElementById(btn.dataset.tab).classList.add("active");
    });
  });
</script>
</body>
</html>
