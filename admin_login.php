<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bookwagon_db";

// Create connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to ensure proper handling of special characters
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Initialize variables
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Check input errors before checking database
    if (empty($username_err) && empty($password_err)) {
        try {
            // Prepare a select statement
            $sql = "SELECT id, username, password FROM admin WHERE username = ? AND password = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ss", $param_username, $param_password);
                
                // Set parameters
                $param_username = $username;
                $param_password = $password;
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    $stmt->store_result();
                    
                    // Check if username exists
                    if ($stmt->num_rows == 1) {                    
                        // Bind result variables
                        $stmt->bind_result($id, $username, $hashed_password);
                        if ($stmt->fetch()) {
                            // Start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["admin_loggedin"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $username;

                            // Redirect to admin dashboard
                            header("Location: admin_dashboard.php");
                            exit();
                        }
                    } else {
                        // Username doesn't exist or password incorrect
                        $login_err = "Invalid username or password.";
                        error_log("Login failed for username: " . $username);
                    }
                } else {
                    throw new Exception("Something went wrong. Please try again later.");
                }

                // Close statement
                $stmt->close();
            }
        } catch (Exception $e) {
            $login_err = $e->getMessage();
            error_log("Login error: " . $e->getMessage());
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
    <title>BookWagon - Admin Sign In</title>
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
            max-width: 400px;
            margin: 40px auto;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: white;
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
            color: var(--primary-color);
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
            color: white;
            margin-top: 20px;
            width: 100%;
        }
        
        .btn-signin:hover {
            background-color: #e09000;
        }
        
        .alert {
            margin-bottom: 20px;
        }
        
        .text-danger {
            color: #dc3545;
            font-size: 14px;
            margin-top: -15px;
            margin-bottom: 15px;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-home a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .back-to-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <img src="images/logo.png" alt="BookWagon Logo">
            </div>
            <h2>Admin Sign In</h2>
            
            <?php if(!empty($login_err)): ?>
                <div class="alert alert-danger"><?php echo $login_err; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo $username; ?>" placeholder="Username">
                    <span class="text-danger"><?php echo $username_err; ?></span>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                        placeholder="Password">
                    <span class="text-danger"><?php echo $password_err; ?></span>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-signin">Sign In</button>
                </div>
            </form>
            
            <div class="back-to-home">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
