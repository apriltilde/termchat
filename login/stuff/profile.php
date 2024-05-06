<?php
session_start();

// Get the raw POST data
$postData = file_get_contents("php://input");

// Decode the JSON data
$data = json_decode($postData, true);

// Check if either textcolour or bgcolour is set in the JSON data
if(isset($data["textcolour"]) || isset($data["bgcolour"])) {
    // Get the username from the session data
    $username = $_SESSION["username"];

    // Connect to the users database
    $userDb = new SQLite3('../users.db');

    // Prepare the SQL query to update the user's row with the new colour
    $userQuery = "UPDATE users SET ";
    $userParams = [];
    if(isset($data["textcolour"])) {
        $userQuery .= "textcolour = :textcolour";
        $userParams[':textcolour'] = $data["textcolour"];
    }
    if(isset($data["bgcolour"])) {
        if(isset($data["textcolour"])) {
            $userQuery .= ", ";
        }
        $userQuery .= "bgcolour = :bgcolour";
        $userParams[':bgcolour'] = $data["bgcolour"];
    }
    $userQuery .= " WHERE username = :username";
    $userParams[':username'] = $username;

    // Prepare the statement for updating user colours
    $userStmt = $userDb->prepare($userQuery);
    if(!$userStmt) {
        // Error handling if the statement could not be prepared
        http_response_code(500);
        echo "Error: Failed to prepare user colour update statement.";
        exit;
    }

    // Bind parameters and execute the user colour update statement
    foreach ($userParams as $paramName => $paramValue) {
        $userStmt->bindValue($paramName, $paramValue);
    }
    $userResult = $userStmt->execute();

    // Check if the user colour update query was executed successfully
    if (!$userResult) {
        // Set the HTTP response code to 500 Internal Server Error
        http_response_code(500);
        echo "Error: Failed to update user colour.";
        exit;
    }

    // Connect to the msg database
    $msgDb = new SQLite3('msg.db');

    // Prepare the SQL query to update message colours
    $msgQuery = "UPDATE msg SET ";
    $msgParams = [];
    if(isset($data["textcolour"])) {
        $msgQuery .= "textcolour = :textcolour";
        $msgParams[':textcolour'] = $data["textcolour"];
    }
    if(isset($data["bgcolour"])) {
        if(isset($data["textcolour"])) {
            $msgQuery .= ", ";
        }
        $msgQuery .= "bgcolour = :bgcolour";
        $msgParams[':bgcolour'] = $data["bgcolour"];
    }

    // Bind the common parameter (username) for updating messages
    $msgQuery .= " WHERE username = :username";
    $msgParams[':username'] = $username;

    // Prepare the statement for updating message colours
    $msgStmt = $msgDb->prepare($msgQuery);
    if(!$msgStmt) {
        // Error handling if the statement could not be prepared
        http_response_code(500);
        echo "Error: Failed to prepare message colour update statement.";
        exit;
    }

    // Bind parameters and execute the message colour update statement
    foreach ($msgParams as $paramName => $paramValue) {
        $msgStmt->bindValue($paramName, $paramValue);
    }
    $msgResult = $msgStmt->execute();

    // Check if the message colour update query was executed successfully
    if ($msgResult) {
        // Set the HTTP response code to 200 OK
        http_response_code(200);
        echo "Colour updated successfully.";
    } else {
        // Set the HTTP response code to 500 Internal Server Error
        http_response_code(500);
        echo "Error: Failed to update colour for messages.";
    }
} else {
    // Set the HTTP response code to 400 Bad Request
    http_response_code(400);
    echo "Error: No colour data received.";
}
?>
