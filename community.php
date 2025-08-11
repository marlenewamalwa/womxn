<?php
session_start();
require_once 'db.php';

// Check login state
$isLoggedIn = isset($_SESSION['user_id']);
$pic = $isLoggedIn ? $_SESSION['profile_pic'] ?? 'uploads/default.jpeg' : 'uploads/default.jpeg';

$members = [];
$q = '';

// Default query
$sql = "SELECT id, name, pronouns, profile_pic FROM users ORDER BY name ASC";

// Handle search query
if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
    $q = mysqli_real_escape_string($conn, $q);

    $sql = "SELECT id, name, pronouns, profile_pic FROM users 
            WHERE name LIKE '%$q%' 
               OR pronouns LIKE '%$q%' 
               OR email LIKE '%$q%' 
            ORDER BY created_at DESC";
}

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>WOMXN Community</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet" />
     
  <style>
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
    body {
      font-family: 'Poppins', sans-serif;
      display: flex;
      background-color: #fffafc;
      color: #2b2b2b;
    }

    .container {
      display: flex;
      width: 100%;
    }

    .community-container {
      margin-left: 240px;
      padding: 2rem;
      flex: 1;
    }
    .search-bar {
  display: flex;
  justify-content: center;
  margin: 20px 0;
}

.search-bar input {
  padding: 8px;
  width: 300px;
  border: 1px solid #ccc;
  border-radius: 5px 0 0 5px;
  outline: none;
}

.search-bar button {
  padding: 8px 15px;
  background: #872657;
  color: white;
  border: none;
  border-radius: 0 5px 5px 0;
  cursor: pointer;
}

.search-bar button:hover {
  background: #68212fff;
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
<div class="container">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>    

  <!-- Main content -->
  <div class="community-container">
      <form method="GET" action="" class="search-bar">
          <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($q) ?>" required>
          <button type="submit">Search</button>
      </form>

      <?php if ($q): ?>
          <h2>Search results for: <?= htmlspecialchars($q) ?></h2>
      <?php else: ?>
          <h2>WOMXN Community</h2>
      <?php endif; ?>

      <?php if (!empty($members)): ?>
          <?php foreach ($members as $row): ?>
              <a href="user_profile.php?id=<?= urlencode($row['id']) ?>" style="text-decoration:none; color:inherit;">
                  <div class="member-card">
                      <img src="<?= htmlspecialchars($row['profile_pic'] ?: 'uploads/default.jpeg') ?>" alt="Profile">
                      <div class="member-info">
                          <p><strong><?= htmlspecialchars($row['name']) ?></strong></p>
                          <p class="pronouns"><?= htmlspecialchars($row['pronouns']) ?></p>
                      </div>
                  </div>
              </a>
          <?php endforeach; ?>
      <?php else: ?>
          <p>No <?= $q ? 'results' : 'members' ?> found.</p>
      <?php endif; ?>
  </div>

</div>
</body>
</html>
