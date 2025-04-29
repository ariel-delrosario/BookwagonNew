<?php
include("../session.php");
include("../connect.php");

$userId = $_SESSION['id'];

// Get all available books except the current user's books
$query = "SELECT bs.*, u.firstname, u.lastname, u.profile_picture as user_avatar 
          FROM book_swaps bs 
          JOIN users u ON bs.user_id = u.id 
          WHERE bs.status = 'available' 
          AND bs.user_id != ? 
          ORDER BY bs.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$books = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Clean and format the data
        $books[] = array(
            'id' => $row['id'],
            'book_title' => htmlspecialchars($row['book_title']),
            'author' => htmlspecialchars($row['author']),
            'description' => htmlspecialchars($row['description']),
            'condition' => htmlspecialchars($row['condition']),
            'image_path' => htmlspecialchars($row['image_path']),
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'firstname' => htmlspecialchars($row['firstname']),
            'lastname' => htmlspecialchars($row['lastname']),
            'user_avatar' => $row['user_avatar'] ? htmlspecialchars($row['user_avatar']) : 'images/default-avatar.png'
        );
    }
}

$stmt->close();

header('Content-Type: application/json');
echo json_encode([
    'success' => true, 
    'books' => $books,
    'total' => count($books)
]);
?>