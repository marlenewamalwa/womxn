<?php
session_start();
require_once 'db.php';

// Google sends back an ID token via POST
if (isset($_POST['credential'])) {
    $id_token = $_POST['credential'];

    // Verify token with Google
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['email'])) {
        $email = $data['email'];
        $name  = $data['name'] ?? 'Google User';

        // Check if user already exists
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            // Existing user → log in
            $stmt->bind_result($id, $dbName);
            $stmt->fetch();

            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $dbName;

        } else {
            // New user → create account with provider=google
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, provider) VALUES (?, ?, NULL, 'google')");
            $stmt->bind_param("ss", $name, $email);
            $stmt->execute();
            $newId = $stmt->insert_id;

            $_SESSION['user_id']   = $newId;
            $_SESSION['user_name'] = $name;
        }

        $stmt->close();
        header("Location: profile.php");
        exit();
    } else {
        echo "Google login failed.";
    }
} else {
    echo "No credential received.";
}
?>
