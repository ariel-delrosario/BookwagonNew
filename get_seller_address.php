<?php
include("connect.php");

// Initialize response array
$response = [
    'success' => false,
    'address' => null,
    'error' => null
];

// Check if seller_id is provided
if (!isset($_GET['seller_id']) || empty($_GET['seller_id'])) {
    $response['error'] = "Seller ID is required";
    echo json_encode($response);
    exit();
}

$sellerId = intval($_GET['seller_id']);

try {
    // Query to get seller address information
    $sellerQuery = $conn->prepare("
        SELECT 
            shop_name,
            business_name,
            first_name,
            last_name,
            location,
            address,
            zip_code
        FROM 
            sellers 
        WHERE 
            id = ?
    ");
    
    $sellerQuery->bind_param("i", $sellerId);
    $sellerQuery->execute();
    $result = $sellerQuery->get_result();
    
    if ($result->num_rows > 0) {
        $sellerData = $result->fetch_assoc();
        
        // Format the address details
        $response['success'] = true;
        $response['address'] = [
            'name' => !empty($sellerData['shop_name']) ? $sellerData['shop_name'] : $sellerData['business_name'],
            'address' => $sellerData['address'],
            'city' => $sellerData['location'],
            'province' => '', // Add this if available in your database
            'postal_code' => $sellerData['zip_code'],
            'contact_person' => $sellerData['first_name'] . ' ' . $sellerData['last_name']
        ];
    } else {
        $response['error'] = "Seller not found";
    }
} catch (Exception $e) {
    $response['error'] = "Database error: " . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?> 