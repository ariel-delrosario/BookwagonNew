<?php
include("session.php");
include("connect.php");

$userType = $_SESSION['usertype'] ?? '';
$firstName = $_SESSION['firstname'] ?? '';
$lastName = $_SESSION['lastname'] ?? '';
$email = $_SESSION['email'] ?? '';
$userId = $_SESSION['id'] ?? 0; // Changed from user_id to id

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify the user exists in the users table
$userCheckQuery = "SELECT * FROM users WHERE id = ?";
$userCheckStmt = $conn->prepare($userCheckQuery);
$userCheckStmt->bind_param("i", $userId);
$userCheckStmt->execute();
$userResult = $userCheckStmt->get_result();

if ($userResult->num_rows == 0) {
    die("Invalid user. Please log in again.");
}

// Check if a request is already pending for this user
$checkQuery = "SELECT * FROM sellers WHERE user_id = ? AND status = 'pending'";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$result = $checkStmt->get_result();

$hasPendingRequest = ($result->num_rows > 0);
$isAlreadySeller = ($userType == 'seller');

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$hasPendingRequest && !$isAlreadySeller) {
    // If logo upload is requested
    if(isset($_POST['action']) && $_POST['action'] == 'upload_logo') {
        $targetDir = "uploads/shop_logos/";
        $shopLogo = "";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        if(isset($_FILES["shop_logo"]) && $_FILES["shop_logo"]["error"] == 0) {
            $fileName = basename($_FILES["shop_logo"]["name"]);
            $targetFilePath = $targetDir . $userId . "_" . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            
            // Allow certain file formats
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
            if(in_array(strtolower($fileType), $allowTypes)) {
                // Upload file to server
                if(move_uploaded_file($_FILES["shop_logo"]["tmp_name"], $targetFilePath)) {
                    // Store the logo path in session for later use in business info page
                    $_SESSION['temp_shop_logo'] = $targetFilePath;
                    
                    // Redirect to business information page
                    header("Location: seller_business_info.php");
                    exit();
                } else {
                    $errorMsg = "Sorry, there was an error uploading your file.";
                }
            } else {
                $errorMsg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            $errorMsg = "Please select a file to upload.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Seller - BookWagon</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/tab.css">
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
            height: 60px;
        }
        
        /* Seller Request Form */
        .seller-request {
            max-width: 700px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .seller-request-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 15px;
            text-align: center;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .required::after {
            content: '*';
            color: red;
            margin-left: 4px;
        }
        
        .form-info {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 5px;
        }
        
        .upload-box {
            border: 2px dashed #ddd;
            padding: 25px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            background-color: #f9f9f9;
            margin-bottom: 15px;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .upload-icon {
            font-size: 40px;
            color: #aaa;
            margin-bottom: 15px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #e09000;
            border-color: #e09000;
        }
        
        .notification-box {
            background-color: #e8f4fd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #3498db;
        }
        
        .notification-box i {
            color: #3498db;
            margin-right: 10px;
        }
        
        .status-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .status-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }
        
        .status-icon.pending {
            color: #f39c12;
        }
        
        .status-icon.approved {
            color: #27ae60;
        }
        
        .status-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .status-message {
            color: var(--text-muted);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php 
    if ($userType == 'user') {
        include("include/user_header.php");
    } elseif ($userType == 'seller') {
        include("include/seller_header.php");
    }
    ?>

    <div class="container pt-3">
        <div class="seller-request">
            <h2 class="seller-request-title">Become a Seller on BookWagon</h2>
            
            <?php if($isAlreadySeller): ?>
                <div class="status-card">
                    <div class="status-icon approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="status-title">You're Already a Seller!</div>
                    <div class="status-message">
                        Your account already has seller privileges. Start selling your books today!
                    </div>
                    <a href="dashboard.php" class="btn btn-primary">Go to Seller Dashboard</a>
                </div>
            <?php elseif($hasPendingRequest): ?>
                <div class="status-card">
                    <div class="status-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="status-title">Request Pending</div>
                    <div class="status-message">
                        You already have a pending seller application. We'll notify you once it's approved.
                    </div>
                    <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
                </div>
            <?php else: ?>
                <div class="notification-box mb-4">
                    <i class="fas fa-info-circle"></i>
                    Submit your request to become a seller on BookWagon. Once approved, you'll be able to list and sell your books on our platform.
                </div>
                
                <?php if(isset($errorMsg)): ?>
                    <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-section">
                        <div class="form-group">
                            <label class="form-label required">Shop Logo</label>
                            <div class="upload-box" id="logo-upload-box">
                                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <p>Click to upload or drag and drop</p>
                                <p class="form-info">Supported formats: JPG, PNG, GIF (Max: 2MB)</p>
                            </div>
                            <input type="file" class="form-control d-none" id="shop_logo" name="shop_logo" accept="image/*" required>
                            <div id="logo-preview" class="mt-2 text-center" style="display: none;">
                                <img id="preview-img" src="#" alt="Logo Preview" style="max-height: 200px; max-width: 100%;">
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="remove-logo">Remove</button>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="action" value="upload_logo">
                    
                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-primary" id="continue-btn" disabled>Continue</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Shop logo upload preview
        document.addEventListener('DOMContentLoaded', function() {
            const logoUploadBox = document.getElementById('logo-upload-box');
            const logoInput = document.getElementById('shop_logo');
            const logoPreview = document.getElementById('logo-preview');
            const previewImg = document.getElementById('preview-img');
            const removeBtn = document.getElementById('remove-logo');
            const continueBtn = document.getElementById('continue-btn');
            
            logoUploadBox.addEventListener('click', function() {
                logoInput.click();
            });
            
            logoInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        logoPreview.style.display = 'block';
                        logoUploadBox.style.display = 'none';
                        continueBtn.disabled = false;
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            removeBtn.addEventListener('click', function() {
                logoInput.value = '';
                logoPreview.style.display = 'none';
                logoUploadBox.style.display = 'block';
                continueBtn.disabled = true;
            });
            
            // Enable drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                logoUploadBox.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                logoUploadBox.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                logoUploadBox.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                logoUploadBox.classList.add('border-primary');
            }
            
            function unhighlight() {
                logoUploadBox.classList.remove('border-primary');
            }
            
            logoUploadBox.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                logoInput.files = files;
                
                if (files && files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        logoPreview.style.display = 'block';
                        logoUploadBox.style.display = 'none';
                        continueBtn.disabled = false;
                    }
                    
                    reader.readAsDataURL(files[0]);
                }
            }
        });
    </script>
</body>
</html>