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

// Check user authentication and type
$userType = isset($_SESSION['usertype']) ? $_SESSION['usertype'] : 'guest';
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Pagination settings
$resultsPerPage = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $resultsPerPage;

// Filter and search parameters
$searchQuery = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$genre = isset($_GET['genre']) ? $conn->real_escape_string($_GET['genre']) : '';
$bookType = isset($_GET['book_type']) ? $conn->real_escape_string($_GET['book_type']) : '';
$sortBy = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'newest';

// Build dynamic query
$whereConditions = [];
if (!empty($searchQuery)) {
    $whereConditions[] = "(title LIKE '%$searchQuery%' OR author LIKE '%$searchQuery%' OR ISBN LIKE '%$searchQuery%')";
}
if (!empty($genre)) {
    $whereConditions[] = "genre = '$genre'";
}
if (!empty($bookType)) {
    $whereConditions[] = "book_type = '$bookType'";
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Sorting logic
$orderByClause = match($sortBy) {
    'price_asc' => 'ORDER BY price ASC',
    'price_desc' => 'ORDER BY price DESC',
    'popularity' => 'ORDER BY stock DESC',
    default => 'ORDER BY created_at DESC'
};

// Total results query
$totalQuery = "SELECT COUNT(*) as total FROM books $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $resultsPerPage);

// Main books query
$booksQuery = "SELECT * FROM books 
               $whereClause 
               $orderByClause 
               LIMIT $resultsPerPage OFFSET $offset";
$booksResult = $conn->query($booksQuery);

// Fetch genres for filter
$genreQuery = "SELECT DISTINCT genre FROM books ORDER BY genre";
$genreResult = $conn->query($genreQuery);

// Fetch book types for filter
$bookTypeQuery = "SELECT DISTINCT book_type FROM books ORDER BY book_type";
$bookTypeResult = $conn->query($bookTypeQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookwagon - Explore Books</title>
    <!-- Bootstrap and custom CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/tab.css">
    <style>
:root {
            --primary-color: #f8a100;
            --secondary-color: #f8a100;
            --background-light: #f8f9fc;
            --text-dark: #2c3e50;
        }

        body {
            background-color: var(--background-light);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .navbar-brand img {
            height: 50px;
        }
        
        .book-card {
            transition: all 0.3s ease;
            border: none;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .book-card .card-img-top {
            height: 350px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .book-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .book-card .overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.8);
            border-radius: 20px;
            padding: 5px 10px;
        }

        .filter-section {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .genre-tag {
            background-color: var(--secondary-color);
            color: white;
            padding: 5px 12px;
            margin: 5px;
            border-radius: 20px;
            display: inline-block;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .genre-tag:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
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

    <div class="container pt-4">
        <!-- Navigation tabs -->
        <div class="tab-menu mb-4">
            <a href="dashboard.php">Home</a>
            <a href="rentbooks.php">Rentbooks</a>
            <a href="explore.php" class="active">Explore</a>
            <a href="libraries.php">Libraries</a>
            <a href="bookswap.php">Bookswap</a>
        </div>

        <div class="row">
            <!-- Filters -->
            <div class="col-md-3">
                <div class="filter-section mb-4">
                    <h5 class="mb-3 d-flex align-items-center">
                        <i class="ri-filter-line me-2"></i> 
                        Filters
                    </h5>
                    <form method="get" action="">
                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center">
                                <i class="ri-search-line me-2"></i>
                                Search Books
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="ri-search-line"></i></span>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Title, Author, ISBN" 
                                       value="<?= htmlspecialchars($searchQuery) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center">
                                <i class="ri-book-open-line me-2"></i>
                                Genre
                            </label>
                            <select name="genre" class="form-select">
                                <option value="">All Genres</option>
                                <?php while($genreRow = $genreResult->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($genreRow['genre']) ?>" 
                                        <?= $genre == $genreRow['genre'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($genreRow['genre']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center">
                                <i class="ri-book-line me-2"></i>
                                Book Type
                            </label>
                            <select name="book_type" class="form-select">
                                <option value="">All Types</option>
                                <?php while($typeRow = $bookTypeResult->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($typeRow['book_type']) ?>" 
                                        <?= $bookType == $typeRow['book_type'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($typeRow['book_type']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center">
                                <i class="ri-sort-asc me-2"></i>
                                Sort By
                            </label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?= $sortBy == 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                                <option value="price_asc" <?= $sortBy == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_desc" <?= $sortBy == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="popularity" <?= $sortBy == 'popularity' ? 'selected' : '' ?>>Most Popular</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="ri-filter-2-line me-2"></i>
                            Apply Filters
                        </button>
                    </form>
                </div>

                <!-- Quick Genre Tags -->
                <div class="mb-4">
                    <h5 class="d-flex align-items-center">
                        <i class="ri-book-mark-line me-2"></i>
                        Quick Genres
                    </h5>
                    <?php 
                    // Reset genre result pointer
                    $genreResult->data_seek(0);
                    while($genreRow = $genreResult->fetch_assoc()): ?>
                        <a href="explore.php?genre=<?= urlencode($genreRow['genre']) ?>" 
                           class="genre-tag">
                            <?= htmlspecialchars($genreRow['genre']) ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Book Listings -->
            <div class="col-md-9">
                <div class="row">
                    <?php if($booksResult->num_rows > 0): ?>
                        <?php while($book = $booksResult->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card book-card">
                                    <div class="overlay">
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($book['book_type']) ?>
                                        </span>
                                    </div>
                                    <img src="<?= htmlspecialchars($book['cover_image'] ?: 'assets/default-book.jpg') ?>" 
                                         class="card-img-top" alt="<?= htmlspecialchars($book['title']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title mb-2"><?= htmlspecialchars($book['title']) ?></h5>
                                        <p class="card-text text-muted mb-3">
                                            By <?= htmlspecialchars($book['author']) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0 text-primary">
                                                ₱<?= number_format($book['price'], 2) ?>
                                            </span>
                                            <div>
                                                <a href="book_details.php?id=<?= $book['book_id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary me-2">
                                                    <i class="ri-eye-line"></i> View
                                                </a>
                                                <?php if($userType == 'user'): ?>
                                                    <button class="btn btn-sm btn-primary add-to-cart" 
                                                            data-book-id="<?= $book['book_id'] ?>">
                                                        <i class="ri-shopping-cart-line"></i> Cart
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="ri-book-open-line"></i>
                                <h4>No Books Found</h4>
                                <p class="text-center">Try adjusting your search or filters to find what you're looking for.</p>
                                <a href="explore.php" class="btn btn-primary">
                                    Reset Filters
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <nav aria-label="Book navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 
                                    . ($searchQuery ? "&search=" . urlencode($searchQuery) : '')
                                    . ($genre ? "&genre=" . urlencode($genre) : '')
                                    . ($bookType ? "&book_type=" . urlencode($bookType) : '')
                                    . ($sortBy ? "&sort=" . urlencode($sortBy) : '') 
                                ?>">
                                    <i class="ri-arrow-left-line"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php 
                        // Show first page
                        if($page > 3) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1' . 
                                ($searchQuery ? "&search=" . urlencode($searchQuery) : '') .
                                ($genre ? "&genre=" . urlencode($genre) : '') .
                                ($bookType ? "&book_type=" . urlencode($bookType) : '') .
                                ($sortBy ? "&sort=" . urlencode($sortBy) : '') . 
                                '">1</a></li>';
                            
                            if($page > 4) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        // Range of pages around current page
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        for($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i 
                                    . ($searchQuery ? "&search=" . urlencode($searchQuery) : '')
                                    . ($genre ? "&genre=" . urlencode($genre) : '')
                                    . ($bookType ? "&book_type=" . urlencode($bookType) : '')
                                    . ($sortBy ? "&sort=" . urlencode($sortBy) : '') 
                                ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php 
                        // Show last page
                        if($page < $totalPages - 2) {
                            if($page < $totalPages - 3) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . 
                                ($searchQuery ? "&search=" . urlencode($searchQuery) : '') .
                                ($genre ? "&genre=" . urlencode($genre) : '') .
                                ($bookType ? "&book_type=" . urlencode($bookType) : '') .
                                ($sortBy ? "&sort=" . urlencode($sortBy) : '') . 
                                '">' . $totalPages . '</a></li>';
                        }

                        if($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 
                                    . ($searchQuery ? "&search=" . urlencode($searchQuery) : '')
                                    . ($genre ? "&genre=" . urlencode($genre) : '')
                                    . ($bookType ? "&book_type=" . urlencode($bookType) : '')
                                    . ($sortBy ? "&sort=" . urlencode($sortBy) : '') 
                                ?>">
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Cart Interaction Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButtons = document.querySelectorAll('.add-to-cart');
            
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const bookId = this.getAttribute('data-book-id');
                    
                    fetch('ajax/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `book_id=${bookId}&quantity=1`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Book Added',
                                text: 'Book successfully added to your cart!',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message || 'Failed to add book to cart',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while adding to cart',
                        });
                    });
                });
            });
        });
    </script>

    <!-- Sweet Alert 2 for better notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>