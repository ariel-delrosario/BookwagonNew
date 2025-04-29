<?php
include("session.php");
include("connect.php");
include_once('order_utils.php');

$userType = $_SESSION['usertype'] ?? '';
$userId = $_SESSION['id'] ?? 0;

// Redirect if not logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get order total from session or query, depending on your setup
$orderTotal = $_SESSION['order_total'] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? '';
    $orderId = $_POST['order_id'] ?? 0;
    
    // Validate the payment method
    $validMethods = ['cod', 'pickup', 'bank_transfer'];
    if (!in_array($paymentMethod, $validMethods)) {
        $_SESSION['payment_error'] = "Invalid payment method selected.";
        header("Location: payment_methods.php");
        exit();
    }

    // Process based on payment method
    switch ($paymentMethod) {
        case 'cod':
            // Update order with COD payment method
            $updateStmt = $conn->prepare("UPDATE orders SET payment_method = ?, payment_status = 'pending' WHERE order_id = ? AND user_id = ?");
            $updateStmt->bind_param("sii", $paymentMethod, $orderId, $userId);
            $updateStmt->execute();
            
            // Make sure total amount is set correctly
            if ($order['total_amount'] <= 0) {
                calculateOrderTotal($conn, $orderId);
            }
            
            // Update purchase_type in order_items - ADD THIS LINE
            updateOrderItemPurchaseTypes($conn, $orderId);
            
            // Redirect to order confirmation
            $_SESSION['payment_success'] = true;
            header("Location: order_confirmation.php?order_id=" . $orderId);
            exit();
            break;
            
        case 'pickup':
            // Update order with pickup payment method
            $pickupLocation = $_POST['pickup_location'] ?? '';
            $pickupDate = $_POST['pickup_date'] ?? '';
            
            // Validate pickup details
            if (empty($pickupLocation) || empty($pickupDate)) {
                $_SESSION['payment_error'] = "Please provide pickup location and date.";
                header("Location: payment_methods.php");
                exit();
            }
            
            // Update the order
            $updateStmt = $conn->prepare("UPDATE orders SET payment_method = ?, payment_status = 'pending' WHERE order_id = ? AND user_id = ?");
            $updateStmt->bind_param("sii", $paymentMethod, $orderId, $userId);
            $updateStmt->execute();
            
            // Make sure total amount is set correctly
            if ($order['total_amount'] <= 0) {
                calculateOrderTotal($conn, $orderId);
            }
            
            // Update purchase_type in order_items - ADD THIS LINE
            updateOrderItemPurchaseTypes($conn, $orderId);
            
            // Redirect to order confirmation
            $_SESSION['payment_success'] = true;
            header("Location: order_confirmation.php?order_id=" . $orderId);
            exit();
            break;
            
        case 'bank_transfer':
            // Update order with bank transfer payment method
            $updateStmt = $conn->prepare("UPDATE orders SET payment_method = ?, payment_status = 'pending' WHERE order_id = ? AND user_id = ?");
            $updateStmt->bind_param("sii", $paymentMethod, $orderId, $userId);
            $updateStmt->execute();
            
            // Make sure total amount is set correctly
            if ($order['total_amount'] <= 0) {
                calculateOrderTotal($conn, $orderId);
            }
            
            // Update purchase_type in order_items - ADD THIS LINE
            updateOrderItemPurchaseTypes($conn, $orderId);
            
            // Redirect to order confirmation
            $_SESSION['payment_success'] = true;
            header("Location: order_confirmation.php?order_id=" . $orderId);
            exit();
            break;
    }
}

// Function to update order_items purchase type from cart data
function updateOrderItemPurchaseTypes($conn, $orderId) {
    $itemsQuery = "SELECT 
                    oi.item_id, 
                    oi.book_id,
                    oi.unit_price,
                    b.price,
                    b.rent_price,
                    oi.purchase_type as current_purchase_type
                   FROM order_items oi 
                   JOIN books b ON oi.book_id = b.book_id
                   WHERE oi.order_id = ?";
    $itemsStmt = $conn->prepare($itemsQuery);
    $itemsStmt->bind_param("i", $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    while ($item = $itemsResult->fetch_assoc()) {
        // Determine purchase type based on unit price
        $purchaseType = ($item['unit_price'] == $item['price']) ? 'buy' : 'rent';
        
        // Update the order item
        $updateItemStmt = $conn->prepare("UPDATE order_items 
            SET purchase_type = ?
            WHERE item_id = ?");
        $updateItemStmt->bind_param("si", $purchaseType, $item['item_id']);
        $updateItemStmt->execute();
        
        // Log for debugging
        error_log("Updated purchase type for item ID: " . $item['item_id'] . 
                   " from " . $item['current_purchase_type'] . 
                   " to: " . $purchaseType . 
                   " (Unit Price: {$item['unit_price']}, Book Price: {$item['price']})");
    }
}
// Fetch the order ID from the URL or session
$orderId = $_GET['order_id'] ?? ($_SESSION['order_id'] ?? 0);

// Fetch basic order details
$orderQuery = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$orderStmt = $conn->prepare($orderQuery);
$orderStmt->bind_param("ii", $orderId, $userId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows === 0) {
    // Order not found or doesn't belong to this user
    header("Location: cart.php");
    exit();
}

$order = $orderResult->fetch_assoc();
if ($order['total_amount'] <= 0) {
    // If the total amount is not set or zero, recalculate it
    $orderTotal = calculateOrderTotal($conn, $orderId);
} else {
    $orderTotal = $order['total_amount'];
}
error_log("Order Total in payment_methods.php: " . $orderTotal);


$itemsQuery = "SELECT oi.*, b.title, b.author 
              FROM order_items oi 
              JOIN books b ON oi.book_id = b.book_id 
              WHERE oi.order_id = ?";
$itemsStmt = $conn->prepare($itemsQuery);
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$orderItems = $itemsResult->fetch_all(MYSQLI_ASSOC);

// Calculate subtotal and tax for display purposes
$subtotal = $orderTotal / 1.1; // Remove the 10% tax
$tax = $orderTotal - $subtotal;

// Fetch available pickup locations (this would typically come from seller settings)
$pickupLocationsQuery = "SELECT DISTINCT u.address, u.city
                         FROM users u 
                         JOIN books b ON u.id = b.user_id
                         JOIN order_items oi ON b.book_id = oi.book_id
                         WHERE oi.order_id = ?";
$pickupStmt = $conn->prepare($pickupLocationsQuery);
$pickupStmt->bind_param("i", $orderId);
$pickupStmt->execute();
$pickupResult = $pickupStmt->get_result();
$pickupLocations = $pickupResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods - BookWagon</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f8a100;
            --secondary-color: #f8f9fa;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            color: var(--text-dark);
            background-color: #f4f6f9;
        }
        .navbar {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .navbar-brand img {
            height: 60px;
        }
        
        .payment-container {
            max-width: 800px;
            margin: 40px auto;
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-methods {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .payment-method-option {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .payment-method-option:last-child {
            border-bottom: none;
        }
        
        .payment-method-option:hover {
            background-color: var(--secondary-color);
        }
        
        .payment-method-option.active {
            background-color: rgba(248, 161, 0, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .payment-icon {
            font-size: 24px;
            margin-right: 15px;
            color: var(--primary-color);
        }
        
        .payment-details {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: none;
        }
        
        .payment-details.active {
            display: block;
        }
        
        .order-summary {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .order-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
        }
        
        .continue-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.2s;
            width: 100%;
            margin-top: 20px;
        }
        
        .continue-btn:hover {
            background-color: #e69400;
            transform: translateY(-2px);
        }
        
        .bank-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .bank-account-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .bank-account-label {
            width: 150px;
            font-weight: 600;
        }
        
        .bank-account-value {
            flex: 1;
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php 
    if ($userType == 'user') {
        include("include/user_header.php");
    } elseif ($userType == 'seller') {
        include("include/seller_header.php");
    }
    ?>

    <div class="container payment-container">
        <div class="payment-header">
            <h2>Choose Payment Method</h2>
            <p>Order #<?php echo $orderId; ?></p>
        </div>
        
        <?php if (isset($_SESSION['payment_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['payment_error']; unset($_SESSION['payment_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        <div class="order-summary-row">
                <span>Total</span>
                <span>₱<?php echo number_format($orderTotal, 2); ?></span>
            </div>
            <div class="order-summary">
                <h4 class="mb-3">Order Summary</h4>
                <div class="order-summary-row">
                    <span>Subtotal</span>
                    <span>₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="order-summary-row">
                    <span>Tax (10%)</span>
                    <span>₱<?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="order-summary-row">
                    <span>Total</span>
                    <span>₱<?php echo number_format($orderTotal, 2); ?></span>
                </div>
            </div>
        
        <div class="payment-methods">
            <!-- Payment Method Options -->
            <div class="payment-method-option active" data-method="cod">
                <div class="d-flex align-items-center">
                    <i class="fas fa-money-bill-wave payment-icon"></i>
                    <div>
                        <h5 class="mb-1">Cash on Delivery</h5>
                        <p class="text-muted mb-0">Pay when you receive your books</p>
                    </div>
                </div>
            </div>
            
            <div class="payment-method-option" data-method="pickup">
                <div class="d-flex align-items-center">
                    <i class="fas fa-store payment-icon"></i>
                    <div>
                        <h5 class="mb-1">Pickup / Meet-up</h5>
                        <p class="text-muted mb-0">Meet the seller and pay in person</p>
                    </div>
                </div>
            </div>
            
            <div class="payment-method-option" data-method="bank_transfer">
                <div class="d-flex align-items-center">
                    <i class="fas fa-university payment-icon"></i>
                    <div>
                        <h5 class="mb-1">Bank Transfer</h5>
                        <p class="text-muted mb-0">Pay via bank transfer</p>
                    </div>
                </div>
            </div>
            
            <!-- Payment Method Details -->
            <div id="cod-details" class="payment-details active">
                <form action="payment_methods.php" method="post">
                    <input type="hidden" name="payment_method" value="cod">
                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                    
                    <p>Your order will be delivered to your address. You'll pay the delivery person when you receive your items.</p>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirm-cod" required>
                        <label class="form-check-label" for="confirm-cod">
                            I confirm that I will pay ₱<?php echo number_format($orderTotal, 2); ?> upon delivery
                        </label>
                    </div>
                    
                    <button type="submit" class="continue-btn">Complete Order</button>
                </form>
            </div>
            
            <div id="pickup-details" class="payment-details">
                <form action="payment_methods.php" method="post">
                    <input type="hidden" name="payment_method" value="pickup">
                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                    
                    <div class="mb-3">
                        <label for="pickup-location" class="form-label">Pickup Location</label>
                        <select class="form-select" id="pickup-location" name="pickup_location" required>
                            <option value="">Select a pickup location</option>
                            <?php foreach ($pickupLocations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location['address'] . ', ' . $location['city']); ?>">
                                <?php echo htmlspecialchars($location['address'] . ', ' . $location['city']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pickup-date" class="form-label">Preferred Pickup Date</label>
                        <input type="date" class="form-control" id="pickup-date" name="pickup_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirm-pickup" required>
                        <label class="form-check-label" for="confirm-pickup">
                            I confirm that I will pay ₱<?php echo number_format($orderTotal, 2); ?> at pickup
                        </label>
                    </div>
                    
                    <button type="submit" class="continue-btn">Complete Order</button>
                </form>
            </div>
            
            <div id="bank-transfer-details" class="payment-details">
                <form action="payment_methods.php" method="post">
                    <input type="hidden" name="payment_method" value="bank_transfer">
                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                    
                    <p>Please transfer the total amount to the following bank account:</p>
                    
                    <div class="bank-details">
                        <div class="bank-account-row">
                            <div class="bank-account-label">Bank Name:</div>
                            <div class="bank-account-value">BookWagon Bank</div>
                        </div>
                        <div class="bank-account-row">
                            <div class="bank-account-label">Account Name:</div>
                            <div class="bank-account-value">BookWagon, Inc.</div>
                        </div>
                        <div class="bank-account-row">
                            <div class="bank-account-label">Account Number:</div>
                            <div class="bank-account-value">1234-5678-9012-3456</div>
                        </div>
                        <div class="bank-account-row">
                            <div class="bank-account-label">Reference:</div>
                            <div class="bank-account-value">Order #<?php echo $orderId; ?></div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        After completing your bank transfer, you'll need to upload a receipt of the transaction.
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirm-transfer" required>
                        <label class="form-check-label" for="confirm-transfer">
                            I understand that my order will be processed after payment verification
                        </label>
                    </div>
                    
                    <button type="submit" class="continue-btn">Proceed to Payment Instructions</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Payment method selection
            const paymentOptions = document.querySelectorAll('.payment-method-option');
            const paymentDetails = document.querySelectorAll('.payment-details');
            
            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Update active option
                    paymentOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding details
                    const method = this.getAttribute('data-method');
                    paymentDetails.forEach(detail => detail.classList.remove('active'));
                    document.getElementById(`${method}-details`).classList.add('active');
                });
            });
            
            // Set minimum date for pickup to tomorrow
            const pickupDateInput = document.getElementById('pickup-date');
            if (pickupDateInput) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                pickupDateInput.min = tomorrow.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>