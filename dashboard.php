<?php
include("session.php");
include("connect.php");

$userType = $_SESSION['usertype'] ?? '';
$firstName = $_SESSION['firstname'] ?? '';
$lastName = $_SESSION['lastname'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone'] ?? '';
$photo = $_SESSION['profile_picture'] ?? '';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch most popular books from database
$popularBooksQuery = "SELECT * FROM books WHERE popularity = 'Most popular' ORDER BY created_at DESC LIMIT 5";
$popularBooksResult = $conn->query($popularBooksQuery);

// Fetch new releases
$newReleasesQuery = "SELECT * FROM books WHERE popularity = 'New Releases' ORDER BY created_at DESC LIMIT 4";
$newReleasesResult = $conn->query($newReleasesQuery);

// Fetch all themes for category tabs
$themesQuery = "SELECT DISTINCT theme FROM books WHERE theme IS NOT NULL AND theme != ''";
$themesResult = $conn->query($themesQuery);
$themes = [];
while ($row = $themesResult->fetch_assoc()) {
    $themes[] = $row['theme'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWagon - Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/tab.css">
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
            background-color: #f8f9fa;
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
        
        .dropdown-toggle::after {
            margin-left: 0.5em;
        }
        
        /* Header styles */
        .navbar {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .navbar-brand img {
            height: 60px;
        }
        
        /* Carousel styles */
        .carousel {
            margin: 20px 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .carousel-item img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
        }
        
        .carousel-control-prev, .carousel-control-next {
            width: 5%;
            opacity: 0.8;
            background-color: rgba(0,0,0,0.2);
            border-radius: 50%;
            height: 50px;
            width: 50px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .carousel-indicators {
            bottom: 10px;
        }
        
        .carousel-indicators button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #ccc;
            margin: 0 5px;
        }
        
        .carousel-indicators .active {
            background-color: var(--primary-color);
        }
        
        /* Section headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            margin-top: 40px;
        }
        
        .section-title {
            font-weight: 700;
            margin: 0;
            font-size: 1.5rem;
            color: var(--text-dark);
            position: relative;
            padding-left: 15px;
        }
        
        .section-title:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 5px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }
        
        .see-more {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .see-more:hover {
            color: #d18a00;
            text-decoration: underline;
        }
        
        /* Book cards */
        .books-container {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px; /* Negative margin to counteract padding */
        }
        
        .book-col {
            padding: 10px; /* Spacing between books */
        }
        
        .book-card {
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .book-img-container {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .book-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .book-card:hover .book-img {
            transform: scale(1.05);
        }
        
        .book-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--primary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .book-details {
            padding: 15px;
        }
        
        .book-author {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }
        
        .book-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 5px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 40px;
        }
        
        .book-price {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            align-items: center;
        }
        
        .price-week {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .price-value {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .book-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .btn-rent {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .btn-rent:hover {
            background-color: #e69500;
            color: white;
        }
        
        .btn-buy {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .btn-buy:hover {
            background-color: #218838;
            color: white;
        }
        
        /* Theme tabs */
        .theme-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap;
        }
        
        .theme-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            color: var(--text-muted);
            margin-right: 5px;
        }
        
        .theme-tab:hover {
            color: var(--primary-color);
        }
        
        .theme-tab.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background-color: rgba(248, 161, 0, 0.05);
        }
        
        /* Libraries section */
        .libraries-section {
            margin-top: 40px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .search-bar {
            margin-bottom: 20px;
            position: relative;
        }
        
        .search-bar .form-control {
            padding-left: 40px;
            border-radius: 25px;
            border: 1px solid var(--border-color);
        }
        
        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        
        .library-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            transition: all 0.3s;
            background-color: white;
        }
        
        .library-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        
        .library-img {
            width: 120px;
            height: 80px;
            border-radius: 5px;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .library-details {
            flex: 1;
        }
        
        .library-name {
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .library-type {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .library-rating {
            display: flex;
            align-items: center;
        }
        
        .rating-value {
            margin-right: 5px;
        }
        
        .stars {
            color: var(--primary-color);
            font-size: 0.8rem;
        }
        
        .library-location {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 5px 0;
        }
        
        .library-address {
            font-size: 0.85rem;
            margin-bottom: 5px;
        }
        
        .library-status {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            margin-top: 10px;
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-open {
            background-color: #28a745;
        }
        
        .status-closed {
            background-color: #dc3545;
        }
        
        .directions-btn {
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .directions-btn:hover {
            color: #d18a00;
            text-decoration: underline;
        }
        
        /* Footer */
        .footer {
            padding: 40px 0;
            border-top: 1px solid var(--border-color);
            margin-top: 40px;
            background-color: #f8f9fa;
        }
        
        .footer-heading {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .footer-link {
            display: block;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer-link:hover {
            color: var(--primary-color);
            padding-left: 5px;
        }
        
        .app-download {
            display: block;
            margin-bottom: 10px;
        }
        
        .app-download img {
            height: 40px;
        }
        
        .social-links {
            margin-top: 20px;
        }
        
        .social-icon {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--text-dark);
            color: white;
            margin-right: 10px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-icon:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        .copyright {
            padding: 20px 0;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            background-color: white;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .book-img-container {
                height: 200px;
            }
            
            .library-card {
                flex-direction: column;
            }
            
            .library-img {
                width: 100%;
                height: 120px;
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .book-col {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        
        @media (max-width: 576px) {
            .book-col {
                flex: 0 0 100%;
                max-width: 100%;
            }
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

    <!-- Navigation tabs -->
    <div class="container  pt-3">
    <div class="tab-menu">
      <a href="dashboard.php" class="active">
          
          Home
      </a>
      <a href="rentbooks.php">
         
          Rentbooks
      </a>
      <a href="explore.php">
          
          Explore
      </a>
      <a href="libraries.php">
          
          Libraries
      </a>
      <a href="bookswap.php">
          
          Bookswap
      </a>
    </div>

    <!-- Hero Carousel -->
    <div class="container text-center">
        <div id="heroCarousel" class="carousel slide mx-auto" data-bs-ride="carousel" style="max-width: 1200px;">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="images/1.jpg" class="d-block w-100" alt="Philippine Book Festival">
                </div>
                <div class="carousel-item">
                    <img src="images/2.png" class="d-block w-100" alt="BookWagon Feature">
                </div>
                <div class="carousel-item">
                    <img src="images/3.png" class="d-block w-100" alt="BookWagon Promotion">
                </div>
                <div class="carousel-item">
                    <img src="images/4.jpg" class="d-block w-100" alt="BookWagon Event">
                </div>
                <div class="carousel-item">
                    <img src="images/5.jpg" class="d-block w-100" alt="BookWagon Special">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="4" aria-label="Slide 5"></button>
            </div>
        </div>
    </div>
    
    <!-- Most Popular -->
    <div class="container">
        <div class="section-header">
            <h4 class="section-title">Most Popular</h4>
            <a href="explore.php?filter=popular" class="see-more">See More <i class="fas fa-chevron-right"></i></a>
        </div>
        
        <div class="books-container">
            <?php if ($popularBooksResult->num_rows > 0): ?>
                <?php while($book = $popularBooksResult->fetch_assoc()): ?>
                    <div class="book-col col-md-2 col-6">
                        <div class="book-card">
                            <div class="book-img-container">
                                <img src="<?php echo $book['cover_image'] ?: 'https://via.placeholder.com/200x250?text=No+Cover'; ?>" class="book-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <span class="book-badge">Popular</span>
                            </div>
                            <div class="book-details">
                                <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                <div class="book-price">
                                    <span class="price-week">₱<?php echo number_format($book['rent_price'], 2); ?>/week</span>
                                    <span class="price-value">₱<?php echo number_format($book['price'], 2); ?></span>
                                </div>
                                <div class="book-actions">
                                    <a href="rentbooks.php?book_id=<?php echo $book['book_id']; ?>" class="btn-rent">Rent</a>
                                    <a href="checkout.php?book_id=<?php echo $book['book_id']; ?>" class="btn-buy">Buy</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p>No popular books found. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- New Releases -->
    <div class="container">
        <div class="section-header">
            <h4 class="section-title">New Releases</h4>
            <a href="explore.php?filter=new" class="see-more">See More <i class="fas fa-chevron-right"></i></a>
        </div>
        
        <div class="books-container">
            <?php if ($newReleasesResult->num_rows > 0): ?>
                <?php while($book = $newReleasesResult->fetch_assoc()): ?>
                    <div class="book-col col-md-3 col-6">
                        <div class="book-card">
                            <div class="book-img-container">
                                <img src="<?php echo $book['cover_image'] ?: 'https://via.placeholder.com/200x250?text=No+Cover'; ?>" class="book-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <span class="book-badge">New</span>
                            </div>
                            <div class="book-details">
                                <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                <div class="book-price">
                                    <span class="price-week">₱<?php echo number_format($book['rent_price'], 2); ?>/week</span>
                                    <span class="price-value">₱<?php echo number_format($book['price'], 2); ?></span>
                                </div>
                                <div class="book-actions">
                                    <a href="rentbooks.php?book_id=<?php echo $book['book_id']; ?>" class="btn-rent">Rent</a>
                                    <a href="checkout.php?book_id=<?php echo $book['book_id']; ?>" class="btn-buy">Buy</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p>No new releases found. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Explore Section -->
    <div class="container">
        <div class="section-header">
            <h4 class="section-title">Explore by Theme</h4>
        </div>
        
        <div class="theme-tabs">
            <div class="theme-tab active">All</div>
            <?php foreach($themes as $theme): ?>
                <div class="theme-tab"><?php echo htmlspecialchars($theme); ?></div>
            <?php endforeach; ?>
        </div>
        
        <div class="books-container">
            <?php 
            // Fetch 4 random books to display in explore section
            $exploreQuery = "SELECT * FROM books ORDER BY RAND() LIMIT 4";
            $exploreResult = $conn->query($exploreQuery);
            
            if ($exploreResult->num_rows > 0): 
                while($book = $exploreResult->fetch_assoc()): ?>
                    <div class="book-col col-md-3 col-6">
                        <div class="book-card">
                            <div class="book-img-container">
                                <img src="<?php echo $book['cover_image'] ?: 'https://via.placeholder.com/200x250?text=No+Cover'; ?>" class="book-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            </div>
                            <div class="book-details">
                                <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                <?php if($book['theme']): ?>
                                    <div class="book-theme"><small class="text-muted">Theme: <?php echo htmlspecialchars($book['theme']); ?></small></div>
                                <?php endif; ?>
                                <div class="book-price">
                                    <span class="price-value">₱<?php echo number_format($book['price'], 2); ?></span>
                                </div>
                                <div class="book-actions">
                                    <a href="book_details.php?id=<?php echo $book['book_id']; ?>" class="btn-rent">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p>No books found. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-end mt-3 mb-4">
            <a href="rentbooks.php" class="see-more">Browse All Books <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
    
    <!-- Libraries Section -->
    <div class="container libraries-section">
        <h4 class="section-title mb-3">Libraries Near You</h4>
        
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Search libraries...">
        </div>
        
        <div class="mb-2">Suggested for you</div>
        
        <div class="library-card">
            <img src="https://i.ibb.co/nrDHDDZ/davao-central-college.jpg" class="library-img" alt="Davao Central College Library">
            <div class="library-details">
                <div class="library-name">
                    <div>Davao Central College Library <span class="library-type">(Private)</span></div>
                    <div class="library-rating">
                        <span class="rating-value">5.0</span>
                        <div class="stars">★★★★★</div>
                    </div>
                </div>
                <div class="library-location">
                    <i class="fa-solid fa-location-dot"></i> 3 kilometers from your current location
                </div>
                <div class="library-address">
                    Don Juan Street, Toril<br>
                    Davao City 8000
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="#" class="directions-btn">
                        Get Directions <i class="fa-solid fa-turn-down"></i>
                    </a>
                    <div class="library-status">
                        <span class="status-indicator status-open"></span>
                        Open • Weekdays 8am to 7pm
                    </div>
                </div>
            </div>
        </div>
        
        <div class="library-card">
            <img src="https://i.ibb.co/wWGsCWB/davao-city-library.jpg" class="library-img" alt="Davao City Library">
            <div class="library-details">
                <div class="library-name">
                    <div>Davao City Library and Information Center <span class="library-type">(Public)</span></div>
                    <div class="library-rating">
                        <span class="rating-value">5.0</span>
                        <div class="stars">★★★★★</div>
                    </div>
                </div>
                <div class="library-location">
                    <i class="fa-solid fa-location-dot"></i> 6 kilometers from your current location
                </div>
                <div class="library-address">
                    152 San Pedro St, Poblacion District,<br>
                    Davao City, 8000 Davao del Sur
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="#" class="directions-btn">
                        Get Directions <i class="fa-solid fa-turn-down"></i>
                    </a>
                    <div class="library-status">
                        <span class="status-indicator status-open"></span>
                        Open • Weekdays 8am to 7pm
                    </div>
                </div>
            </div>
        </div>
        
        <div class="library-card">
            <img src="https://i.ibb.co/1LqDVdR/university-library.jpg" class="library-img" alt="University Library">
            <div class="library-details">
                <div class="library-name">
                    <div>University Library <span class="library-type">(Private)</span></div>
                    <div class="library-rating">
                        <span class="rating-value">5.0</span>
                        <div class="stars">★★★★★</div>
                    </div>
                </div>
                <div class="library-location">
                    <i class="fa-solid fa-location-dot"></i> 5 kilometers from your current location
                </div>
                <div class="library-address">
                    JHMP+WH, Tugbok,<br>
                    Davao City, Davao del Sur
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="#" class="directions-btn">
                        Get Directions <i class="fa-solid fa-turn-down"></i>
                    </a>
                    <div class="library-status">
                        <span class="status-indicator status-closed"></span>
                        Closed • Opens 8 AM Mon
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <h5 class="footer-heading">Company</h5>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Help/FAQ</a>
                    <a href="#" class="footer-link">Works</a>
                    <a href="#" class="footer-link">Careers</a>
                </div>
                
                <div class="col-md-3 col-6 mb-4">
                    <h5 class="footer-heading">Help</h5>
                    <a href="#" class="footer-link">Customer Support</a>
                    <a href="#" class="footer-link">Delivery Details</a>
                    <a href="#" class="footer-link">Terms & Conditions</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                </div>
                
                <div class="col-md-3 col-6 mb-4">
                    <h5 class="footer-heading">Resources</h5>
                    <a href="#" class="footer-link">Free eBooks</a>
                    <a href="#" class="footer-link">Development</a>
                    <a href="#" class="footer-link">Tutorials</a>
                    <a href="#" class="footer-link">How-to Videos</a>
                    <a href="#" class="footer-link">YouTube Playlist</a>
                </div>
                
                <div class="col-md-3 col-6 mb-4">
                    <div class="mb-4">
                        <h5 class="footer-heading">Get the App</h5>
                        <a href="#" class="app-download">
                            <img src="https://i.ibb.co/kSzkRgQ/app-store.png" alt="App Store">
                        </a>
                        <a href="#" class="app-download">
                            <img src="https://i.ibb.co/FqJsKND/play-store.png" alt="Play Store">
                        </a>
                    </div>
                    <div class="social-links">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <div class="copyright">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>© Copyright <?php echo date('Y'); ?>, All Rights Reserved by BookWagon</div>
                <div>
                    <a href="#" class="text-muted me-3">Terms</a>
                    <a href="#" class="text-muted">Privacy</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize carousel
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Carousel(document.querySelector('#heroCarousel'), {
                interval: 3000, // Change slides every 3 seconds
                wrap: true     // Continue from last to first slide
            });
            
            // Theme tab functionality
            const tabs = document.querySelectorAll('.theme-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    // Here you would typically filter books by theme
                    // For now it's just UI demonstration
                });
            });
        });
    </script>
    <script>
        // Prevent back button after logout
        window.onload = function() {
            if(typeof window.history.pushState == 'function') {
                window.history.pushState({}, "Hide", location.href);
            }
            window.onpopstate = function() {
                window.history.go(1);
            };
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('#userDropdown').addEventListener('click', function(e) {
                e.preventDefault();
                var dropdown = bootstrap.Dropdown.getOrCreateInstance(this);
                dropdown.toggle();
            });
        });
    </script>

    <script src="https://kit.fontawesome.com/yourkitcode.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>