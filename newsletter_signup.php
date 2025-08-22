<?php
// connect to DB
require 'db.php'; // Your DB connection (must set $conn as MySQLi object)

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color:red;'>❌ Invalid email format.</p>";
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO newsletter_subscribers (email) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $email);

    try {
        mysqli_stmt_execute($stmt);
        echo "<p style='color:green;'>✅ Thanks for subscribing!</p>";
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { 
            echo "<p style='color:orange;'>⚠️ You're already subscribed with this email.</p>";
        } else {
            echo "<p style='color:red;'>❌ Something went wrong. Please try again.</p>";
        }
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
