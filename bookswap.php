<?php
include("session.php");
include("connect.php");

$userType = $_SESSION['usertype'] ?? '';
$userId = $_SESSION['id'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWagon - Book Swap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/tab.css">
    <style>
        :root {
            --primary-color: #f8a100;
            --secondary-color: #f8f9fa;
            --accent-color: #ff6b6b;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --card-shadow: 0 4px 15px rgba(0,0,0,0.1);
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background-color: #f5f7fa;
        }
        .navbar {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .navbar-brand img {
            height: 60px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Enhanced Book Card */
        .book-card {
            perspective: 1000px;
            background: transparent;
            height: 400px; /* Fixed height for consistent flipping */
        }

        .book-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 0.8s;
            transform-style: preserve-3d;
            cursor: pointer;
        }

        .book-card.flipped .book-card-inner {
            transform: rotateY(180deg);
        }

        .book-card-front,
        .book-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            background: white;
            overflow: hidden;
        }

        .book-card-back {
            transform: rotateY(180deg);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
        }

        .book-description-back {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
        }

        .flip-hint {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8rem;
            color: var(--text-muted);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .book-card:hover .flip-hint {
            opacity: 1;
        }

        /* Adjust existing styles */
        .book-image-container {
            height: 65%;
        }

        .book-details {
            height: 35%;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .action-container {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .book-image {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform var(--transition-speed);
        }

        .book-card:hover .book-image {
            transform: scale(1.05);
        }

        .book-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .book-author {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .book-condition {
            position: absolute;
            top: -12px;
            right: 15px;
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background: var(--accent-color);
            color: white;
            box-shadow: 0 2px 8px rgba(255,107,107,0.3);
        }

        .book-description {
            font-size: 0.9rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        /* Enhanced Buttons */
        .swap-btn {
            background: linear-gradient(45deg, var(--accent-color), #ff8585);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            transition: all var(--transition-speed);
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            margin: 0;
        }

        .swap-btn:hover {
            background: linear-gradient(45deg, #ff8585, var(--accent-color));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,107,107,0.3);
        }

        /* Enhanced Search and Filters */
        .search-filter {
            width: 300px;
            position: relative;
        }

        .search-filter input {
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border-radius: 25px;
            border: 1px solid var(--border-color);
            transition: all var(--transition-speed);
        }

        .search-filter input:focus {
            box-shadow: 0 0 0 3px rgba(248,161,0,0.1);
            border-color: var(--primary-color);
        }

        .search-filter::before {
            content: '\f002';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .filters {
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        .filters .btn-outline-secondary {
            border-radius: 20px;
            padding: 0.5rem 1.2rem;
            margin-right: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all var(--transition-speed);
        }

        .filters .btn-outline-secondary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Book Owner Section */
        .book-owner {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: var(--secondary-color);
            border-radius: 8px;
            margin-top: 0.5rem;
        }

        .owner-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .owner-info {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Section Headers */
        .section-header {
            margin-bottom: 2rem;
            position: relative;
        }

        .section-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .section-header::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
            margin-top: 0.5rem;
            border-radius: 2px;
        }

        /* Modal Enhancements */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .modal-header {
            background: linear-gradient(45deg, var(--primary-color), #ffc107);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .form-control {
            border-radius: 8px;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border-color);
            transition: all var(--transition-speed);
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(248,161,0,0.1);
            border-color: var(--primary-color);
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
      <a href="dashboard.php" >
          
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
      <a href="bookswap.php" class="active">
          
          Bookswap
      </a>
    </div>


    <div class="container mt-5">
        <!-- My Books Section -->
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <h2 class="mb-0">My Books for Swap</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                    <i class="fas fa-plus"></i> Add Book for Swap
                </button>
            </div>
        </div>
        <div class="row mb-5" id="myBooksContainer">
            <!-- My books will be loaded here dynamically -->
        </div>

        <!-- Available Books Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Available Books for Swap</h2>
                    <div class="search-filter">
                        <input type="text" class="form-control" id="searchBooks" placeholder="Search books...">
                    </div>
                </div>
                <div class="filters mt-3">
                    <button class="btn btn-outline-secondary btn-sm me-2 active" data-condition="all">All</button>
                    <button class="btn btn-outline-secondary btn-sm me-2" data-condition="New">New</button>
                    <button class="btn btn-outline-secondary btn-sm me-2" data-condition="Like New">Like New</button>
                    <button class="btn btn-outline-secondary btn-sm me-2" data-condition="Good">Good</button>
                    <button class="btn btn-outline-secondary btn-sm" data-condition="Fair">Fair</button>
                </div>
            </div>
        </div>
        <div class="row" id="availableBooksContainer">
            <!-- Available books will be loaded here dynamically -->
        </div>
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBookModalLabel">Add Book for Swap</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBookForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Book Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="condition" class="form-label">Condition</label>
                            <select class="form-control" id="condition" name="condition" required>
                                <option value="">Select condition...</option>
                                <option value="New">New</option>
                                <option value="Like New">Like New</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="book_image" class="form-label">Book Image</label>
                            <input type="file" class="form-control" id="book_image" name="book_image" accept="image/*" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitBook">Add Book</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/swap.js"></script>
    <script>
        // Initialize Bootstrap modals
        document.addEventListener('DOMContentLoaded', function() {
            const addBookModal = new bootstrap.Modal(document.getElementById('addBookModal'));
            
            // Show modal when button is clicked
            document.querySelector('[data-bs-target="#addBookModal"]').addEventListener('click', function() {
                addBookModal.show();
            });
        });
    </script>
</body>
</html>