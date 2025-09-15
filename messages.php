<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch logged in user data
$stmt = $conn->prepare("SELECT name, pronouns, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $pronouns, $profile_pic);
$stmt->fetch();
$stmt->close();

$profile_pic_url = $profile_pic && file_exists("uploads/$profile_pic") 
    ? "uploads/$profile_pic" 
    : "uploads/default.jpeg";

// Fetch distinct conversations (last message per user)
$query = "
    SELECT m.id, m.sender_id, m.receiver_id, m.content, m.created_at,
           u.id AS other_id, u.name AS other_name, u.profile_pic AS other_pic
    FROM messages m
    JOIN users u ON (CASE 
                        WHEN m.sender_id = ? THEN m.receiver_id = u.id
                        ELSE m.sender_id = u.id
                     END)
    WHERE m.sender_id = ? OR m.receiver_id = ?
    AND m.id = (
        SELECT MAX(id) FROM messages 
        WHERE (sender_id = m.sender_id AND receiver_id = m.receiver_id)
           OR (sender_id = m.receiver_id AND receiver_id = m.sender_id)
    )
    ORDER BY m.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$conversations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Conversations</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #ffe6f0; min-height: 100vh; color: #333; }
        .container { display: flex; width: 100%; min-height: 100vh; }
        main { margin-left: 240px; flex: 1; padding: 2rem; background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); }
        .messages-section { background: #fff; border-radius: 20px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .section-header { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: .5rem; }
        .section-header h2 { font-size: 1.5rem; font-weight: 600; color: #2d3748; }
        .conversation { display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid #f1f1f1; text-decoration: none; color: inherit; }
        .conversation:hover { background: #fdf2f8; }
        .conversation img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; }
        .conv-info { flex: 1; }
        .conv-info h3 { margin: 0; font-size: 1rem; font-weight: 600; color: #872657; }
        .conv-info p { margin: .2rem 0 0; font-size: .9rem; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conv-time { font-size: .8rem; color: #999; }
        .empty-state { text-align: center; padding: 3rem; color: #777; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; color: #ccc; }
    </style>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main>
        <div class="messages-section">
            <div class="section-header">
                <i class="fas fa-comments"></i><h2>Your Conversations</h2>
            </div>

            <?php if (!empty($conversations)): ?>
                <?php foreach ($conversations as $conv): ?>
                    <?php 
                        $pic = (!empty($conv['other_pic']) && file_exists("uploads/" . $conv['other_pic'])) 
                            ? "uploads/" . $conv['other_pic'] 
                            : "uploads/default.jpeg"; 
                    ?>
                    <a href="chat.php?user=<?= $conv['other_id'] ?>" class="conversation">
                        <img src="<?= htmlspecialchars($pic) ?>" alt="Profile">
                        <div class="conv-info">
                            <h3><?= htmlspecialchars($conv['other_name']) ?></h3>
                            <p><?= htmlspecialchars(substr($conv['content'], 0, 40)) ?>...</p>
                        </div>
                        <div class="conv-time"><?= htmlspecialchars(date("M d, H:i", strtotime($conv['created_at']))) ?></div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No conversations yet. Start messaging someone!</p>
                </div>
                 
            <?php endif; ?>
             
        </div>
    </main>
</div>
</body>
</html>
