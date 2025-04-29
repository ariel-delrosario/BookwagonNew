<?php
include("../session.php");
include("../connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['id'];
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $description = $conn->real_escape_string($_POST['description']);
    $condition = $conn->real_escape_string($_POST['condition']);
    
    // Handle file upload
    $targetDir = "../uploads/books/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($_FILES["book_image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    // Validate file type
    $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
    if (in_array($imageFileType, $allowTypes)) {
        if (move_uploaded_file($_FILES["book_image"]["tmp_name"], $targetFilePath)) {
            $query = "INSERT INTO book_swaps (user_id, book_title, author, description, 
                     `condition`, image_path, status) 
                     VALUES (?, ?, ?, ?, ?, ?, 'available')";
            
            $stmt = $conn->prepare($query);
            $imagePath = "uploads/books/" . $fileName;
            $stmt->bind_param("isssss", $userId, $title, $author, $description, $condition, $imagePath);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Book added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding book: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error uploading image']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.']);
    }
}
?>