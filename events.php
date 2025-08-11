<?php
include 'db.php';
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$defaultPic = 'uploads/default.jpeg';
$pic = $isLoggedIn && !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : $defaultPic;

$searchTerm = '';
$whereClauses = [];
$orderBy = "ORDER BY event_date ASC";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchTerm = trim($_GET['search']);
    $words = explode(' ', $searchTerm);

    foreach ($words as $word) {
        $wordEscaped = mysqli_real_escape_string($conn, $word);
        $whereClauses[] = "(title LIKE '%$wordEscaped%' 
                        OR description LIKE '%$wordEscaped%' 
                        OR location LIKE '%$wordEscaped%')";
    }
}

$sql = "SELECT * FROM events";
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' OR ', $whereClauses);
}
$sql .= " $orderBy";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>WOMXN | Events</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet" />
    <style>
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
        .event-search {
    margin: 20px 0;
    display: flex;
    gap: 10px;
}
{
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.container {
  display: flex;
  width: 100%;
}


main {
  margin-left: 240px;
  padding: 2rem;
  flex: 1;
}
.event-search input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

.event-search button {
    padding: 8px 15px;
    background: #872657;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.event-search button:hover {
    background: #68212fff;
}

        .events-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .event-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: 0.3s;
        }
        .event-card:hover {
            transform: scale(1.02);
        }
        .event-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .event-info {
            padding: 15px;
        }
        .event-info h3 {
            margin: 0 0 10px;
        }
        .event-info p {
            font-size: 14px;
            color: #555;
        }
        .event-date {
            color: #c0392b;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .ticket-btn {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .ticket-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
<div class="container">
<?php include 'sidebar.php'; ?>

<main>
<h2>Upcoming Events</h2>

<form method="GET" action="events.php" class="event-search">
    <input type="text" name="search" placeholder="Search events..." value="<?= htmlspecialchars($searchTerm) ?>">
    <button type="submit">Search</button>
</form>

<div class="events-container">
<?php
$sql = "SELECT * FROM events ORDER BY event_date ASC";
$result = $conn->query($sql);

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
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>No upcoming events.</p>";
}
?>
</div>
</main>
</div>
</body>
</html>
