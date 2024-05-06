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
        // Password matches, generate a token
        $token = bin2hex(random_bytes(32)); // Generate a random token

        // Update the user's token in the database
        $updateStmt = $db->prepare("UPDATE users SET token = :token WHERE username = :username");
        $updateStmt->bindValue(':token', $token, SQLITE3_TEXT);
        $updateStmt->bindValue(':username', $username, SQLITE3_TEXT);
        $updateStmt->execute();

        $_SESSION["username"] = $username;
        $_SESSION["token"] = $token;
    } else {
        // Password does not match or username does not exist
        http_response_code(401); // Unauthorized
        echo json_encode(["error" => "Invalid username or password."]);
    }
} else {
    // Invalid request method or missing parameters
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Invalid request."]);
}
?>
