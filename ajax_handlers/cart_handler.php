<?php
include("../session.php");
include("../connect.php");

header('Content-Type: application/json');

$response = [
    'success' => false, 
    'message' => '', 
    'cartCount' => 0
];

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    $response['message'] = 'Please log in to add items to cart';
    echo json_encode($response);
    exit();
}

$userId = $_SESSION['id'];

try {
    // Validate inputs
    $bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $purchaseType = $_POST['purchase_type'] ?? 'buy';
    $rentalWeeks = isset($_POST['rental_weeks']) ? intval($_POST['rental_weeks']) : 1;

    // Check book exists and has stock
    $checkStockStmt = $conn->prepare("SELECT stock FROM books WHERE book_id = ?");
    $checkStockStmt->bind_param("i", $bookId);
    $checkStockStmt->execute();
    $stockResult = $checkStockStmt->get_result();

    if ($stockResult->num_rows === 0) {
        $response['message'] = 'Book not found.';
        echo json_encode($response);
        exit();
    }

    $bookStock = $stockResult->fetch_assoc()['stock'];

    if ($bookStock <= 0) {
        $response['message'] = 'Sorry, this book is out of stock.';
        echo json_encode($response);
        exit();
    }

    // Check if book is already in cart
    $checkCartStmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND book_id = ? AND purchase_type = ?");
    $checkCartStmt->bind_param("iis", $userId, $bookId, $purchaseType);
    $checkCartStmt->execute();
    $cartResult = $checkCartStmt->get_result();

    if ($cartResult->num_rows > 0) {
        // Update existing cart item
        $cartItem = $cartResult->fetch_assoc();
        $newQuantity = $cartItem['quantity'] + 1;

        $updateStmt = $conn->prepare("UPDATE cart SET quantity = ?, rental_weeks = ? WHERE cart_id = ?");
        $updateStmt->bind_param("iii", $newQuantity, $rentalWeeks, $cartItem['cart_id']);
        $updateStmt->execute();
    } else {
        // Add new item to cart
        $insertStmt = $conn->prepare("INSERT INTO cart (user_id, book_id, quantity, purchase_type, rental_weeks) VALUES (?, ?, 1, ?, ?)");
        $insertStmt->bind_param("iiss", $userId, $bookId, $purchaseType, $rentalWeeks);
        $insertStmt->execute();
    }

    // Get updated cart count
    $countStmt = $conn->prepare("SELECT COUNT(*) as cart_count FROM cart WHERE user_id = ?");
    $countStmt->bind_param("i", $userId);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $cartCount = $countResult->fetch_assoc()['cart_count'];

    $response['success'] = true;
    $response['message'] = 'Item added to cart successfully!';
    $response['cartCount'] = $cartCount;
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

echo json_encode($response);