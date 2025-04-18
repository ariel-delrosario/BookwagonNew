<?php
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check seller status and verification status
try {
    $stmt = $pdo->prepare("
        SELECT u.is_seller, sd.verification_status 
        FROM users u 
        LEFT JOIN seller_details sd ON u.id = sd.user_id 
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    error_log("Error checking seller status: " . $e->getMessage());
    $user = ['is_seller' => false, 'verification_status' => null];
}

// Determine button text and link based on status
$button_text = "Start Selling";
$button_link = "start_selling.php";
$button_class = "btn btn-primary me-3";

if (isset($user)) {
    if ($user['is_seller']) {
        $button_text = "Manage Your Sales";
        $button_link = "seller_dashboard.php";
        $button_class .= " active";
    } elseif (isset($user['verification_status'])) {
        switch($user['verification_status']) {
            case 'pending':
                $button_text = "Application Pending";
                $button_link = "#";
                $button_class .= " disabled btn-warning";
                break;
            case 'rejected':
                $button_text = "Application Rejected";
                $button_link = "start_selling.php";
                $button_class .= " btn-danger";
                break;
        }
    }
}
?>

<!-- Header/Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="images/logo.png" alt="BookWagon">
        </a>
        
        <div class="d-flex align-items-center">
            <a href="<?php echo $button_link; ?>" class="<?php echo $button_class; ?>">
                <?php echo $button_text; ?>
            </a>
            <a href="#" class="nav-link me-3"><i class="fa-regular fa-bell"></i></a>
            <a href="#" class="nav-link me-3"><i class="fa-regular fa-envelope"></i></a>
            <a href="#" class="nav-link me-3"><?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['email']; ?></a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </div>
</nav>

<style>
.btn-primary {
    background-color: #f8a100;
    border-color: #f8a100;
    color: white;
}

.btn-primary:hover {
    background-color: #e69100;
    border-color: #e69100;
}

.btn-primary.active {
    background-color: #5b6bff;
    border-color: #5b6bff;
}

.btn.disabled {
    cursor: not-allowed;
    opacity: 0.8;
}
</style> 