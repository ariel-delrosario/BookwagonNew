<?php
include("session.php");
include("connect.php");

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get user ID
$userId = $_SESSION['id'];

// Check if required parameters are provided
if (!isset($_POST['book_id']) || !isset($_POST['is_favorite'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$bookId = intval($_POST['book_id']);
$isFavorite = intval($_POST['is_favorite']) ? 1 : 0;

// First check if the record exists
$checkQuery = "SELECT * FROM user_favorites WHERE user_id = ? AND book_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("ii", $userId, $bookId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Record exists - update it
    if ($isFavorite) {
        $query = "UPDATE user_favorites SET is_favorite = 1 WHERE user_id = ? AND book_id = ?";
    } else {
        $query = "UPDATE user_favorites SET is_favorite = 0 WHERE user_id = ? AND book_id = ?";
    }
} else {
    // Record doesn't exist - insert it
    $query = "INSERT INTO user_favorites (user_id, book_id, is_favorite) VALUES (?, ?, ?)";
}

$stmt = $conn->prepare($query);

if ($result->num_rows > 0) {
    $stmt->bind_param("ii", $userId, $bookId);
} else {
    $stmt->bind_param("iii", $userId, $bookId, $isFavorite);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>