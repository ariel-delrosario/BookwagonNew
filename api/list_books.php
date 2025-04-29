<?php
include("../session.php");
include("../connect.php");

$query = "SELECT bs.*, u.firstname, u.lastname 
          FROM book_swaps bs 
          JOIN users u ON bs.user_id = u.id 
          WHERE bs.status = 'available' 
          ORDER BY bs.created_at DESC";

$result = $conn->query($query);
$books = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

echo json_encode(['success' => true, 'books' => $books]);