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
    sendResponse(false, 'You must be logged in to comment');
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
$requiredFields = ['post_id', 'content'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        sendResponse(false, ucfirst($field) . ' is required');
    }
}

// Sanitize and validate input
$postId = (int)$_POST['post_id'];
$content = $conn->real_escape_string(trim($_POST['content']));
$parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

// Check if post exists and is active
$postCheck = $conn->query("SELECT status FROM forum_posts WHERE post_id = $postId");
if ($postCheck->num_rows === 0) {
    sendResponse(false, 'Invalid post');
}

$postStatus = $postCheck->fetch_assoc()['status'];
if ($postStatus === 'closed') {
    sendResponse(false, 'This discussion is closed and cannot receive new comments');
}

// Check if parent comment exists if replying
if ($parentId) {
    $parentCheck = $conn->query("SELECT comment_id FROM forum_comments WHERE comment_id = $parentId");
    if ($parentCheck->num_rows === 0) {
        sendResponse(false, 'Invalid parent comment');
    }
}

// Insert the comment
$parentIdSql = $parentId ? $parentId : 'NULL';
$insertQuery = "INSERT INTO forum_comments (post_id, user_id, content, parent_id) 
                VALUES ($postId, $userId, '$content', " . ($parentId ? $parentId : "NULL") . ")";

if ($conn->query($insertQuery)) {
    $commentId = $conn->insert_id;
    sendResponse(true, 'Comment posted successfully', ['comment_id' => $commentId]);
} else {
    sendResponse(false, 'Failed to post comment: ' . $conn->error);
} 