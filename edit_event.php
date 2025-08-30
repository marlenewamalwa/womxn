<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Event not found.");

$stmt = $conn->prepare("SELECT * FROM events WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) die("Event not found or not yours.");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = $_POST['title'];
  $description = $_POST['description'];
  $event_date = $_POST['event_date'];
  $event_time = $_POST['event_time'];
  $location = $_POST['location'];
  $ticket_link = $_POST['ticket_link'];

  $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, location=?, ticket_link=? WHERE id=? AND user_id=?");
  $stmt->bind_param("ssssssii", $title, $description, $event_date, $event_time, $location, $ticket_link, $id, $_SESSION['user_id']);
  $stmt->execute();

  header("Location: events.php");
  exit;
}
?>
    <doctype html>
<html>
<head>
  <title>Edit Event</title>
   <style>
    body {
      background: #f0f2f5;
      font-family: 'Poppins', sans-serif;
      color: #333;
      margin: 0; padding: 0;
    }
    form {
  max-width: 500px;
  margin: 50px auto;
  padding: 25px;
  background-color: #f8f8f8;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  font-family: Arial, sans-serif;
}

form h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #333;
}

form input[type="text"],
form input[type="date"],
form input[type="time"],
form input[type="url"],
form textarea {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}

form input:focus,
form textarea:focus {
  border-color: #ff4d6d; /* accent color */
  outline: none;
}

form textarea {
  min-height: 100px;
  resize: vertical;
}

form button {
  width: 100%;
  padding: 12px;
  background: #ff4d6d;
  color: #fff;
  font-size: 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}

form button:hover {
  background: #e04360;
}

  </style>

<form method="POST">
  <h2>Edit Event</h2>
  <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>
  <textarea name="description"><?= htmlspecialchars($event['description']) ?></textarea>
  <input type="date" name="event_date" value="<?= $event['event_date'] ?>" required>
  <input type="time" name="event_time" value="<?= $event['event_time'] ?>">
  <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>">
  <input type="url" name="ticket_link" value="<?= htmlspecialchars($event['ticket_link']) ?>">
  <button type="submit">Update Event</button>
</form>
