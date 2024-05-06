<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
	http_response_code(200);
} else {
    http_response_code(404);
    exit;
}
?>
