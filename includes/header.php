<?php
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check seller status
try {
    $stmt = $pdo->prepare("SELECT is_seller FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    error_log("Error checking seller status: " . $e->getMessage());
    $user = ['is_seller' => false];
}
?>

<!-- Header/Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="images/logo.png" alt="BookWagon">
        </a>
        
        <div class="d-flex align-items-center">
            <?php if (isset($user) && $user['is_seller']): ?>
                <a href="seller_dashboard.php" class="nav-link me-3">Seller Dashboard</a>
            <?php else: ?>
                <a href="start_selling.php" class="nav-link me-3">Start selling</a>
            <?php endif; ?>
            <a href="#" class="nav-link me-3"><i class="fa-regular fa-bell"></i></a>
            <a href="#" class="nav-link me-3"><i class="fa-regular fa-envelope"></i></a>
            <a href="#" class="nav-link me-3"><?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['email']; ?></a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </div>
</nav> 