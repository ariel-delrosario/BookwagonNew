<?php
include("session.php");
include("connect.php");

// Build the WHERE clause based on filters
$where_clauses = array();
$params = array();

if(isset($_GET['min_price']) && $_GET['min_price'] != '') {
    $where_clauses[] = "price >= ?";
    $params[] = $_GET['min_price'];
}

if(isset($_GET['max_price']) && $_GET['max_price'] != '') {
    $where_clauses[] = "price <= ?";
    $params[] = $_GET['max_price'];
}

if(isset($_GET['popularity']) && $_GET['popularity'] != '') {
    $where_clauses[] = "popularity = ?";
    $params[] = $_GET['popularity'];
}

if(isset($_GET['genre']) && $_GET['genre'] != '') {
    $where_clauses[] = "genre = ?";
    $params[] = $_GET['genre'];
}

if(isset($_GET['author']) && $_GET['author'] != '') {
    $where_clauses[] = "author = ?";
    $params[] = $_GET['author'];
}

if(isset($_GET['theme']) && $_GET['theme'] != '') {
    $where_clauses[] = "theme = ?";
    $params[] = $_GET['theme'];
}

// Add new filters for book type and condition
if(isset($_GET['book_type']) && $_GET['book_type'] != '') {
    $where_clauses[] = "book_type = ?";
    $params[] = $_GET['book_type'];
}

if(isset($_GET['condition']) && $_GET['condition'] != '') {
    $where_clauses[] = "`condition` = ?";
    $params[] = $_GET['condition'];
}

if(isset($_GET['search']) && $_GET['search'] != '') {
    $where_clauses[] = "(title LIKE ? OR author LIKE ? OR description LIKE ?)";
    $search_term = '%' . $_GET['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Default query
$query = "SELECT * FROM books";

// Add WHERE if we have conditions
if(!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add ORDER BY
if(isset($_GET['sort'])) {
    switch($_GET['sort']) {
        case 'price_asc':
            $query .= " ORDER BY price ASC";
            break;
        case 'price_desc':
            $query .= " ORDER BY price DESC";
            break;
        case 'newest':
            $query .= " ORDER BY created_at DESC";
            break;
        default:
            $query .= " ORDER BY title ASC";
    }
} else {
    // Default sorting
    $query .= " ORDER BY title ASC";
}

// Pagination
$books_per_page = 9;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $books_per_page;

// Add LIMIT for pagination
$query .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $books_per_page;

// Prepare and execute the query
$conn = new mysqli("localhost", "root", "", "bookwagon_db"); // Replace with your actual connection details
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare($query);
if(!empty($params)) {
    $types = str_repeat('s', count($params) - 2) . 'ii'; // All string params except the last two which are integers
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Add custom CSS for book images
echo '<style>
    .book-img-container {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        background-color: #f8f9fa;
    }
    .book-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.3s;
    }
    .book-card:hover .book-img {
        transform: scale(1.05);
    }
    .card-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }
    .book-link {
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }
    .book-link:hover {
        text-decoration: none;
        color: inherit;
    }
    .book-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 10;
    }
</style>';

// Display books
if ($result->num_rows > 0) {
    while($book = $result->fetch_assoc()) {
        // Generate random rating for demo (1-5)
        $rating = rand(3, 5);
        // Use rent_price from database or default to 0 if not set
        $rent_price = isset($book['rent_price']) ? $book['rent_price'] : 0;
        
        // Handle cover image path correctly
        if (!empty($book['cover_image'])) {
            // Check if the path already contains 'uploads/covers/'
            if (strpos($book['cover_image'], 'uploads/covers/') === 0) {
                $cover_image = $book['cover_image'];
            } else {
                $cover_image = 'uploads/covers/' . $book['cover_image'];
            }
        } else {
            $cover_image = 'uploads/covers/default_book.jpg';
        }
        
        // Get condition class for badge
        $conditionClass = 'bg-success';
        switch($book['condition'] ?? 'New') {
            case 'New':
                $conditionClass = 'bg-success';
                break;
            case 'Like New':
                $conditionClass = 'bg-success';
                break;
            case 'Very Good':
            case 'Good':
                $conditionClass = 'bg-info';
                break;
            case 'Fair':
                $conditionClass = 'bg-warning';
                break;
            case 'Poor':
                $conditionClass = 'bg-danger';
                break;
        }
        
        // Display the book with link to book_details.php
        echo '
        <div class="col">
            <div class="card h-100 book-card">
                <a href="book_details.php?id=' . $book['book_id'] . '" class="book-link">
                    <div class="book-img-container">
                        <img src="' . $cover_image . '" 
                            class="book-img" 
                            alt="' . $book['title'] . '" 
                            loading="lazy"
                            onerror="this.src=\'uploads/covers/default_book.jpg\'">
                            
                        <!-- Book Type & Condition Badges -->
                        <div class="book-badge">
                            <span class="badge bg-primary mb-1 d-block">' . htmlspecialchars($book['book_type'] ?? 'Paperback') . '</span>
                            <span class="badge ' . $conditionClass . ' d-block">' . htmlspecialchars($book['condition'] ?? 'New') . '</span>
                        </div>

                        <div class="card-actions">
                            <div class="action-icon">
                                <i class="far fa-heart" data-book-id="' . $book['book_id'] . '"></i>
                            </div>
                            <div class="action-icon">
                                <i class="far fa-bookmark" data-book-id="' . $book['book_id'] . '"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted mb-1">' . $book['author'] . '</h6>
                        <h5 class="card-title">' . $book['title'] . '</h5>
                        <div class="book-rating mb-2">';
                        
                        // Generate star rating
                        for($i = 1; $i <= 5; $i++) {
                            if($i <= $rating) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="fas fa-star star-gray"></i>';
                            }
                        }
                        
                        echo '</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="book-price">₱' . number_format($book['price'], 2) . '</span>
                                <div class="price-per-week">₱' . number_format($rent_price, 2) . '/week</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>';
    }
} else {
    echo '<div class="col-12 text-center py-5">
            <h4>No books found matching your criteria</h4>
            <p>Try adjusting your filters or search term</p>
          </div>';
}

$conn->close();
?>