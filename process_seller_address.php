<?php
include("session.php");
include("connect.php");

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug: Check session data
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("POST data: " . print_r($_POST, true));
        
        // Check if user is logged in
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            throw new Exception("User not logged in. Please login first.");
        }
        
        // Check if seller_id exists in session
        if (!isset($_SESSION['temp_seller_id'])) {
            throw new Exception("Seller information not found. Please complete the previous step first.");
        }

        // Store form data in session immediately
        $_SESSION['address_form_data'] = $_POST;
        
        // Validate required fields
        $required_fields = ['country', 'city', 'postal_code', 'detailed_address'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error_message'] = implode("<br>", $errors);
            header("Location: seller_address.php");
            exit();
        }

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Insert address
            $sql = "INSERT INTO seller_addresses (
                seller_id, 
                street_address,
                city,
                state,
                postal_code,
                country,
                is_default
            ) VALUES (?, ?, ?, ?, ?, ?, 1)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_SESSION['temp_seller_id'],
                $_POST['detailed_address'],
                $_POST['city'],
                $_POST['state'] ?? '',
                $_POST['postal_code'],
                $_POST['country']
            ]);

            // Update seller_details to mark registration as complete
            $sql = "UPDATE seller_details 
                   SET registration_complete = 1,
                       verification_status = 'pending'
                   WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['temp_seller_id']]);

            // Commit transaction
            $pdo->commit();

            // Clear temporary session data
            unset($_SESSION['temp_seller_id']);
            unset($_SESSION['address_form_data']);

            // Set success message
            $_SESSION['success_message'] = "Your seller application has been submitted successfully! Please wait for admin approval.";
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        error_log("Error in process_seller_address.php: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred while processing your request: " . $e->getMessage();
        header("Location: seller_address.php");
        exit();
    }
} else {
    header("Location: seller_address.php");
    exit();
}
?> 