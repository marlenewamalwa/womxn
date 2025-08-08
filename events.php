<?php
session_start();
require_once 'db.php';

// Fetch all events, newest first
$sql = "SELECT e.*, u.name AS creator_name FROM events e 
        JOIN users u ON e.created_by = u.id 
        ORDER BY e.event_date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Upcoming Events</title>
  <style>
    .events-container {
      margin: 40px auto;
      max-width: 800px;
      padding: 20px;
    }

    .event-card {
      background: #222;
      color: #fff;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
    }

    .event-card img {
      width: 100%;
      max-height: 300px;
      object-fit: cover;
      border-radius: 10px;
      margin-top: 10px;
    }

    .event-meta {
      font-size: 14px;
      color: #ccc;
    }

    .ticket-btn {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 12px;
      background: #e91e63;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
    }

    .ticket-btn:hover {
      background: #c2185b;
    }
  </style>
</head>
<body>
  <div class="events-container">
    <h2>Upcoming Events</h2>

    <?php if ($result->num_rows > 0): ?>
      <?php while($event = $result->fetch_assoc()): ?>
        <div class="event-card">
          <h3><?= htmlspecialchars($event['title']) ?></h3>
          <p class="event-meta">
            <?= date("F j, Y", strtotime($event['event_date'])) ?>
            <?= $event['event_time'] ? 'at ' . htmlspecialchars($event['event_time']) : '' ?>
            • Posted by <?= htmlspecialchars($event['creator_name']) ?>
          </p>
          <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

          <?php if ($event['image']): ?>
            <img src="<?= htmlspecialchars($event['image']) ?>" alt="Event Image">
          <?php endif; ?>

          <?php if ($event['ticket_link']): ?>
            <a href="<?= htmlspecialchars($event['ticket_link']) ?>" target="_blank" class="ticket-btn">Get Tickets</a>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No events posted yet.</p>
    <?php endif; ?>
  </div>
</body>
</html>
