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
/* Modern Signup Form CSS with Lesbian Colors - Light Mode */
:root {
  --lesbian-orange: #D62900;
  --lesbian-light-orange: #FF9B56;
  --lesbian-white: #FFFFFF;
  --lesbian-light-pink: #D462A6;
  --lesbian-dark-pink: #A50062;
  --text-dark: #1f2937;
  --text-gray: #6b7280;
  --text-light: #9ca3af;
  --bg-light: #fdf2f8;
  --bg-alt: #fef7ff;
  --input-bg: #f9fafb;
  --border-light: #e5e7eb;
  --border-focus: #d1d5db;
  --error-red: #dc2626;
  --error-bg: #fef2f2;
  --success-green: #16a34a;
  --success-bg: #f0fdf4;
  --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
  --shadow-medium: 0 10px 35px rgba(0, 0, 0, 0.12);
  --border-radius: 16px;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
  box-sizing: border-box;
}

body {
  margin: 0;
  padding: 0;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--bg-light);
  color: var(--text-dark);
  line-height: 1.6;
}

.main-container {
  display: flex;
  min-height: 100vh;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.signup-container {
  flex: 1;
  max-width: 480px;
  width: 100%;
  margin: auto;
  padding: 3rem;
  background: var(--lesbian-white);
  border-radius: 24px;
  box-shadow: var(--shadow-medium);
  position: relative;
  overflow: hidden;
}

/* Decorative top border */
.signup-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, 
    var(--lesbian-orange), 
    var(--lesbian-light-orange), 
    var(--lesbian-white), 
    var(--lesbian-light-pink), 
    var(--lesbian-dark-pink)
  );
}

.signup-container h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: var(--lesbian-dark-pink);
  font-size: 2rem;
  font-weight: 700;
  font-family: 'Space Grotesk', sans-serif;
}

.signup-container form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.signup-container label {
  display: block;
  margin: 0 0 0.5rem 0;
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--text-dark);
}

.required {
  color: var(--lesbian-orange);
}

.signup-container input,
.signup-container select,
.signup-container textarea {
  width: 100%;
  padding: 1rem;
  border: 2px solid var(--border-light);
  border-radius: 12px;
  font-size: 1rem;
  font-family: inherit;
  background: var(--input-bg);
  color: var(--text-dark);
  transition: var(--transition);
}

.signup-container input:focus,
.signup-container select:focus,
.signup-container textarea:focus {
  border-color: var(--lesbian-light-pink);
  background: var(--lesbian-white);
  outline: none;
  box-shadow: 0 0 0 4px rgba(212, 98, 166, 0.1);
  transform: translateY(-1px);
}

.signup-container input:hover,
.signup-container select:hover,
.signup-container textarea:hover {
  border-color: var(--lesbian-light-orange);
}

.signup-container input::placeholder,
.signup-container textarea::placeholder {
  color: var(--text-light);
  font-size: 0.95rem;
}

/* Select styling */
.signup-container select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}

.signup-container select:focus {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23D462A6' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
}

/* Textarea specific */
.signup-container textarea {
  resize: vertical;
  min-height: 100px;
  max-height: 200px;
}

/* Button styling */
.signup-container button {
  margin-top: 1.5rem;
  padding: 1rem 2rem;
  background: var(--lesbian-light-pink);
  color: var(--lesbian-white);
  font-weight: 600;
  font-size: 1rem;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: var(--transition);
  font-family: inherit;
  position: relative;
  overflow: hidden;
}

.signup-container button:hover {
  background: var(--lesbian-dark-pink);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(165, 0, 98, 0.3);
}

.signup-container button:active {
  transform: translateY(0);
}

.signup-container button:focus {
  outline: none;
  box-shadow: 0 0 0 4px rgba(212, 98, 166, 0.3);
}

.signup-container button:disabled {
  background: var(--text-light);
  cursor: not-allowed;
  transform: none;
}

.signup-container button:disabled:hover {
  transform: none;
  box-shadow: none;
}

/* Error styling */
.error-list {
  color: var(--error-red);
  background: var(--error-bg);
  border: 1px solid #fecaca;
  border-left: 4px solid var(--error-red);
  border-radius: 8px;
  margin: 1rem 0;
  padding: 1rem 1rem 1rem 2.5rem;
  list-style: none;
  font-size: 0.9rem;
}

.error-list li {
  margin-bottom: 0.5rem;
}

.error-list li:last-child {
  margin-bottom: 0;
}

/* Input error state */
.input-error {
  border-color: var(--error-red) !important;
  background-color: var(--error-bg) !important;
}

.input-error:focus {
  border-color: var(--error-red) !important;
  box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1) !important;
}

/* Success messages */
.success-message {
  color: var(--success-green);
  background: var(--success-bg);
  border: 1px solid #bbf7d0;
  border-left: 4px solid var(--success-green);
  border-radius: 8px;
  margin: 1rem 0;
  padding: 1rem;
  font-size: 0.95rem;
}

/* Form grid for multiple columns */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* Checkbox and radio styling */
.checkbox-group,
.radio-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 1rem 0;
}

.signup-container input[type="checkbox"],
.signup-container input[type="radio"] {
  width: auto;
  margin: 0;
  transform: scale(1.2);
  accent-color: var(--lesbian-light-pink);
}

.checkbox-group label,
.radio-group label {
  margin: 0;
  font-weight: 400;
  cursor: pointer;
}

/* Links */
.signup-container a {
  color: var(--lesbian-dark-pink);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.signup-container a:hover {
  color: var(--lesbian-orange);
  text-decoration: underline;
}

.text-center {
  text-align: center;
  margin-top: 1.5rem;
  color: var(--text-gray);
  font-size: 0.95rem;
}

/* File upload styling */
.file-upload {
  position: relative;
  display: inline-block;
  cursor: pointer;
  width: 100%;
}

.file-upload input[type="file"] {
  position: absolute;
  opacity: 0;
  width: 0.1px;
  height: 0.1px;
}

.file-upload-label {
  display: block;
  padding: 1rem;
  border: 2px dashed var(--border-light);
  border-radius: 12px;
  text-align: center;
  color: var(--text-gray);
  background: var(--input-bg);
  transition: var(--transition);
  cursor: pointer;
}

.file-upload-label:hover,
.file-upload input:focus + .file-upload-label {
  border-color: var(--lesbian-light-pink);
  background: var(--lesbian-white);
  color: var(--lesbian-dark-pink);
}

/* Mobile Optimization */
@media (max-width: 768px) {
  .main-container {
    padding: 0.5rem;
    align-items: flex-start;
    padding-top: 2rem;
  }
  
  .signup-container {
    padding: 2rem;
    border-radius: 16px;
    margin: 0;
    max-width: 100%;
  }
  
  .signup-container h2 {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1.25rem;
  }
  
  .signup-container input,
  .signup-container select,
  .signup-container textarea {
    padding: 0.875rem;
    font-size: 16px; /* Prevents zoom on iOS */
  }
  
  .signup-container button {
    padding: 0.875rem 1.5rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 0;
  }
}

@media (max-width: 480px) {
  .signup-container {
    padding: 1.5rem;
    border-radius: 12px;
  }
  
  .signup-container h2 {
    font-size: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  .signup-container input,
  .signup-container select,
  .signup-container textarea {
    padding: 0.75rem;
  }
  
  .signup-container button {
    padding: 0.75rem 1.25rem;
    font-size: 0.95rem;
  }
}

/* Landscape mobile */
@media (max-height: 600px) and (orientation: landscape) {
  .main-container {
    padding-top: 1rem;
    align-items: flex-start;
  }
  
  .signup-container {
    padding: 1.5rem;
  }
  
  .signup-container h2 {
    font-size: 1.375rem;
    margin-bottom: 1rem;
  }
  
  .form-group {
    margin-bottom: 0.875rem;
  }
}

/* Loading animation for button */
.btn-loading {
  position: relative;
  color: transparent;
}

.btn-loading::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  top: 50%;
  left: 50%;
  margin-left: -10px;
  margin-top: -10px;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  color: white;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Form animation */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.signup-container {
  animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus ring for accessibility */
@media (prefers-reduced-motion: no-preference) {
  .signup-container input,
  .signup-container select,
  .signup-container textarea,
  .signup-container button {
    transition: var(--transition);
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .signup-container input,
  .signup-container select,
  .signup-container textarea {
    border-width: 2px;
  }
  
  .signup-container input:focus,
  .signup-container select:focus,
  .signup-container textarea:focus {
    border-width: 3px;
  }
  
  .signup-container button {
    border: 2px solid var(--lesbian-dark-pink);
  }
}

/* Password strength indicator */
.password-strength {
  margin-top: 0.5rem;
  height: 4px;
  background: var(--border-light);
  border-radius: 2px;
  overflow: hidden;
}

.password-strength-bar {
  height: 100%;
  transition: var(--transition);
  border-radius: 2px;
}

.strength-weak { background: var(--error-red); width: 33%; }
.strength-medium { background: var(--lesbian-light-orange); width: 66%; }
.strength-strong { background: var(--success-green); width: 100%; }

/* Tooltip styling */
.tooltip {
  position: relative;
  display: inline-block;
}

.tooltip-text {
  visibility: hidden;
  width: 200px;
  background: var(--text-dark);
  color: white;
  text-align: center;
  border-radius: 6px;
  padding: 8px;
  font-size: 0.8rem;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  margin-left: -100px;
  opacity: 0;
  transition: opacity 0.3s;
}

.tooltip:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}
</style>
</head>
<body>
  <div class="main-container">
  

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
        <p>Already have an account? <a href="login.php">Log in here</a></p>
      </form>
    </div>
  </div>
</body>
</html>

