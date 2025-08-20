<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WOMXN Admin Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9fb;
      margin: 0;
      padding: 0;
    }
    header {
      background: #d63384;
      color: white;
      padding: 1rem;
      text-align: center;
    }
    nav {
      background: #343a40;
      width: 200px;
      height: 100vh;
      float: left;
      padding-top: 2rem;
    }
    nav a {
      display: block;
      color: white;
      padding: 10px;
      text-decoration: none;
    }
    nav a:hover {
      background: #495057;
    }
    main {
      margin-left: 200px;
      padding: 2rem;
    }
    .card {
      background: white;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    footer {
      text-align: center;
      padding: 1rem;
      margin-top: 2rem;
      color: #666;
    }
  </style>
</head>
<body>
  <header>
    <h1>WOMXN Admin Dashboard</h1>
  </header>

  <nav>
    <a href="admin.php?page=dashboard">Dashboard</a>
    <a href="admin.php?page=users">Manage Users</a>
    <a href="admin.php?page=events">Manage Events</a>
    <a href="admin.php?page=orgs">Manage Organizations</a>
  </nav>

  <main>
    <?php
    // Simple page routing
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

    if ($page == "dashboard") {
        echo "<div class='card'><h2>Welcome, Admin!</h2><p>Use the menu to manage WOMXN content.</p></div>";
    } elseif ($page == "users") {
        echo "<div class='card'><h2>Users</h2><p>Here you can view and manage users.</p></div>";
    } elseif ($page == "events") {
        echo "<div class='card'><h2>Events</h2><p>Here you can create, edit, or delete events.</p></div>";
    } elseif ($page == "orgs") {
        echo "<div class='card'><h2>Organizations</h2><p>Here you can manage organizations and partnerships.</p></div>";
    } else {
        echo "<div class='card'><h2>404</h2><p>Page not found.</p></div>";
    }
    ?>
  </main>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> WOMXN Admin Panel</p>
  </footer>
</body>
</html>
