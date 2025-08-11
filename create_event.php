<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = $_POST['title'];
  $description = $_POST['description'];
  $event_date = $_POST['event_date'];
  $event_time = $_POST['event_time'];
  $location = $_POST['location'];
  $ticket_link = $_POST['ticket_link'];
  $user_id = $_SESSION['user_id'];

  // Image upload
$imageName = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $uploadDir = 'post_uploads/';
    $originalName = basename($_FILES['image']['name']);
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $newName = uniqid('event_', true) . '.' . $extension;
    $targetPath = $uploadDir . $newName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imageName = $newName; // ✅ Save this into the DB
    }
}


  $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, image, ticket_link) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $title, $description, $event_date, $imageName, $ticket_link);
  $stmt->execute();
  $stmt->close();

  header("Location: events.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Create Event</title>
  <style>

    body {
      font-family: Poppins, sans-serif;
      background-color: #ffe6f0;
      margin: 0;
      padding: 0;
    }
    form {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
    }
    input, textarea {
      width: 80%;
      margin-bottom: 15px;
      padding: 10px;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <form action="create_event.php" method="POST" enctype="multipart/form-data">
    <h2>Create an Event</h2>
    <input type="text" name="title" placeholder="Event Title" required>
    <textarea name="description" placeholder="Event Description" required></textarea>
    <input type="date" name="event_date" required>
    <input type="time" name="event_time">
    <input type="text" name="location" placeholder="Location">
    <input type="url" name="ticket_link" placeholder="Ticket Link (optional)">
    <input type="file" name="image" accept="image/*">
    <button type="submit">Submit Event</button>
  </form>
</body>
</html>
