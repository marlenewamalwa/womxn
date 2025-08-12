<?php
session_start();
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$pic = 'uploads/default.jpeg';

// Get user profile pic if logged in
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($profilePic);
    if ($stmt->fetch()) {
        $pic = $profilePic ?: $pic;
    }
    $stmt->close();
}

// Fetch posts with user info
$sql = "SELECT posts.*, users.name, users.profile_pic 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC";
$result = $conn->query($sql);

$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}


if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
    $q = mysqli_real_escape_string($conn, $q);

    // Example: search in posts table
    $sql = "SELECT * FROM posts 
            WHERE content LIKE '%$q%' 
          
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h2>Search results for: " . htmlspecialchars($q) . "</h2>";
        while ($row = $result->fetch_assoc()) {
            echo "<div>";
            echo "<h3>" . htmlspecialchars($row['content']) . "</h3>";
            
            echo "</div>";
        }
    } else {
        echo "<p>No results found for <strong>" . htmlspecialchars($q) . "</strong>.</p>";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>WOMXN | Feed</title>
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
      background-color: #ffe6f0;
      color: #2b2b2b;
      margin: 0;
    }
    .container {
      display: flex;
      width: 100%;
    }
   
    main {
      margin-left: 340px;
      padding: 2rem;
      flex: 1;
    }
    .search-bar {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;

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

    .post-form, .post {
      background: white;
      border-radius: 10px;
      max-width: 600px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .post img {
       width: 60%;
      height: 60%;
      border-radius: 8px;
      margin-top: 10px;
    }
    .post .meta {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }
    .post .meta img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
    }
    textarea {
      width: 100%;
      padding: 10px;
      font-size: 15px;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main>
<form method="GET" action="" class="search-bar">
  <input type="text" name="q" placeholder="Search..." required>
  <button type="submit">Search</button>
</form>

      <?php if ($isLoggedIn): ?>
        <div class="post-form">
          <form action="create_post.php" method="POST" enctype="multipart/form-data">
            <textarea name="content" placeholder="What's on your mind?" required></textarea><br>
            <input type="file" name="image" accept="image/*"><br><br>
            <button type="submit">Post</button>
          </form>
        </div>
      <?php endif; ?>

      <?php foreach ($posts as $post): ?>
        <div class="post">
          <div class="meta">
            <img src="<?= htmlspecialchars($post['profile_pic'] ?? 'uploads/default.jpeg') ?>" alt="user">
            <strong><?= htmlspecialchars($post['name']) ?></strong>
            <span style="color: gray; font-size: 12px;"><?= htmlspecialchars($post['created_at']) ?></span>
          </div>
          <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
          <?php if (!empty($post['image'])): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Post Image">
          <?php endif; ?>
          <?php
// Fetch comments for this post
$post_id = $post['id'];
$stmt = $conn->prepare("SELECT comments.comment_text, comments.created_at, users.name 
                        FROM comments 
                        JOIN users ON comments.user_id = users.id 
                        WHERE comments.post_id = ? 
                        ORDER BY comments.created_at ASC");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments_result = $stmt->get_result();
?>

<div class="comments-section" style="margin-top: 15px; padding-left: 20px; border-left: 2px solid #ccc;">
  <h4>Comments:</h4>
  <?php if ($comments_result->num_rows > 0): ?>
    <?php while ($comment = $comments_result->fetch_assoc()): ?>
      <div class="comment" style="margin-bottom: 10px;">
        <strong><?= htmlspecialchars($comment['name']) ?></strong>
        <span style="color: gray; font-size: 12px;">(<?= htmlspecialchars($comment['created_at']) ?>)</span>
        <p><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No comments yet. Be the first to comment!</p>
  <?php endif; ?>

  <?php if ($isLoggedIn): ?>
    <form action="submit_comment.php" method="POST" style="margin-top: 10px;">
      <input type="hidden" name="post_id" value="<?= $post_id ?>">
      <textarea name="comment_text" placeholder="Write your comment..." required style="width: 100%; height: 60px;"></textarea><br>
      <button type="submit" style="background:#872657; color:#fff; padding:5px 10px; border:none; border-radius:5px; cursor:pointer;">Post Comment</button>
    </form>
  <?php else: ?>
    <p><em>Log in to leave a comment.</em></p>
  <?php endif; ?>
</div>

<?php $stmt->close(); ?>

        </div>
      <?php endforeach; ?>

      
    </main>
  </div>
</body>
</html>
