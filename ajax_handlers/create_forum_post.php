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
    sendResponse(false, 'You must be logged in to create a post');
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
$requiredFields = ['title', 'content', 'category_id'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        sendResponse(false, ucfirst($field) . ' is required');
    }
}

// Sanitize and validate input
$title = $conn->real_escape_string(trim($_POST['title']));
$content = $conn->real_escape_string(trim($_POST['content']));
$categoryId = (int)$_POST['category_id'];
$tags = isset($_POST['tags']) ? $conn->real_escape_string(trim($_POST['tags'])) : '';

// Check if category exists
$categoryCheck = $conn->query("SELECT category_id FROM forum_categories WHERE category_id = $categoryId");
if ($categoryCheck->num_rows === 0) {
    sendResponse(false, 'Invalid category selected');
}

// Insert the post
$insertQuery = "INSERT INTO forum_posts (category_id, user_id, title, content, tags) 
                VALUES ($categoryId, $userId, '$title', '$content', '$tags')";

if ($conn->query($insertQuery)) {
    $postId = $conn->insert_id;
    sendResponse(true, 'Post created successfully', ['post_id' => $postId]);
} else {
    sendResponse(false, 'Failed to create post: ' . $conn->error);
} 