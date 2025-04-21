<?php
include("connect.php");

// Build the WHERE clause based on filters (same as in get_books.php)
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

if(isset($_GET['search']) && $_GET['search'] != '') {
    $where_clauses[] = "(title LIKE ? OR author LIKE ? OR description LIKE ?)";
    $search_term = '%' . $_GET['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM books";
if(!empty($where_clauses)) {
    $count_query .= " WHERE " . implode(" AND ", $where_clauses);
}

$conn = new mysqli("localhost", "root", "", "bookwagon_db"); // Replace with your actual connection details
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare($count_query);
if(!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$count_result = $stmt->get_result();
$row = $count_result->fetch_assoc();
$total_books = $row['total'];

// Pagination
$books_per_page = 9;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_pages = ceil($total_books / $books_per_page);

// Generate pagination HTML only if we have more than 1 page
if($total_pages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php
            // Previous page
            if($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($current_page - 1); ?><?php 
                        // Add other params
                        foreach($_GET as $key => $value) {
                            if($key != 'page') echo '&' . $key . '=' . urlencode($value);
                        }
                    ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php
            // Page numbers
            for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php 
                        // Add other params
                        foreach($_GET as $key => $value) {
                            if($key != 'page') echo '&' . $key . '=' . urlencode($value);
                        }
                    ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <?php
            // Next page
            if($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($current_page + 1); ?><?php 
                        // Add other params
                        foreach($_GET as $key => $value) {
                            if($key != 'page') echo '&' . $key . '=' . urlencode($value);
                        }
                    ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif;

$conn->close();
?>