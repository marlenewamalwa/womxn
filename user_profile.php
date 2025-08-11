<?php
// user_profile.php
require 'db.php'; // Your DB connection

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    die('Invalid profile');
}

// Fetch user details
$stmt = $pdo->prepare("SELECT name, pronouns, profile_pic, bio, location FROM members WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    die('Member not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($member['name']) ?> - Profile</title>
    <link rel="stylesheet" href="styles.css"> <!-- your main CSS -->
</head>
<body>
    <div class="profile-page">
        <div class="profile-header">
            <img src="<?= htmlspecialchars($member['profile_pic'] ?: 'uploads/default.jpeg') ?>" alt="Profile Picture" class="profile-pic">
            <h1><?= htmlspecialchars($member['name']) ?></h1>
            <p class="pronouns"><?= htmlspecialchars($member['pronouns']) ?></p>
        </div>

        <div class="profile-details">
            <p><strong>Location:</strong> <?= htmlspecialchars($member['location'] ?: 'Not specified') ?></p>
            <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($member['bio'] ?: 'No bio available')) ?></p>
        </div>

        <div class="profile-actions">
            <a href="community.php" class="btn">Back to Community</a>
        </div>
    </div>
</body>
</html>
