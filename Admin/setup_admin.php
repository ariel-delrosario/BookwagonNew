<?php
// This file is used to create an initial admin user
// After using it once, you should remove it from your server for security

// Include database connection
require_once "db_connect.php";

// Define admin credentials
$admin_username = "admin"; // Change this to your desired username
$admin_password = "admin123"; // Change this to your desired password

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if admin table is empty
$check_query = "SELECT COUNT(*) as count FROM admin";
$result = $conn->query($check_query);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert admin user
    $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
    
    if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("ss", $admin_username, $hashed_password);
        
        if($stmt->execute()){
            echo "Admin user created successfully!<br>";
            echo "Username: " . $admin_username . "<br>";
            echo "Password: " . $admin_password . "<br>";
            echo "<strong>Note:</strong> Please delete this file after use for security reasons.";
        } else{
            echo "Error: Could not execute query. " . $conn->error;
        }
        
        $stmt->close();
    } else {
        echo "Error: Could not prepare statement. " . $conn->error;
    }
} else {
    echo "Admin users already exist. This script should only be used for initial setup.";
}

$conn->close();
?>