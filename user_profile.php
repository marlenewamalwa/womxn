<?php
// user_profile.php
session_start();
require 'db.php'; // Your DB connection (must set $conn as MySQLi object)

$loggedInUser = $_SESSION['user_id'] ?? 0;
if (!$loggedInUser) {
    die('You must be logged in to view profiles.');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    die('Invalid profile');
}

// Get the profile member info
$stmt = $conn->prepare("SELECT name, pronouns, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if (!$member) {
    die('Member not found');
}

// Fetch all users except logged-in for optional "Message" list
$stmt = $conn->prepare("SELECT id, name FROM users WHERE id != ?");
$stmt->bind_param("i", $loggedInUser);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... keep your head content ... -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($member['name']) ?> - Profile</title>
    <link rel="stylesheet" href="styles.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet" />
     <style>
        /* profile.css or add to styles.css */

/* General page layout */
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

    body {
      font-family: 'Poppins', sans-serif;
      display: flex;
      background-color: #ffe6f0;
      color: #2b2b2b;
      margin: 0;
    }
    .container {
      display: flex;
      width: 100%;
    }
   
    main {
      margin-left: 240px;
      padding: 2rem;
      flex: 1;
    }

/* Profile container */
.profile-page {
    max-width: 700px;
    margin: 40px auto;
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.08);
}

/* Header section */
.profile-header {
    text-align: center;
    margin-bottom: 30px;
}

.profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #872657;
    margin-bottom: 15px;
}

.profile-header h1 {
    font-size: 1.8rem;
    margin: 5px 0;
}

.pronouns {
    color: #888;
    font-size: 0.95rem;
}

/* Details section */
.profile-details {
    margin-bottom: 30px;
    line-height: 1.6;
}

.profile-details p {
    margin-bottom: 10px;
}

/* Button styling */
.btn {
    display: inline-block;
    background-color: #872657;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s;
}

.btn:hover {
    background-color: #68212f;
}

    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main>
            <div class="profile-page">
                <div class="profile-header">
                    <img src="<?= htmlspecialchars($member['profile_pic'] ?: 'uploads/default.jpeg') ?>" alt="Profile Picture" class="profile-pic">
                    <h1><?= htmlspecialchars($member['name']) ?></h1>
                    <p class="pronouns"><?= htmlspecialchars($member['pronouns']) ?></p>                 
                </div>
                <div class="profile-actions">
                    <?php if ($id !== $loggedInUser): ?>
    <a href="chat.php?user=<?= $id ?>" class="btn">Message</a>
<?php endif; ?>

                </div>
            </div>
               
        </main>
    </div>
</body>
</html>
