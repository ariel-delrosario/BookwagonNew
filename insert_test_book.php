<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("connect.php");

$sql = "INSERT INTO book_swaps (user_id, book_title, author, description, `condition`, image_path, status) 
        VALUES (1, 'Test Book', 'Test Author', 'Test Description', 'New', 'images/test-book.jpg', 'available')";

if ($conn->query($sql) === TRUE) {
    echo "Test book inserted successfully\n";
} else {
    echo "Error: " . $sql . "\n" . $conn->error . "\n";
}

$conn->close();
?> 