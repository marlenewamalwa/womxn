<?php
session_start();
require 'db.php';

$sender_id = $_SESSION['user_id']; // currently logged-in user

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $content = $_POST['content'];

    // Handle file upload
    $media_path = NULL;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'message_uploads/'; // make sure this folder exists and is writable
        $filename = basename($_FILES['media']['name']);
        $targetFile = $uploadDir . time() . '_' . $filename;

        if (move_uploaded_file($_FILES['media']['tmp_name'], $targetFile)) {
            $media_path = $targetFile;
        } else {
            echo "Failed to upload file.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, media) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $content, $media_path);
    $stmt->execute();
    $stmt->close();

    echo "Message sent!";
}
?>

<form method="POST" enctype="multipart/form-data">
  <label for="receiver">Send to (user id):</label>
  <input type="number" name="receiver_id" id="receiver" required><br>

  <textarea name="content" placeholder="Type your message..." required></textarea><br>

  <label for="media">Attach image/video:</label>
  <input type="file" name="media" id="media" accept="image/*,video/*"><br>

  <button type="submit">Send Message</button>
</form>
