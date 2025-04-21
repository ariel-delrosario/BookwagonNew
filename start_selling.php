<?php
include("session.php");
include("connect.php");

$userType = $_SESSION['usertype'] ?? '';
$firstName = $_SESSION['firstname'] ?? '';
$lastName = $_SESSION['lastname'] ?? '';
$email = $_SESSION['email'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// At the top of start_selling.php


// Check if the form was submitted to handle upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'upload_logo') {
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
                $error = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } else {
        $error = "Please select a file to upload.";
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
        
        /* Seller Registration Form */
        .seller-registration {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .progress-steps::after {
            content: '';
            position: absolute;
            top: 14px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e0e0e0;
            z-index: 1;
        }
        
        .step {
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .step-circle {
            width: 30px;
            height: 30px;
            background-color: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
        }
        
        .step.active .step-circle {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-circle {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .step-label {
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
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
            min-height: 200px;
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
        <div class="seller-registration">
            <div class="progress-steps">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <div class="step-label">Shop Information</div>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <div class="step-label">Business Information</div>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <div class="step-label">Submit</div>
                </div>
            </div>
            
            <div class="notification-box mb-4">
                <i class="fas fa-info-circle"></i>
                This information will be used to ensure proper compliance to seller onboarding requirements to e-marketplace and invoicing purposes.
                <br>
                <small>Note: BookWagon will not re-use any invoices or tax documents due to incomplete or incorrect information provided by sellers.</small>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <!-- Shop Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Shop Information</h3>
                    
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
        
        // Prevent back button after logout
        window.onload = function() {
            if(typeof window.history.pushState == 'function') {
                window.history.pushState({}, "Hide", location.href);
            }
            window.onpopstate = function() {
                window.history.go(1);
            };
        };
    </script>
</body>
</html>-check mb-4">
                    <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                    <label class="form-check-label" for="agree_terms">
                        I agree to these <a href="#" target="_blank">Terms and Conditions</a> and <a href="#" target="_blank">Data Privacy Policy</a>
                    </label>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
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
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            removeBtn.addEventListener('click', function() {
                logoInput.value = '';
                logoPreview.style.display = 'none';
                logoUploadBox.style.display = 'block';
            });
        });
        
        // Prevent back button after logout
        window.onload = function() {
            if(typeof window.history.pushState == 'function') {
                window.history.pushState({}, "Hide", location.href);
            }
            window.onpopstate = function() {
                window.history.go(1);
            };
        };
    </script>
</body>
</html>