


<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="images/logo.png" alt="BookWagon">
        </a>
        
        <div class="d-flex align-items-center">
            <a href="seller_request.php" class="nav-link me-4">Start selling</a>
            <li class="nav-item" style="position: relative; list-style: none; margin-right: 1rem;">
                <a class="nav-link d-flex align-items-center" href="cart.php" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-shopping-cart" style="font-size: 1.2rem;"></i>
                    <span class="cart-count badge bg-danger" style="position: absolute; top: -8px; right: -10px; font-size: 0.75rem; padding: 2px 5px; border-radius: 50%;">0</span>
                </a>
            </li>
            <a href="#" class="nav-link me-3" style="margin-left: 0.5rem;">
                <i class="fa-regular fa-bell"></i>
            </a>
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
                                // Get phone from session or database if not available in session
                                $phone = $_SESSION['phone'] ?? '';
                                if (empty($phone) && isset($_SESSION['id'])) {
                                    $phone_query = "SELECT phone FROM users WHERE id = ?";
                                    $stmt = $conn->prepare($phone_query);
                                    $stmt->bind_param("i", $_SESSION['id']);
                                    $stmt->execute();
                                    $stmt->bind_result($phone);
                                    $stmt->fetch();
                                    $stmt->close();
                                    
                                    // Update session with phone if found
                                    if (!empty($phone)) {
                                        $_SESSION['phone'] = $phone;
                                    }
                                }
                                ?>
                                <small class="text-muted d-block"><?php echo !empty($phone) ? $phone : 'No phone number'; ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Menu Items -->
                    <a class="dropdown-item py-2 d-flex align-items-center" href="account.php">
                        <i class="fa-solid fa-user me-3"></i> Account
                        <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a class="dropdown-item py-2 d-flex align-items-center" href="cart.php">
                        <i class="fa-solid fa-shopping-cart me-3"></i> Cart
                        <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a class="dropdown-item py-2 d-flex align-items-center" href="rented_books.php">
                        <i class="fa-solid fa-book me-3"></i> Rented books
                        <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a class="dropdown-item py-2 d-flex align-items-center" href="history.php">
                        <i class="fa-solid fa-clock-rotate-left me-3"></i> History
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