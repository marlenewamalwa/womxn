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


  $stmt = $conn->prepare("INSERT INTO events 
    (title, description, event_date, event_time, location, image, ticket_link, user_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssi", 
    $title, 
    $description, 
    $event_date, 
    $event_time, 
    $location, 
    $imageName, 
    $ticket_link, 
    $user_id
);

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
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    input, textarea, button {
      width: 100%;
      margin-bottom: 15px;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }

    button {
      background: #ff4d88;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      border: none;
      transition: 0.3s;
    }

    button:hover {
      background: #e63971;
    }
    /* Mobile adjustments */
    @media (max-width: 600px) {
      form {
        margin: 10px;
        padding: 15px;
      }

      input, textarea {
        font-size: 14px;
        padding: 10px;
      }
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
