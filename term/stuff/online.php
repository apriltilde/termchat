<?php
session_start();
// Connect to the users database
$userDb = new SQLite3('../users.db');
$current_username = $_SESSION["username"];

$onlineUsers = [];
$offlineUsers = [];
$chats = [];
$dms = [];

$db = new SQLite3('chats.db');

$stmt = $db->prepare("SELECT * FROM chats WHERE username = :current_username AND dm = 1");
$stmt->bindValue(':current_username', $current_username, SQLITE3_TEXT);
$result = $stmt->execute();
$dmChats = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $dmChats[] = $row;
}


// Prepare and execute the SQL query to select all non-DM chats for the current user
$stmt = $db->prepare("SELECT * FROM chats WHERE username = :current_username AND dm = 0");
$stmt->bindValue(':current_username', $current_username, SQLITE3_TEXT);
$result = $stmt->execute();
$chats = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $chats[] = $row;
}

// Get the current time in GMT
$currentTime = strtotime(gmdate('Y-m-d H:i:s'));

// Prepare and execute the SQL query to select all users and their last active time
$selectUsersQuery = "SELECT username, last_active_time FROM users";
$usersResult = $userDb->query($selectUsersQuery);

// Check if there are any rows returned
if ($usersResult) {
    // Fetch each row as an associative array and check activity status
    while ($row = $usersResult->fetchArray(SQLITE3_ASSOC)) {
        // Calculate the time difference in seconds
        $lastActiveTime = strtotime($row['last_active_time']);
        $timeDifference = $currentTime - $lastActiveTime;

        // If the time difference is less than 10 minutes (600 seconds), the user is considered online
        if ($timeDifference < 600) {
            $onlineUsers[] = $row['username'];
        } else {
            // Otherwise, the user is considered offline
            $offlineUsers[] = $row['username'];
        }
    }
} else {
    echo "Error: Failed to retrieve user data.";
}

// Export online and offline users as associative arrays
$usersData = [
    'onlineUsers' => $onlineUsers,
    'offlineUsers' => $offlineUsers,
    'dmChats' => $dmChats,
    'chats' => $chats,
    'username' => $current_username,
]; 

// Convert the arrays to JSON format for exporting
$jsonData = json_encode($usersData);

// Output the JSON data
echo $jsonData;
?>
