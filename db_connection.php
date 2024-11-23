<?php
// Database connection credentials
$host = 'localhost'; // Database host
$username = 'root'; // Database username
$password = ''; // Database password (if any)
$database = 'inventory_system'; // Database name

// Create a connection to the database
$connection = new mysqli($host, $username, $password, $database);

// Check if the connection is successful
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Optional: Set the character set for the connection to handle special characters
$connection->set_charset('utf8mb4');
?>
