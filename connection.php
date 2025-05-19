<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "magdalene_management";

// Create connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set charset to ensure proper encoding
$mysqli->set_charset("utf8mb4");

// Return the connection object
return $mysqli;
?>