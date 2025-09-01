<?php
$servername = "localhost";
$username   = "root";    // change if needed
$password   = "";        // change if needed
$dbname     = "gamezone_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Optional: set charset to avoid issues with special chars
$conn->set_charset("utf8mb4");
?>
