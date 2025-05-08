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
        'message' => 'You must be logged in to handle swap requests'
    ]);
    exit;
}

// Check required parameters
if (!isset($_POST['request_id']) || !isset($_POST['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$requestId = $_POST['request_id'];
$action = $_POST['action'];
$userId = $_SESSION['id'];

// Validate action
if ($action !== 'accept' && $action !== 'reject') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

try {
    // First, verify that this request belongs to the current user
    $verifyQuery = "SELECT sr.*, bs.id as book_id, bs.book_title 
                    FROM swap_requests sr
                    JOIN book_swaps bs ON sr.book_id = bs.id
                    WHERE sr.id = ? AND sr.owner_id = ? AND sr.status = 'pending'";
    
    $verifyStmt = $conn->prepare($verifyQuery);
    $verifyStmt->bind_param("ii", $requestId, $userId);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Swap request not found or you are not authorized to handle it'
        ]);
        exit;
    }
    
    $requestData = $verifyResult->fetch_assoc();
    $bookId = $requestData['book_id'];
    $bookTitle = $requestData['book_title'];
    
    // Update the request status
    $status = ($action === 'accept') ? 'accepted' : 'rejected';
    $updateQuery = "UPDATE swap_requests SET status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $status, $requestId);
    $success = $updateStmt->execute();
    
    if (!$success) {
        throw new Exception("Failed to update swap request");
    }
    
    // If accepted, update the book status to 'swapped'
    if ($action === 'accept') {
        $bookUpdateQuery = "UPDATE book_swaps SET status = 'swapped' WHERE id = ?";
        $bookUpdateStmt = $conn->prepare($bookUpdateQuery);
        $bookUpdateStmt->bind_param("i", $bookId);
        $bookUpdateStmt->execute();
        
        // Also reject any other pending requests for this book
        $rejectOthersQuery = "UPDATE swap_requests 
                             SET status = 'rejected' 
                             WHERE book_id = ? AND id != ? AND status = 'pending'";
        $rejectOthersStmt = $conn->prepare($rejectOthersQuery);
        $rejectOthersStmt->bind_param("ii", $bookId, $requestId);
        $rejectOthersStmt->execute();
    } else {
        // If rejected, check if there are no other pending requests and update book status back to 'available'
        $checkPendingQuery = "SELECT COUNT(*) as pending_count 
                             FROM swap_requests 
                             WHERE book_id = ? AND status = 'pending'";
        $checkPendingStmt = $conn->prepare($checkPendingQuery);
        $checkPendingStmt->bind_param("i", $bookId);
        $checkPendingStmt->execute();
        $pendingResult = $checkPendingStmt->get_result();
        $pendingData = $pendingResult->fetch_assoc();
        
        if ($pendingData['pending_count'] === 0) {
            $bookUpdateQuery = "UPDATE book_swaps SET status = 'available' WHERE id = ?";
            $bookUpdateStmt = $conn->prepare($bookUpdateQuery);
            $bookUpdateStmt->bind_param("i", $bookId);
            $bookUpdateStmt->execute();
        }
    }
    
    // Return success response
    $message = ($action === 'accept') 
        ? "Swap request for '$bookTitle' accepted successfully!" 
        : "Swap request for '$bookTitle' rejected.";
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    // Log the error (in a production environment)
    error_log('Error handling swap request: ' . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process your request: ' . $e->getMessage()
    ]);
}
?>