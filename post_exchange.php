<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = mysqli_real_escape_string($conn, $_POST['type'] ?? '');
    $title = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $category = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
    $location = mysqli_real_escape_string($conn, trim($_POST['location'] ?? ''));
    $payment = mysqli_real_escape_string($conn, trim($_POST['payment'] ?? ''));
    $contact_method = mysqli_real_escape_string($conn, $_POST['contact_method'] ?? 'dm');

    if ($type && $title && $description) {
        $sql = "INSERT INTO exchange_listings 
                (user_id, type, title, description, category, location, payment, contact_method) 
                VALUES ('{$_SESSION['user_id']}', '$type', '$title', '$description', '$category', '$location', '$payment', '$contact_method')";
 if (mysqli_query($conn, $sql)) {
        echo "<script>
            alert('Your listing has been posted!');
            window.location.href = 'exchange.php';
        </script>";
        exit;
    } else {
        $error = addslashes(mysqli_error($conn));
        echo "<script>
            alert('Something went wrong: $error');
            window.history.back();
        </script>";
        exit;
    }
    
    }}
    

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Post an Exchange Listing</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet" />
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
  background-color: #ffe6f0;
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
}
        form { display: flex; 
            flex-direction: column; 
            gap: 10px; }
        label { font-weight: bold; }
        input, select, textarea { padding: 8px; font-size: 14px; width: 100%; }
        button { padding: 10px; background: #d63384; color: white; border: none; cursor: pointer; }
        button:hover { background: #b0276e; }
        .msg { padding: 10px; background: #eee; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main>
   
<h2>Skill Exchange Listing</h2>

<?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">
    <label>Type *</label>
    <select name="type" required>
        <option value="">-- Select --</option>
        <option value="Skill_Offer">Skill Offer</option>
        <option value="Skill_Request">Skill Request</option>
       
    </select>

    <label>Title *</label>
    <input type="text" name="title" required>

    <label>Description *</label>
    <textarea name="description" rows="4" required></textarea>

    <label>Category</label>
    <input type="text" name="category" placeholder="e.g. Tech, Creative, Writing">

    <label>Location</label>
    <input type="text" name="location" placeholder="e.g. Nairobi, Remote">

    <label>Payment / Terms</label>
    <input type="text" name="payment" placeholder="e.g. Ksh 2000, skill swap, volunteer">

    <label>Contact Method</label>
    <select name="contact_method">
        <option value="dm">Direct Message</option>
        <option value="email">Email</option>
    </select>

    <button type="submit">Post Listing</button>
</form>
</main>
</div>

</body>
</html>
