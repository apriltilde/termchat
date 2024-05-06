<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    http_response_code(401);
    exit;
}

// Get the username from the POST data
$username = isset($_POST["username"]) ? $_POST["username"] : "";

if (empty($username)) {
    http_response_code(400);
    echo "Error: Username is empty.";
    exit;
}

$msgdb = new SQLite3('msg.db');

// Prepare the SQL query to count the messages sent by the username
$msgquery = "SELECT COUNT(msg) FROM msg WHERE username = :username";

$msgstmt = $msgdb->prepare($msgquery);
$msgstmt->bindValue(':username', $username, SQLITE3_TEXT);
$msgresult = $msgstmt->execute();

$msgCount = 0;
if ($msgrow = $msgresult->fetchArray(SQLITE3_NUM)) {
    $msgCount = $msgrow[0];
}


// Connect to the database
$db = new SQLite3('../users.db');

// Prepare the SQL query to retrieve user information
$query = "SELECT username, last_active_time, joindate, bgcolour, textcolour FROM users WHERE username = :username";

$stmt = $db->prepare($query);
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();

if ($result) {
    // Fetch user information from the result
    $userInfo = $result->fetchArray(SQLITE3_ASSOC);

    if ($userInfo) {
        // Return user information as JSON response
        $userInfo['msg_count'] = $msgCount;
        http_response_code(200);
        echo json_encode($userInfo);
    } else {
        // User not found
        http_response_code(404);
        echo "Error: User not found.";
    }
} else {
    // Error in executing the query
    http_response_code(500);
    echo "Error: Failed to retrieve user information.";
}

// Close the database connection
$db->close();
?>
