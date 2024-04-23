<?php
session_start();
$db = new SQLite3('users.db');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Retrieve the user's information from the database
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result && password_verify($password, $result["password_hash"])) {
        // Password matches, set session variables
        $_SESSION["username"] = $username;
        $_SESSION["logged_in"] = true;

        // You can redirect the user to a different page or echo a success message here
        header("Location: https://april.lexiqqq.com/term/stuff/");
    } else {
        // Password does not match or username does not exist
        http_response_code(401); // Unauthorized
        echo "Invalid username or password.";
    }
}
?>
