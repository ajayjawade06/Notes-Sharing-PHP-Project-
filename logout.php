<?php
// Include config file
require_once './config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: $base_url");
exit();
?>