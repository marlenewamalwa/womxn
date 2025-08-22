<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Post</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WOMXN | Queer Platform</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #d33b79;
      --primary-light: #f06292;
      --primary-hover: #b42e66;
      --bg-card: #fff;
      --border-radius: 10px;
      --border-radius-lg: 12px;
      --border-light: #eee;
      --border-color: #ddd;
      --text-light: #999;
      --text-muted: #666;
      --text-dark: #222;
      --shadow-sm: 0 2px 5px rgba(0,0,0,0.05);
      --shadow-md: 0 4px 10px rgba(0,0,0,0.08);
      --shadow-lg: 0 6px 16px rgba(0,0,0,0.12);
      --transition: all 0.2s ease;
    }

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
    /* Post Form */
    .post-form {
      background: var(--bg-card);
      border-radius: var(--border-radius-lg);
      padding: 2rem;
      margin: 0 auto;
      max-width: 600px;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border-light);
      transition: var(--transition);
      position: relative;
    }

    .post-form:hover {
      box-shadow: var(--shadow-lg);
      transform: translateY(-2px);
    }

    .post-form textarea {
      width: 100%;
      padding: 16px;
      border: 2px solid var(--border-color);
      border-radius: var(--border-radius);
      outline: none;
      font-size: 1rem;
      font-family: inherit;
      resize: vertical;
      min-height: 120px;
      transition: var(--transition);
      background: #fafbfc;
    }

    .post-form textarea:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(211,59,121,0.15);
      background: white;
    }

    .post-form textarea::placeholder {
      color: var(--text-light);
    }

    .form-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1rem;
      gap: 1rem;
    }

    .file-input-container {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .post-form input[type="file"] {
      display: none;
    }

    .file-label {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      background: var(--border-light);
      color: var(--text-muted);
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: var(--transition);
      font-size: 0.9rem;
    }

    .file-label:hover {
      background: var(--border-color);
      color: var(--text-dark);
    }

    .post-form button[type="submit"] {
      padding: 12px 24px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
      color: white;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-weight: 600;
      font-size: 1rem;
      transition: var(--transition);
      box-shadow: var(--shadow-sm);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .post-form button[type="submit"]:hover {
      background: linear-gradient(135deg, var(--primary-hover) 0%, #9a2a5a 100%);
      transform: translateY(-1px);
      box-shadow: var(--shadow-md);
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
      
  <div class="post-form">
    <form action="create_post.php" method="POST" enctype="multipart/form-data">
      <textarea name="content" placeholder="What's on your mind? Share your thoughts with the community..." required></textarea>
      <div class="form-actions">
        <div class="file-input-container">
          <input type="file" name="image" accept="image/*" id="image-upload">
          <label for="image-upload" class="file-label">
            <i class="fas fa-image"></i> Add Photo
          </label>
        </div>
        <button type="submit">
          <i class="fas fa-paper-plane"></i> Share Post
        </button>
      </div>
    </form>
  </div>
    </main>
</div>
</body>
</html>
