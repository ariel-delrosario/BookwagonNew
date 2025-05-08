
<?php
include("../connect.php");
include("../session.php");

header('Content-Type: application/json');

if (!isset($_GET['request_id'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit;
}

$requestId = $_GET['request_id'];
$userId = $_SESSION['id'] ?? 0;

try {
    // Verify user has permission to view this
    $verifyQuery = "SELECT sr.id 
                    FROM swap_requests sr
                    WHERE sr.id = ? AND (sr.requester_id = ? OR sr.owner_id = ?)";
    
    $verifyStmt = $conn->prepare($verifyQuery);
    $verifyStmt->bind_param("iii", $requestId, $userId, $userId);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Not authorized']);
        exit;
    }

    // Get logistics
    $query = "SELECT * FROM swap_logistics WHERE request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => true,
            'data' => $result->fetch_assoc()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No logistics info found'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Error getting logistics: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to get logistics info']);
}
?>
