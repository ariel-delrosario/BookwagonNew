<?php
include("session.php");
include("connect.php");

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Testing Rental Record Creation</h1>";

// Get current user ID
$userId = $_SESSION['id'] ?? 2; // Default to user ID 2 if not in session

// Set up test data
$bookId = 26; // Use a real book ID from your database
$sellerId = 2; // This should be a real seller ID from the sellers table, not user ID
$orderId = 34; // Use a real order ID
$rentalDate = date('Y-m-d H:i:s');
$dueDate = date('Y-m-d H:i:s', strtotime('+1 week'));
$rentalWeeks = 1;
$status = 'active';
$totalPrice = 100.00;

try {
    // First, let's check if the seller ID exists
    $checkSeller = $conn->prepare("SELECT * FROM sellers WHERE id = ?");
    $checkSeller->bind_param("i", $sellerId);
    $checkSeller->execute();
    $sellerResult = $checkSeller->get_result();
    
    echo "<p>Checking seller ID $sellerId: ";
    if ($sellerResult->num_rows > 0) {
        $sellerData = $sellerResult->fetch_assoc();
        echo "Found seller: " . htmlspecialchars($sellerData['shop_name'] ?? 'Unknown') . "</p>";
    } else {
        echo "No seller found with ID $sellerId</p>";
        
        // Let's find a valid seller ID
        $findSeller = $conn->query("SELECT id, shop_name FROM sellers LIMIT 1");
        if ($findSeller->num_rows > 0) {
            $validSeller = $findSeller->fetch_assoc();
            $sellerId = $validSeller['id'];
            echo "<p>Using alternative seller ID: $sellerId (" . htmlspecialchars($validSeller['shop_name']) . ")</p>";
        } else {
            echo "<p>ERROR: No sellers found in the database!</p>";
        }
    }
    
    // Now insert the test record
    $stmt = $conn->prepare("
        INSERT INTO book_rentals (
            user_id, book_id, seller_id, order_id, rental_date, due_date, 
            rental_weeks, status, total_price
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("iiisssiss", $userId, $bookId, $sellerId, $orderId, 
                     $rentalDate, $dueDate, $rentalWeeks, $status, $totalPrice);
    
    echo "<p>Attempting to insert rental record with: <br>
        user_id: $userId<br>
        book_id: $bookId<br>
        seller_id: $sellerId<br>
        order_id: $orderId<br>
        rental_date: $rentalDate<br>
        due_date: $dueDate<br>
        rental_weeks: $rentalWeeks<br>
        status: $status<br>
        total_price: $totalPrice</p>";
    
    if ($stmt->execute()) {
        $rentalId = $conn->insert_id;
        echo "<p style='color:green;'>Successfully created rental record with ID: $rentalId</p>";
    } else {
        echo "<p style='color:red;'>Error creating rental: " . $stmt->error . "</p>";
    }
    
    // Now verify it exists
    $check = $conn->prepare("SELECT * FROM book_rentals WHERE user_id = ? ORDER BY rental_id DESC LIMIT 1");
    $check->bind_param("i", $userId);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $rental = $result->fetch_assoc();
        echo "<p>Verified rental exists with ID: " . $rental['rental_id'] . "</p>";
        echo "<pre>" . print_r($rental, true) . "</pre>";
    } else {
        echo "<p style='color:red;'>Could not find the newly created rental in the database!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception occurred: " . $e->getMessage() . "</p>";
}
?>