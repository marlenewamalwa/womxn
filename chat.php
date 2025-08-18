<?php
session_start();
require 'db.php'; // Your DB connection (mysqli)

$loggedInUser = $_SESSION['user_id'] ?? 0;
if (!$loggedInUser) {
    die('You must be logged in to use the chat.');
}

$chatUser = isset($_GET['user']) ? (int)$_GET['user'] : 0;
if ($chatUser === 0) {
    die('No user selected for chat.');
}

$stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $chatUser);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();
$stmt->close();

if (!$partner) {
    die('User not found.');
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');
    $filePath = null;

    // File upload handling
    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['image']['tmp_name'];
            $fileType = mime_content_type($fileTmp);
            $fileSize = $_FILES['image']['size'];

            if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
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

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $loggedInUser, $chatUser, $msg, $filePath);
    $stmt->execute();
    $stmt->close();

    header("Location: chat.php?user=$chatUser");
    exit;
}

// Fetch messages
$stmt = $conn->prepare("
    SELECT sender_id, receiver_id, content, file_path, created_at 
    FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC
");
$stmt->bind_param("iiii", $loggedInUser, $chatUser, $chatUser, $loggedInUser);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Chat with <?= htmlspecialchars($partner['name']) ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; display: flex; background-color: #fffafc; color: #2b2b2b; }
        .container { display: flex; width: 100%; }
        main { margin-left: 240px; padding: 2rem; flex: 1; }
        h1 { text-align: center; color: #872657; }
        .messages { border: 1px solid #ccc; height: 400px; overflow-y: scroll; padding: 10px; background: #fff; margin-bottom: 1rem; border-radius: 5px; }
        .message { margin-bottom: 10px; max-width: 70%; padding: 10px; border-radius: 10px; clear: both; }
        .sent { background: #f4c2c2; float: right; text-align: right; }
        .received { background: #d1b2d9; float: left; text-align: left; }
        form { display: flex; gap: 10px; }
        textarea { flex-grow: 1; resize: none; padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #872657; color: white; border: none; padding: 0 20px; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #68212f; }
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
                <div class="message <?= $msg['sender_id'] === $loggedInUser ? 'sent' : 'received' ?>">
                    <?php if (!empty($msg['content'])): ?>
                        <?= nl2br(htmlspecialchars($msg['content'])) ?><br>
                    <?php endif; ?>
                    <?php if (!empty($msg['file_path'])): ?>
                        <img src="<?= htmlspecialchars($msg['file_path']) ?>" alt="Sent image" style="max-width:200px; display:block; margin-top:5px;">
                    <?php endif; ?>
                    <small style="font-size: 0.7rem; color: #666;">
                        <?= date("M j, H:i", strtotime($msg['created_at'])) ?>
                    </small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <form method="post" action="chat.php?user=<?= $chatUser ?>" enctype="multipart/form-data">
        <textarea name="message" rows="2" placeholder="Type your message..."></textarea>
        <input type="file" name="image" accept="image/*">
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
