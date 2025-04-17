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
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Check input errors before checking database
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, email, password FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);
            
            // Set parameters
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if email exists, if yes then verify password
                if ($stmt->num_rows == 1) {                    
                    // Bind result variables
                    $stmt->bind_result($id, $email, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["user"] = $email;

                            // Get user details
                            $user_query = "SELECT firstName, lastName FROM users WHERE id = ?";
                            $stmt_user = $conn->prepare($user_query);
                            $stmt_user->bind_param("i", $id);
                            $stmt_user->execute();
                            $stmt_user->bind_result($firstName, $lastName);
                            $stmt_user->fetch();

                            $_SESSION["firstname"] = $firstName;
                            $_SESSION["lastname"] = $lastName;

                            // Check if there's a redirect URL stored in session
                            if (isset($_SESSION['redirect_after_login'])) {
                                $redirect = $_SESSION['redirect_after_login'];
                                unset($_SESSION['redirect_after_login']);
                                header("Location: " . $redirect);
                            } else {
                                header("Location: dashboard.php");
                            }
                            exit();
                        } else {
                            // Password is not valid
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // Email doesn't exist
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
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
    <title>BookWagon - Sign In</title>
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
        
        .login-container {
            max-width: 800px;
            margin: 40px auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: white;
        }
        
        .login-row {
            display: flex;
            min-height: 500px;
        }
        
        .login-image {
            flex: 1;
            background-color: var(--bg-cream);
            position: relative;
            overflow: hidden;
        }
        
        .login-image img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 110%;
            height: 110%;
            object-fit: cover;
        }
        
        .login-form {
            flex: 1;
            padding: 40px;
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
        
        .btn-signin {
            height: 50px;
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 18px;
            margin-top: 10px;
        }
        
        .btn-signin:hover {
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
        
        .signup-link {
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
        
        .waves {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 1;
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
        

    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-row">
                <!-- Left side - Decorative illustration -->
                <div class="login-image">
                    <div class="blob-blue blob-blue-1"></div>
                    <div class="blob-blue blob-blue-2"></div>
                    <div class="blob-blue blob-blue-3"></div>
                    
                </div>
                
                <!-- Right side - Login form -->
                <div class="login-form">
                    <div class="logo">
                        <img src="images/logo.png" alt="BookWagon Logo">
                    </div>
                    
                    <h2>Sign In</h2>
                    
                    <?php 
                    if(!empty($login_err)){
                        echo '<div class="alert alert-danger">' . $login_err . '</div>';
                    }        
                    ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo $email; ?>" placeholder="Email">
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>    
                        <div class="form-group">
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                                   placeholder="Password">
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="rememberPasswordCheck">
                            <label class="form-check-label" for="rememberPasswordCheck">
                                Remember password
                            </label>
                            <a href="forgot-password.php" class="float-end text-primary">Forgot password?</a>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-signin w-100">Sign In</button>
                        </div>
                        <div class="form-divider">Or</div>
                        <div class="signup-link">
                            Don't have an account? <a href="signup.php" class="text-primary">Sign up</a>
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