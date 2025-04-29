<?php
include("session.php");
include("connect.php");

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    $_SESSION['error_message'] = "Please log in to continue.";
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];

// Process return request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'initiate_return') {
    
    // Validate required fields
    if (!isset($_POST['rental_id']) || !isset($_POST['return_method']) || !isset($_POST['condition_confirmation'])) {
        $_SESSION['error_message'] = "Missing required information. Please try again.";
        header("Location: rented_books.php?tab=rentals");
        exit();
    }
    
    $rentalId = intval($_POST['rental_id']);
    $returnMethod = $_POST['return_method'];
    
    // Verify the rental belongs to the user
    $rentalStmt = $conn->prepare("
        SELECT 
            br.rental_id, 
            br.book_id, 
            br.user_id, 
            br.seller_id, 
            br.order_id, 
            br.rental_date, 
            br.due_date, 
            br.total_price,
            b.title as book_title,
            b.author as book_author,
            b.cover_image,
            b.rent_price
        FROM book_rentals br
        JOIN books b ON br.book_id = b.book_id
        WHERE br.rental_id = ? AND br.user_id = ? AND br.status = 'active'
    ");
    $rentalStmt->bind_param("ii", $rentalId, $userId);
    $rentalStmt->execute();
    $result = $rentalStmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = "Invalid rental or you don't have permission to return this book.";
        header("Location: rented_books.php?tab=rentals");
        exit();
    }
    
    $rentalData = $result->fetch_assoc();
    
    // Check if the book is overdue
    $dueDate = strtotime($rentalData['due_date']);
    $currentDate = time();
    $isOverdue = $currentDate > $dueDate;
    $daysOverdue = 0;
    $lateFee = 0;
    
    if ($isOverdue) {
        $daysOverdue = ceil(($currentDate - $dueDate) / (60 * 60 * 24));
        $dailyLateFee = $rentalData['rent_price'] * 0.2; // 20% of daily rate
        $lateFee = $daysOverdue * $dailyLateFee;
    }
    
    // Process based on return method
    $returnStatus = 'pending';
    $additionalFee = 0;
    $returnDetails = '';
    
    if ($returnMethod === 'dropoff') {
        // Process dropoff
        if (!isset($_POST['dropoff_location'])) {
            $_SESSION['error_message'] = "Please select a dropoff location.";
            header("Location: rented_books.php?tab=rentals");
            exit();
        }
        
        $dropoffLocation = $_POST['dropoff_location'];
        $locationNames = [
            'main_office' => 'Main Office (123 Book Street, Manila)',
            'downtown_branch' => 'Downtown Branch (456 Reading Ave, Quezon City)',
            'mall_kiosk' => 'Mall Kiosk (SM Megamall, Level 3)',
            'university_hub' => 'University Hub (Near UP Diliman Campus)'
        ];
        
        $locationName = $locationNames[$dropoffLocation] ?? $dropoffLocation;
        $returnDetails = json_encode([
            'dropoff_location' => $locationName
        ]);
        
    } else if ($returnMethod === 'pickup') {
        // Process pickup
        if (!isset($_POST['pickup_date']) || !isset($_POST['pickup_time']) || !isset($_POST['pickup_address'])) {
            $_SESSION['error_message'] = "Missing pickup information. Please try again.";
            header("Location: rented_books.php?tab=rentals");
            exit();
        }
        
        $pickupDate = $_POST['pickup_date'];
        $pickupTime = $_POST['pickup_time'];
        $pickupAddress = $_POST['pickup_address'];
        $pickupNotes = $_POST['pickup_notes'] ?? '';
        
        // Validate pickup date (must be a future date)
        $pickupDateObj = new DateTime($pickupDate);
        $tomorrow = new DateTime('tomorrow');
        
        if ($pickupDateObj < $tomorrow) {
            $_SESSION['error_message'] = "Pickup date must be at least tomorrow or later.";
            header("Location: rented_books.php?tab=rentals");
            exit();
        }
        
        // Add pickup fee
        $additionalFee = 50; // ₱50 pickup fee
        
        $timeSlots = [
            'morning' => 'Morning (9AM - 12PM)',
            'afternoon' => 'Afternoon (1PM - 5PM)',
            'evening' => 'Evening (6PM - 8PM)'
        ];
        
        $formattedTime = $timeSlots[$pickupTime] ?? $pickupTime;
        $formattedDate = date('F j, Y', strtotime($pickupDate));
        
        $returnDetails = json_encode([
            'pickup_date' => $formattedDate,
            'pickup_time' => $formattedTime,
            'pickup_address' => $pickupAddress,
            'pickup_notes' => $pickupNotes
        ]);
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Create return request entry
        $createReturnStmt = $conn->prepare("
            INSERT INTO book_returns (
                rental_id, 
                user_id, 
                book_id, 
                seller_id, 
                return_method, 
                return_details,
                status,
                request_date,
                is_overdue,
                days_overdue,
                late_fee,
                additional_fee
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)
        ");
        
        $createReturnStmt->bind_param(
            "iiissssiidi",
            $rentalId,
            $userId,
            $rentalData['book_id'],
            $rentalData['seller_id'],
            $returnMethod,
            $returnDetails,
            $returnStatus,
            $isOverdue,
            $daysOverdue,
            $lateFee,
            $additionalFee
        );
        
        $createReturnStmt->execute();
        $returnId = $conn->insert_id;
        
        // Update the rental status to 'return_pending'
        $updateRentalStmt = $conn->prepare("
            UPDATE book_rentals 
            SET status = 'return_pending', return_requested_date = NOW()
            WHERE rental_id = ? AND user_id = ?
        ");
        
        $updateRentalStmt->bind_param("ii", $rentalId, $userId);
        $updateRentalStmt->execute();
        
        // Log the return request in payment_logs using the order_id from rental
        $logStmt = $conn->prepare("
            INSERT INTO payment_logs (
                order_id, 
                user_id, 
                action, 
                status, 
                amount, 
                details
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $orderIdForLog = $rentalData['order_id']; // Using order_id from rental
        $logAction = 'return_request';
        $logStatus = 'initiated';
        $logAmount = $lateFee + $additionalFee;
        $logDetails = "Return request initiated: " . $rentalData['book_title'] . 
                    " via " . ucfirst($returnMethod) . 
                    ($isOverdue ? " (Overdue: $daysOverdue days)" : "");
        
        $logStmt->bind_param("iissds", 
            $orderIdForLog, 
            $userId, 
            $logAction, 
            $logStatus, 
            $logAmount, 
            $logDetails
        );
        $logStmt->execute();
        
        // If everything is successful, commit the transaction
        $conn->commit();
        
        // Set success message based on return method
        if ($returnMethod === 'dropoff') {
            $_SESSION['success_message'] = "Return request submitted successfully. Please drop off your book at the selected location within 3 days.";
        } else {
            $_SESSION['success_message'] = "Return request submitted successfully. Your pickup has been scheduled. A pickup fee of ₱50 has been added.";
        }
        
        // Add overdue fee message if applicable
        if ($isOverdue) {
            $_SESSION['success_message'] .= " An overdue fee of ₱" . number_format($lateFee, 2) . " has been applied.";
        }
        
        // MODIFICATION: Redirect to history page with returns tab
        header("Location: history.php?tab=returns");
        exit();
        
    } catch (Exception $e) {
        // If anything goes wrong, roll back the transaction
        $conn->rollback();
        $_SESSION['error_message'] = "Failed to process return request: " . $e->getMessage();
        error_log("Return request error: " . $e->getMessage());
        
        // Redirect back to rentals page
        header("Location: rented_books.php?tab=rentals");
        exit();
    }
}

// Process return confirmation (for seller)
if (isset($_GET['action']) && $_GET['action'] === 'confirm_return' && isset($_GET['return_id'])) {
    // Additional processing for return confirmation by seller
    // (This would be implemented in seller interface)
    
    $_SESSION['error_message'] = "This feature is still being implemented.";
    header("Location: rented_books.php?tab=rentals");
    exit();
}

// If we get here, something went wrong with the request
$_SESSION['error_message'] = "Invalid request.";
header("Location: rented_books.php");
exit();
?>