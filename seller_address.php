<?php
include("session.php");
include("connect.php");

// Redirect if no temp_seller_id
if (!isset($_SESSION['temp_seller_id'])) {
    header("Location: start_selling.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWagon - Seller Address</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            height: 40px;
        }
        
        .nav-item {
            margin: 0 10px;
        }
        
        .nav-link {
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid var(--primary-color);
        }

        /* Form container styles */
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .form-select, .form-control {
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 8px;
        }
        .form-select:focus, .form-control:focus {
            border-color: #ddd;
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333 !important;
            font-weight: 600;
        }
        .btn-dark {
            padding: 12px 24px;
            font-weight: 500;
        }

        /* Add hover styles for navigation links */
        .nav-link.me-3:hover {
            color: var(--primary-color);
            transition: color 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Header/Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="images/logo.png" alt="BookWagon">
            </a>
            
            <div class="d-flex align-items-center">
                <a href="#" class="nav-link me-3" style="transition: color 0.3s ease;"><i class="fa-regular fa-bell"></i></a>
                <a href="#" class="nav-link me-3" style="transition: color 0.3s ease;"><i class="fa-regular fa-envelope"></i></a>
                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : htmlspecialchars($_SESSION['email']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Navigation tabs -->
    <div class="container">
        <ul class="nav nav-underline mb-4 justify-content-center mt-5">
            <li class="nav-item">
                <a class="nav-link active" href="#">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Rent Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Explore</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Libraries</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Book Swap</a>
            </li>
        </ul>
    </div>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Pickup Address Information</h2>

            <?php if(isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="process_seller_address.php" method="POST" id="sellerAddressForm">
                <div class="mb-4">
                    <label for="country" class="form-label">Country/Region</label>
                    <select class="form-select" id="country" name="country" required>
                        <option value="">Select Country</option>
                        <option value="Philippines">Philippines</option>
                        <option value="Singapore">Singapore</option>
                        <option value="Malaysia">Malaysia</option>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Thailand">Thailand</option>
                        <option value="Vietnam">Vietnam</option>
                        <option value="Japan">Japan</option>
                        <option value="South Korea">South Korea</option>
                        <option value="Taiwan">Taiwan</option>
                        <option value="Hong Kong">Hong Kong</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="province" class="form-label">Province</label>
                    <select class="form-select" id="province" name="province" required>
                        <option value="">Select Province</option>
                        <option value="Davao del Sur">Davao del Sur</option>
                        <option value="Metro Manila">Metro Manila</option>
                        <option value="Cebu">Cebu</option>
                        <option value="Rizal">Rizal</option>
                        <option value="Cavite">Cavite</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="city" class="form-label">City</label>
                    <select class="form-select" id="city" name="city" required>
                        <option value="">Select City</option>
                        <option value="Davao City">Davao City</option>
                        <option value="Quezon City">Quezon City</option>
                        <option value="Manila">Manila</option>
                        <option value="Cebu City">Cebu City</option>
                        <option value="Makati">Makati</option>
                        <option value="Taguig">Taguig</option>
                        <option value="Pasig">Pasig</option>
                        <option value="Mandaluyong">Mandaluyong</option>
                        <option value="Pasay">Pasay</option>
                        <option value="Paranaque">Paranaque</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="barangay" class="form-label">Barangay</label>
                    <select class="form-select" id="barangay" name="barangay" required>
                        <option value="">Select Barangay</option>
                        <option value="Poblacion">Poblacion</option>
                        <option value="Talomo">Talomo</option>
                        <option value="Buhangin">Buhangin</option>
                        <option value="Agdao">Agdao</option>
                        <option value="Bangkal">Bangkal</option>
                        <option value="Matina">Matina</option>
                        <option value="Bucana">Bucana</option>
                        <option value="Toril">Toril</option>
                        <option value="Mintal">Mintal</option>
                        <option value="Catalunan Grande">Catalunan Grande</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="postal_code" class="form-label">Postal Code</label>
                    <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                </div>

                <div class="mb-4">
                    <label for="detailed_address" class="form-label">Detailed Address</label>
                    <textarea class="form-control" id="detailed_address" name="detailed_address" rows="3" required 
                        placeholder="House/Unit/Floor No., Building Name, Block or Lot No., Street Name"></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-dark">Next</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center px-4 py-4">
                    <i class="fas fa-check-circle text-success mb-4" style="font-size: 4rem;"></i>
                    <h4 class="mb-4">Thank you!</h4>
                    <p class="mb-4">We're reviewing your submission. You'll be able to start selling once approved.</p>
                    <button type="button" class="btn btn-dark" onclick="window.location.href='dashboard.php'">
                        Return to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('sellerAddressForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Send form data using fetch
            fetch('process_seller_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Show the success modal
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html> 