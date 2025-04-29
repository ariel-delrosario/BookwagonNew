<?php
include("../session.php");
include("../connect.php");

$userId = $_SESSION['id'];

$query = "SELECT bs.*, u.firstname, u.lastname, u.profile_picture as user_avatar 
          FROM book_swaps bs 
          JOIN users u ON bs.user_id = u.id 
          WHERE bs.user_id = $userId 
          ORDER BY bs.created_at DESC";

$result = $conn->query($query);
$books = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Clean the data before sending to client
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
            'user_avatar' => htmlspecialchars($row['user_avatar'])
        );
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'books' => $books]);
?>