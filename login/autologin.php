<?php
session_start();
$db = new SQLite3('users.db');

// Check if the session is set and contains the necessary tokens
if (isset($_SESSION["username"]) && isset($_SESSION["token"])) {
    $username = $_SESSION["username"];
    $sessionToken = $_SESSION["token"];

    // Retrieve the user's token from the database
    $stmt = $db->prepare("SELECT token FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result && $result["token"] === $sessionToken) {
        http_response_code(200); // User is logged in
    } else {
        http_response_code(404); // User is not logged in
        exit;
    }
} else {
    http_response_code(404); // User is not logged in
    exit;
}
?>
