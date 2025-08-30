<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Listing not found.");

// Fetch listing
$stmt = $conn->prepare("SELECT * FROM exchange_listings WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) die("Listing not found or not yours.");

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $payment = trim($_POST['payment'] ?? '');
    $contact_method = $_POST['contact_method'] ?? 'dm';

    if ($type && $title && $description) {
        $stmt = $conn->prepare("UPDATE exchange_listings 
            SET type=?, title=?, description=?, category=?, location=?, payment=?, contact_method=? 
            WHERE id=? AND user_id=?");
        $stmt->bind_param("sssssssii", $type, $title, $description, $category, $location, $payment, $contact_method, $id, $_SESSION['user_id']);
        $stmt->execute();

        header("Location: exchange.php");
        exit;
    }
}
?>
<doctype html>
<html>  
<head>
  <title>Edit Listing</title>
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
form select, form textarea {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 16px;
  box-sizing: border-box;
}
form input[type="text"]:focus,



form select:focus, form textarea:focus {
  border-color: #ff4d88;
  outline: none;
}                       

form button {
  background: #ff4d88;
  color: #fff;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s ease;
}
form button:hover {
  background: #e63971;
}
form a {
  display: inline-block;
  margin-top: 10px;
  color: #ff4d88;
  text-decoration: none;
}
form a:hover {
  text-decoration: underline;
}
   </style>
<h2>Edit Listing</h2>
<form method="POST">
    <label>Type *</label>
    <select name="type" required>
        <option value="">-- Select --</option>
        <option value="Skill_Offer" <?= $listing['type'] === 'Skill_Offer' ? 'selected' : '' ?>>Skill Offer</option>
        <option value="Skill_Request" <?= $listing['type'] === 'Skill_Request' ? 'selected' : '' ?>>Skill Request</option>
    </select>

    <label>Title *</label>
    <input type="text" name="title" value="<?= htmlspecialchars($listing['title']) ?>" required>

    <label>Description *</label>
    <textarea name="description" rows="4" required><?= htmlspecialchars($listing['description']) ?></textarea>

    <label>Category</label>
    <input type="text" name="category" value="<?= htmlspecialchars($listing['category']) ?>">

    <label>Location</label>
    <input type="text" name="location" value="<?= htmlspecialchars($listing['location']) ?>">

    <label>Payment / Terms</label>
    <input type="text" name="payment" value="<?= htmlspecialchars($listing['payment']) ?>">

    <label>Contact Method</label>
    <select name="contact_method">
        <option value="dm" <?= $listing['contact_method'] === 'dm' ? 'selected' : '' ?>>Direct Message</option>
        <option value="email" <?= $listing['contact_method'] === 'email' ? 'selected' : '' ?>>Email</option>
    </select>

    <button type="submit">Update Listing</button>
    <a href="exchange.php">Cancel</a>
</form>
