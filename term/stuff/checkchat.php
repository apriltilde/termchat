<?php
session_start();
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    http_response_code(401);
    exit;
}
$current_username = $_SESSION["username"];
$chatname = $_GET["chat"];
$db = new SQLite3('chats.db');

$stmt = $db->prepare("SELECT * FROM chats WHERE chatname = :chatname AND username = :current_username");
$stmt->bindValue(':chatname', $chatname, SQLITE3_TEXT);
$stmt->bindValue(':current_username', $current_username, SQLITE3_TEXT);
$result = $stmt->execute();

if ($result) {
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    if (count($rows) > 0) {
        http_response_code(200);
        echo json_encode($rows);
    } else {
        http_response_code(404);
        echo "Error: Chat not found.";
    }
} else {
    http_response_code(404);
    echo "Error: Chat not found.";
}
?>
