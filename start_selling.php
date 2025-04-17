<?php
include("session.php");
include("connect.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWagon - Start Selling</title>
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
    <!-- Header/Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/logo.png" alt="BookWagon">
            </a>
            
            <div class="d-flex align-items-center">
                <a href="#" class="nav-link me-3">Start selling</a>
                <a href="#" class="nav-link me-3"><i class="fa-regular fa-bell"></i></a>
                <a href="#" class="nav-link me-3"><i class="fa-regular fa-envelope"></i></a>
                <a href="#" class="nav-link"><?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['email']; ?></a>
                <a href="logout.php" class="nav-link">Logout</a>
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




    

    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>