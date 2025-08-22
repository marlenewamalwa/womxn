<?php
include 'db.php';
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$defaultPic = 'uploads/default.jpeg';
$pic = $isLoggedIn && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : $defaultPic;

// -------------------- FILTERS --------------------
$whereParts = [];

// Location filter
if (!empty($_GET['location'])) {
    $loc = $conn->real_escape_string($_GET['location']);
    $whereParts[] = "location = '$loc'";
}

// Date filter
if (!empty($_GET['date_filter'])) {
    $dateFilter = $_GET['date_filter'];
    if ($dateFilter === "upcoming") {
        $whereParts[] = "event_date >= CURDATE()";
    } elseif ($dateFilter === "past") {
        $whereParts[] = "event_date < CURDATE()";
    } elseif ($dateFilter === "this_month") {
        $whereParts[] = "MONTH(event_date) = MONTH(CURDATE()) 
                         AND YEAR(event_date) = YEAR(CURDATE())";
    }
}

// Build WHERE only if filters exist
$whereClause = !empty($whereParts) ? "WHERE " . implode(" AND ", $whereParts) : "";

// -------------------- PAGINATION --------------------
$limit = 6; // events per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total events
$countSql = "SELECT COUNT(*) AS total FROM events $whereClause";
$countResult = $conn->query($countSql);
$totalEvents = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalEvents / $limit);

// Fetch events
$sql = "SELECT * FROM events $whereClause ORDER BY event_date ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// -------------------- DROPDOWNS --------------------
$locationsRes = $conn->query("SELECT DISTINCT location FROM events ORDER BY location ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>WOMXN | Events</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background:#fff; color:#2b2b2b; display:flex; }
        .container { display:flex; width:100%; }
        main { margin-left:240px; padding:2rem; flex:1; margin-top:60px; }
        .btn { background:#872657; color:white; padding:10px 20px; border-radius:5px; text-decoration:none; }
        .events-container { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:20px; margin-top:20px; }
        .event-card { background:white; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); transition:0.3s; }
        .event-card:hover { transform:scale(1.02); }
        .event-card img { width:100%; height:180px; object-fit:cover; border-top-left-radius:8px; border-top-right-radius:8px; }
        .event-info { padding:15px; }
        .event-date { color:#c0392b; font-weight:bold; margin-bottom:10px; }
        .ticket-btn { background:#e74c3c; color:white; padding:8px 12px; border-radius:5px; text-decoration:none; }
        .ticket-btn:hover { background:#c0392b; }
        .filters { margin:20px 0; display:flex; gap:10px; }
        .filters select { padding:8px; border:1px solid #ccc; border-radius:6px; }
        .filters button { background:#872657; color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; }
        .filters button:hover { background:#68212f; }
        .pagination { margin-top:20px; text-align:center; }
        .pagination a { display:inline-block; padding:8px 12px; margin:0 5px; border:1px solid #872657; border-radius:4px; text-decoration:none; color:#872657; }
        .pagination a.active, .pagination a:hover { background:#872657; color:white; }
    </style>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>
    <main>
        <a href="create_event.php" class="btn">Add an Event</a>

        <!-- Filters -->
        <form class="filters" method="GET" action="">
            <select name="location">
                <option value="">All Locations</option>
                <?php while($loc = $locationsRes->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($loc['location']) ?>" 
                        <?= (isset($_GET['location']) && $_GET['location']==$loc['location']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['location']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="date_filter">
                <option value="">Any Date</option>
                <option value="upcoming" <?= (isset($_GET['date_filter']) && $_GET['date_filter']=="upcoming") ? 'selected' : '' ?>>Upcoming</option>
                <option value="past" <?= (isset($_GET['date_filter']) && $_GET['date_filter']=="past") ? 'selected' : '' ?>>Past</option>
                <option value="this_month" <?= (isset($_GET['date_filter']) && $_GET['date_filter']=="this_month") ? 'selected' : '' ?>>This Month</option>
            </select>

            <button type="submit">Apply</button>
        </form>

        <!-- Events -->
        <div class="events-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='event-card'>";
                echo "<img src='post_uploads/" . htmlspecialchars($row['image']) . "' alt='Event Image'>";
                echo "<div class='event-info'>";
                echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                echo "<div class='event-date'>" . date("F j, Y", strtotime($row['event_date'])) . " at " . date("g:i A", strtotime($row['event_time'])) . "</div>";
                echo "<p>" . nl2br(htmlspecialchars(substr($row['description'], 0, 100))) . "...</p>";
                echo "<p><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
                if (!empty($row['ticket_link'])) {
                    echo "<a class='ticket-btn' href='" . htmlspecialchars($row['ticket_link']) . "' target='_blank'>Get Tickets</a>";
                }
                echo "</div></div>";
            }
        } else {
            echo "<p>No events found.</p>";
        }
        ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
        <?php
        if ($totalPages > 1) {
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? "active" : "";
                $queryStr = http_build_query(array_merge($_GET, ['page' => $i]));
                echo "<a class='$active' href='?{$queryStr}'>$i</a>";
            }
        }
        ?>
        </div>
    </main>
</div>
</body>
</html>
