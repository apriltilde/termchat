<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    http_response_code(401);
    exit;
}

// Get the message from the request body
$message = isset($_POST["message"]) ? $_POST["message"] : "";
$chatid = isset($_POST["chatid"]) ? $_POST["chatid"] : "";
// Get the username from the session
$username = $_SESSION["username"];

// Validate message
if (empty($message)) {
    http_response_code(400);
    echo "Error: Message is empty.";
    exit;
}

// Connect to the users database
$userDb = new SQLite3('../users.db');

// Prepare and execute the SQL query to select bgcolour and textcolour for the logged-in user
$userQuery = "SELECT bgcolour, textcolour FROM users WHERE username = :username";
$userStmt = $userDb->prepare($userQuery);
$userStmt->bindValue(':username', $username, SQLITE3_TEXT);
$userResult = $userStmt->execute();

// Check if there are any rows returned
if ($userResult) {
    $userRow = $userResult->fetchArray(SQLITE3_ASSOC);
    $bgcolour = $userRow['bgcolour'];
    $textcolour = $userRow['textcolour'];
} else {
    // Set default values if no data is retrieved or an error occurs
    $bgcolour = 'black';
    $textcolour = 'white';
}

// Connect to the msg database
$msgDb = new SQLite3('msg.db');
$chatsDb = new SQLite3('chats.db');

// Prepare and execute the SQL query to insert the message into the msg table
$stmt = $msgDb->prepare("INSERT INTO msg (chat, username, msg, bgcolour, textcolour) VALUES (:chatid, :username, :message, :bgcolour, :textcolour)");
$stmt->bindValue(':chatid', $chatid, SQLITE3_TEXT); // Assuming $chatid is already defined
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$stmt->bindValue(':message', $message, SQLITE3_TEXT);
$stmt->bindValue(':bgcolour', $bgcolour, SQLITE3_TEXT);
$stmt->bindValue(':textcolour', $textcolour, SQLITE3_TEXT);
$result = $stmt->execute();

$notifStmt = $chatsDb->prepare("UPDATE chats SET notif = notif + 1 WHERE chat = :chatid AND username != :username");
$notifStmt->bindValue(':chatid', $chatid, SQLITE3_TEXT);
$notifStmt->bindValue(':username', $username, SQLITE3_TEXT);
$notifResult = $notifStmt->execute();


if ($result) {
    http_response_code(200);
    echo "Message sent successfully.";
} else {
    http_response_code(500);
    echo "Error: Failed to send message.";
}
?>
