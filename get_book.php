<?php
include("session.php");
include("connect.php");

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Book ID is required']);
    exit();
}

$bookId = intval($_GET['id']);
$userId = $_SESSION['id'];

// Get book details
$query = "SELECT * FROM books WHERE book_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bookId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Book not found']);
    exit();
}

$book = $result->fetch_assoc();

// Check if the book belongs to the user (for sellers)
if ($_SESSION['usertype'] === 'seller' && $book['user_id'] != $userId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You do not have permission to access this book']);
    exit();
}

// Return book details
header('Content-Type: application/json');
echo json_encode(['success' => true, 'book' => $book]);
exit();
?>