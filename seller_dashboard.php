<?php
include("session.php");
include("connect.php");

// Check if user is a seller
try {
    $stmt = $pdo->prepare("SELECT is_seller FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || $user['is_seller'] != 1) {
        header("Location: dashboard.php");
        exit();
    }
} catch(PDOException $e) {
    error_log("Error checking seller status: " . $e->getMessage());
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWagon - Seller Dashboard</title>
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
            --success-color: #28a745;
            --warning-color: #ffc107;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            color: var(--text-dark);
            background-color: #f8f9fa;
        }
        
        /* Header styles */
        .navbar {
            padding: 15px 0;
            background: #fff;
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

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background: white;
            min-height: calc(100vh - 70px);
            padding: 20px;
            border-right: 1px solid var(--border-color);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-dark);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .sidebar-link:hover, .sidebar-link.active {
            background: var(--secondary-color);
            color: var(--primary-color);
        }

        .sidebar-link i {
            margin-right: 10px;
            width: 20px;
        }

        /* Dashboard styles */
        .dashboard-container {
            padding: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .revenue-amount {
            font-size: 24px;
            font-weight: 600;
            margin: 10px 0;
        }

        .percentage-change {
            color: var(--success-color);
            font-size: 14px;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .orders-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .status-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-pending {
            background: #fff8e1;
            color: #f57c00;
        }

        .book-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }

        .book-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            margin-right: 15px;
            object-fit: cover;
        }

        .renter-info {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .renter-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .view-all {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }

        /* Add hover styles for Manage your sales button */
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

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="#" class="sidebar-link active">
                <i class="fas fa-th-large"></i>
                Dashboard
            </a>
            <a href="#" class="sidebar-link">
                <i class="fas fa-book"></i>
                Product
            </a>
            <a href="#" class="sidebar-link">
                <i class="fas fa-shopping-cart"></i>
                Order
            </a>
            <a href="#" class="sidebar-link">
                <i class="fas fa-users"></i>
                Renter
            </a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 dashboard-container">
            <div class="row">
                <!-- Revenue Stats -->
                <div class="col-md-8 mb-4">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-0">Revenue</h6>
                                <div class="revenue-amount">PHP 5,000.00</div>
                                <div class="percentage-change">
                                    <i class="fas fa-arrow-up"></i> 2.1% vs last week
                                </div>
                                <small class="text-muted">Sales from 1-12 Dec, 2023</small>
                            </div>
                            <a href="#" class="view-all">View Report</a>
                        </div>
                        <div class="chart-container mt-4" style="height: 200px;">
                            <!-- Chart will be added here using JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Most Rented Books -->
                <div class="col-md-4 mb-4">
                    <div class="stats-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0">Most Rented Books</h6>
                            <a href="#" class="view-all">See All Books</a>
                        </div>
                        <div class="book-list">
                            <!-- Sample Book Items -->
                            <div class="book-item">
                                <img src="images/book1.jpg" alt="Book" class="book-image">
                                <div>
                                    <div class="fw-bold">The Great Gatsby</div>
                                    <small class="text-muted">PHP 45.00/day</small>
                                </div>
                            </div>
                            <div class="book-item">
                                <img src="images/book2.jpg" alt="Book" class="book-image">
                                <div>
                                    <div class="fw-bold">To Kill a Mockingbird</div>
                                    <small class="text-muted">PHP 75.00/day</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <div class="row">
                <div class="col-md-8">
                    <div class="orders-table">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0">Orders</h6>
                            <a href="#" class="view-all">See All Orders</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Customer</th>
                                        <th>Book</th>
                                        <th>Amount</th>
                                        <th>Location</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="status-badge status-completed">Completed</span></td>
                                        <td>John Doe</td>
                                        <td>The Great Gatsby</td>
                                        <td>₱182.94</td>
                                        <td>Toril</td>
                                        <td><i class="fas fa-ellipsis-v"></i></td>
                                    </tr>
                                    <tr>
                                        <td><span class="status-badge status-pending">Pending</span></td>
                                        <td>Jane Smith</td>
                                        <td>1984</td>
                                        <td>₱182.94</td>
                                        <td>Bajada</td>
                                        <td><i class="fas fa-ellipsis-v"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Renters -->
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0">Recent Renters</h6>
                            <a href="#" class="view-all">See All Renters</a>
                        </div>
                        <div class="renters-list">
                            <div class="renter-info">
                                <img src="images/avatar1.jpg" alt="Renter" class="renter-avatar">
                                <div>
                                    <div class="fw-bold">Jenny Wilson</div>
                                    <small class="text-muted">w.lawson@example.com</small>
                                </div>
                                <div class="ms-auto">₱450.00</div>
                            </div>
                            <div class="renter-info">
                                <img src="images/avatar2.jpg" alt="Renter" class="renter-avatar">
                                <div>
                                    <div class="fw-bold">Devon Lane</div>
                                    <small class="text-muted">devon@example.com</small>
                                </div>
                                <div class="ms-auto">₱450.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sample chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.createElement('canvas');
            document.querySelector('.chart-container').appendChild(ctx);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
                    datasets: [{
                        label: 'Last 6 days',
                        data: [65, 59, 80, 81, 56, 55, 40, 65, 59, 80, 81, 56],
                        backgroundColor: '#4e73df',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
</body>
</html> 