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
        'message' => 'You must be logged in to view swap requests'
    ]);
    exit;
}

$userId = $_SESSION['id'];

try {
    // Get incoming requests (where current user is the book owner)
    $incomingQuery = "SELECT sr.*, 
                      bs.book_title, bs.condition as book_condition, bs.image_path as book_image,
                      u.firstname as requester_firstname, u.lastname as requester_lastname,
                      u.profile_picture as requester_avatar
                      FROM swap_requests sr
                      JOIN book_swaps bs ON sr.book_id = bs.id
                      JOIN users u ON sr.requester_id = u.id
                      WHERE sr.owner_id = ?
                      ORDER BY sr.created_at DESC";
    
    $incomingStmt = $conn->prepare($incomingQuery);
    $incomingStmt->bind_param("i", $userId);
    $incomingStmt->execute();
    $incomingResult = $incomingStmt->get_result();
    
    $incomingRequests = [];
    while ($row = $incomingResult->fetch_assoc()) {
        // Process image paths
        if (empty($row['book_image'])) {
            $row['book_image'] = 'images/default-book.jpg';
        }
        
        if (empty($row['requester_avatar'])) {
            $row['requester_avatar'] = 'images/default-avatar.png';
        }
        
        $incomingRequests[] = [
            'id' => $row['id'],
            'book_id' => $row['book_id'],
            'book_title' => $row['book_title'],
            'book_condition' => $row['book_condition'],
            'book_image' => $row['book_image'],
            'requester_name' => $row['requester_firstname'] . ' ' . $row['requester_lastname'],
            'requester_avatar' => $row['requester_avatar'],
            'message' => $row['message'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
    
    // Get outgoing requests (where current user is the requester)
    $outgoingQuery = "SELECT sr.*, 
                     bs.book_title, bs.condition as book_condition, bs.image_path as book_image,
                     u.firstname as owner_firstname, u.lastname as owner_lastname,
                     u.profile_picture as owner_avatar
                     FROM swap_requests sr
                     JOIN book_swaps bs ON sr.book_id = bs.id
                     JOIN users u ON sr.owner_id = u.id
                     WHERE sr.requester_id = ?
                     ORDER BY sr.created_at DESC";
    
    $outgoingStmt = $conn->prepare($outgoingQuery);
    $outgoingStmt->bind_param("i", $userId);
    $outgoingStmt->execute();
    $outgoingResult = $outgoingStmt->get_result();
    
    $outgoingRequests = [];
    while ($row = $outgoingResult->fetch_assoc()) {
        // Process image paths
        if (empty($row['book_image'])) {
            $row['book_image'] = 'images/default-book.jpg';
        }
        
        if (empty($row['owner_avatar'])) {
            $row['owner_avatar'] = 'images/default-avatar.png';
        }
        
        $outgoingRequests[] = [
            'id' => $row['id'],
            'book_id' => $row['book_id'],
            'book_title' => $row['book_title'],
            'book_condition' => $row['book_condition'],
            'book_image' => $row['book_image'],
            'owner_name' => $row['owner_firstname'] . ' ' . $row['owner_lastname'],
            'owner_avatar' => $row['owner_avatar'],
            'message' => $row['message'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'incoming_requests' => $incomingRequests,
        'outgoing_requests' => $outgoingRequests
    ]);
    
} catch (Exception $e) {
    // Log the error (in a production environment)
    error_log('Error loading swap requests: ' . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load swap requests: ' . $e->getMessage()
    ]);
}
?>