<?php
$host = 'localhost';
$db = 'womxn';
$user = 'root';
$pass = ''; // leave blank if no password on XAMPP

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
