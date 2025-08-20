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

// Get other members except current user
$otherMembers = [];
$stmt = $conn->prepare("SELECT id, name FROM users WHERE id != ? ORDER BY name ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $otherMembers[] = $row;
}
$stmt->close();

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #ffe6f0;
            min-height: 100vh;
            color: #333;
        }

        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        main {
            margin-left: 240px;
            flex: 1;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        /* Profile Header Section */
        .profile-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .profile-avatar {
            position: relative;
        }

        .profile-avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .profile-avatar img:hover {
            transform: scale(1.05);
        }

        .profile-details h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .profile-details .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: #718096;
            font-size: 0.9rem;
        }

        .profile-details .info-item i {
            color: #872657;
            width: 16px;
        }

        .profile-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background:  #872657;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: #872657;
            border: 2px solid #872657;
        }

        .btn-outline:hover {
            background: #668726577eea;
            color: white;
            transform: translateY(-2px);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        /* Posts Section */
        .posts-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
           height:auto;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
        }

        .section-header i {
            color: #872657;
            font-size: 1.2rem;
        }

        .post {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .post:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .post img {
            max-width: 80%;

            margin-top: 1rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .post-meta {
            color: #718096;
            font-size: 0.8rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }
         .postbtn {
            display: inline-block;
            background: #872657;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;}

        /* Sidebar Panels */
        .sidebar-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .panel-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
        }

        .panel-header i {
            color: #872657;
        }

        .member-item, .chat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: background-color 0.3s ease;
         
        }

        .member-item:hover, .chat-item:hover {
            background-color: #f1f5f9;
        }

        .chat-avatar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .chat-avatar img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        .msg-link {
            font-size: 0.8rem;
            color: #872657;
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            border: 1px solid #872657;
            transition: all 0.3s ease;
        }

        .msg-link:hover {
            background: #872657;
            color: white;
        }

        /* Messages Section */
        .messages-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            main {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .profile-info {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-actions {
                justify-content: center;
            }
            
            main {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <main>
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar">
                        <img src="<?= htmlspecialchars($profile_pic_url) ?>" alt="Profile Picture">
                    </div>
                    <div class="profile-details">
                        <h1><?= htmlspecialchars($name) ?></h1>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($email) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-user"></i>
                            <span><?= htmlspecialchars($pronouns) ?></span>
                        </div>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Profile
                    </a>
                    <a href="logout.php" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Posts Section -->
                <div class="posts-section">
                    <div class="section-header">
                        <i class="fas fa-newspaper"></i>
                        <h2>Your Posts</h2>
                    
                    </div>
                        <div>
                            <a href="create_post.php" class="postbtn">
                                <i class="fas fa-plus"></i>
                                Create New Post
                            </a>
                        </div>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post">
                                <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                                <?php if ($post['image']): ?>
                                    <img src="<?= htmlspecialchars($post['image']) ?>" alt="Post Image">
                                <?php endif; ?>
                                <div class="post-meta">
                                    <i class="fas fa-clock"></i>
                                    <span>Posted on <?= htmlspecialchars($post['created_at']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-pen-alt"></i>
                            <p>You haven't made any posts yet.</p>
                        </div>
                    <?php endif; ?>
                       
                </div>
                

                <!-- Sidebar Panels -->
                <div class="sidebar-content">
                    <!-- Other Members Panel -->
                    <div class="sidebar-panel">
                        <div class="panel-header">
                            <i class="fas fa-users"></i>
                            <h3>Other Members</h3>
                        </div>
                        
                        <?php if (!empty($otherMembers)): ?>
                            <?php foreach ($otherMembers as $m): ?>
                                <div class="member-item">
                                    <span><?= htmlspecialchars($m['name']) ?></span>
                                    <a href="user_profile.php?id=<?= $m['id'] ?>" class="msg-link">
                                        <i class="fas fa-user"></i>
                                        View Profile
                                    </a>
                                  </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-user-friends"></i>
                                <p>No other members found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                        <!-- Messages Section -->
            <div class="messages-section">
                <div class="section-header">
                    <i class="fas fa-comments"></i>
                    <h2>Your Messages</h2>
                </div>
                
                <?php if (!empty($chat_partners)): ?>
                    <?php foreach ($chat_partners as $partner): ?>
                        <div class="chat-item">
                            <div class="chat-avatar">
                                <?php 
                                $pic = (!empty($partner['profile_pic']) && file_exists("uploads/" . $partner['profile_pic'])) 
                                    ? "uploads/" . $partner['profile_pic'] 
                                    : "uploads/default.jpeg"; 
                                ?>
                                <img src="<?= htmlspecialchars($pic) ?>" alt="Profile Pic">
                                <span><?= htmlspecialchars($partner['name']) ?></span>
                            </div>
                            <a href="chat.php?user=<?= $partner['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                                <i class="fas fa-comment"></i>
                                Chat
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>You have no messages yet.</p>
                    </div>
                <?php endif; ?>
            </div>
                </div>
                
            </div>

        
        </main>
    </div>
</body>
</html>