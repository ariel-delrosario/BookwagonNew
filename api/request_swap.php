<?php
// Include connection and session files
include("../connect.php");
include("../session.php");

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to request a swap'
    ]);
    exit;
}

// Check if book_id is provided
if (!isset($_POST['book_id']) || empty($_POST['book_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Book ID is required'
    ]);
    exit;
}

// Get the current user's ID and the book ID
$requesterId = $_SESSION['id'];
$bookId = $_POST['book_id'];
$message = isset($_POST['message']) ? $_POST['message'] : null;

try {
    // First, check if this book exists and get owner info
    $bookQuery = "SELECT bs.*, u.id as owner_id 
                  FROM book_swaps bs
                  JOIN users u ON bs.user_id = u.id
                  WHERE bs.id = ? AND bs.status = 'available'";
    
    $bookStmt = $conn->prepare($bookQuery);
    $bookStmt->bind_param("i", $bookId);
    $bookStmt->execute();
    $bookResult = $bookStmt->get_result();
    
    if ($bookResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Book not found or not available for swap'
        ]);
        exit;
    }
    
    $bookData = $bookResult->fetch_assoc();
    $ownerId = $bookData['owner_id'];
    
    // Check if the requester is not the owner
    if ($requesterId == $ownerId) {
        echo json_encode([
            'success' => false,
            'message' => 'You cannot request a swap for your own book'
        ]);
        exit;
    }
    
    // Check if there's already a pending request for this book from this user
    $checkQuery = "SELECT id FROM swap_requests 
                   WHERE requester_id = ? AND book_id = ? AND status = 'pending'";
    
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $requesterId, $bookId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'You already have a pending swap request for this book'
        ]);
        exit;
    }
    
    // Insert the swap request
    $insertQuery = "INSERT INTO swap_requests (requester_id, book_id, owner_id, message) 
                    VALUES (?, ?, ?, ?)";
    
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iiis", $requesterId, $bookId, $ownerId, $message);
    $success = $insertStmt->execute();
    
    if ($success) {
        // Update the book status to 'requested'
        $updateQuery = "UPDATE book_swaps SET status = 'requested' WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $bookId);
        $updateStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Swap request sent successfully!'
        ]);
    } else {
        throw new Exception("Failed to insert swap request");
    }
    
} catch (Exception $e) {
    // Log the error (in a production environment)
    error_log('Database error: ' . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process swap request. Please try again.'
    ]);
}
?>