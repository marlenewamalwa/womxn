<?php
session_start();
require 'db.php';

$sender_id = $_SESSION['user_id']; // currently logged-in user

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $content);
    $stmt->execute();
    $stmt->close();

    echo "Message sent!";
}
?>
<form method="POST">
  <label for="receiver">Send to (user id):</label>
  <input type="number" name="receiver_id" id="receiver" required>
  <br>
  <textarea name="content" placeholder="Type your message..." required></textarea><br>
  <button type="submit">Send Message</button>
</form>
