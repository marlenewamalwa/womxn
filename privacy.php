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
  <meta charset="UTF-8">
  <title>Privacy Policy | Lavender</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WOMXN | Queer Platform</title>
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
      margin-top: 60px;
    }
    .privacy {
      max-width: 800px;
      margin: 0 auto;
    }
    .privacy h1 {
      font-size: 2rem;
      color: #872657;
      margin-bottom: 1rem;
    }
    .privacy h2 {
      font-size: 1.3rem;
      margin-top: 1.5rem;
    }
    .privacy p {
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
    <section class="privacy">
      <h1>Privacy Policy</h1>
      <p>Your privacy and safety are our top priorities at <strong>Lavender</strong>. We understand the sensitivity of being part of this community and take every step to protect you.</p>
      
      <h2>1. Minimal Data</h2>
      <p>We only collect the information necessary to create your account (such as username and email). You may use a pseudonym instead of your real name.</p>
      
      <h2>2. Confidentiality</h2>
      <p>We will never share, sell, or disclose your information to third parties, governments, or organizations without your consent, except where legally required.</p>
      
      <h2>3. Security</h2>
      <p>Your data is stored securely with safeguards to prevent unauthorized access. We strongly encourage users to use secure passwords and avoid sharing account details.</p>
      
      <h2>4. Anonymity</h2>
      <p>You are free to participate without revealing personal details. Profile pictures, names, and locations are optional.</p>
      
      <h2>5. Your Control</h2>
      <p>You can request deletion of your account and data at any time by contacting our support team.</p>
      
      <h2>6. Awareness</h2>
      <p>We operate in a context where queer identity is not legally protected. For your safety, please avoid posting sensitive personal information that may put you at risk.</p>
    </section>
  </main>
</div>
</body>
</html>
