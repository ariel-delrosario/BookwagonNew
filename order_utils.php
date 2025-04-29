<?php
// Add this to connect.php or create a new file called order_utils.php and include it where needed

function calculateOrderTotal($conn, $orderId) {
    // Calculate total based on order items with correct purchase type handling
    $query = "SELECT SUM(
                CASE 
                    WHEN oi.purchase_type = 'rent' THEN oi.unit_price * oi.quantity
                    ELSE oi.unit_price * oi.quantity
                END
              ) as total
              FROM order_items oi
              WHERE oi.order_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    // Apply tax (10%)
    $subtotal = $data['total'] ?? 0;
    $tax = $subtotal * 0.10;
    $total = $subtotal + $tax;
    
    // Update the orders table with the correct total
    $updateQuery = "UPDATE orders SET total_amount = ? WHERE order_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("di", $total, $orderId);
    $updateStmt->execute();
    
    return $total;
}
?>