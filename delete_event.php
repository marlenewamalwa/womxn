<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("No event selected.");
}

// First, check if event belongs to the logged in user
$stmt = $conn->prepare("SELECT id FROM events WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found or not yours.");
}

// If user confirmed deletion
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare("DELETE FROM events WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();

    header("Location: events.php");
    exit;
}
?>

<h2>Delete Event</h2>
<p>Are you sure you want to delete this event? This action cannot be undone.</p>

<form method="POST">
    <button type="submit">Yes, Delete</button>
    <a href="events.php">Cancel</a>
</form>
