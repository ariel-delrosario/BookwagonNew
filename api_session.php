<?php
// Start output buffering
ob_start();

// Prevent any output before headers
error_reporting(0);
ini_set('display_errors', 0);

// Start session without output
@session_start();

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to access this API.']);
    exit;
}

// Include database connection
require_once(__DIR__ . '/db.php');
?> 