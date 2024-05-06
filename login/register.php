<?php
// Connect to your SQLite database
$db = new SQLite3('users.db');

    $username = $_POST["username"];
    $password = $_POST["password"];
    $chatid	= "main";

    $check_stmt = $db->prepare("SELECT COUNT(*) AS count FROM users WHERE username = :username");
    $check_stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $check_result = $check_stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($check_result['count'] > 0) {
        http_response_code(400);
        echo "Error: Username already exists.";
        exit;
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Get the current datetime
$current_time = gmdate("Y-m-d H:i:s");
$current_date = gmdate("Y-m-d");

    // Insert the data into the users table
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, last_active_time, joindate) VALUES (:username, :password_hash, :last_active_time,:joindate)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password_hash', $password_hash, SQLITE3_TEXT);
    $stmt->bindValue(':last_active_time', $current_time, SQLITE3_TEXT);
    $stmt->bindValue(':joindate', $current_date, SQLITE3_TEXT);

    $stmt->execute();

    $chatDb = new SQLite3('stuff/chats.db');
    $stmt = $chatDb->prepare("INSERT INTO chats (username, chat, chatname) VALUES (:username, :chat_id, :chatname)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':chat_id', $chatid, SQLITE3_TEXT);
    $stmt->bindValue(':chatname', $chatid, SQLITE3_TEXT);
    $stmt->execute();

    setcookie("username", $username, time() + (86400 * 30), "/", $_SERVER['HTTP_HOST']);

?>