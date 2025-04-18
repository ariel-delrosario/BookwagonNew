<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bookwagon_db";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debug session information
error_log("Session variables: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please login to continue.";
    header("Location: login.php");
    exit();
}

// Check if user is already a seller
try {
    error_log("Checking seller status for user ID: " . $_SESSION['user_id']);
    
    $sql = "SELECT u.is_seller, sd.verification_status 
            FROM users u 
            LEFT JOIN seller_details sd ON u.id = sd.user_id 
            WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $_SESSION['user_id']);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    error_log("User data from seller check: " . print_r($user, true));
    
    // Only redirect to dashboard if user is a verified seller
    if ($user && $user['is_seller'] == 1 && $user['verification_status'] === 'verified') {
        $_SESSION['error_message'] = "You are already registered as a seller.";
        header("Location: seller_dashboard.php");
        exit();
    }

    // If user has started the process but not completed it, continue
    if ($user && $user['is_seller'] == 1 && $user['verification_status'] !== 'verified') {
        // Check which step they need to complete
        if (!isset($user['verification_status']) || $user['verification_status'] === 'pending') {
            // They need to complete the address step
            header("Location: seller_address.php");
            exit();
        }
    }

    // Fetch user data for the form
    $sql = "SELECT firstName, lastName, middleInitial, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $_SESSION['user_id']);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    error_log("User details data: " . print_r($userData, true));
    
    if (!$userData) {
        error_log("No user data found for ID: " . $_SESSION['user_id']);
        $_SESSION['error_message'] = "User data not found. Please try logging in again.";
        header("Location: login.php");
        exit();
    }

} catch(Exception $e) {
    error_log("Error in start_selling.php: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred while processing your request. Please try again later.";
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWagon - Start Selling</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            background-color: #fff;
        }
        
        /* Header styles */
        .navbar {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .nav-item {
            margin: 0 10px;
        }
        
        .nav-link {
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid var(--primary-color);
        }
        
        /* Form container styles */
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .form-select, .form-control {
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 8px;
        }
        .form-select:focus, .form-control:focus {
            border-color: #ddd;
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333 !important;
            font-weight: 600;
        }
        .btn-dark {
            padding: 12px 24px;
            font-weight: 500;
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        /* Add hover styles for navigation links */
        .nav-link.me-3:hover {
            color: var(--primary-color);
            transition: color 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Header/Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="images/logo.png" alt="BookWagon">
            </a>
            
            <div class="d-flex align-items-center">
                <a href="#" class="nav-link me-3" style="transition: color 0.3s ease;"><i class="fa-regular fa-bell"></i></a>
                <a href="#" class="nav-link me-3" style="transition: color 0.3s ease;"><i class="fa-regular fa-envelope"></i></a>
                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : htmlspecialchars($_SESSION['email']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Navigation tabs -->
    <div class="container">
    <ul class="nav nav-underline mb-4 justify-content-center mt-5">
        <li class="nav-item">
            <a class="nav-link active" href="#">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Rent Books</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Explore</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Libraries</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Book Swap</a>
        </li>
    </ul>
</div>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Seller Registration</h2>
            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="process_owner_details.php" method="POST" enctype="multipart/form-data">
                <!-- Personal Information -->
                <div class="mb-4">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" class="form-control bg-light" id="lastName" name="lastName" 
                           value="<?php echo htmlspecialchars($userData['lastName']); ?>" readonly>
                </div>
                <div class="mb-4">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" class="form-control bg-light" id="firstName" name="firstName" 
                           value="<?php echo htmlspecialchars($userData['firstName']); ?>" readonly>
                </div>
                <div class="mb-4">
                    <label for="middleInitial" class="form-label">Middle Initial</label>
                    <input type="text" class="form-control bg-light" id="middleInitial" name="middleInitial" 
                           value="<?php echo htmlspecialchars($userData['middleInitial']); ?>" readonly>
                </div>
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control bg-light" id="email" name="email" 
                           value="<?php echo htmlspecialchars($userData['email']); ?>" readonly>
                </div>

                <!-- Contact Information -->
                <div class="mb-4">
                    <label for="phoneNumber" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" required>
                </div>
                <div class="mb-4">
                    <label for="socialMedia" class="form-label">Social Media Link (Optional)</label>
                    <input type="text" class="form-control" id="socialMedia" name="socialMedia">
                </div>

                <!-- ID Information -->
                <div class="mb-4">
                    <label for="primaryIdType" class="form-label">Primary ID Type</label>
                    <select class="form-select" id="primaryIdType" name="primaryIdType" required>
                        <option value="">Select ID Type</option>
                        <option value="passport">Passport</option>
                        <option value="driver_license">Driver's License</option>
                        <option value="national_id">National ID</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-4" id="otherPrimaryIdTypeContainer" style="display: none;">
                    <label for="otherPrimaryIdType" class="form-label">Specify Other ID Type</label>
                    <input type="text" class="form-control" id="otherPrimaryIdType" name="otherPrimaryIdType">
                </div>

                <!-- Primary ID Upload -->
                <div class="mb-4">
                    <label class="form-label">Primary ID Images</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="primaryIdFront" class="form-label">Front Image</label>
                            <input type="file" class="form-control" id="primaryIdFront" name="primaryIdFront" accept="image/*" required>
                            <small class="text-muted">Upload the front side of your ID</small>
                        </div>
                        <div class="col-md-6">
                            <label for="primaryIdBack" class="form-label">Back Image</label>
                            <input type="file" class="form-control" id="primaryIdBack" name="primaryIdBack" accept="image/*" required>
                            <small class="text-muted">Upload the back side of your ID</small>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="secondaryIdType" class="form-label">Secondary ID Type</label>
                    <select class="form-select" id="secondaryIdType" name="secondaryIdType" required>
                        <option value="">Select ID Type</option>
                        <option value="passport">Passport</option>
                        <option value="driver_license">Driver's License</option>
                        <option value="national_id">National ID</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-4" id="otherSecondaryIdTypeContainer" style="display: none;">
                    <label for="otherSecondaryIdType" class="form-label">Specify Other ID Type</label>
                    <input type="text" class="form-control" id="otherSecondaryIdType" name="otherSecondaryIdType">
                </div>

                <!-- Secondary ID Upload -->
                <div class="mb-4">
                    <label class="form-label">Secondary ID Images</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="secondaryIdFront" class="form-label">Front Image</label>
                            <input type="file" class="form-control" id="secondaryIdFront" name="secondaryIdFront" accept="image/*" required>
                            <small class="text-muted">Upload the front side of your ID</small>
                        </div>
                        <div class="col-md-6">
                            <label for="secondaryIdBack" class="form-label">Back Image</label>
                            <input type="file" class="form-control" id="secondaryIdBack" name="secondaryIdBack" accept="image/*" required>
                            <small class="text-muted">Upload the back side of your ID</small>
                        </div>
                    </div>
                </div>

                <!-- Selfie with ID -->
                <div class="mb-4">
                    <label class="form-label">Selfie with Primary ID</label>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="file" class="form-control" id="selfieImage" name="selfieImage" accept="image/*" required>
                            <small class="text-muted">Upload a selfie of you holding your primary ID</small>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-dark">Next</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('primaryIdType').addEventListener('change', function() {
            document.getElementById('otherPrimaryIdTypeContainer').style.display = 
                this.value === 'other' ? 'block' : 'none';
        });

        document.getElementById('secondaryIdType').addEventListener('change', function() {
            document.getElementById('otherSecondaryIdTypeContainer').style.display = 
                this.value === 'other' ? 'block' : 'none';
        });
    </script>
</body>
</html>