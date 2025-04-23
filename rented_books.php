<?php
include("session.php");
include("connect.php");

$userType = $_SESSION['usertype'] ?? '';
$userId = $_SESSION['id'] ?? 0;

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Handle rental actions (return, extend)
if (isset($_GET['action']) && isset($_GET['rental_id'])) {
    $rentalId = $_GET['rental_id'];
    $action = $_GET['action'];
    
    if ($action == 'return') {
        // Update rental status to returned
        $returnDate = date('Y-m-d H:i:s');
        $updateStmt = $conn->prepare("UPDATE book_rentals SET status = 'returned', return_date = ? WHERE rental_id = ? AND user_id = ?");
        $updateStmt->bind_param("sii", $returnDate, $rentalId, $userId);
        $updateStmt->execute();
        
        // Redirect to prevent resubmission
        header("Location: rented_books.php?return_success=1");
        exit();
    } elseif ($action == 'extend') {
        // Extend rental by 1 week
        $extendWeeks = 1;
        
        // Get current rental info
        $rentalStmt = $conn->prepare("SELECT due_date, rental_weeks, total_price, book_id FROM book_rentals WHERE rental_id = ? AND user_id = ?");
        $rentalStmt->bind_param("ii", $rentalId, $userId);
        $rentalStmt->execute();
        $rentalResult = $rentalStmt->get_result();
        
        if ($rental = $rentalResult->fetch_assoc()) {
            // Get book's weekly rental price
            $bookStmt = $conn->prepare("SELECT rent_price FROM books WHERE book_id = ?");
            $bookStmt->bind_param("i", $rental['book_id']);
            $bookStmt->execute();
            $bookResult = $bookStmt->get_result();
            $book = $bookResult->fetch_assoc();
            
            // Calculate new due date and price
            $currentDueDate = new DateTime($rental['due_date']);
            $currentDueDate->modify("+{$extendWeeks} week");
            $newDueDate = $currentDueDate->format('Y-m-d H:i:s');
            
            $newRentalWeeks = $rental['rental_weeks'] + $extendWeeks;
            $newTotalPrice = $rental['total_price'] + ($book['rent_price'] * $extendWeeks);
            
            // Update rental
            $updateStmt = $conn->prepare("UPDATE book_rentals SET due_date = ?, rental_weeks = ?, total_price = ? WHERE rental_id = ? AND user_id = ?");
            $updateStmt->bind_param("sidii", $newDueDate, $newRentalWeeks, $newTotalPrice, $rentalId, $userId);
            $updateStmt->execute();
            
            // Redirect to prevent resubmission
            header("Location: rented_books.php?extend_success=1");
            exit();
        }
    }
}

// Fetch user's active rentals
$rentedBooksStmt = $conn->prepare("
    SELECT br.*, b.title, b.author, b.cover_image, b.description, b.ISBN, 
           u.firstname, u.lastname, u.username
    FROM book_rentals br
    JOIN books b ON br.book_id = b.book_id
    JOIN users u ON br.seller_id = u.id
    WHERE br.user_id = ?
    ORDER BY br.status = 'active' DESC, br.rental_date DESC
");
$rentedBooksStmt->bind_param("i", $userId);
$rentedBooksStmt->execute();
$rentedBooksResult = $rentedBooksStmt->get_result();
$rentedBooks = $rentedBooksResult->fetch_all(MYSQLI_ASSOC);

// Group rentals by seller for better organization
$rentalsBySeller = [];
foreach ($rentedBooks as $rental) {
    $sellerName = !empty($rental['username']) ? $rental['username'] : $rental['firstname'] . ' ' . $rental['lastname'];
    
    if (!isset($rentalsBySeller[$sellerName])) {
        $rentalsBySeller[$sellerName] = [];
    }
    
    $rentalsBySeller[$sellerName][] = $rental;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rented Books - BookWagon</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #f8a100;
            --secondary-color: #f8f9fa;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            color: var(--text-dark);
            background-color: #fff;
        }

        .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .dropdown-item:active {
            background-color: rgba(0,0,0,0.1);
        }

        /* Fix dropdown toggle arrow */
        .dropdown-toggle::after {
            margin-left: 0.5em;
        }
        
        /* Header styles */
        .navbar {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .navbar-brand img {
            height: 60px;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px 0;
            height: 100%;
        }
        
        .sidebar-link {
            display: block;
            padding: 12px 20px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            background-color: rgba(0, 123, 255, 0.05);
            color: #4a6cf7;
            border-left: 3px solid #4a6cf7;
        }
        
        .sidebar-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        
        /* Rentals list styles */
        .rentals-container {
            margin-bottom: 40px;
        }
        
        .rentals-header {
            background-color: #f8f9fa;
            padding: 12px 15px;
            font-weight: 600;
            border-radius: 8px 8px 0 0;
            border: 1px solid var(--border-color);
            margin-bottom: -1px;
        }
        
        .rental-book {
            border: 1px solid var(--border-color);
            border-radius: 0;
            margin-bottom: -1px;
            transition: all 0.2s;
        }
        
        .rental-book:last-child {
            border-radius: 0 0 8px 8px;
            margin-bottom: 20px;
        }
        
        .rental-book:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            z-index: 1;
            position: relative;
        }
        
        .book-details {
            display: flex;
            padding: 20px;
        }
        
        .book-image {
            width: 100px;
            min-width: 100px;
            height: 140px;
            object-fit: cover;
            margin-right: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .book-info {
            flex-grow: 1;
        }
        
        .book-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--text-dark);
        }
        
        .book-author {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .book-description {
            font-size: 0.9rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .rental-status {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .status-returned {
            background-color: rgba(108, 117, 125, 0.1);
            color: var(--text-muted);
        }
        
        .status-overdue {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .status-shipped {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .due-date {
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .due-date-overdue {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .rental-actions {
            display: flex;
            gap: 10px;
        }
        
        .rental-action-btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .action-return {
            background-color: var(--success-color);
            color: white;
        }
        
        .action-return:hover {
            background-color: #218838;
            color: white;
        }
        
        .action-extend {
            background-color: #6c757d;
            color: white;
        }
        
        .action-extend:hover {
            background-color: #5a6268;
            color: white;
        }
        
        .seller-pill {
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .seller-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-size: 0.8rem;
            color: var(--text-dark);
        }
        
        /* Empty state */
        .empty-rentals {
            text-align: center;
            padding: 60px 0;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .empty-rentals-icon {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-rentals-title {
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-rentals-message {
            color: var(--text-muted);
            margin-bottom: 25px;
        }
        
        .browse-books-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .browse-books-btn:hover {
            background-color: #e69400;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php 
    if ($userType == 'user') {
        include("include/user_header.php");
    } elseif ($userType == 'seller') {
        include("include/seller_header.php");
    }
    ?>

    <div class="container py-5">
        <div class="row">
            <!-- Sidebar Column -->
            <div class="col-md-3 mb-4">
                <div class="sidebar">
                    <h4 class="px-4 mb-4">My Profile</h4>
                    <a href="account.php" class="sidebar-link">
                        <i class="fa-solid fa-user"></i> Account
                    </a>
                    <a href="cart.php" class="sidebar-link">
                        <i class="fa-solid fa-shopping-cart"></i> Cart
                    </a>
                    <a href="rented_books.php" class="sidebar-link active">
                        <i class="fa-solid fa-book"></i> Rented books
                    </a>
                    <a href="history.php" class="sidebar-link">
                        <i class="fa-solid fa-clock-rotate-left"></i> History
                    </a>
                </div>
            </div>
            
            <!-- Main Content Column -->
            <div class="col-md-9">
                <h2 class="mb-4">Rented Books</h2>
                
                <?php if (isset($_GET['return_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Book successfully marked as returned!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php elseif (isset($_GET['extend_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Rental period successfully extended!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (empty($rentedBooks)): ?>
                <div class="empty-rentals">
                    <div class="empty-rentals-icon">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <h3 class="empty-rentals-title">No rented books yet</h3>
                    <p class="empty-rentals-message">You don't have any rented books in your collection yet.</p>
                    <a href="rentbooks.php" class="browse-books-btn">Browse books to rent</a>
                </div>
                <?php else: ?>
                    <div class="rentals-container">
                        <?php foreach ($rentalsBySeller as $sellerName => $rentals): ?>
                        <div class="rentals-header d-flex justify-content-between align-items-center">
                            <div class="seller-pill">
                                <div class="seller-avatar">
                                    <?php echo substr($sellerName, 0, 1); ?>
                                </div>
                                Books from <?php echo $sellerName; ?>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all-<?php echo md5($sellerName); ?>">
                                <label class="form-check-label" for="select-all-<?php echo md5($sellerName); ?>">Select all</label>
                            </div>
                        </div>
                        
                        <?php foreach ($rentals as $rental): ?>
                        <div class="rental-book">
                            <div class="book-details">
                                <div class="d-flex align-items-start">
                                    <div class="form-check mt-2 me-3">
                                        <input class="form-check-input" type="checkbox" id="rental-<?php echo $rental['rental_id']; ?>">
                                    </div>
                                
                                    <img src="<?php echo $rental['cover_image']; ?>" alt="<?php echo $rental['title']; ?>" class="book-image">
                                </div>
                                
                                <div class="book-info">
                                    <h3 class="book-title"><?php echo $rental['title']; ?></h3>
                                    <div class="book-author">by <?php echo $rental['author']; ?></div>
                                    <div class="book-description">
                                        <?php 
                                        if (!empty($rental['description'])) {
                                            echo $rental['description'];
                                        } else {
                                            echo 'No description available for this book.';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="rental-status">
                                <div>
                                    <?php 
                                    // Status badge
                                    $statusClass = '';
                                    $statusText = ucfirst($rental['status']);
                                    
                                    switch ($rental['status']) {
                                        case 'active':
                                            $statusClass = 'status-active';
                                            break;
                                        case 'returned':
                                            $statusClass = 'status-returned';
                                            break;
                                        case 'overdue':
                                            $statusClass = 'status-overdue';
                                            break;
                                        case 'shipped':
                                            $statusClass = 'status-shipped';
                                            break;
                                        default:
                                            $statusClass = '';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    
                                    <?php if ($rental['status'] != 'returned'): ?>
                                    <div class="due-date <?php echo (strtotime($rental['due_date']) < time()) ? 'due-date-overdue' : ''; ?>">
                                        Due: <?php echo date('m/d/Y', strtotime($rental['due_date'])); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="rental-actions">
                                    <?php if ($rental['status'] == 'active' || $rental['status'] == 'overdue'): ?>
                                    <a href="rented_books.php?action=return&rental_id=<?php echo $rental['rental_id']; ?>" 
                                       class="rental-action-btn action-return">Return</a>
                                    <a href="rented_books.php?action=extend&rental_id=<?php echo $rental['rental_id']; ?>" 
                                       class="rental-action-btn action-extend">Extend</a>
                                    <?php elseif ($rental['status'] == 'shipped'): ?>
                                    <a href="#" class="rental-action-btn action-extend" style="background-color: #f8a100;">Order Received</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Select all functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckboxes = document.querySelectorAll('[id^="select-all-"]');
            
            selectAllCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const container = this.closest('.rentals-header').nextElementSibling;
                    let currentContainer = container;
                    
                    while (currentContainer && currentContainer.classList.contains('rental-book')) {
                        const itemCheckbox = currentContainer.querySelector('.form-check-input');
                        if (itemCheckbox) {
                            itemCheckbox.checked = this.checked;
                        }
                        currentContainer = currentContainer.nextElementSibling;
                    }
                });
            });
        });
    </script>
</body>
</html>