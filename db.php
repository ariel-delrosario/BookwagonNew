<?php
$db_host = "localhost";      // Usually "localhost" for local development
$db_user = "root";           // Database username (default is "root" for XAMPP/MAMP)
$db_pass = "";               // Database password (often empty for local development)
$db_name = "bookwagon_db";   // Your database name

// Create connection using the variables
$con = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($con->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Set charset to ensure proper JSON encoding
$con->set_charset("utf8mb4");
?> 