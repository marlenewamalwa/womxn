<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$where = "WHERE status='open' AND type != 'job'";



// If type filter exists
if (!empty($_GET['type'])) {
    $type = mysqli_real_escape_string($conn, $_GET['type']);
    $where .= " AND type='$type'";
}


$sql = "SELECT e.*, u.name 
        FROM exchange_listings e
        JOIN users u ON e.user_id = u.id
        $where
        ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Skill & Job Exchange</title>
    <link rel="stylesheet" href="styles.css">
   <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
                 /* Global styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  display: flex;
  background-color: #ffffffff;
  color: #2b2b2b;
}

.container {
  display: flex;
  width: 100%;
}

/* Main content */
main {
  margin-left: 240px;
  padding: 2rem;
  flex: 1;
  min-height: 100vh;
  margin-top: 60px;  /* space for topbar */
}
        .listing-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px; /* space between cards */
}
.listing { 
            border: 1px solid #ddd; 
            background: #fff;
             padding: 15px;
             margin-top: 15px;  
            margin-bottom: 15px; 
            border-radius: 6px; }

        .listing h3 { 
            margin: 0 0 5px;
        margin-top: 15px;  
            margin-bottom: 15px;  }

        .listing small { 
            color: #666; }

        .btn { display: inline-block; 
            padding: 5px 5px; 
            font-size: 14px;
            background: #872657; 
            color: white; 
            text-decoration: none; 
            margin-top: 15px;  
            margin-bottom: 15px; 
            border-radius: 4px; }

        .btn:hover { background: #b0276e; }
        .hero { margin-bottom: 20px; }
        /* Filter & Search Form Styling */
form.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    background: #fff;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    width: 500px;
}

form.filter-form input,
form.filter-form select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    flex: 1;
    min-width: 150px;
}

form.filter-form button {
    background: #e63980; /* pinkish for WOMXN vibe */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s ease;
}

form.filter-form button:hover {
    background: #cc2e6c; /* slightly darker on hover */
}

/* Mobile friendly layout */
@media (max-width: 600px) {
    form.filter-form {
        flex-direction: column;
    }
    form.filter-form input,
    form.filter-form select,
    form.filter-form button {
        width: 100%;
    }
}

    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php'; ?>
<?php include 'topbar.php'; ?>
    <!-- Main Content -->
    <main>

<form method="GET" class="filter-form">
    <select name="type">
        <option value="">-- All Types --</option>
        <option value="skill_offer" <?php if(isset($_GET['type']) && $_GET['type']=='skill_offer') echo 'selected'; ?>>Skill Offer</option>
        <option value="skill_request" <?php if(isset($_GET['type']) && $_GET['type']=='skill_request') echo 'selected'; ?>>Skill Request</option>
    </select>

    <button type="submit">Filter</button>
</form>

<div class="hero">
       <?php if (isset($_SESSION['user_id'])): ?>
        <a class="btn" href="post_exchange.php">Post New Listing</a>
    <?php else: ?>
        <a class="btn" href="login.php">Login to Post</a>
    <?php endif; ?>
</div>

<div class="listing-container">
<?php if (mysqli_num_rows($result) > 0): ?>
    <?php while ($l = mysqli_fetch_assoc($result)): ?>
        <div class="listing">
            <?php
$type_labels = [
    'skill_offer' => 'Skill Offer',
    'skill_request' => 'Skill Request'
];
?>
<h3><?= isset($type_labels[$l['type']]) ? $type_labels[$l['type']] : htmlspecialchars($l['type']) ?></h3>

            
              <?php if (!empty($l['description'])): ?>
                <p><strong>Title:</strong> <?= htmlspecialchars($l['description']) ?></p>
            <?php endif; ?>
            <?php if (!empty($l['category'])): ?>
                <p><strong>Category:</strong> <?= htmlspecialchars($l['category']) ?></p>
            <?php endif; ?>
            <?php if (!empty($l['location'])): ?>
                <p><strong>Location:</strong> <?= htmlspecialchars($l['location']) ?></p>
            <?php endif; ?>
            <?php if (!empty($l['payment'])): ?>
                <p><strong>Payment:</strong> <?= htmlspecialchars($l['payment']) ?></p>
            <?php endif; ?>
            <p><strong>Posted by:</strong> <?= htmlspecialchars($l['name']) ?> on <?= date("M d, Y", strtotime($l['created_at'])) ?></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="btn" href="chat.php?user=<?= $l['user_id'] ?>">Message</a>
            <?php else: ?>
                <a class="btn" href="login.php">Login to Contact</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No listings available right now. <a href="post_exchange.php">Post the first one!</a></p>
<?php endif; ?>

<?php mysqli_close($conn); ?>
</div>
</main>
</div>
</body>
</html>
