<?php
session_start();
require 'db.php';

$loggedInUser = $_SESSION['user_id'] ?? 0;
if (!$loggedInUser) die('You must be logged in to use the chat.');

$chatUser = isset($_GET['user']) ? (int)$_GET['user'] : 0;
if ($chatUser === 0) die('No user selected for chat.');

// Fetch partner info
$stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $chatUser);
$stmt->execute();
$partner = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$partner) die('User not found.');

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');
    $filePath = null;

    if (!empty($_FILES['media']['name'])) {
        $allowedTypes = [
            'image/jpeg','image/png','image/gif','image/webp',
            'video/mp4','video/webm','video/ogg'
        ];
        $maxSize = 50 * 1024 * 1024; // 50MB

        if ($_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['media']['tmp_name'];
            $fileType = mime_content_type($fileTmp);
            $fileSize = $_FILES['media']['size'];

            if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $newName = uniqid('chat_', true) . '.' . $ext;
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filePath = 'uploads/' . $newName;

                if (!move_uploaded_file($fileTmp, $uploadDir . $newName)) {
                    die('Error saving uploaded file.');
                }
            } else {
                die('Invalid file type or file too large.');
            }
        } else {
            die('File upload error.');
        }
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, media_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $loggedInUser, $chatUser, $msg, $filePath);
    $stmt->execute();
    $stmt->close();

    header("Location: chat.php?user=$chatUser");
    exit;
}

// Fetch messages
$stmt = $conn->prepare("
    SELECT sender_id, receiver_id, content, media_path, created_at
    FROM messages
    WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
    ORDER BY created_at ASC
");
$stmt->bind_param("iiii", $loggedInUser, $chatUser, $chatUser, $loggedInUser);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Chat with <?= htmlspecialchars($partner['name']) ?></title>
<link rel="stylesheet" href="styles.css">
<style>
/* Mobile-First Chat CSS */
* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
}

body { 
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
    display: flex; 
    background: #f8f9fa; 
    color: #333; 
    line-height: 1.6;
    min-height: 100vh;
}

.container { 
    display: flex; 
    width: 100%; 
    position: relative;
}

/* Desktop styles */
main { 
    margin-left: 240px; 
    padding: 2rem; 
    flex: 1; 
    max-width: 100%;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

h1 { 
    text-align: center; 
    color: #872657; 
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: 600;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.messages { 
    border: 1px solid #e1e5e9; 
    height: 400px; 
    overflow-y: scroll; 
    padding: 1rem; 
    background: white; 
    margin-bottom: 1rem; 
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex: 1;
    scroll-behavior: smooth;
}

.message { 
    margin-bottom: 1rem; 
    max-width: 75%; 
    padding: 0.75rem 1rem; 
    border-radius: 18px; 
    clear: both;
    word-wrap: break-word;
    position: relative;
}

.sent { 
    background: #872657; 
    color: white;
    float: right; 
    text-align: left;
    border-bottom-right-radius: 4px;
}

.received { 
    background: #e9ecef; 
    color: #333;
    float: left; 
    text-align: left;
    border-bottom-left-radius: 4px;
}

form { 
    display: flex; 
    gap: 0.5rem; 
    background: white;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    align-items: flex-end;
}

textarea { 
    flex-grow: 1; 
    resize: none; 
    padding: 0.75rem; 
    font-size: 1rem; 
    border-radius: 20px; 
    border: 1px solid #e1e5e9;
    outline: none;
    font-family: inherit;
    min-height: 44px;
    max-height: 120px;
}

textarea:focus {
    border-color: #872657;
    box-shadow: 0 0 0 3px rgba(135, 38, 87, 0.1);
}

button { 
    background: #872657; 
    color: white; 
    border: none; 
    padding: 0.75rem 1.5rem; 
    border-radius: 20px; 
    cursor: pointer; 
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
    min-height: 44px;
}

button:hover { 
    background: #68212f; 
    transform: translateY(-1px);
}

button:active {
    transform: translateY(0);
}

input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 0.1px;
    height: 0.1px;
}

.file-input-label {
    background: #6c757d;
    color: white;
    padding: 0.5rem;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    transition: all 0.2s ease;
}

.file-input-label:hover {
    background: #5a6268;
}

img, video { 
    max-width: 200px; 
    display: block; 
    margin-top: 0.5rem; 
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

video { 
    max-height: 150px; 
}

.message small {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.7);
    margin-top: 0.25rem;
    display: block;
}

.received small {
    color: #6c757d;
}

/* Mobile Styles */
@media (max-width: 768px) {
    body {
        background: white;
    }
    
    main { 
        margin-left: 0; 
        padding: 0;
        height: 100vh;
        width: 100%;
    }
    
    .container { 
        flex-direction: column; 
    }
    
    h1 {
        margin: 0;
        border-radius: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        font-size: 1.25rem;
        padding: 1rem;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .messages {
        height: calc(100vh - 200px);
        border: none;
        border-radius: 0;
        box-shadow: none;
        padding: 1rem;
        margin-bottom: 0;
        background: #f8f9fa;
    }
    
    .message {
        max-width: 85%;
        margin-bottom: 0.75rem;
        padding: 0.75rem;
        font-size: 0.95rem;
    }
    
    .sent {
        margin-right: 0.5rem;
    }
    
    .received {
        margin-left: 0.5rem;
    }
    
    form {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 0;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
        gap: 0.75rem;
        padding: 1rem;
        margin: 0;
        background: white;
        border-top: 1px solid #e1e5e9;
    }
    
    textarea {
        font-size: 1rem;
        border-radius: 22px;
        padding: 0.75rem 1rem;
        min-height: 44px;
    }
    
    button {
        padding: 0.75rem 1rem;
        border-radius: 22px;
        font-size: 0.95rem;
        white-space: nowrap;
    }
    
    .file-input-label {
        width: 44px;
        height: 44px;
        font-size: 1.2rem;
    }
    
    img, video {
        max-width: 250px;
        max-width: min(250px, 80vw);
    }
    
    video {
        max-height: 200px;
    }
}

/* Extra small devices */
@media (max-width: 480px) {
    h1 {
        font-size: 1.1rem;
        padding: 0.75rem 1rem;
    }
    
    .messages {
        height: calc(100vh - 180px);
        padding: 0.75rem;
    }
    
    .message {
        max-width: 90%;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    form {
        padding: 0.75rem;
        gap: 0.5rem;
    }
    
    textarea {
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
    }
    
    button {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .file-input-label {
        width: 40px;
        height: 40px;
        font-size: 1.1rem;
    }
    
    img, video {
        max-width: min(200px, 75vw);
    }
}

/* Landscape mobile */
@media (max-height: 500px) and (orientation: landscape) {
    .messages {
        height: calc(100vh - 140px);
    }
    
    h1 {
        padding: 0.5rem 1rem;
        font-size: 1.1rem;
    }
    
    form {
        padding: 0.5rem;
    }
}

/* Focus and accessibility improvements */
@media (max-width: 768px) {
    button:focus,
    textarea:focus,
    .file-input-label:focus {
        outline: 2px solid #872657;
        outline-offset: 2px;
    }
    
    /* Smooth scrolling for messages */
    .messages {
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Better tap targets */
    button,
    .file-input-label {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    @media (max-width: 768px) {
        body {
            background: #1a1a1a;
        }
        
        h1 {
            background: #2d2d2d;
            color: #ff69b4;
        }
        
        .messages {
            background: #1a1a1a;
        }
        
        .received {
            background: #2d2d2d;
            color: #fff;
        }
        
        form {
            background: #2d2d2d;
            border-top-color: #333;
        }
        
        textarea {
            background: #1a1a1a;
            color: #fff;
            border-color: #333;
        }
        
        textarea:focus {
            border-color: #ff69b4;
        }
    }
}
 
</style>

</head>
<body>
<div class="container">
<?php include 'sidebar.php'; ?>
<main>
<h1>Chat with <?= htmlspecialchars($partner['name']) ?></h1>
<div class="messages" id="messages">
<?php if (!$messages): ?>
<p>No messages yet. Say hi!</p>
<?php else: ?>
<?php foreach ($messages as $msg): ?>
<div class="message <?= $msg['sender_id']===$loggedInUser?'sent':'received' ?>">
    <?php if (!empty($msg['content'])): ?>
        <?= nl2br(htmlspecialchars($msg['content'])) ?><br>
    <?php endif; ?>
    <?php if (!empty($msg['media_path'])): ?>
        <?php
        $ext = strtolower(pathinfo($msg['media_path'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4','webm','ogg'])): ?>
            <video controls>
                <source src="<?= htmlspecialchars($msg['media_path']) ?>" type="video/<?= $ext ?>">
                Your browser does not support the video tag.
            </video>
        <?php else: ?>
            <img src="<?= htmlspecialchars($msg['media_path']) ?>" alt="Sent media">
        <?php endif; ?>
    <?php endif; ?>
    <small style="font-size:0.7rem;color:#666;">
        <?= date("M j, H:i", strtotime($msg['created_at'])) ?>
    </small>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<form method="post" action="chat.php?user=<?= $chatUser ?>" enctype="multipart/form-data">
    <textarea name="message" rows="2" placeholder="Type your message..."></textarea>
    <input type="file" name="media" accept="image/*,video/*">
    <button type="submit">Send</button>
</form>
</main>
</div>
<script>
const messagesDiv = document.getElementById('messages');
messagesDiv.scrollTop = messagesDiv.scrollHeight;
</script>
</body>
</html>
