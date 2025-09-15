<?php
$host = 'localhost';
$db = 'womxn';
$user = 'root';
$pass = ''; // leave blank if no password on XAMPP

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// ✅ Force MySQL to use Nairobi timezone (+03:00)
$conn->query("SET time_zone = '+03:00'");
 

function getProfilePic() {
    $defaultPic = 'default.jpeg';

    if (isset($_SESSION['user_id']) && !empty($_SESSION['profile_pic'])) {
        return '/uploads/' . $_SESSION['profile_pic'];
    }

    return '/uploads/' . $defaultPic;
}
?>
