<?php
// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
