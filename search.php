<?php
session_start();
require 'db.php';

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (!empty($keyword)) {
    $searchTerm = "%$keyword%";
    $stmt = $conn->prepare("SELECT * FROM posts WHERE content LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
</head>
<body>
<?php include 'header.php'; ?>

<h2>Search results for: <?= htmlspecialchars($keyword) ?></h2>

<?php if(empty($results)): ?>
    <p>No posts found.</p>
<?php else: ?>
    <ul>
    <?php foreach($results as $post): ?>
        <li><?= htmlspecialchars($post['content']) ?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
</body>
</html>
