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
    sendResponse(false, 'Please log in to like posts');
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
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    sendResponse(false, 'Post ID is required');
}

// Sanitize and validate input
$postId = (int)$_POST['post_id'];

// Check if post exists
$postCheck = $conn->query("SELECT post_id FROM forum_posts WHERE post_id = $postId");
if ($postCheck->num_rows === 0) {
    sendResponse(false, 'Invalid post');
}

// Check if user already liked this post
$likeCheck = $conn->query("SELECT interaction_id FROM forum_user_interactions 
                         WHERE user_id = $userId AND post_id = $postId AND interaction_type = 'like'");

if ($likeCheck->num_rows > 0) {
    // User already liked this post, so unlike it
    $conn->query("DELETE FROM forum_user_interactions 
                WHERE user_id = $userId AND post_id = $postId AND interaction_type = 'like'");
    
    // Get new like count
    $likesResult = $conn->query("SELECT COUNT(*) as likes FROM forum_user_interactions 
                               WHERE post_id = $postId AND interaction_type = 'like'");
    $likes = $likesResult->fetch_assoc()['likes'];
    
    sendResponse(true, 'Post unliked successfully', ['likes' => $likes, 'liked' => false]);
} else {
    // Add new like
    $insertQuery = "INSERT INTO forum_user_interactions (user_id, post_id, interaction_type) 
                   VALUES ($userId, $postId, 'like')";
    
    if ($conn->query($insertQuery)) {
        // Get new like count
        $likesResult = $conn->query("SELECT COUNT(*) as likes FROM forum_user_interactions 
                                   WHERE post_id = $postId AND interaction_type = 'like'");
        $likes = $likesResult->fetch_assoc()['likes'];
        
        sendResponse(true, 'Post liked successfully', ['likes' => $likes, 'liked' => true]);
    } else {
        sendResponse(false, 'Failed to like post: ' . $conn->error);
    }
} 