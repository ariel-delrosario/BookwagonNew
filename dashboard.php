<?php
include("session.php");
include("connect.php");


$userType = $_SESSION['usertype'] ?? ''; // Change to lowercase 'usertype'
$firstName = $_SESSION['firstname'] ?? ''; // Change to lowercase 'firstname'
$lastName = $_SESSION['lastname'] ?? ''; // Change to lowercase 'lastname'
$email = $_SESSION['email'] ?? '';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
            background-color: #fff;
        }
        
        /* Header styles */
        .navbar {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .navbar-brand img {
            height: 60px;
        }
        
 
        /* Carousel styles */
        .carousel {
            margin: 20px 0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .carousel-item img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }
        
        .carousel-control-prev, .carousel-control-next {
            width: 5%;
            opacity: 0.8;
        }
        
        .carousel-indicators {
            bottom: -30px;
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
            margin-top: 30px;
        }
        
        .section-title {
            font-weight: 600;
            margin: 0;
        }
        
        .see-more {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        /* Book cards */
        .book-card {
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
        }
        
        .book-img {
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .book-author {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }
        
        .book-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-top: 10px;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .book-price {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
        }
        
        .price-week {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .price-value {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        /* Category tabs */
        .category-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .category-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .category-tab.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }
        
        /* Libraries section */
        .libraries-section {
            margin-top: 40px;
        }
        
        .search-bar {
            margin-bottom: 20px;
        }
        
        .library-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
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
        }
        
        /* Footer */
        .footer {
            padding: 40px 0;
            border-top: 1px solid var(--border-color);
            margin-top: 40px;
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
        }
        
        .footer-link:hover {
            color: var(--primary-color);
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
        }
        
        .copyright {
            padding: 20px 0;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
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
      <a href="grocery.php">
          
          Explore
      </a>
      <a href="libraries.php">
          
          Libraries
      </a>
      <a href="bookswap.php">
          
          Bookswap
      </a>
    </div>


<div class="container text-center">
    <div id="heroCarousel" class="carousel slide mx-auto" data-bs-ride="carousel" style="max-width: 800px;">
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
            <a href="#" class="see-more">See More</a>
        </div>
        
        <div class="row">
            <div class="col-md-2 col-6 book-card">
                <div class="text-center">
                    <div class="book-author">Kris Johanna Laniaza</div>
                    <img src="https://i.ibb.co/9VpvJQn/noli-me-tangere.jpg" class="book-img img-fluid" alt="Noli Me Tangere">
                    <div class="book-title">NOLI ME TANGERE</div>
                    <div class="book-price">
                        <span class="price-week">₱60/week</span>
                        <span class="price-value">₱360</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 col-6 book-card">
                <div class="text-center">
                    <div class="book-author">Kris Johanna Laniaza</div>
                    <img src="https://i.ibb.co/NnmH9QK/harry-potter.jpg" class="book-img img-fluid" alt="Harry Potter">
                    <div class="book-title">Harry Potter and the Goblet of Fire</div>
                    <div class="book-price">
                        <span class="price-week">₱60/week</span>
                        <span class="price-value">₱360</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 col-6 book-card">
                <div class="text-center">
                    <div class="book-author">Zenepachi Zenny</div>
                    <img src="https://i.ibb.co/Ws7vwQ7/handmaids-tale.jpg" class="book-img img-fluid" alt="The Handmaid's Tale">
                    <div class="book-title">The Handmaid's Tale</div>
                    <div class="book-price">
                        <span class="price-week">₱70/week</span>
                        <span class="price-value">₱380</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 col-6 book-card">
                <div class="text-center">
                    <div class="book-author">Jay anne Galas</div>
                    <img src="https://i.ibb.co/gj5rvTV/to-kill-mockingbird.jpg" class="book-img img-fluid" alt="To Kill a Mockingbird">
                    <div class="book-title">To Kill a Mockingbird</div>
                    <div class="book-price">
                        <span class="price-week">₱50/week</span>
                        <span class="price-value">₱350</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 col-6 book-card">
                <div class="text-center">
                    <div class="book-author">Jay anne Galas</div>
                    <img src="https://i.ibb.co/rQbXPJx/pride-prejudice.jpg" class="book-img img-fluid" alt="Pride and Prejudice">
                    <div class="book-title">Pride and Prejudice</div>
                    <div class="book-price">
                        <span class="price-week">₱65/week</span>
                        <span class="price-value">₱350</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Explore Section -->
    <div class="container">
        <div class="section-header">
            <h4 class="section-title">Explore</h4>
        </div>
        
        <div class="category-tabs">
            <div class="category-tab active">All</div>
            <div class="category-tab">Sci-Fi</div>
            <div class="category-tab">Education</div>
            <div class="category-tab">Non-Fiction</div>
            <div class="category-tab">Fiction</div>
            <div class="category-tab">Drama</div>
        </div>
        
        <div class="row">
            <div class="col-md-3 col-6 book-card">
                <div class="text-center">
                    <img src="https://i.ibb.co/ZfkKKqH/drama-queen.jpg" class="book-img img-fluid" alt="The Fall of a Drama Queen">
                    <div class="book-title">The Fall of a Drama Queen</div>
                </div>
            </div>
            
            <div class="col-md-3 col-6 book-card">
                <div class="text-center">
                    <img src="https://i.ibb.co/gj5rvTV/to-kill-mockingbird.jpg" class="book-img img-fluid" alt="To Kill a Mockingbird">
                    <div class="book-title">To Kill a Mockingbird</div>
                </div>
            </div>
            
            <div class="col-md-3 col-6 book-card">
                <div class="text-center">
                    <img src="https://i.ibb.co/rQbXPJx/pride-prejudice.jpg" class="book-img img-fluid" alt="Pride and Prejudice">
                    <div class="book-title">Pride and Prejudice</div>
                </div>
            </div>
            
            <div class="col-md-3 col-6 book-card">
                <div class="text-center">
                    <img src="https://i.ibb.co/Ws7vwQ7/handmaids-tale.jpg" class="book-img img-fluid" alt="The Handmaid's Tale">
                    <div class="book-title">The Handmaid's Tale</div>
                </div>
            </div>
        </div>
        
        <div class="text-end mt-3 mb-4">
            <a href="#" class="see-more">See More</a>
        </div>
    </div>
    
    <!-- Libraries Section -->
    <div class="container libraries-section">
        <h4 class="section-title mb-3">Libraries</h4>
        
        <div class="search-bar">
            <input type="text" class="form-control" placeholder="Search">
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
                    <a class="directions-btn">
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
                    <a class="directions-btn">
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
                    <a class="directions-btn">
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
                        <a href="#" class="app-download">
                            <img src="https://i.ibb.co/kSzkRgQ/app-store.png" alt="App Store">
                        </a>
                        <a href="#" class="app-download">
                            <img src="https://i.ibb.co/FqJsKND/play-store.png" alt="Play Store">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <div class="copyright">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>© Copyright 2022, All Rights Reserved by ClarkSys</div>
                <div class="social-links">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
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
</body>
</html>