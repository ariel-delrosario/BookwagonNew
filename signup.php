<?php
// Database configuration
$db_host = "localhost";      // Usually "localhost" for local development
$db_user = "root";           // Database username (default is "root" for XAMPP/MAMP)
$db_pass = "";               // Database password (often empty for local development)
$db_name = "bookwagon_db";   // Your database name

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$firstname = $middlename = $lastname = $email = $password = $confirm_password = "";
$firstname_err = $lastname_err = $email_err = $password_err = $confirm_password_err = $signup_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate firstname
    if (empty(trim($_POST["firstname"]))) {
        $firstname_err = "Please enter your first name.";
    } else {
        $firstname = trim($_POST["firstname"]);
    }
    
    // Middle name is optional, just get it if provided
    $middlename = trim($_POST["middlename"]);
    
    // Validate lastname
    if (empty(trim($_POST["lastname"]))) {
        $lastname_err = "Please enter your last name.";
    } else {
        $lastname = trim($_POST["lastname"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        // Prepare a select statement to check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }
    
    // Check input errors before inserting into database
    if (empty($firstname_err) && empty($lastname_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (firstname, middlename, lastname, email, password, usertype) VALUES (?, ?, ?, ?, ?, ?)";
         
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssss", $param_firstname, $param_middlename, $param_lastname, $param_email, $param_password, $param_usertype);
            
            // Set parameters
            $param_firstname = $firstname;
            $param_middlename = $middlename;
            $param_lastname = $lastname;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_usertype = "user"; // Set default user type
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                echo "<script>
                alert('Account created successfully!');
                window.location.href = 'login.php';
            </script>";
            } else {
                $signup_err = "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWagon - Sign Up</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #f8a100;
            --secondary-color: #f8f9fa;
            --accent-blue: #5b6bff;
            --bg-cream: #faebc8;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        
        .signup-container {
            max-width: 1000px; /* Increased from 800px to 1000px */
            margin: 40px auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: white;
        }
        
        .signup-row {
            display: flex;
            min-height: 650px;
        }
        
        .signup-image {
            flex: 0.8; /* Slightly reduced from 1 to give more space to the form */
            background-color: var(--bg-cream);
            position: relative;
            overflow: hidden;
        }
        
        .signup-form {
            flex: 1.2; /* Increased from 1 to give more space for the form fields */
            padding: 40px 50px; /* Increased horizontal padding */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            height: 100px;
        }
        
        h2 {
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-control {
            height: 50px;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        
        .btn-signup {
            height: 50px;
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 18px;
            margin-top: 10px;
        }
        
        .btn-signup:hover {
            background-color: #e09000;
        }
        
        .form-divider {
            text-align: center;
            position: relative;
            margin: 30px 0;
        }
        
        .form-divider::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: #ddd;
        }
        
        .form-divider::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: #ddd;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .blob-blue {
            position: absolute;
            background-color: var(--accent-blue);
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            z-index: 0;
        }
        
        .blob-blue-1 {
            width: 200px;
            height: 200px;
            top: -50px;
            left: -50px;
        }
        
        .blob-blue-2 {
            width: 300px;
            height: 300px;
            bottom: -100px;
            left: -50px;
        }
        
        .blob-blue-3 {
            width: 180px;
            height: 180px;
            top: 50%;
            right: -50px;
            transform: translateY(-50%);
        }
        
        .name-row {
            display: flex;
            gap: 15px; /* Increased gap between name fields */
            margin-bottom: 0;
        }
        
        .name-row .form-group {
            flex: 1;
        }
        
        .optional-field {
            font-size: 12px;
            color: #6c757d;
            margin-left: 5px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .signup-container {
                max-width: 90%;
            }
        }
        
        @media (max-width: 768px) {
            .signup-row {
                flex-direction: column;
            }
            
            .signup-image {
                display: none; /* Hide the image on smaller screens */
            }
            
            .signup-form {
                padding: 30px;
            }
            
            .name-row {
                flex-direction: column;
                gap: 0;
            }
            
            .name-row .form-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-container">
            <div class="signup-row">
                <!-- Left side - Decorative illustration -->
                <div class="signup-image">
                    <div class="blob-blue blob-blue-1"></div>
                    <div class="blob-blue blob-blue-2"></div>
                    <div class="blob-blue blob-blue-3"></div>
                </div>
                
                <!-- Right side - Signup form -->
                <div class="signup-form">
                    <div class="logo">
                        <img src="images/logo.png" alt="BookWagon Logo">
                    </div>
                    
                    <h2>Create an Account</h2>
                    
                    <?php 
                    if(!empty($signup_err)){
                        echo '<div class="alert alert-danger">' . $signup_err . '</div>';
                    }        
                    ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="name-row">
                            <div class="form-group">
                                <input type="text" name="firstname" class="form-control <?php echo (!empty($firstname_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $firstname; ?>" placeholder="First Name">
                                <span class="invalid-feedback"><?php echo $firstname_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <input type="text" name="middlename" class="form-control" 
                                       value="<?php echo $middlename; ?>" placeholder="Middle Name">
                            </div>
                            
                            <div class="form-group">
                                <input type="text" name="lastname" class="form-control <?php echo (!empty($lastname_err)) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo $lastname; ?>" placeholder="Last Name">
                                <span class="invalid-feedback"><?php echo $lastname_err; ?></span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo $email; ?>" placeholder="Email Address">
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>
                        
                        <div class="form-group">
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                                   placeholder="Password">
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>
                        
                        <div class="form-group">
                            <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                                   placeholder="Confirm Password">
                            <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="termsCheck" required>
                            <label class="form-check-label" for="termsCheck">
                                I agree to the <a href="terms.php" class="text-primary">Terms of Service</a> and <a href="privacy.php" class="text-primary">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-signup w-100">Sign Up</button>
                        </div>
                        
                        <div class="form-divider">Or</div>
                        
                        <div class="login-link">
                            Already have an account? <a href="login.php" class="text-primary">Sign in</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>