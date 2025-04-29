<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="images/logo.png" alt="BookWagon">
        </a>
        
        <div class="d-flex align-items-center">
            <a href="manage_books.php" class="nav-link me-4">Manage Books</a>
            <a href="order.php" class="nav-link me-4">Manage Orders</a>
            <a href="#" class="nav-link me-3"><i class="fa-regular fa-bell"></i></a>
            <a href="#" class="nav-link me-3"><i class="fa-regular fa-envelope"></i></a>
            
            <!-- Dropdown Menu -->
            <div class="dropdown">
                <button class="nav-link dropdown-toggle border-0 bg-transparent d-flex align-items-center" 
                        id="userDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-1"><?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['email']; ?></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-0" style="min-width: 280px;" aria-labelledby="userDropdown">
                    <!-- User Profile Header -->
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <?php 
                                // Check if user has a profile picture
                                $photo = '';
                                $query = "SELECT profile_picture FROM users WHERE id = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $_SESSION['id']);
                                $stmt->execute();
                                $stmt->bind_result($photo);
                                $stmt->fetch();
                                $stmt->close();
                                
                                if ($photo && file_exists($photo)) {
                                    // Display profile picture
                                    echo '<img src="'.$photo.'" alt="photo" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">';
                                } else {
                                    // Display initial letter if no profile picture
                                    echo '<span class="text-white fw-bold">'.substr(isset($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['email'], 0, 1).'</span>';
                                }
                                ?>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold"><?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] : $_SESSION['email']; ?></h6>
                                <?php
                                // Get shop name from database for seller
                                $shop_name = '';
                                if (isset($_SESSION['id'])) {
                                    $shop_query = "SELECT shop_name FROM sellers WHERE user_id = ?";
                                    $stmt = $conn->prepare($shop_query);
                                    $stmt->bind_param("i", $_SESSION['id']);
                                    $stmt->execute();
                                    $stmt->bind_result($shop_name);
                                    $stmt->fetch();
                                    $stmt->close();
                                }
                                ?>
                                <small class="text-muted d-block"><?php echo !empty($shop_name) ? $shop_name : 'Seller Account'; ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Menu Items -->
                    <a class="dropdown-item py-2 d-flex align-items-center" href="seller_account.php">
                        <i class="fa-solid fa-user me-3"></i> Account
                        <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a class="dropdown-item py-2 d-flex align-items-center" href="manage_books.php">
                        <i class="fa-solid fa-book me-3"></i> Manage Books
                        <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a class="dropdown-item py-2 d-flex align-items-center" href="order.php">
                        <i class="fa-solid fa-shopping-cart me-3"></i> Manage Orders
                        <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a class="dropdown-item py-2 d-flex align-items-center" href="sales_report.php">
                        <i class="fa-solid fa-chart-line me-3"></i> Sales Report
                        <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a class="dropdown-item py-2 d-flex align-items-center" href="seller_settings.php">
                        <i class="fa-solid fa-cog me-3"></i> Shop Settings
                        <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    
                    <!-- Logout -->
                    <a class="dropdown-item py-2 d-flex align-items-center border-top mt-2" href="logout.php">
                        <i class="fa-solid fa-sign-out-alt me-3"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>