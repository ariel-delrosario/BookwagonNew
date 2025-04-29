<?php
include("session.php");
include("connect.php");

$userType = $_SESSION['usertype'] ?? '';
$userId = $_SESSION['id'] ?? 0;

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Fetch return requests and rental history
$returnsQuery = "
    SELECT 
        br.return_id,
        br.rental_id,
        br.return_method,
        br.return_details,
        br.status,
        br.request_date,
        br.completed_date,
        b.title as book_title,
        b.author as book_author,
        b.cover_image,
        r.rental_weeks,
        r.total_price
    FROM book_returns br
    JOIN book_rentals r ON br.rental_id = r.rental_id
    JOIN books b ON br.book_id = b.book_id
    WHERE br.user_id = ?
    ORDER BY br.request_date DESC
";

$returnsStmt = $conn->prepare($returnsQuery);
$returnsStmt->bind_param("i", $userId);
$returnsStmt->execute();
$returnsResult = $returnsStmt->get_result();
$returns = $returnsResult->fetch_all(MYSQLI_ASSOC);

// Fetch completed orders and rentals
$historyQuery = "
    SELECT 
        'rental' as type,
        r.rental_id as id,
        r.rental_date as date,
        r.return_date,
        b.title,
        b.author,
        b.cover_image,
        r.total_price,
        r.rental_weeks,
        r.status
    FROM book_rentals r
    JOIN books b ON r.book_id = b.book_id
    WHERE r.user_id = ? AND r.status IN ('returned', 'cancelled')
    
    UNION
    
    SELECT 
        'purchase' as type,
        o.order_id as id,
        o.order_date as date,
        NULL as return_date,
        b.title,
        b.author,
        b.cover_image,
        oi.unit_price as total_price,
        NULL as rental_weeks,
        o.order_status as status
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN books b ON oi.book_id = b.book_id
    WHERE o.user_id = ? AND o.order_status IN ('completed', 'cancelled')
    ORDER BY date DESC
";

$historyStmt = $conn->prepare($historyQuery);
$historyStmt->bind_param("ii", $userId, $userId);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();
$history = $historyResult->fetch_all(MYSQLI_ASSOC);

// Determine active tab
$activeTab = $_GET['tab'] ?? 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order & Rental History - BookWagon</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Similar styles to rented_books.php */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
        }
                .navbar {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .navbar-brand img {
            height: 60px;
        }
        
        .sidebar {
            background-color: white;
            border-radius: 8px;
            padding: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .sidebar-link {
            display: block;
            padding: 12px 20px;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-link.active, .sidebar-link:hover {
            background-color: #f8f9fa;
            color: #f8a100;
        }
        
        .history-card {
            background-color: white;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .history-body {
            display: flex;
            padding: 15px;
        }
        
        .history-image {
            width: 80px;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-returned {
            background-color: #e8f5e9;
            color: #4caf50;
        }
        
        .status-completed {
            background-color: #e0f2f1;
            color: #009688;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #f44336;
        }
        
        .status-pending {
            background-color: #fff3e0;
            color: #ff9800;
        }
        
        .status-in-transit {
            background-color: #e3f2fd;
            color: #2196f3;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .history-tabs {
            display: flex;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .history-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            color: #6c757d;
            text-decoration: none;
            position: relative;
        }
        
        .history-tab.active {
            color: #f8a100;
        }
        
        .history-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #f8a100;
        }
    </style>
</head>
<body>
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
                    <a href="rented_books.php" class="sidebar-link">
                        <i class="fa-solid fa-book"></i> My Orders
                    </a>
                    <a href="history.php" class="sidebar-link active">
                        <i class="fa-solid fa-clock-rotate-left"></i> History
                    </a>
                </div>
            </div>
            
            <!-- Main Content Column -->
            <div class="col-md-9">
                <h2 class="mb-4">Order & Rental History</h2>
                
                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- History Tabs -->
                <div class="history-tabs">
                    <a href="?tab=all" class="history-tab <?php echo $activeTab == 'all' ? 'active' : ''; ?>">
                        All History
                    </a>
                    <a href="?tab=purchases" class="history-tab <?php echo $activeTab == 'purchases' ? 'active' : ''; ?>">
                        Purchases
                    </a>
                    <a href="?tab=rentals" class="history-tab <?php echo $activeTab == 'rentals' ? 'active' : ''; ?>">
                        Rentals
                    </a>
                    <a href="?tab=returns" class="history-tab <?php echo $activeTab == 'returns' ? 'active' : ''; ?>">
                        Returns
                    </a>
                </div>

                <!-- History Content -->
                <?php 
                // Determine which items to display based on active tab
                $displayItems = [];
                $emptyMessage = "No history items found.";

                switch ($activeTab) {
                    case 'purchases':
                        $displayItems = array_filter($history, function($item) {
                            return $item['type'] == 'purchase';
                        });
                        $emptyMessage = "You haven't completed any purchases yet.";
                        break;
                    case 'rentals':
                        $displayItems = array_filter($history, function($item) {
                            return $item['type'] == 'rental';
                        });
                        $emptyMessage = "You have no completed or cancelled rentals.";
                        break;
                    case 'returns':
                        $displayItems = $returns;
                        $emptyMessage = "You have no return requests.";
                        break;
                    default: // 'all'
                        $displayItems = array_merge($history, $returns);
                        $emptyMessage = "You have no history items.";
                        break;
                }
                ?>

                <?php if (empty($displayItems)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                        <h4><?php echo $emptyMessage; ?></h4>
                        <p class="text-muted">Your past orders, rentals, and returns will appear here.</p>
                        <a href="rentbooks.php" class="btn btn-primary mt-3">Start Browsing</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($displayItems as $item): ?>
                        <div class="history-card">
                            <div class="history-header">
                                <div>
                                    <?php if (isset($item['type']) && $item['type'] == 'rental'): ?>
                                        <span class="text-muted">Rental</span>
                                    <?php elseif (isset($item['type']) && $item['type'] == 'purchase'): ?>
                                        <span class="text-muted">Purchase</span>
                                    <?php else: ?>
                                        <span class="text-muted">Return Request</span>
                                    <?php endif; ?>
                                    <span class="ms-2 order-number">
                                        #<?php echo $item['id'] ?? $item['return_id']; ?>
                                    </span>
                                </div>
                                
                                <?php 
                                // Determine status badge
                                $statusBadgeClass = '';
                                $statusText = '';
                                
                                if (isset($item['status'])) {
                                    switch (strtolower($item['status'])) {
                                        case 'returned':
                                        case 'completed':
                                            $statusBadgeClass = 'status-completed';
                                            $statusText = 'Completed';
                                            break;
                                        case 'cancelled':
                                            $statusBadgeClass = 'status-cancelled';
                                            $statusText = 'Cancelled';
                                            break;
                                        case 'pending':
                                            $statusBadgeClass = 'status-pending';
                                            $statusText = 'Pending';
                                            break;
                                        case 'in_transit':
                                            $statusBadgeClass = 'status-in-transit';
                                            $statusText = 'In Transit';
                                            break;
                                        default:
                                            $statusBadgeClass = 'status-pending';
                                            $statusText = ucfirst($item['status']);
                                    }
                                } elseif (isset($item['return_id'])) {
                                    // For return requests
                                    switch (strtolower($item['status'])) {
                                        case 'pending':
                                            $statusBadgeClass = 'status-pending';
                                            $statusText = 'Pending';
                                            break;
                                        case 'in_transit':
                                            $statusBadgeClass = 'status-in-transit';
                                            $statusText = 'In Transit';
                                            break;
                                        case 'completed':
                                            $statusBadgeClass = 'status-completed';
                                            $statusText = 'Completed';
                                            break;
                                        default:
                                            $statusBadgeClass = 'status-pending';
                                            $statusText = ucfirst($item['status']);
                                    }
                                }
                                ?>
                                
                                <div class="status-badge <?php echo $statusBadgeClass; ?>">
                                    <?php echo $statusText; ?>
                                </div>
                            </div>
                            
                            <div class="history-body">
                                <img 
                                    src="<?php echo $item['cover_image'] ?? ''; ?>" 
                                    alt="<?php echo $item['title'] ?? $item['book_title']; ?>" 
                                    class="history-image"
                                >
                                
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">
                                        <?php echo $item['title'] ?? $item['book_title']; ?>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        by <?php echo $item['author'] ?? $item['book_author']; ?>
                                    </p>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php if (isset($item['type']) && $item['type'] == 'rental'): ?>
                                                <p class="mb-1">
                                                    <strong>Rental Period:</strong> 
                                                    <?php echo $item['rental_weeks']; ?> week<?php echo $item['rental_weeks'] > 1 ? 's' : ''; ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($item['return_id'])): ?>
                                                <p class="mb-1">
                                                    <strong>Return Method:</strong> 
                                                    <?php echo ucfirst($item['return_method']); ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <p class="mb-1">
                                                <strong>Date:</strong> 
                                                <?php 
                                                echo isset($item['date']) 
                                                    ? date('F j, Y', strtotime($item['date'])) 
                                                    : date('F j, Y', strtotime($item['request_date'])); 
                                                ?>
                                            </p>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong>Total:</strong> 
                                                ₱<?php echo number_format($item['total_price'], 2); ?>
                                            </p>
                                            
                                            <?php if (isset($item['return_date'])): ?>
                                                <p class="mb-1">
                                                    <strong>Return Date:</strong> 
                                                    <?php echo date('F j, Y', strtotime($item['return_date'])); ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($item['return_id']) && $item['completed_date']): ?>
                                                <p class="mb-1">
                                                    <strong>Completed:</strong> 
                                                    <?php echo date('F j, Y', strtotime($item['completed_date'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="modal fade" id="returnDetailsModal" tabindex="-1" aria-labelledby="returnDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnDetailsModalLabel">Return Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img id="return_book_image" src="" alt="Book Cover" class="img-fluid mb-3">
                        </div>
                        <div class="col-md-8">
                            <h4 id="return_book_title"></h4>
                            <p class="text-muted" id="return_book_author"></p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Return Request #</strong>
                                    <p id="return_request_id"></p>
                                    
                                    <strong>Return Method</strong>
                                    <p id="return_method"></p>
                                    
                                    <strong>Request Date</strong>
                                    <p id="return_request_date"></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Status</strong>
                                    <p id="return_status"></p>
                                    
                                    <strong>Rental Period</strong>
                                    <p id="return_rental_weeks"></p>
                                    
                                    <strong>Total Rental Cost</strong>
                                    <p id="return_total_price"></p>
                                </div>
                            </div>
                            
                            <div id="return_method_details">
                                <!-- Dynamic return method details will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to parse return details
        function parseReturnDetails(detailsJson) {
            try {
                return JSON.parse(detailsJson);
            } catch (e) {
                return {};
            }
        }

        // Add click event to return request cards
        document.querySelectorAll('.return-details-trigger').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                // Retrieve data attributes
                const bookTitle = this.getAttribute('data-book-title');
                const bookAuthor = this.getAttribute('data-book-author');
                const bookImage = this.getAttribute('data-book-image');
                const returnId = this.getAttribute('data-return-id');
                const returnMethod = this.getAttribute('data-return-method');
                const requestDate = this.getAttribute('data-request-date');
                const status = this.getAttribute('data-status');
                const rentalWeeks = this.getAttribute('data-rental-weeks');
                const totalPrice = this.getAttribute('data-total-price');
                const returnDetails = this.getAttribute('data-return-details');

                // Parse return details
                const parsedDetails = parseReturnDetails(returnDetails);

                // Update modal content
                document.getElementById('return_book_title').textContent = bookTitle;
                document.getElementById('return_book_author').textContent = `by ${bookAuthor}`;
                document.getElementById('return_book_image').src = bookImage;
                document.getElementById('return_request_id').textContent = returnId;
                document.getElementById('return_method').textContent = returnMethod.charAt(0).toUpperCase() + returnMethod.slice(1);
                document.getElementById('return_request_date').textContent = new Date(requestDate).toLocaleDateString();
                document.getElementById('return_status').textContent = status.charAt(0).toUpperCase() + status.slice(1);
                document.getElementById('return_rental_weeks').textContent = `${rentalWeeks} week${rentalWeeks > 1 ? 's' : ''}`;
                document.getElementById('return_total_price').textContent = `₱${parseFloat(totalPrice).toFixed(2)}`;

                // Handle return method details
                const methodDetailsContainer = document.getElementById('return_method_details');
                methodDetailsContainer.innerHTML = ''; // Clear previous content

                if (returnMethod === 'pickup') {
                    methodDetailsContainer.innerHTML = `
                        <strong>Pickup Details</strong>
                        <p>Date: ${parsedDetails.pickup_date || 'N/A'}</p>
                        <p>Time Slot: ${parsedDetails.pickup_time || 'N/A'}</p>
                        <p>Address: ${parsedDetails.pickup_address || 'N/A'}</p>
                        ${parsedDetails.pickup_notes ? `<p>Notes: ${parsedDetails.pickup_notes}</p>` : ''}
                    `;
                } else if (returnMethod === 'dropoff') {
                    methodDetailsContainer.innerHTML = `
                        <strong>Drop-off Location</strong>
                        <p>${parsedDetails.dropoff_location || 'N/A'}</p>
                    `;
                }

                // Show the modal
                var returnDetailsModal = new bootstrap.Modal(document.getElementById('returnDetailsModal'));
                returnDetailsModal.show();
            });
        });
    });
    </script>
</body>
</html>