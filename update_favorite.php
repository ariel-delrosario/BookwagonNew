<?php
include("session.php");
include("connect.php");

if(!isset($_SESSION['user_id'])) {
    // Return error if user is not logged in
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if(isset($_POST['book_id']) && isset($_POST['is_favorite'])) {
    $book_id = $_POST['book_id'];
    $is_favorite = $_POST['is_favorite'];
    $user_id = $_SESSION['user_id'];
    
    $conn = new mysqli("localhost", "root", "", "bookwagon_db"); // Replace with your actual connection details
    
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Check if favorite already exists
    $check_stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND book_id = ?");
    $check_stmt->bind_param("ii", $user_id, $book_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if($result->num_rows > 0) {
        // Update existing record
        if($is_favorite == 1) {
            $update_stmt = $conn->prepare("UPDATE favorites SET is_favorite = 1 WHERE user_id = ? AND book_id = ?");
        } else {
            $update_stmt = $conn->prepare("UPDATE favorites SET is_favorite = 0 WHERE user_id = ? AND book_id = ?");
        }
        $update_stmt->bind_param("ii", $user_id, $book_id);
        $success = $update_stmt->execute();
    } else {
        // Insert new record
        $insert_stmt = $conn->prepare("INSERT INTO favorites (user_id, book_id, is_favorite) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $user_id, $book_id, $is_favorite);
        $success = $insert_stmt->execute();
    }
    
    header('Content-Type: application/json');
    if($success) {
        echo json_encode(['success' => true, 'message' => 'Favorite updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update favorite']);
    }
    
    $conn->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
}
?>