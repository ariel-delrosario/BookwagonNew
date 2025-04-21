<?php
// Include connection to database and session
include("connect.php");
include("session.php");

$userType = $_SESSION['usertype'] ?? '';
$userId = $_SESSION['id'] ?? 0;

// Ensure only sellers can access this page
if ($userType !== 'seller') {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Only sellers can access this resource.']);
    exit;
}

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Book ID is required.']);
    exit;
}

$book_id = intval($_GET['id']);

// Query to fetch book details
$query = "SELECT * FROM books WHERE book_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $book_id, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $book = $result->fetch_assoc();
    
    // Return book details as JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'book' => $book]);
} else {
    // Book not found or doesn't belong to the current user
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Book not found or you do not have permission to access it.']);
}

$stmt->close();
$conn->close();
?>