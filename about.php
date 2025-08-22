<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$defaultPic = 'uploads/default.jpeg';
$pic = $isLoggedIn && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : $defaultPic;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LAVENDER | Queer Platform</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background: #fff;
      color: #333;
    }
    main {
      margin-left: 240px;
      padding: 2rem;
      margin-top: 60px; /* space for topbar */
    }
    .about {
      padding: 3rem 1.5rem;
      background: #fafafa;
    }
    .about-container {
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
    }
    .about h1 {
      font-size: 2.2rem;
      color: #872657;
      margin-bottom: 1rem;
    }
    .about p {
      font-size: 1.1rem;
      line-height: 1.6;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
<div class="container">
  <?php include 'sidebar.php'; ?>
  <?php include 'topbar.php'; ?>

  <main>
    <section class="about">
      <div class="about-container">
        <h1>About Lavender</h1>
        <p>
          <strong>Lavender</strong> is a safe, inclusive space built for queer women in Kenya 
          to connect, share, and grow together. Our mission is to highlight community stories, 
          showcase events, and create opportunities that celebrate identity, creativity, 
          and empowerment.
        </p>
        <p>
          Through Lavender, we’re nurturing a movement 
          that fosters visibility, support, and belonging. Whether you’re here to explore events, 
          find opportunities, or simply connect with others, Lavender is your space to feel seen, 
          heard, and valued.
        </p>
      </div>
    </section>
  </main>
</div>
</body>
</html>
