<?php
include("../connect.php");
include("../session.php");

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Book ID is required']);
    exit;
}

$bookId = $_GET['id'];

try {
    $query = "SELECT bs.*, u.firstname, u.lastname 
              FROM book_swaps bs
              JOIN users u ON bs.user_id = u.id
              WHERE bs.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }
    
    $book = $result->fetch_assoc();
    
    // Ensure image path exists
    if (empty($book['image_path'])) {
        $book['image_path'] = 'images/default-book.jpg';
    }
    
    echo json_encode([
        'success' => true,
        'book' => $book
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch book details: ' . $e->getMessage()
    ]);
}
?>