<?php
session_start();
require_once 'connect.php';

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Check if the script is being accessed
file_put_contents('debug.txt', 'Script accessed at ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug: Check session data
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));
        
        // Check if user is logged in
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            error_log("User not logged in");
            throw new Exception("User not logged in. Please login first.");
        }
        
        // Get user_id from session
        if (!isset($_SESSION['user_id'])) {
            error_log("User ID not found in session");
            throw new Exception("User session information not found");
        }
        
        // Store all form data in session immediately
        $_SESSION['form_data'] = $_POST;
        
        // Validate required fields
        $required_fields = ['firstName', 'lastName', 'phoneNumber', 'email', 'primaryIdType', 'secondaryIdType'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
            }
        }
        
        // Validate email format
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        
        // Validate phone number (basic validation)
        if (!empty($_POST['phoneNumber']) && !preg_match("/^[0-9+\-\s()]*$/", $_POST['phoneNumber'])) {
            $errors[] = "Invalid phone number format.";
        }
        
        // Check if there are any errors
        if (!empty($errors)) {
            error_log("Validation errors: " . print_r($errors, true));
            $_SESSION['error_message'] = implode("<br>", $errors);
            header("Location: start_selling.php");
            exit();
        }

        // Start transaction
        $pdo->beginTransaction();
        error_log("Transaction started");

        // Insert seller details without setting is_seller status
        $stmt = $pdo->prepare("INSERT INTO seller_details (user_id, phone_number, social_media_link, verification_status)
            VALUES (:user_id, :phoneNumber, :socialMedia, 'pending')");
        
        try {
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':phoneNumber' => $_POST['phoneNumber'],
                ':socialMedia' => $_POST['socialMedia']
            ]);
            error_log("Seller details inserted successfully");
        } catch (PDOException $e) {
            error_log("Database error while inserting seller details: " . $e->getMessage());
            throw new Exception("Database error while inserting seller details: " . $e->getMessage());
        }

        $seller_id = $pdo->lastInsertId();
        error_log("Seller ID: " . $seller_id);

        // Handle file uploads for primary ID
        if (isset($_FILES['primaryIdFront'])) {
            error_log("Primary ID front file info: " . print_r($_FILES['primaryIdFront'], true));
            
            if ($_FILES['primaryIdFront']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Create uploads directory if it doesn't exist
                    $baseUploadDir = "uploads";
                    if (!file_exists($baseUploadDir)) {
                        if (!mkdir($baseUploadDir, 0777, true)) {
                            throw new Exception("Failed to create base upload directory");
                        }
                    }
                    
                    // Create seller-specific directory
                    $uploadDir = $baseUploadDir . "/seller_" . $seller_id;
                    if (!file_exists($uploadDir)) {
                        if (!mkdir($uploadDir, 0777, true)) {
                            throw new Exception("Failed to create seller upload directory");
                        }
                    }
                    
                    // Generate unique filename
                    $timestamp = time();
                    $random = bin2hex(random_bytes(8));
                    $extension = pathinfo($_FILES['primaryIdFront']['name'], PATHINFO_EXTENSION);
                    $filename = "id_front_" . $timestamp . "_" . $random . "." . $extension;
                    $filepath = $uploadDir . "/" . $filename;
                    
                    // Check if file was uploaded successfully
                    if (!move_uploaded_file($_FILES['primaryIdFront']['tmp_name'], $filepath)) {
                        throw new Exception("Failed to move uploaded file");
                    }
                    
                    $primary_id_front = $filepath;
                    error_log("Primary ID front uploaded successfully to: " . $primary_id_front);
                } catch (Exception $e) {
                    error_log("Primary ID front upload error: " . $e->getMessage());
                    throw new Exception("Primary ID front image upload failed: " . $e->getMessage());
                }
            } else {
                $error = getUploadErrorMessage($_FILES['primaryIdFront']['error']);
                error_log("Primary ID front upload error: " . $error);
                throw new Exception("Primary ID front image upload failed: " . $error);
            }
        } else {
            error_log("Primary ID front file not found in upload");
            throw new Exception("Primary ID front image is required");
        }

        // Similar update for primaryIdBack
        if (isset($_FILES['primaryIdBack'])) {
            error_log("Primary ID back file info: " . print_r($_FILES['primaryIdBack'], true));
            
            if ($_FILES['primaryIdBack']['error'] === UPLOAD_ERR_OK) {
                try {
                    $uploadDir = "uploads/seller_" . $seller_id;
                    
                    // Generate unique filename
                    $timestamp = time();
                    $random = bin2hex(random_bytes(8));
                    $extension = pathinfo($_FILES['primaryIdBack']['name'], PATHINFO_EXTENSION);
                    $filename = "id_back_" . $timestamp . "_" . $random . "." . $extension;
                    $filepath = $uploadDir . "/" . $filename;
                    
                    if (!move_uploaded_file($_FILES['primaryIdBack']['tmp_name'], $filepath)) {
                        throw new Exception("Failed to move uploaded file");
                    }
                    
                    $primary_id_back = $filepath;
                    error_log("Primary ID back uploaded successfully to: " . $primary_id_back);
                } catch (Exception $e) {
                    error_log("Primary ID back upload error: " . $e->getMessage());
                    throw new Exception("Primary ID back image upload failed: " . $e->getMessage());
                }
            } else {
                $error = getUploadErrorMessage($_FILES['primaryIdBack']['error']);
                error_log("Primary ID back upload error: " . $error);
                throw new Exception("Primary ID back image upload failed: " . $error);
            }
        } else {
            error_log("Primary ID back file not found in upload");
            throw new Exception("Primary ID back image is required");
        }

        // Insert primary ID
        $stmt = $pdo->prepare("INSERT INTO seller_ids (seller_id, id_type, id_name, id_image_path)
            VALUES (:seller_id, 'primary', :id_name, :id_image_path)");
        
        try {
            $stmt->execute([
                ':seller_id' => $seller_id,
                ':id_name' => $_POST['primaryIdType'],
                ':id_image_path' => $primary_id_front
            ]);
            error_log("Primary ID front inserted successfully");

            // Insert back image as a separate record
            $stmt->execute([
                ':seller_id' => $seller_id,
                ':id_name' => $_POST['primaryIdType'] . '_back',
                ':id_image_path' => $primary_id_back
            ]);
            error_log("Primary ID back inserted successfully");
        } catch (PDOException $e) {
            error_log("Database error while inserting primary ID: " . $e->getMessage());
            throw new Exception("Database error while inserting primary ID: " . $e->getMessage());
        }

        // Handle file uploads for secondary ID
        if (isset($_FILES['secondaryIdFront']) && $_FILES['secondaryIdFront']['error'] === UPLOAD_ERR_OK) {
            try {
                $secondary_id_front = handleFileUpload('secondaryIdFront', $seller_id, 'id_front');
                error_log("Secondary ID front uploaded: " . $secondary_id_front);
            } catch (Exception $e) {
                error_log("Secondary ID front upload error: " . $e->getMessage());
                throw new Exception("Secondary ID front image upload failed: " . $e->getMessage());
            }
        } else {
            $error = getUploadErrorMessage($_FILES['secondaryIdFront']['error']);
            error_log("Secondary ID front upload error: " . $error);
            throw new Exception("Secondary ID front image upload failed: " . $error);
        }

        if (isset($_FILES['secondaryIdBack']) && $_FILES['secondaryIdBack']['error'] === UPLOAD_ERR_OK) {
            try {
                $secondary_id_back = handleFileUpload('secondaryIdBack', $seller_id, 'id_back');
                error_log("Secondary ID back uploaded: " . $secondary_id_back);
            } catch (Exception $e) {
                error_log("Secondary ID back upload error: " . $e->getMessage());
                throw new Exception("Secondary ID back image upload failed: " . $e->getMessage());
            }
        } else {
            $error = getUploadErrorMessage($_FILES['secondaryIdBack']['error']);
            error_log("Secondary ID back upload error: " . $error);
            throw new Exception("Secondary ID back image upload failed: " . $error);
        }

        // Insert secondary ID
        $stmt = $pdo->prepare("INSERT INTO seller_ids (seller_id, id_type, id_name, id_image_path)
            VALUES (:seller_id, 'secondary', :id_name, :id_image_path)");
        
        try {
            $stmt->execute([
                ':seller_id' => $seller_id,
                ':id_name' => $_POST['secondaryIdType'],
                ':id_image_path' => $secondary_id_front
            ]);
            error_log("Secondary ID front inserted successfully");

            // Insert back image as a separate record
            $stmt->execute([
                ':seller_id' => $seller_id,
                ':id_name' => $_POST['secondaryIdType'] . '_back',
                ':id_image_path' => $secondary_id_back
            ]);
            error_log("Secondary ID back inserted successfully");
        } catch (PDOException $e) {
            error_log("Database error while inserting secondary ID: " . $e->getMessage());
            throw new Exception("Database error while inserting secondary ID: " . $e->getMessage());
        }

        // Handle selfie upload
        if (isset($_FILES['selfieImage']) && $_FILES['selfieImage']['error'] === UPLOAD_ERR_OK) {
            try {
                $selfie_path = handleFileUpload('selfieImage', $seller_id, 'selfie');
                error_log("Selfie uploaded: " . $selfie_path);
            } catch (Exception $e) {
                error_log("Selfie upload error: " . $e->getMessage());
                throw new Exception("Selfie image upload failed: " . $e->getMessage());
            }
        } else {
            $error = getUploadErrorMessage($_FILES['selfieImage']['error']);
            error_log("Selfie upload error: " . $error);
            throw new Exception("Selfie image upload failed: " . $error);
        }

        // Insert selfie
        $stmt = $pdo->prepare("INSERT INTO seller_selfies (seller_id, selfie_path) VALUES (:seller_id, :selfie_path)");
        try {
            $stmt->execute([
                ':seller_id' => $seller_id,
                ':selfie_path' => $selfie_path
            ]);
            error_log("Selfie inserted successfully");
        } catch (PDOException $e) {
            error_log("Database error while inserting selfie: " . $e->getMessage());
            throw new Exception("Database error while inserting selfie: " . $e->getMessage());
        }

        // Insert verification status
        $stmt = $pdo->prepare("INSERT INTO seller_verification (seller_id, status) VALUES (:seller_id, 'pending')");
        try {
            $stmt->execute([':seller_id' => $seller_id]);
            error_log("Verification status inserted successfully");
        } catch (PDOException $e) {
            error_log("Database error while inserting verification status: " . $e->getMessage());
            throw new Exception("Database error while inserting verification status: " . $e->getMessage());
        }

        // If everything is successful, commit the transaction
        $pdo->commit();
        error_log("Transaction committed successfully");
        
        // Store the seller data in session for the next step
        $_SESSION['seller_data'] = [
            'seller_id' => $seller_id,
            'phoneNumber' => $_POST['phoneNumber'],
            'socialMedia' => $_POST['socialMedia']
        ];
        
        // Set the temp_seller_id for the next step
        $_SESSION['temp_seller_id'] = $seller_id;
        
        // Clear any previous error messages and form data
        unset($_SESSION['error_message']);
        unset($_SESSION['form_data']);
        unset($_SESSION['redirect_to_dashboard']);
        
        // Set success message
        $_SESSION['success_message'] = "Personal details saved successfully! Please proceed to add your address.";
        
        // Redirect to the next step
        header("Location: seller_address.php");
        exit();

    } catch (Exception $e) {
        // If there's an error, roll back the transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            error_log("Transaction rolled back due to error");
        }
        
        // Log the error
        error_log("Error in process_owner_details.php: " . $e->getMessage());
        
        // Store error in session
        $_SESSION['error_message'] = "An error occurred while processing your request: " . $e->getMessage();
        
        // Store form data in session to preserve input
        $_SESSION['form_data'] = $_POST;
        
        // Redirect back to the form
        header("Location: start_selling.php");
        exit();
    }
} else {
    // Debug: Log if not POST request
    file_put_contents('debug.txt', 'Not a POST request: ' . $_SERVER["REQUEST_METHOD"] . "\n", FILE_APPEND);
    
    // Redirect to the form page if accessed directly
    header("Location: start_selling.php");
    exit();
}

// Function to handle file uploads
function handleFileUpload($fieldName, $seller_id, $type, $index = null) {
    $uploadDir = "uploads/seller_" . $seller_id . "/";
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("Failed to create upload directory: " . $uploadDir);
        }
    }
    
    // Generate unique filename
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    
    if ($index !== null) {
        $file = $_FILES[$fieldName]['name'][$index];
        $tmp_name = $_FILES[$fieldName]['tmp_name'][$index];
    } else {
        $file = $_FILES[$fieldName]['name'];
        $tmp_name = $_FILES[$fieldName]['tmp_name'];
    }
    
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $filename = $type . "_" . $timestamp . "_" . $random . "." . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($tmp_name, $filepath)) {
        throw new Exception("Failed to move uploaded file: " . $file);
    }
    
    return $filepath;
}

// Function to get upload error message
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload";
        default:
            return "Unknown upload error";
    }
}
?> 