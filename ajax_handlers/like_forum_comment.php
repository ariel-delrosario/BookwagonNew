<?php
session_start();
include("../connect.php");

// Return response function
function sendResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['id']) && !isset($_SESSION['user_id'])) {
    sendResponse(false, 'Please log in to like comments');
}

// Get user ID from session
$userId = $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;

if (!$userId) {
    sendResponse(false, 'Invalid user session');
}

// Validate form data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Required fields
if (!isset($_POST['comment_id']) || empty($_POST['comment_id'])) {
    sendResponse(false, 'Comment ID is required');
}

// Sanitize and validate input
$commentId = (int)$_POST['comment_id'];

// Check if comment exists
$commentCheck = $conn->query("SELECT comment_id FROM forum_comments WHERE comment_id = $commentId");
if ($commentCheck->num_rows === 0) {
    sendResponse(false, 'Invalid comment');
}

// Check if user already liked this comment
$likeCheck = $conn->query("SELECT interaction_id FROM forum_user_interactions 
                         WHERE user_id = $userId AND comment_id = $commentId AND interaction_type = 'like'");

if ($likeCheck->num_rows > 0) {
    // User already liked this comment, so unlike it
    $conn->query("DELETE FROM forum_user_interactions 
                WHERE user_id = $userId AND comment_id = $commentId AND interaction_type = 'like'");
    
    // Get new like count
    $likesResult = $conn->query("SELECT COUNT(*) as likes FROM forum_user_interactions 
                               WHERE comment_id = $commentId AND interaction_type = 'like'");
    $likes = $likesResult->fetch_assoc()['likes'];
    
    sendResponse(true, 'Comment unliked successfully', ['likes' => $likes]);
} else {
    // Add new like
    $insertQuery = "INSERT INTO forum_user_interactions (user_id, comment_id, interaction_type) 
                   VALUES ($userId, $commentId, 'like')";
    
    if ($conn->query($insertQuery)) {
        // Get new like count
        $likesResult = $conn->query("SELECT COUNT(*) as likes FROM forum_user_interactions 
                                   WHERE comment_id = $commentId AND interaction_type = 'like'");
        $likes = $likesResult->fetch_assoc()['likes'];
        
        sendResponse(true, 'Comment liked successfully', ['likes' => $likes]);
    } else {
        sendResponse(false, 'Failed to like comment: ' . $conn->error);
    }
}