<?php
include("../connect.php");
include("../session.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit;
}

if (!isset($_POST['request_id']) || !isset($_POST['delivery_method'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$requestId = $_POST['request_id'];
$deliveryMethod = $_POST['delivery_method'];
$scheduledDate = isset($_POST['scheduled_date']) ? $_POST['scheduled_date'] : null;
$location = isset($_POST['location']) ? $_POST['location'] : null;
$notes = isset($_POST['notes']) ? $_POST['notes'] : null;
$userId = $_SESSION['id'];

try {
    // Verify the user has permission to update this request
    $verifyQuery = "SELECT sr.id 
                    FROM swap_requests sr
                    WHERE sr.id = ? AND (sr.requester_id = ? OR sr.owner_id = ?)
                    AND sr.status = 'accepted'";
    
    $verifyStmt = $conn->prepare($verifyQuery);
    $verifyStmt->bind_param("iii", $requestId, $userId, $userId);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid request or no permission']);
        exit;
    }

    // Update or insert logistics
    $query = "INSERT INTO swap_logistics (request_id, delivery_method, scheduled_date, location, notes, status)
              VALUES (?, ?, ?, ?, ?, 'pending')
              ON DUPLICATE KEY UPDATE 
              delivery_method = VALUES(delivery_method),
              scheduled_date = VALUES(scheduled_date),
              location = VALUES(location),
              notes = VALUES(notes)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issss", $requestId, $deliveryMethod, $scheduledDate, $location, $notes);
    $success = $stmt->execute();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Delivery details updated successfully']);
    } else {
        throw new Exception("Failed to update delivery details");
    }
    
} catch (Exception $e) {
    error_log('Error updating logistics: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update delivery details']);
}
?>
