<?php
include("session.php");
include("connect.php");

$userType = $_SESSION['usertype'] ?? '';
$userId = $_SESSION['id'] ?? 0;

// Ensure only sellers can access this page
if ($userType !== 'seller') {
    header("Location: login.php");
    exit();
}

// First, get the seller's ID
$sellerStmt = $conn->prepare("SELECT id FROM sellers WHERE user_id = ?");
$sellerStmt->bind_param("i", $userId);
$sellerStmt->execute();
$sellerResult = $sellerStmt->get_result();
$sellerData = $sellerResult->fetch_assoc();

// If no seller found, exit
if (!$sellerData) {
    $_SESSION['error_message'] = "Seller profile not found.";
    header("Location: dashboard.php");
    exit();
}

$sellerId = $sellerData['id'];

// Fetch rental records with additional details
$rentalQuery = "
    SELECT 
        br.rental_id,
        br.rental_date,
        br.due_date,
        br.return_date,
        br.rental_weeks,
        br.status,
        br.total_price,
        br.order_id,
        b.title as book_title,
        b.author as book_author,
        b.cover_image,
        b.ISBN,
        u.firstname as renter_firstname,
        u.lastname as renter_lastname,
        u.email as renter_email
    FROM book_rentals br
    JOIN books b ON br.book_id = b.book_id
    JOIN users u ON br.user_id = u.id
    WHERE br.seller_id = ?
    ORDER BY br.rental_date DESC
";

$stmt = $conn->prepare($rentalQuery);
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$result = $stmt->get_result();
$rentals = $result->fetch_all(MYSQLI_ASSOC);

// Calculate rental statistics
$totalRentals = count($rentals);
$activeRentals = 0;
$overdueRentals = 0;
$returnedRentals = 0;
$totalRentalRevenue = 0;

foreach ($rentals as $rental) {
    $totalRentalRevenue += $rental['total_price'];
    
    switch ($rental['status']) {
        case 'active':
            $activeRentals++;
            if (strtotime($rental['due_date']) < time()) {
                $overdueRentals++;
            }
            break;
        case 'returned':
            $returnedRentals++;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rentals - BookWagon</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
     
    <style>
:root {
    --primary-color: #6366f1;
    --primary-light: #818cf8;
    --primary-dark: #4f46e5;
    --secondary-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
    --sidebar-width: 260px;
    --topbar-height: 70px;
    --card-border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
        
body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: #374151;
        }
        
        .sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary-color) 100%);
    box-shadow: var(--shadow-lg);
    padding-top: var(--topbar-height);
    z-index: 1000;
    transition: all 0.3s ease;
}

.sidebar-logo {
    height: var(--topbar-height);
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    background-color: rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    padding: 12px 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
    margin-bottom: 5px;
    border-radius: 0 50px 50px 0;
}

.sidebar-menu li.active, 
.sidebar-menu li:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    padding-left: 30px;
}

.sidebar-menu li i {
    width: 24px;
    text-align: center;
}

.sidebar-menu a {
    color: inherit;
    text-decoration: none;
    font-weight: 500;
}

/* Main content and topbar */
.main-content {
    margin-left: var(--sidebar-width);
    padding: 20px;
    min-height: 100vh;
}

.topbar {
    height: var(--topbar-height);
    background-color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
    box-shadow: var(--shadow-sm);
    position: fixed;
    top: 0;
    left: var(--sidebar-width);
    right: 0;
    z-index: 999;
}

.search-bar {
    position: relative;
    flex: 1;
    max-width: 400px;
    margin-right: 20px;
}

.search-bar input {
    width: 100%;
    padding: 10px 20px;
    padding-left: 40px;
    border-radius: 50px;
    border: 1px solid #e5e7eb;
    background-color: #f9fafb;
    transition: all 0.3s ease;
}

.search-bar input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    outline: none;
}

.search-bar i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.topbar-icons {
    display: flex;
    align-items: center;
    gap: 20px;
}

.icon-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f9fafb;
    color: #4b5563;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.icon-btn:hover {
    background-color: #f3f4f6;
    color: var(--primary-color);
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background-color: var(--primary-light);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
}

.user-role {
    font-size: 12px;
    color: #6b7280;
}

.content-wrapper {
    margin-top: calc(var(--topbar-height) + 20px);
    padding: 10px;
}

/* Responsive tweaks */
@media (max-width: 991px) {
    :root {
        --sidebar-width: 70px;
    }
    
    .sidebar {
        overflow: hidden;
    }
    
    .sidebar-menu li span,
    .sidebar-logo span {
        display: none;
    }
    
    .sidebar-menu li {
        justify-content: center;
        padding: 12px;
    }
    
    .sidebar-menu li.active, 
    .sidebar-menu li:hover {
        padding-left: 12px;
    }
    
    .sidebar-menu li i {
        margin-right: 0;
        font-size: 20px;
    }
}

@media (max-width: 767px) {
    .topbar {
        padding: 0 15px;
    }
    
    .search-bar {
        max-width: 200px;
    }
    
    .user-info {
        display: none;
    }
}
        
        .content-wrapper {
            margin-top: var(--topbar-height);
            padding: 20px;
        }
        
        .rental-stats {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
        }
                    
        .rental-card {
            background-color: white;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .rental-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .rental-body {
            display: flex;
            padding: 15px;
        }
        
        .book-thumbnail {
            width: 100px;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .rental-details {
            flex-grow: 1;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-active {
            background-color: #e3f2fd;
            color: #2196f3;
        }
        
        .badge-overdue {
            background-color: #ffebee;
            color: #f44336;
        }
        
        .badge-returned {
            background-color: #e8f5e9;
            color: #4caf50;
        }
        
        .badge-cancelled {
            background-color: #f8f8f8;
            color: #6c757d;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .empty-state-icon {
            font-size: 60px;
            color: #d1d1d1;
            margin-bottom: 20px;
        }

        .rental-stats {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stats-icon {
            font-size: 32px;
            opacity: 0.7;
            margin-right: 15px;
        }
        
        .stats-content {
            flex-grow: 1;
        }
        
        .stats-title {
            font-size: 1rem;
            margin-bottom: 8px;
        }
        
        .stats-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            word-break: break-word;
        }
        
        .currency-value {
            font-family: monospace;
            white-space: nowrap;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }
            .main-content,
            .topbar {
                left: 200px;
            }
            :root {
                --sidebar-width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .rental-body {
                flex-direction: column;
            }
            .book-thumbnail {
                margin-bottom: 15px;
                margin-right: 0;
            }
            .stats-value {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
    <div class="sidebar-logo">
        <img src="images/logo.png" alt="BookWagon" height="40">
        <span class="ms-2 text-white fw-bold fs-5">BookWagon</span>
    </div>
    <ul class="sidebar-menu">
        <li>
            <i class="fas fa-th-large"></i>
            <span><a href="dashboard.php" class="text-decoration-none text-inherit">Dashboard</a></span>
        </li>
        <li>
            <i class="fas fa-book"></i>
            <span><a href="manage_books.php" class="text-decoration-none text-inherit">Manage Books</a></span>
        </li>
        <li>
            <i class="fas fa-shopping-cart"></i>
            <span><a href="order.php" class="text-decoration-none text-inherit">Orders</a></span>
        </li>
        <li>
            <i class="fas fa-exchange-alt"></i>
            <span><a href="rentals.php" class="text-decoration-none text-inherit">Rentals</a></span>
        </li>
        <li>
            <i class="fas fa-undo-alt"></i>
            <span><a href="rental_request.php" class="text-decoration-none text-inherit">Return Requests</a></span>
        </li>
        <li class="active">
            <i class="fas fa-user-friends"></i>
            <span>Customers</span>
        </li>
        <li>
            <i class="fas fa-chart-line"></i>
            <span><a href="reports.php" class="text-decoration-none text-inherit">Reports</a></span>
        </li>
        <li>
            <i class="fas fa-cog"></i>
            <span><a href="settings.php" class="text-decoration-none text-inherit">Settings</a></span>
        </li>
    </ul>
</div>

    <!-- Topbar -->
    <div class="topbar">
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search returns..." id="returnSearch">
    </div>
    <div class="topbar-icons">
        <a href="dashboard.php" class="nav-link" title="Home">Home</a>
        <button class="icon-btn" title="Notifications">
            <i class="fas fa-bell"></i>
        </button>
        <button class="icon-btn" title="Messages">
            <i class="fas fa-envelope"></i>
        </button>
        <div class="user-profile">
            <div class="avatar">
                <?php echo substr(isset($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['email'], 0, 1); ?>
            </div>
            <div class="user-info">
                <div class="user-name">
                    <?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] . ' ' . ($_SESSION['lastname'] ?? '') : $_SESSION['email']; ?>
                </div>
                <div class="user-role">Seller</div>
            </div>
        </div>
    </div>
</div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="mb-3">Rental Management</h2>
                
                <!-- Stats cards - IMPROVED LAYOUT -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="rental-stats">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="stats-title text-muted">Total Rentals</h5>
                                    <p class="stats-value"><?php echo $totalRentals; ?></p>
                                </div>
                                <i class="fas fa-book-reader text-primary stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="rental-stats">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="stats-title text-muted">Active Rentals</h5>
                                    <p class="stats-value"><?php echo $activeRentals; ?></p>
                                </div>
                                <i class="fas fa-hourglass-half text-warning stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="rental-stats">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="stats-title text-muted">Overdue Rentals</h5>
                                    <p class="stats-value"><?php echo $overdueRentals; ?></p>
                                </div>
                                <i class="fas fa-exclamation-triangle text-danger stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="rental-stats">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="stats-title text-muted">Revenue</h5>
                                    <p class="stats-value currency-value">₱<?php echo number_format($totalRentalRevenue, 2); ?></p>
                                </div>
                                <i class="fas fa-wallet text-success stats-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rental Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-12 mb-2">
                                <input type="text" id="searchRentals" class="form-control" placeholder="Search rentals...">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-2">
                                <select id="statusFilter" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="returned">Returned</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-2">
                                <select id="weeksFilter" class="form-select">
                                    <option value="">All Rental Periods</option>
                                    <option value="1">1 Week</option>
                                    <option value="2-4">2-4 Weeks</option>
                                    <option value="5+">5+ Weeks</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rental List -->
<!-- Rental List -->
<?php if (empty($rentals)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>No Rental Records</h4>
                        <p class="text-muted">You haven't started any book rentals yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($rentals as $rental): ?>
                        <div class="rental-card" 
                             data-status="<?php echo $rental['status']; ?>" 
                             data-weeks="<?php echo $rental['rental_weeks']; ?>"
                             data-title="<?php echo htmlspecialchars(strtolower($rental['book_title'])); ?>"
                             data-author="<?php echo htmlspecialchars(strtolower($rental['book_author'])); ?>">
                            <div class="rental-header">
                                <div>
                                    <h5 class="mb-0">Rental #<?php echo $rental['rental_id']; ?></h5>
                                    <small class="text-muted">
                                        Rented on <?php echo date('M j, Y, g:i a', strtotime($rental['rental_date'])); ?>
                                    </small>
                                </div>
                                <span class="status-badge 
                                    <?php 
                                    if ($rental['status'] == 'active') {
                                        echo strtotime($rental['due_date']) < time() ? 'badge-overdue' : 'badge-active';
                                    } elseif ($rental['status'] == 'returned') {
                                        echo 'badge-returned';
                                    } else {
                                        echo 'badge-cancelled';
                                    }
                                    ?>">
                                    <?php if ($rental['status'] == 'active') {
                                        echo strtotime($rental['due_date']) < time() ? 'Overdue' : 'Active';
                                    } elseif ($rental['status'] == 'returned') {
                                        echo 'Returned';
                                    } else {
                                        echo ucfirst($rental['status']);
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="rental-body">
                                <img 
                                    src="<?php echo !empty($rental['cover_image']) ? $rental['cover_image'] : 'img/default-book-cover.jpg'; ?>" 
                                    alt="<?php echo htmlspecialchars($rental['book_title']); ?>" 
                                    class="book-thumbnail"
                                >
                                <div class="rental-details">
                                    <h5><?php echo htmlspecialchars($rental['book_title']); ?></h5>
                                    <p class="text-muted mb-2">by <?php echo htmlspecialchars($rental['book_author']); ?></p>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Renter:</strong> 
                                                <?php echo htmlspecialchars($rental['renter_firstname'] . ' ' . $rental['renter_lastname']); ?>
                                            </p>
                                            <p class="mb-1"><strong>Renter Email:</strong> 
                                                <?php echo htmlspecialchars($rental['renter_email']); ?>
                                            </p>
                                            <p class="mb-1"><strong>ISBN:</strong> 
                                                <?php echo htmlspecialchars($rental['ISBN']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Rental Period:</strong> 
                                                <?php echo $rental['rental_weeks']; ?> week<?php echo $rental['rental_weeks'] > 1 ? 's' : ''; ?>
                                            </p>
                                            <p class="mb-1"><strong>Rental Start:</strong> 
                                                <?php echo date('M j, Y, g:i a', strtotime($rental['rental_date'])); ?>
                                            </p>
                                            <p class="mb-1"><strong>Due Date:</strong> 
                                                <?php echo date('M j, Y, g:i a', strtotime($rental['due_date'])); ?>
                                            </p>
                                            <?php if ($rental['return_date']): ?>
                                            <p class="mb-1"><strong>Return Date:</strong> 
                                                <?php echo date('M j, Y, g:i a', strtotime($rental['return_date'])); ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Total Rental Cost:</strong> 
                                            ₱<?php echo number_format($rental['total_price'], 2); ?>
                                        </div>
                                        <div class="rental-actions">
                                            <?php if ($rental['status'] == 'active' && strtotime($rental['due_date']) < time()): ?>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#overdueModal<?php echo $rental['rental_id']; ?>">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Overdue Action
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($rental['status'] == 'active'): ?>
                                                <button class="btn btn-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#returnBookModal<?php echo $rental['rental_id']; ?>">
                                                    <i class="fas fa-book-reader me-1"></i>Mark as Returned
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Modal -->
                        <div class="modal fade" id="overdueModal<?php echo $rental['rental_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Overdue Rental Notice</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-warning">
                                            <strong>This rental is overdue!</strong>
                                        </div>
                                        <p>Book: <?php echo htmlspecialchars($rental['book_title']); ?></p>
                                        <p>Renter: <?php echo htmlspecialchars($rental['renter_firstname'] . ' ' . $rental['renter_lastname']); ?></p>
                                        <p>Due Date: <?php echo date('M j, Y', strtotime($rental['due_date'])); ?></p>
                                        <p>Overdue By: <?php 
                                            $overdueTime = time() - strtotime($rental['due_date']);
                                            $overdueDays = floor($overdueTime / (60 * 60 * 24));
                                            echo $overdueDays . ' day' . ($overdueDays != 1 ? 's' : '');
                                        ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <form action="process_rental.php" method="POST">
                                            <input type="hidden" name="action" value="contact_renter">
                                            <input type="hidden" name="rental_id" value="<?php echo $rental['rental_id']; ?>">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-envelope me-1"></i>Contact Renter
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Return Book Modal -->
                        <div class="modal fade" id="returnBookModal<?php echo $rental['rental_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Mark Book as Returned</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <strong>Book Return Confirmation</strong>
                                        </div>
                                        <p>Book: <?php echo htmlspecialchars($rental['book_title']); ?></p>
                                        <p>Renter: <?php echo htmlspecialchars($rental['renter_firstname'] . ' ' . $rental['renter_lastname']); ?></p>
                                        <p>Rental Period: <?php echo $rental['rental_weeks']; ?> week<?php echo $rental['rental_weeks'] > 1 ? 's' : ''; ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <form action="process_rental.php" method="POST">
                                            <input type="hidden" name="action" value="mark_returned">
                                            <input type="hidden" name="rental_id" value="<?php echo $rental['rental_id']; ?>">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check me-1"></i>Confirm Return
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchRentals');
            const statusFilter = document.getElementById('statusFilter');
            const weeksFilter = document.getElementById('weeksFilter');
            const rentalCards = document.querySelectorAll('.rental-card');

            function filterRentals() {
                rentalCards.forEach(card => {
                    const searchTerm = searchInput.value.toLowerCase();
                    const statusValue = statusFilter.value;
                    const weeksValue = weeksFilter.value;

                    const titleMatch = card.dataset.title.includes(searchTerm);
                    const authorMatch = card.dataset.author.includes(searchTerm);
                    
                    let statusMatch = true;
                    if (statusValue) {
                        if (statusValue === 'overdue') {
                            statusMatch = card.querySelector('.status-badge').textContent.toLowerCase() === 'overdue';
                        } else {
                            statusMatch = card.dataset.status === statusValue;
                        }
                    }

                    let weeksMatch = true;
                    if (weeksValue) {
                        const weeks = parseInt(card.dataset.weeks);
                        switch(weeksValue) {
                            case '1':
                                weeksMatch = weeks === 1;
                                break;
                            case '2-4':
                                weeksMatch = weeks >= 2 && weeks <= 4;
                                break;
                            case '5+':
                                weeksMatch = weeks >= 5;
                                break;
                        }
                    }

                    card.style.display = (titleMatch || authorMatch) && statusMatch && weeksMatch 
                        ? '' 
                        : 'none';
                });
            }

            searchInput.addEventListener('input', filterRentals);
            statusFilter.addEventListener('change', filterRentals);
            weeksFilter.addEventListener('change', filterRentals);
        });
    </script>
</body>
</html>