<?php
include("session.php");
include("connect.php");
$userType = $_SESSION['usertype'] ?? '';
$userId = $_SESSION['id'] ?? '';
$userType = $_SESSION['usertype'] ?? ''; // Change to lowercase 'usertype'
$firstName = $_SESSION['firstname'] ?? ''; // Change to lowercase 'firstname'
$lastName = $_SESSION['lastname'] ?? ''; // Change to lowercase 'lastname'
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone'] ?? '';
$photo = $_SESSION['profile_picture'] ?? '';
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
        .book-title, .book-author {
            text-decoration: none !important;
            border-bottom: none !important;
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
        /* Enhanced Card Design */
        .book-card {
            width: 100%;
            height: auto;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .book-card:hover {
            transform: translateY(-5px);
        }
        .book-image-container {
            height: 300px;
            overflow: hidden;
            position: relative;
        }
        .book-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .book-card:hover .book-image {
            transform: scale(1.05);
        }
        .book-details {
            padding: 1.5rem;
            text-align: left;
        }
        .book-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        .book-author {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
        }
        .book-description {
            font-size: 0.9rem;
            color: #95a5a6;
            margin-bottom: 1.5rem;
        }
        
        /* Book condition badges */
        .book-condition {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            z-index: 2;
        }
        .condition-New {
            background-color: #27ae60;
        }
        .condition-Like-New {
            background-color: #2ecc71;
        }
        .condition-Very-Good {
            background-color: #3498db;
        }
        .condition-Good {
            background-color: #f39c12;
        }
        .condition-Acceptable {
            background-color: #e74c3c;
        }
        
        /* Swap button */
        .swap-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            margin-bottom: 1rem;
        }
        .swap-btn:hover {
            background-color: #e09200;
            transform: translateY(-2px);
        }
        
        /* Owner info */
        .book-owner {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .owner-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        /* Filters */
        .filters {
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-btn {
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 8px 15px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-btn:hover {
            background-color: #f0f0f0;
        }
        .filter-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Search */
        .search-container {
            margin-bottom: 20px;
        }
        .search-input {
            width: 100%;
            padding: 12px 20px;
            border-radius: 30px;
            border: 1px solid var(--border-color);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(248, 161, 0, 0.2);
            border-color: var(--primary-color);
        }
        
        /* Tabs */
        .tab-container {
            margin-top: 30px;
        }
        .nav-tabs {
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 20px;
        }
        .nav-tabs .nav-link {
            border: none;
            padding: 15px 25px;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary-color);
        }
        .nav-tabs .nav-link.active {
            border: none;
            border-bottom: 3px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        /* Modal */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            padding: 20px;
        }
        .modal-body {
            padding: 30px;
        }
        .modal-footer {
            border-top: none;
            padding: 20px;
        }
        
        /* Form styles */
        .form-control {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        /* No books message */
        .no-books-message {
            text-align: center;
            padding: 50px 0;
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        .no-books-message h4 {
            color: var(--text-dark);
            margin-bottom: 15px;
        }
        .no-books-message p {
            color: var(--text-muted);
        }
        
        /* Swap requests styles */
        .request-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        .request-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
        }
        .requester-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        .request-info {
            flex-grow: 1;
        }
        .requester-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        .request-date {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #f39c12;
        }
        .status-accepted {
            background-color: #27ae60;
        }
        .status-rejected {
            background-color: #e74c3c;
        }
        
        .request-book {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .request-book-image {
            width: 80px;
            height: 120px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 5px;
        }
        .request-book-info h5 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .request-message {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            font-style: italic;
            color: var(--text-muted);
            margin-bottom: 15px;
        }
        .request-actions {
            display: flex;
            gap: 10px;
        }
        .btn-accept, .btn-reject {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-accept {
            background-color: #27ae60;
            color: white;
        }
        .btn-accept:hover {
            background-color: #219653;
        }
        .btn-reject {
            background-color: #e74c3c;
            color: white;
        }
        .btn-reject:hover {
            background-color: #c0392b;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .book-card {
                margin-bottom: 30px;
            }
        }
        @media (max-width: 768px) {
            .filters {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            .filter-btn {
                flex-shrink: 0;
            }
            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            .request-card {
                padding: 15px;
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

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Book Swap</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                <i class="fas fa-plus me-2"></i> Add Book for Swap
            </button>
        </div>

        <div class="tab-container">
            <ul class="nav nav-tabs" id="bookSwapTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="available-tab" data-bs-toggle="tab" data-bs-target="#available-books" type="button" role="tab" aria-controls="available-books" aria-selected="true">
                        <i class="fas fa-book me-2"></i> Available Books
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="my-books-tab" data-bs-toggle="tab" data-bs-target="#my-books" type="button" role="tab" aria-controls="my-books" aria-selected="false">
                        <i class="fas fa-bookmark me-2"></i> My Books
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="requests-tab" data-bs-toggle="tab" data-bs-target="#swap-requests" type="button" role="tab" aria-controls="swap-requests" aria-selected="false">
                        <i class="fas fa-exchange-alt me-2"></i> Swap Requests
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="bookSwapTabsContent">
                <!-- Available Books Tab -->
                <div class="tab-pane fade show active" id="available-books" role="tabpanel" aria-labelledby="available-tab">
                    <div class="search-container">
                        <input type="text" id="searchBooks" class="search-input" placeholder="Search books by title...">
                    </div>
                    
                    <div class="filters">
                        <button class="filter-btn active" data-condition="all">All Conditions</button>
                        <button class="filter-btn" data-condition="New">New</button>
                        <button class="filter-btn" data-condition="Like New">Like New</button>
                        <button class="filter-btn" data-condition="Very Good">Very Good</button>
                        <button class="filter-btn" data-condition="Good">Good</button>
                        <button class="filter-btn" data-condition="Acceptable">Acceptable</button>
                    </div>
                    
                    <div class="row" id="availableBooksContainer">
                        <!-- Books will be loaded here via JavaScript -->
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading available books...</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- My Books Tab -->
                <div class="tab-pane fade" id="my-books" role="tabpanel" aria-labelledby="my-books-tab">
                    <p class="mb-4">These are the books you've added for swapping:</p>
                    
                    <div class="row" id="myBooksContainer">
                        <!-- My books will be loaded here via JavaScript -->
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading your books...</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Swap Requests Tab -->
                <div class="tab-pane fade" id="swap-requests" role="tabpanel" aria-labelledby="requests-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="mb-4">Incoming Requests</h3>
                            <div id="incomingRequestsContainer">
                                <!-- Incoming requests will be loaded here via JavaScript -->
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading incoming requests...</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3 class="mb-4">Outgoing Requests</h3>
                            <div id="outgoingRequestsContainer">
                                <!-- Outgoing requests will be loaded here via JavaScript -->
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading outgoing requests...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBookModalLabel">Add a Book for Swap</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBookForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Book Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="author" class="form-label">Author *</label>
                                    <input type="text" class="form-control" id="author" name="author" required>
                                </div>
                                <div class="mb-3">
                                    <label for="condition" class="form-label">Condition *</label>
                                    <select class="form-select" id="condition" name="condition" required>
                                        <option value="">Select condition</option>
                                        <option value="New">New</option>
                                        <option value="Like New">Like New</option>
                                        <option value="Very Good">Very Good</option>
                                        <option value="Good">Good</option>
                                        <option value="Acceptable">Acceptable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="book_image" class="form-label">Book Cover Image *</label>
                                    <input type="file" class="form-control" id="book_image" name="book_image" accept="image/*" required>
                                    <div class="form-text">Upload a clear image of your book cover (max 5MB)</div>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5" placeholder="Add any relevant details about your book..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="submitBook">Add Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Swap Request Modal -->
    <div class="modal fade" id="swapRequestModal" tabindex="-1" aria-labelledby="swapRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="swapRequestModalLabel">Request Book Swap</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3">You are requesting to swap for:</h6>
                    <div id="requestedBookInfo" class="d-flex align-items-start mb-4">
                        <!-- Book info will be loaded here -->
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading book information...</span>
                    </div>
                    
                    <form id="swapRequestForm">
                    <input type="hidden" id="book_id" name="book_id">
                        
                        <div class="mb-4">
                            <label for="myBookSelect" class="form-label">Select one of your books to offer</label>
                            <select class="form-select" id="myBookSelect" name="my_book_id" required>
                                <option value="">Loading your books...</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="swapMessage" class="form-label">Message (optional)</label>
                            <textarea class="form-control" id="swapMessage" name="message" rows="3" placeholder="Add a message for the book owner..."></textarea>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="submitSwapRequest">
                                <span id="submitSwapSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="submitSwapText">Send Swap Request</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/swap.js"></script>
</body>
</html>