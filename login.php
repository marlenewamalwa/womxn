<?php
session_start();
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = "Both fields are required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                header("Location: profile.php");
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
/* Modern Login Form CSS with Lesbian Colors */
:root {
  --lesbian-orange: #D62900;
  --lesbian-light-orange: #FF9B56;
  --lesbian-white: #FFFFFF;
  --lesbian-light-pink: #D462A6;
  --lesbian-dark-pink: #A50062;
  --text-dark: #1a1a1a;
  --text-gray: #6b7280;
  --bg-light: #fef7ff;
  --error-red: #dc2626;
  --success-green: #16a34a;
  --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
  --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.12);
  --border-radius: 16px;
}

* {
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  padding: 1rem;
  background: var(--bg-light);
  color: var(--text-dark);
  line-height: 1.6;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0;
}

.login-box {
  background: var(--lesbian-white);
  padding: 3rem;
  max-width: 420px;
  width: 100%;
  margin: 0 auto;
  border-radius: 24px;
  box-shadow: var(--shadow-medium);
  position: relative;
  overflow: hidden;
}

/* Decorative top border */
.login-box::before {
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

.login-box h1, .login-box h2 {
  text-align: center;
  color: var(--lesbian-dark-pink);
  margin-bottom: 2rem;
  font-size: 1.75rem;
  font-weight: 700;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-dark);
  font-weight: 500;
  font-size: 0.95rem;
}

input {
  width: 100%;
  padding: 1rem;
  margin: 0;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  font-family: inherit;
  transition: all 0.3s ease;
  background: var(--lesbian-white);
  color: var(--text-dark);
}

input:focus {
  outline: none;
  border-color: var(--lesbian-light-pink);
  box-shadow: 0 0 0 4px rgba(212, 98, 166, 0.1);
  transform: translateY(-1px);
}

input:hover {
  border-color: var(--lesbian-light-orange);
}

input::placeholder {
  color: var(--text-gray);
  font-size: 0.95rem;
}

/* Error state */
input.error {
  border-color: var(--error-red);
  background-color: #fef2f2;
}

input.error:focus {
  border-color: var(--error-red);
  box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
}

button {
  width: 100%;
  padding: 1rem 1.5rem;
  background: var(--lesbian-light-pink);
  color: var(--lesbian-white);
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 1rem;
  position: relative;
  overflow: hidden;
}

button:hover {
  background: var(--lesbian-dark-pink);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(165, 0, 98, 0.3);
}

button:active {
  transform: translateY(0);
}

button:focus {
  outline: none;
  box-shadow: 0 0 0 4px rgba(212, 98, 166, 0.3);
}

/* Loading state */
button:disabled {
  background: var(--text-gray);
  cursor: not-allowed;
  transform: none;
}

button:disabled:hover {
  transform: none;
  box-shadow: none;
}

/* Secondary button */
.btn-secondary {
  background: transparent;
  color: var(--lesbian-dark-pink);
  border: 2px solid var(--lesbian-light-pink);
  margin-top: 0.75rem;
}

.btn-secondary:hover {
  background: var(--lesbian-light-pink);
  color: var(--lesbian-white);
  border-color: var(--lesbian-light-pink);
}

/* Error messages */
ul {
  color: var(--error-red);
  padding-left: 1.25rem;
  margin: 1rem 0;
  background: #fef2f2;
  padding: 1rem 1rem 1rem 2.5rem;
  border-radius: 8px;
  border-left: 4px solid var(--error-red);
  font-size: 0.9rem;
}

ul li {
  margin-bottom: 0.25rem;
}

/* Success messages */
.success-message {
  color: var(--success-green);
  background: #f0fdf4;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--success-green);
  margin: 1rem 0;
  font-size: 0.95rem;
}

/* Links */
a {
  color: var(--lesbian-dark-pink);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

a:hover {
  color: var(--lesbian-orange);
  text-decoration: underline;
}

.text-center {
  text-align: center;
  margin-top: 1.5rem;
  color: var(--text-gray);
  font-size: 0.95rem;
}

/* Checkbox styling */
input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
  transform: scale(1.1);
  accent-color: var(--lesbian-light-pink);
}

.checkbox-group {
  display: flex;
  align-items: center;
  margin: 1rem 0;
}

.checkbox-group label {
  margin: 0;
  margin-left: 0.5rem;
  font-weight: 400;
}

/* Mobile Optimization */
@media (max-width: 768px) {
  body {
    padding: 0.5rem;
    align-items: flex-start;
    padding-top: 2rem;
  }
  
  .login-box {
    padding: 2rem;
    margin: 0;
    border-radius: 16px;
    max-width: 100%;
  }
  
  .login-box h1, .login-box h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  input {
    padding: 0.875rem;
    font-size: 16px; /* Prevents zoom on iOS */
  }
  
  button {
    padding: 0.875rem 1.25rem;
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .login-box {
    padding: 1.5rem;
    border-radius: 12px;
  }
  
  .login-box h1, .login-box h2 {
    font-size: 1.375rem;
  }
  
  .form-group {
    margin-bottom: 1.25rem;
  }
}

/* Landscape mobile */
@media (max-height: 500px) and (orientation: landscape) {
  body {
    padding-top: 1rem;
    align-items: flex-start;
  }
  
  .login-box {
    padding: 1.5rem;
  }
  
  .login-box h1, .login-box h2 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
}

/* Focus ring for accessibility */
@media (prefers-reduced-motion: no-preference) {
  input, button {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  input {
    border-width: 2px;
  }
  
  input:focus {
    border-width: 3px;
  }
  
  button {
    border: 2px solid var(--lesbian-dark-pink);
  }
}

/* Light theme variations */
.light-variant {
  --bg-light: #fdf2f8;
  --lesbian-white: #ffffff;
  --text-dark: #1f2937;
  --text-gray: #6b7280;
}

/* Alternative light backgrounds */
.alt-bg-1 {
  background: linear-gradient(135deg, #fdf2f8 0%, #fef7ff 100%);
}

.alt-bg-2 {
  background: linear-gradient(135deg, #fff1f2 0%, #fef7ff 100%);
}

/* Animation for form appearance */
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

.login-box {
  animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Loading spinner for button */
.btn-loading::after {
  content: '';
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  display: inline-block;
  margin-left: 0.5rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
</head>
<body>
  <div class="login-box">
    <h2>Login</h2>

    <?php if ($errors): ?>
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Password:</label>
      <input type="password" name="password" required>

      <button type="submit">Log In</button>
    <p>Don't have an account? <a href="signup.php">Register here</a></p>
    <!-- Load Google Identity Services -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<div id="g_id_onload"
     data-client_id="422877465316-q4ops1t2mmnovk0slcpfprgdso5gbp0s.apps.googleusercontent.com"
     data-login_uri="http://localhost/womxn/google-callback.php"
     data-auto_prompt="false">
</div>

<div class="g_id_signin"
     data-type="standard"
     data-shape="rectangular"
     data-theme="outline"
     data-text="signin_with"
     data-size="large">
</div>
      </form>
  </div>
</body>
</html>
