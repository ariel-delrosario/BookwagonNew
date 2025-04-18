<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

// Get POST data
$seller_id = isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate input
if ($seller_id <= 0 || !in_array($status, ['approved', 'rejected'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid input']));
}

// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bookwagon_db";

// Create connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]));
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Update seller_details status
    $sql = "UPDATE seller_details SET verification_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $seller_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating seller status: " . $stmt->error);
    }
    
    // Get user_id from seller_details
    $sql = "SELECT user_id FROM seller_details WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seller_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error fetching user_id: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("Seller not found");
    }
    
    // Update users table is_seller status
    $is_seller = ($status === 'approved') ? 1 : 0;
    $sql = "UPDATE users SET is_seller = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_seller, $user['user_id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating user seller status: " . $stmt->error);
    }
    
    // Insert into seller_verification table
    $sql = "INSERT INTO seller_verification (seller_id, verified_by, verification_date, status, notes) 
            VALUES (?, ?, NOW(), ?, ?)";
    $stmt = $conn->prepare($sql);
    $admin_id = $_SESSION['admin_id'];
    $notes = "Status updated to " . ucfirst($status) . " by admin";
    $stmt->bind_param("iiss", $seller_id, $admin_id, $status, $notes);
    
    if (!$stmt->execute()) {
        throw new Exception("Error inserting verification record: " . $stmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Seller status updated successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 