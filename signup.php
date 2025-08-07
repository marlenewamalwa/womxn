<?php
session_start();
require_once 'db.php'; // make sure this connects to your DB

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $pronouns = trim($_POST['pronouns'] ?? '');

    // Profile picture setup
    $profile_pic = 'uploads/default.jpeg'; // default
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $max_size = 2 * 1024 * 1024;

    // Validation
    if (!$name || !$email || !$password) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Email exists?
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Email is already registered.";
        }
        $stmt->close();
    }

    // Handle image upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $size = $file['size'];

        if (in_array($ext, $allowed_ext) && $size <= $max_size) {
            $newName = uniqid('pf_', true) . '.' . $ext;
            $uploadPath = 'uploads/' . $newName;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $profile_pic = $uploadPath;
            }
        }
    }

    // Register user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, pronouns, profile_pic) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashedPassword, $pronouns, $profile_pic);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "Something went wrong. Try again.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Sign Up</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f5f5f5;
    }

    .main-container {
      display: flex;
      min-height: 100vh;
      align-items: center;
      justify-content: center;
    }

    .signup-image {
      flex: 1;
      background: url('uploads/hearts.jpeg') no-repeat center center;
      background-size: cover;
      min-height: 100vh;
    }

    .signup-container {
      flex: 1;
      max-width: 500px;
      margin: auto;
      padding: 40px;
      background: #fff;
      border-radius: 0 12px 12px 0;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
    }

    .signup-container h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #222;
    }

    .signup-container form {
      display: flex;
      flex-direction: column;
    }

    .signup-container label {
      margin: 10px 0 5px;
      font-size: 14px;
      color: #444;
    }

    .signup-container input,
    .signup-container select {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      background: #f9f9f9;
      transition: 0.3s ease;
    }

    .signup-container input:focus,
    .signup-container select:focus {
      border-color: #6b1d45;
      background: #fff;
      outline: none;
    }

    .signup-container button {
      margin-top: 20px;
      padding: 12px;
      background: #872657;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .signup-container button:hover {
      background: #6b1d45;
    }

    .error-list {
      color: red;
      margin: 0 auto 20px;
      padding: 0;
      list-style: none;
    }
  </style>
</head>
<body>
  <div class="main-container">
    <div class="signup-image"></div>

    <div class="signup-container">
      <h2>Sign Up</h2>

      <?php if ($errors): ?>
        <ul class="error-list">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form action="signup.php" method="POST" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="pronouns">Pronouns:</label>
        <select id="pronouns" name="pronouns" required>
          <option value="">-- Select your pronouns --</option>
          <option value="she/her">She/Her</option>
          <option value="he/him">He/Him</option>
          <option value="they/them">They/Them</option>
          <option value="she/they">She/They</option>
          <option value="he/they">He/They</option>
          <option value="other">Other</option>
        </select>

        <label for="profile_pic">Upload Profile Picture:</label>
        <input type="file" name="profile_pic" id="profile_pic" accept="image/*">

        <button type="submit">Sign Up</button>
      </form>
    </div>
  </div>
</body>
</html>

