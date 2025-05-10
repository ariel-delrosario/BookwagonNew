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
    sendResponse(false, 'You must be logged in to update a post');
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
$requiredFields = ['post_id', 'title', 'content', 'category_id'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        sendResponse(false, ucfirst($field) . ' is required');
    }
}

// Sanitize and validate input
$postId = (int)$_POST['post_id'];
$title = $conn->real_escape_string(trim($_POST['title']));
$content = $conn->real_escape_string(trim($_POST['content']));
$categoryId = (int)$_POST['category_id'];
$tags = isset($_POST['tags']) ? $conn->real_escape_string(trim($_POST['tags'])) : '';

// Check if post exists and user is the author
$postCheck = $conn->query("SELECT user_id FROM forum_posts WHERE post_id = $postId");
if ($postCheck->num_rows === 0) {
    sendResponse(false, 'Invalid post');
}

$postUserId = $postCheck->fetch_assoc()['user_id'];
if ($postUserId != $userId) {
    sendResponse(false, 'You do not have permission to edit this post');
}

// Check if category exists
$categoryCheck = $conn->query("SELECT category_id FROM forum_categories WHERE category_id = $categoryId");
if ($categoryCheck->num_rows === 0) {
    sendResponse(false, 'Invalid category selected');
}

// Update the post
$updateQuery = "UPDATE forum_posts 
               SET category_id = $categoryId, 
                   title = '$title', 
                   content = '$content', 
                   tags = '$tags',
                   updated_at = NOW()
               WHERE post_id = $postId";

if ($conn->query($updateQuery)) {
    sendResponse(true, 'Post updated successfully');
} else {
    sendResponse(false, 'Failed to update post: ' . $conn->error);
}