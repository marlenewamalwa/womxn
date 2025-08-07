<?php
session_start();
require_once 'db.php';

// Fetch all users
$sql = "SELECT name, pronouns, profile_pic FROM users ORDER BY name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>WOMXN Community</title>
  <link rel="stylesheet" href="styles.css"> <!-- Link your main CSS -->
  <style>
    .community-container {
      margin-left: 220px; /* adjust if sidebar width changes */
      padding: 20px;
    }

    .member-card {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 15px;
      background-color: #222;
      padding: 15px;
      border-radius: 10px;
      color: #fff;
    }

    .member-card img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
    }

    .member-info p {
      margin: 3px 0;
    }

    .member-info .pronouns {
      font-size: 14px;
      color: #aaa;
    }
  </style>
</head>
<body>

<div class="community-container">
  <h2>WOMXN Community</h2>

  <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="member-card">
        <img src="<?= htmlspecialchars($row['profile_pic'] ?: 'uploads/default.png') ?>" alt="Profile">
        <div class="member-info">
          <p><strong><?= htmlspecialchars($row['name']) ?></strong></p>
          <p class="pronouns"><?= htmlspecialchars($row['pronouns']) ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No members yet.</p>
  <?php endif; ?>

</div>
</body>
</html>
