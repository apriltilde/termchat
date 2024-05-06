<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    http_response_code(404);
    exit;
}

usleep(40000);

$chatid = $_GET['chatid'];

$userDb = new SQLite3('../users.db');

// Prepare and execute the SQL query to update the datetime for the logged-in user
$username = $_SESSION["username"];
$updateQuery = "UPDATE users SET last_active_time = datetime('now') WHERE username = :username";
$stmt = $userDb->prepare($updateQuery);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();

// Check if the update was successful
if ($result) {
    // Connect to the msg database
    $msgDb = new SQLite3('msg.db');

    $msgQuery = "SELECT * FROM msg WHERE chat = :chatid";
    $stmtMsg = $msgDb->prepare($msgQuery);
    $stmtMsg->bindValue(':chatid', $chatid, SQLITE3_TEXT);
    $msgResult = $stmtMsg->execute();
    // Check if there are any rows returned
    if ($msgResult) {
        $messages = [];
        // Fetch each row as an associative array and add it to the $messages array
        while ($row = $msgResult->fetchArray(SQLITE3_ASSOC)) {
            $messages[] = $row;
        }
        // Set the HTTP response code to 200 OK
        http_response_code(200);
        $messagesData = [
        'messages' => $messages
        ];

        $chatsDb = new SQLite3('chats.db');

        $updateNotifQuery = "UPDATE chats SET notif = 0 WHERE chat = :chatid AND username = :username";
        $stmtNotif = $chatsDb->prepare($updateNotifQuery);
        $stmtNotif->bindValue(':chatid', $chatid, SQLITE3_TEXT);
        $stmtNotif->bindValue(':username', $username, SQLITE3_TEXT);
        $stmtNotif->execute();
        
        echo json_encode($messagesData);
    } else {
        // Set the HTTP response code to 500 Internal Server Error
        http_response_code(500);
        echo "Error: Failed to retrieve messages.";
    }
} else {
    // Set the HTTP response code to 500 Internal Server Error
    http_response_code(500);
    echo "Error: Failed to update last login.";
}
?>
