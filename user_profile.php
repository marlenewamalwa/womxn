<?php
// user_profile.php
session_start();
require 'db.php'; // Your DB connection (must set $conn as MySQLi object)

$loggedInUser = $_SESSION['user_id'] ?? 0;
if (!$loggedInUser) {
    die('You must be logged in to view profiles.');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    die('Invalid profile');
}

// Helper function for "time ago"
function timeAgo($datetime) {
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return "just now";
    $units = [
        31536000 => "year",
        2592000  => "month",
        604800   => "week",
        86400    => "day",
        3600     => "hour",
        60       => "minute"
    ];
    foreach ($units as $secs => $str) {
        $val = floor($diff / $secs);
        if ($val >= 1) {
            return $val . ' ' . $str . ($val > 1 ? 's' : '') . ' ago';
        }
    }
    return "just now";
}

// Get the profile member info
$stmt = $conn->prepare("SELECT name, pronouns, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if (!$member) {
    die('Member not found');
}

// Prevent undefined variable notices
$events = [];
$posts = [];
$exchanges = [];

// Fetch all users except logged-in for optional "Message" list
$stmt = $conn->prepare("SELECT id, name FROM users WHERE id != ?");
$stmt->bind_param("i", $loggedInUser);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch user's events
$stmt = $conn->prepare("SELECT id, title, event_date 
                        FROM events 
                        WHERE id = ? 
                        ORDER BY event_date DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch user's posts (with content for excerpt)
$stmt = $conn->prepare("SELECT id, content, image, created_at 
                        FROM posts 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch user's exchanges
$sql = "SELECT id, title, type, location, created_at 
        FROM exchange_listings 
        WHERE user_id = ? 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$exchanges = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($member['name']) ?> - Profile</title>
    <link rel="stylesheet" href="styles.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            background-color: #ffe6f0;
            color: #2b2b2b;
        }
        .container { display: flex; width: 100%; }
        main { margin-left: 240px; padding: 2rem; flex: 1; }
        .profile-page {
            max-width: 700px; margin: 40px auto; background: white;
            border-radius: 15px; padding: 30px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.08);
        }
        .profile-header { text-align: center; margin-bottom: 30px; }
        .profile-pic {
            width: 120px; height: 120px; border-radius: 50%;
            object-fit: cover; border: 3px solid #872657; margin-bottom: 15px;
        }
        .profile-header h1 { font-size: 1.8rem; margin: 5px 0; }
        .pronouns { color: #888; font-size: 0.95rem; }
        .btn {
            display: inline-block; background-color: #872657; color: white;
            padding: 10px 20px; border-radius: 8px; text-decoration: none;
            font-weight: 500; transition: background 0.3s;
        }
        .btn:hover { background-color: #68212f; }
        .user-content { margin-top: 20px; }
        .user-section {
            background: #fff0f5; padding: 15px; margin-bottom: 20px;
            border-radius: 10px;
        }
        .user-section h2 { color: #872657; margin-bottom: 10px; }
        .user-section ul { list-style: none; padding-left: 0; }
        .user-section li { padding: 5px 0; border-bottom: 1px solid #eee; }
        .user-section li:last-child { border-bottom: none; }
        .excerpt { color: #555; font-size: 0.9rem; display: block; margin-top: 3px; }
        .timeago { color: #888; font-size: 0.85rem; margin-left: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main>
            <div class="profile-page">
                <div class="profile-header">
                    <img src="<?= htmlspecialchars($member['profile_pic'] ?: 'uploads/default.jpeg') ?>" 
                         alt="Profile Picture" class="profile-pic">
                    <h1><?= htmlspecialchars($member['name']) ?></h1>
                    <p class="pronouns"><?= htmlspecialchars($member['pronouns']) ?></p>                 
                </div>
                <div class="profile-actions">
                    <?php if ($id !== $loggedInUser): ?>
                        <a href="chat.php?user=<?= $id ?>" class="btn">Message</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="user-content">
                <!-- Events -->
                <section class="user-section">
                    <h2>Events</h2>
                    <?php if (!empty($events)): ?>
                        <ul>
                            <?php foreach ($events as $event): ?>
                                <li>
                                    <a href="event.php?id=<?= (int)$event['id'] ?>">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </a>
                                    <span class="timeago"><?= timeAgo($event['event_date']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No events posted yet.</p>
                    <?php endif; ?>
                </section>

                <!-- Posts -->
                <!-- Posts -->
<section class="user-section">
    <h2>Posts</h2>
    <?php if (!empty($posts)): ?>
        <ul>
            <?php foreach ($posts as $post): ?>
                <?php 
                    $excerpt = mb_substr(strip_tags($post['content']), 0, 100) . 
                               (strlen($post['content']) > 100 ? '...' : '');
                ?>
                <li style="display: flex; gap: 10px; align-items: flex-start;">
                    <?php if (!empty($post['image_path'])): ?>
                        <img src="<?= htmlspecialchars($post['image_path']) ?>" 
                             alt="<?= htmlspecialchars($post['title']) ?>" 
                             style="width:80px; height:80px; object-fit:cover; border-radius:6px;">
                    <?php endif; ?>
                <div style="display: flex; gap: 10px; align-items: flex-start;">
    <?php if (!empty($post['image'])): ?>
        <img src="<?= htmlspecialchars($post['image']) ?>" 
             alt="<?= htmlspecialchars($post['content']) ?>" 
             style="width:80px; height:80px; object-fit:cover; border-radius:6px;">
    <?php endif; ?>
    <div>
        <a href="post.php?id=<?= (int)$post['id'] ?>">
            
        </a>
        <span class="timeago"><?= timeAgo($post['created_at']) ?></span>
        <span class="excerpt"><?= htmlspecialchars($excerpt) ?></span>
    </div>
</div>

                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No posts yet.</p>
    <?php endif; ?>
</section>

             

                <!-- Exchanges -->
                <section class="user-section">
                    <h2>Exchange Listings</h2>
                    <?php if (!empty($exchanges)): ?>
                        <ul>
                            <?php foreach ($exchanges as $exchange): ?>
                                <li>
                                    <a href="exchange_item.php?id=<?= (int)$exchange['id'] ?>">
                                        <?= htmlspecialchars($exchange['title']) ?>
                                    </a>
                                    (<?= htmlspecialchars($exchange['type']) ?>)
                                    <span class="timeago"><?= timeAgo($exchange['created_at']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No exchange listings yet.</p>
                    <?php endif; ?>
                </section>
            </div>
        </main>
                    <?php
$type_labels = [
    'skill_offer' => 'Skill Offer',
    'skill_request' => 'Skill Request'
];
?>
    </div>
</body>
</html>
