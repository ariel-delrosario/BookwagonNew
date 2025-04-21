<?php
$db_host = "localhost";      // Usually "localhost" for local development
$db_user = "root";           // Database username (default is "root" for XAMPP/MAMP)
$db_pass = "";               // Database password (often empty for local development)
$db_name = "bookwagon_db";   // Your database name

// Create connection using the variables
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>