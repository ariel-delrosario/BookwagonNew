<nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="images/logo.png" alt="BookWagon">
            </a>
            
            <div class="d-flex align-items-center">
                <a href="seller_request.php" class="nav-link me-3">Start selling</a>
                <a href="#" class="nav-link me-3"><i class="fa-regular fa-bell"></i></a>
                <a href="#" class="nav-link me-3"><i class="fa-regular fa-envelope"></i></a>
                <a href="#" class="nav-link"><?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['email']; ?></a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>