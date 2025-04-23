<?php
include("session.php");
include("connect.php");

$userType = $_SESSION['usertype'] ?? '';
$firstName = $_SESSION['firstname'] ?? '';
$lastName = $_SESSION['lastname'] ?? '';
$email = $_SESSION['email'] ?? '';

// Check if book_id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: rentbooks.php");
    exit();
}

$book_id = intval($_GET['id']);

// Fetch book details
$query = "SELECT b.*, u.firstname, u.lastname, u.email, u.phone 
          FROM books b 
          LEFT JOIN users u ON b.user_id = u.id 
          WHERE b.book_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: rentbooks.php");
    exit();
}

$book = $result->fetch_assoc();

// Format the cover image path
if (!empty($book['cover_image'])) {
    if (strpos($book['cover_image'], 'uploads/covers/') === 0) {
        $cover_image = $book['cover_image'];
    } else {
        $cover_image = 'uploads/covers/' . $book['cover_image'];
    }
} else {
    $cover_image = 'uploads/covers/default_book.jpg';
}

// Generate random rating for demo (1-5)
$rating = rand(4, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - BookWagon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/tab.css">
    
    <style>
        :root {
            --primary-color: #f8a100;
            --secondary-color: #f8f9fa;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --accent-color: #4e73df;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --card-shadow: 0 4px 6px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.1);
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background-color: #f8f9fc;
            line-height: 1.6;
        }
        
        .navbar {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }
        
        .navbar-brand img {
            height: 60px;
        }
        
        .book-details-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .book-cover-container {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .book-cover {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed);
        }
        
        .book-cover:hover {
            transform: scale(1.02);
        }
        
        .book-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--accent-color);
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .book-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #333;
            line-height: 1.2;
        }
        
        .book-author {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .ratings {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .star {
            color: #f8a100;
            font-size: 1.2rem;
            margin-right: 2px;
        }
        
        .ratings-text {
            margin-left: 10px;
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .divider {
            height: 1px;
            background-color: var(--border-color);
            margin: 1.5rem 0;
        }
        
        /* Enhanced button styling */
        .cart-btn {
            width: 100%;
            padding: 14px;
            background-color: var(--text-dark);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed);
            margin-bottom: 25px;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .cart-btn:hover {
            background-color: #343a40;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .price-row {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            background-color: #f8f9fa;
        }
        
        .price-label {
            width: 130px;
            font-size: 18px;
            font-weight: 600;
            text-align: left;
            padding: 14px 20px;
            background-color: #f1f3f5;
            border-right: 1px solid #e9ecef;
        }
        
        .price-label .per-week {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 400;
        }
        
        .action-button {
            flex: 1;
            padding: 14px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .btn-rent {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-rent:hover {
            background-color: #27ae60;
        }
        
        .btn-buy {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-buy:hover {
            background-color: #c0392b;
        }
        
        /* Accordion styling */
        .book-details-accordion {
            margin-top: 2rem;
        }
        
        .accordion-item {
            border: none;
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .accordion-button {
            padding: 16px 20px;
            font-weight: 600;
            background-color: #f8f9fa;
            color: var(--text-dark);
            border: none;
        }
        
        .accordion-button:not(.collapsed) {
            background-color: #f1f3f5;
            color: var(--accent-color);
        }
        
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0,0,0,.125);
        }
        
        .accordion-button::after {
            background-size: 1.25rem;
            transition: all 0.2s;
        }
        
        .accordion-body {
            padding: 20px;
            background-color: #fff;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 12px;
            border-bottom: 1px dashed #edf2f7;
            padding-bottom: 12px;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .detail-label {
            width: 120px;
            font-weight: 600;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
            color: #666;
        }
        
        /* Tab styling */
        .book-details-tabs {
            margin-top: 2rem;
        }
        
        .nav-tabs {
            border-bottom: none;
            gap: 10px;
        }
        
        .nav-tabs .nav-item {
            margin-bottom: 0;
        }
        
        .nav-tabs .nav-link {
            border: none;
            padding: 12px 20px;
            font-weight: 500;
            color: var(--text-muted);
            border-radius: 8px 8px 0 0;
            transition: all 0.2s;
        }
        
        .nav-tabs .nav-link.active {
            background-color: white;
            color: var(--accent-color);
            font-weight: 600;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.05);
        }
        
        .tab-content {
            background-color: white;
            border-radius: 0 8px 8px 8px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
        }
        
        .tab-pane {
            min-height: 200px;
        }
        
        .edition-details {
            background-color: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .edition-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #444;
        }
        
        .book-description {
            line-height: 1.8;
            color: #555;
        }
        
        /* Review styling */
        .review-item {
            background-color: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        
        .review-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .reviewer-info {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e5e7eb;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #666;
        }
        
        .reviewer-name {
            font-weight: 600;
            color: #444;
        }
        
        .review-date {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-left: auto;
        }
        
        .review-text {
            line-height: 1.7;
            color: #555;
            margin-bottom: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .book-title {
                font-size: 2rem;
            }
            
            .book-cover {
                max-height: 400px;
            }
            
            .tab-content {
                padding: 1.5rem;
            }
        }
        
        /* Fix for action button form style */
        .action-form {
            flex: 1;
            margin: 0;
            padding: 0;
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

    <?php include('include/tab.php'); ?>

    <!-- Book Details -->
    <div class="container">
        <div class="book-details-container">
            <div class="row">
                <!-- Book Cover Column -->
                <div class="col-lg-4 col-md-5">
                    <div class="book-cover-container">
                        <img src="<?php echo $cover_image; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover" onerror="this.src='uploads/covers/default_book.jpg'">
                        <?php if ($book['condition'] === 'New' || $book['condition'] === 'Like New'): ?>
                            <div class="book-badge"><?php echo htmlspecialchars($book['condition']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Book Info Column -->
                <div class="col-lg-8 col-md-7">
                    <h1 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h1>
                    <p class="book-author">Author: <?php echo htmlspecialchars($book['author']); ?></p>
                    
                    <!-- Ratings -->
                    <div class="ratings">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<i class="fas fa-star star"></i>';
                            } else {
                                echo '<i class="far fa-star star"></i>';
                            }
                        }
                        ?>
                        <span class="ratings-text"><?php echo $rating; ?> out of 5</span>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <!-- Add to Cart Button -->
                    <form action="cart.php" method="GET">
                        <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="purchase_type" value="buy">
                        <button type="submit" class="cart-btn">
                            <i class="fas fa-shopping-cart me-2"></i>Add to cart
                        </button>
                    </form>
                    
                    <!-- Price & Action Buttons -->
                    <div class="price-row">
                        <div class="price-label">
                            ₱<?php echo number_format($book['rent_price'], 2); ?>
                            <div class="per-week">/Week</div>
                        </div>
                        <form action="cart.php" method="GET" class="action-form">
                            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="purchase_type" value="rent">
                            <input type="hidden" name="rental_weeks" value="1">
                            <button type="submit" class="action-button btn-rent">
                                <i class="fas fa-book-open me-2"></i>Rent
                            </button>
                        </form>
                    </div>
                    
                    <div class="price-row">
                        <div class="price-label">₱<?php echo number_format($book['price'], 2); ?></div>
                        <form action="cart.php" method="GET" class="action-form">
                            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="purchase_type" value="buy">
                            <button type="submit" class="action-button btn-buy">
                                <i class="fas fa-shopping-bag me-2"></i>Buy
                            </button>
                        </form>
                    </div>
                    
                    <!-- Accordion for Seller & Book Details -->
                    <div class="book-details-accordion mt-4">
                        <div class="accordion" id="bookAccordion">
                            <!-- Seller Details -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="sellerHeading">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sellerCollapse" aria-expanded="true" aria-controls="sellerCollapse">
                                        <i class="fas fa-user me-2"></i>Seller Details
                                    </button>
                                </h2>
                                <div id="sellerCollapse" class="accordion-collapse collapse show" aria-labelledby="sellerHeading" data-bs-parent="#bookAccordion">
                                    <div class="accordion-body">
                                        <div class="detail-row">
                                            <div class="detail-label">Seller:</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($book['firstname'] . ' ' . $book['lastname']); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Location:</div>
                                            <div class="detail-value">Davao City</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Rental Limit:</div>
                                            <div class="detail-value">1 month</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Book Details -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="bookDetailsHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bookDetailsCollapse" aria-expanded="false" aria-controls="bookDetailsCollapse">
                                        <i class="fas fa-info-circle me-2"></i>Book Details
                                    </button>
                                </h2>
                                <div id="bookDetailsCollapse" class="accordion-collapse collapse" aria-labelledby="bookDetailsHeading" data-bs-parent="#bookAccordion">
                                    <div class="accordion-body">
                                        <div class="detail-row">
                                            <div class="detail-label">ISBN:</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($book['ISBN'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Book-ID:</div>
                                            <div class="detail-value"><?php echo $book['book_id']; ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Book Type:</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($book['book_type'] ?? 'Paperback'); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Condition:</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($book['condition'] ?? 'New'); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Damages:</div>
                                            <div class="detail-value">
                                                <?php if (!empty($book['damages'])): ?>
                                                    <?php echo htmlspecialchars($book['damages']); ?>
                                                <?php else: ?>
                                                    None
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Genre:</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($book['genre'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Theme:</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($book['theme'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Description & Reviews Tabs -->
        <div class="book-details-tabs">
            <ul class="nav nav-tabs" id="bookTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">
                        <i class="fas fa-book me-2"></i>Description
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">
                        <i class="fas fa-comments me-2"></i>Reviews <span class="badge bg-secondary">3</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="bookTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                    <div class="edition-details">
                        <h4 class="edition-title">This edition</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-row">
                                    <div class="detail-label">ISBN:</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($book['ISBN'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Genre:</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($book['genre'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Theme:</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($book['theme'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="book-description">
                        <?php if (!empty($book['description'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                        <?php else: ?>
                            <p>No description available for this book.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <h4 class="mb-4">Customer Reviews</h4>
                    
                    <!-- Sample reviews -->
                    <div class="review-item">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">ES</div>
                            <div class="reviewer-name">Emma S.</div>
                            <div class="review-date">April 15, 2025</div>
                        </div>
                        <div class="ratings mb-2">
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                        </div>
                        <p class="review-text">Excellent book! I couldn't put it down. The characters are well developed and the story is engaging from start to finish.</p>
                    </div>
                    
                    <div class="review-item">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">MT</div>
                            <div class="reviewer-name">Michael T.</div>
                            <div class="review-date">April 10, 2025</div>
                        </div>
                        <div class="ratings mb-2">
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="far fa-star star"></i>
                        </div>
                        <p class="review-text">I enjoyed this book overall, though some parts dragged a bit. The ending was satisfying and made up for the slower sections.</p>
                    </div>
                    
                    <div class="review-item">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">SL</div>
                            <div class="reviewer-name">Sofia L.</div>
                            <div class="review-date">April 5,2025</div>
                        </div>
                        <div class="ratings mb-2">
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star-half-alt star"></i>
                        </div>
                        <p class="review-text">Great book with interesting characters and a compelling plot. The author's writing style is beautiful and descriptive.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Add success toast notification JavaScript -->
    <script>
        // Check if there was a redirect from cart.php with success parameter
        document.addEventListener('DOMContentLoaded', function() {
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const successParam = urlParams.get('cart_success');
            
            if (successParam === 'true') {
                // Create toast element
                const toastContainer = document.createElement('div');
                toastContainer.style.position = 'fixed';
                toastContainer.style.bottom = '20px';
                toastContainer.style.right = '20px';
                toastContainer.style.zIndex = '9999';
                
                const toast = document.createElement('div');
                toast.className = 'toast show';
                toast.style.backgroundColor = '#fff';
                toast.style.color = '#333';
                toast.style.padding = '10px 20px';
                toast.style.borderRadius = '4px';
                toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                toast.style.display = 'flex';
                toast.style.alignItems = 'center';
                toast.style.minWidth = '250px';
                
                toast.innerHTML = `
                    <div class="me-3" style="color: #2ecc71;">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;">Success!</div>
                        <div style="font-size: 0.9rem;">Item added to your cart.</div>
                    </div>
                `;
                
                toastContainer.appendChild(toast);
                document.body.appendChild(toastContainer);
                
                // Remove toast after 3 seconds
                setTimeout(function() {
                    toast.classList.remove('show');
                    setTimeout(function() {
                        document.body.removeChild(toastContainer);
                    }, 500);
                }, 3000);
                
                // Clean URL without reloading the page
                history.replaceState({}, document.title, window.location.pathname + window.location.hash);
            }
        });
    </script>
</body>
</html>