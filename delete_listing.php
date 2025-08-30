<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("No listing selected.");

// Fetch listing
$stmt = $conn->prepare("SELECT * FROM exchange_listings WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) die("Listing not found or not yours.");

// Delete on confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare("DELETE FROM exchange_listings WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();

    header("Location: exchange.php");
    exit;
}
?>

<h2>Delete Listing</h2>
<p>Are you sure you want to delete this listing? This action cannot be undone.</p>

<div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
    <h3><?= htmlspecialchars($listing['title']) ?></h3>
    <p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>
</div>

<form method="POST">
    <button type="submit" style="background:red;color:#fff;padding:8px 12px;border:none;">Yes, Delete</button>
    <a href="exchange.php" style="margin-left:10px;">Cancel</a>
</form>
