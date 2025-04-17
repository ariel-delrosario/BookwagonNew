<?php
session_start();
require_once 'connect.php';

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

        // Insert address - using the correct column names from the database
        $stmt = $pdo->prepare("INSERT INTO seller_addresses (seller_id, street_address, city, state, postal_code, country, is_default)
            VALUES (:seller_id, :street_address, :city, :state, :postal_code, :country, TRUE)");
        
        $stmt->execute([
            ':seller_id' => $_SESSION['temp_seller_id'],
            ':street_address' => $_POST['detailed_address'],
            ':city' => $_POST['city'],
            ':state' => $_POST['province'], // Using province as state
            ':postal_code' => $_POST['postal_code'],
            ':country' => $_POST['country']
        ]);

        // Update seller_details to mark registration as complete
        $stmt = $pdo->prepare("UPDATE seller_details SET registration_complete = TRUE WHERE id = :seller_id");
        $stmt->execute([':seller_id' => $_SESSION['temp_seller_id']]);

        // Update user's is_seller status
        $stmt = $pdo->prepare("UPDATE users SET is_seller = TRUE WHERE id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);

        // Commit transaction
        $pdo->commit();

        // Clear temporary session data
        unset($_SESSION['temp_seller_id']);
        unset($_SESSION['address_form_data']);
        unset($_SESSION['seller_data']);
        
        // Set success message
        $_SESSION['success_message'] = "Congratulations! Your seller registration is complete. You can now start selling on Bookwagon!";
        
        // Redirect to seller dashboard
        header("Location: seller_dashboard.php");
        exit();

    } catch (Exception $e) {
        // Roll back transaction if active
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Log error
        error_log("Error in process_seller_address.php: " . $e->getMessage());
        
        // Store error message and form data
        $_SESSION['error_message'] = "An error occurred while processing your request: " . $e->getMessage();
        $_SESSION['address_form_data'] = $_POST;
        
        // Redirect back to form
        header("Location: seller_address.php");
        exit();
    }
} else {
    // Redirect to form if accessed directly
    header("Location: seller_address.php");
    exit();
}
?> 