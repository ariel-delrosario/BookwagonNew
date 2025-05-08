<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("connect.php");

$sql = "SELECT * FROM book_swaps";
$result = $conn->query($sql);

if ($result === false) {
    die("Error executing query: " . $conn->error);
}

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
        echo "\n";
    }
} else {
    echo "No books found";
}

$conn->close();
?> 