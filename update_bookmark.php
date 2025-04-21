<?php
include("session.php");
include("connect.php");

if(!isset($_SESSION['user_id'])) {
    // Return error if user is not logged in
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if(isset($_POST['book_id']) && isset($_POST['is_bookmarked'])) {
    $book_id = $_POST['book_id'];
    $is_bookmarked = $_POST['is_bookmarked'];
    $user_id = $_SESSION['user_id'];
    
    $conn = new mysqli("localhost", "root", "", "bookwagon_db"); // Replace with your actual connection details
    
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Check if bookmark already exists
    $check_stmt = $conn->prepare("SELECT * FROM bookmarks WHERE user_id = ? AND book_id = ?");
    $check_stmt->bind_param("ii", $user_id, $book_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if($result->num_rows > 0) {
        // Update existing record
        if($is_bookmarked == 1) {
            $update_stmt = $conn->prepare("UPDATE bookmarks SET is_bookmarked = 1 WHERE user_id = ? AND book_id = ?");
        } else {
            $update_stmt = $conn->prepare("UPDATE bookmarks SET is_bookmarked = 0 WHERE user_id = ? AND book_id = ?");
        }
        $update_stmt->bind_param("ii", $user_id, $book_id);
        $success = $update_stmt->execute();
    } else {
        // Insert new record
        $insert_stmt = $conn->prepare("INSERT INTO bookmarks (user_id, book_id, is_bookmarked) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $user_id, $book_id, $is_bookmarked);
        $success = $insert_stmt->execute();
    }
    
    header('Content-Type: application/json');
    if($success) {
        echo json_encode(['success' => true, 'message' => 'Bookmark updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update bookmark']);
    }
    
    $conn->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
}
?>