<?php
// Script to fix empty purchase_type values in order_items table

// Include database connection
include("connect.php");

// Set execution time limit
set_time_limit(300); // 5 minutes

// Log the execution
error_log("Starting purchase_type fix script");

// Get all items with empty purchase_type
$findEmptyStmt = $conn->prepare("
    SELECT oi.item_id, oi.order_id, oi.book_id, oi.unit_price, oi.rental_weeks,
           b.price, b.rent_price, b.title
    FROM order_items oi
    JOIN books b ON oi.book_id = b.book_id
    WHERE oi.purchase_type = '' OR oi.purchase_type IS NULL
");

$findEmptyStmt->execute();
$emptyItems = $findEmptyStmt->get_result();
$count = $emptyItems->num_rows;

echo "<h2>Order Items with Empty Purchase Type</h2>";
echo "<p>Found {$count} items with empty purchase type</p>";

if ($count > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>
            <th>Item ID</th>
            <th>Order ID</th>
            <th>Book Title</th>
            <th>Unit Price</th>
            <th>Book Price</th>
            <th>Rent Price</th>
            <th>Rental Weeks</th>
            <th>Determined Type</th>
        </tr>";
    
    // Process each item and update it
    $updateStmt = $conn->prepare("
        UPDATE order_items 
        SET purchase_type = ? 
        WHERE item_id = ?
    ");
    
    $updatedCount = 0;
    
    while ($item = $emptyItems->fetch_assoc()) {
        // Determine the appropriate purchase type
        $purchaseType = 'buy'; // Default
        
        // If rental weeks is set, it's definitely a rental
        if ($item['rental_weeks'] > 0) {
            $purchaseType = 'rent';
        } 
        // If unit price is significantly less than book price, it's likely a rental
        elseif ($item['unit_price'] < ($item['price'] * 0.9)) {
            $purchaseType = 'rent';
        }
        
        // Update the record
        $updateStmt->bind_param("si", $purchaseType, $item['item_id']);
        $updateResult = $updateStmt->execute();
        
        if ($updateResult) {
            $updatedCount++;
        }
        
        // Display the item
        echo "<tr>
                <td>{$item['item_id']}</td>
                <td>{$item['order_id']}</td>
                <td>{$item['title']}</td>
                <td>{$item['unit_price']}</td>
                <td>{$item['price']}</td>
                <td>{$item['rent_price']}</td>
                <td>{$item['rental_weeks']}</td>
                <td style='font-weight: bold;'>{$purchaseType}</td>
            </tr>";
    }
    
    echo "</table>";
    echo "<p>Updated {$updatedCount} items successfully</p>";
} else {
    echo "<p>No items found with empty purchase type. All records are good!</p>";
}

// Add navigation links
echo "<div style='margin-top: 20px;'>";
echo "<a href='index.php' style='padding: 10px 15px; background-color: #f8a100; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Return to Home</a>";
echo "<a href='admin/dashboard.php' style='padding: 10px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>Admin Dashboard</a>";
echo "</div>";

// Log completion
error_log("Purchase type fix script completed. Fixed {$updatedCount} records out of {$count}");
?> 