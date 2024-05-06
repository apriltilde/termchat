<?php
session_start();

// Get the current username from the session
$current_username = $_SESSION["username"];

// Get the other username from the POST data
$other_username = $_POST["username"];
$chatname = $other_username;

// Generate a unique chat ID
$chatid = uniqid();

// Connect to the SQLite database
$db = new SQLite3('chats.db');

$existing_chat_stmt = $db->prepare("SELECT * FROM chats WHERE username = :current_username AND chatname = :chatname AND dm = 1");
$existing_chat_stmt->bindValue(':current_username', $current_username, SQLITE3_TEXT);
$existing_chat_stmt->bindValue(':other_username', $other_username, SQLITE3_TEXT);
$existing_chat_stmt->bindValue(':chatname', $chatname, SQLITE3_TEXT);
$existing_chat_result = $existing_chat_stmt->execute();

// If a chat already exists, return an error response
if ($existing_chat_result->fetchArray(SQLITE3_ASSOC)) {
    http_response_code(501); // Bad Request
    echo "Error: Chat with the same chatname already exists.";
    exit;
}

// Prepare the SQL statement to insert the new DM into the chats database
$insert_stmt = $db->prepare("INSERT INTO chats (username, chat, dm, chatname) VALUES (:current_username, :chatid, 1, :other_username), (:other_username, :chatid, 1, :current_username)");
$insert_stmt->bindValue(':current_username', $current_username, SQLITE3_TEXT);
$insert_stmt->bindValue(':other_username', $other_username, SQLITE3_TEXT);
$insert_stmt->bindValue(':chatid', $chatid, SQLITE3_TEXT);

// Execute the SQL statement
$insert_stmt->execute();

// Set the HTTP response code to indicate success
http_response_code(200);
?>
