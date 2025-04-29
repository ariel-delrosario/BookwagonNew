<?php
include("session.php");
include("connect.php");

// Direct upload function - no helper file needed
function direct_upload_image($file, $upload_dir = 'uploads/covers/') {
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . basename($file['name']);
    $targetPath = $upload_dir . $filename;
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    }
    
    return false;
}

$userType = $_SESSION['usertype'] ?? '';
$userId = $_SESSION['id'] ?? 0;

// Ensure only sellers can access this page
if ($userType !== 'seller') {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which action is being performed
    if (isset($_POST['action'])) {
        
        // Add new book
        if ($_POST['action'] === 'add') {
            // Prepare and sanitize the data
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $author = mysqli_real_escape_string($conn, $_POST['author']);
            $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
            $genre = mysqli_real_escape_string($conn, $_POST['genre']);
            $theme = mysqli_real_escape_string($conn, $_POST['theme']);
            // Add new fields
            $book_type = mysqli_real_escape_string($conn, $_POST['book_type']);
            $condition = mysqli_real_escape_string($conn, $_POST['condition']);
            $damages = mysqli_real_escape_string($conn, $_POST['damages']);
            $popularity = mysqli_real_escape_string($conn, $_POST['popularity']);
            $price = floatval($_POST['price']);
            $rent_price = floatval($_POST['rent_price']);
            $stock = intval($_POST['stock']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            
            // Handle image upload
            $cover_image = '';
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $uploaded_image = direct_upload_image($_FILES['cover_image']);
                if ($uploaded_image) {
                    $cover_image = $uploaded_image; // Assign the uploaded file path to $cover_image
                } else {
                    $error_message = "Error uploading image. Please try again.";
                }
            }
            
            // Insert book into database with new fields
            $query = "INSERT INTO books (user_id, title, author, ISBN, genre, theme, book_type, `condition`, damages, popularity, price, rent_price, stock, description, cover_image) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Add this before your INSERT query
            error_log("Cover image path before DB insert: " . $cover_image);

            $stmt = $conn->prepare($query);
            $stmt->bind_param("isssssssssddiis", $userId, $title, $author, $isbn, $genre, $theme, $book_type, $condition, $damages, $popularity, $price, $rent_price, $stock, $description, $cover_image);
            if ($stmt->execute()) {
                $success_message = "Book added successfully!";
                // Redirect to prevent form resubmission on refresh
                header("Location: manage_books.php?success=add");
                exit();
            }

            $stmt->close();
        }
        
        // Edit existing book
        elseif ($_POST['action'] === 'edit' && isset($_POST['book_id'])) {
            $book_id = intval($_POST['book_id']);
            
            // First check if this book belongs to the current user
            $check_query = "SELECT user_id, cover_image FROM books WHERE book_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("i", $book_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 1) {
                $book_data = $check_result->fetch_assoc();
                
                // Verify ownership
                if ($book_data['user_id'] == $userId) {
                    // Prepare and sanitize the data
                    $title = mysqli_real_escape_string($conn, $_POST['title']);
                    $author = mysqli_real_escape_string($conn, $_POST['author']);
                    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
                    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
                    $theme = mysqli_real_escape_string($conn, $_POST['theme']);
                    // Add new fields
                    $book_type = mysqli_real_escape_string($conn, $_POST['book_type']);
                    $condition = mysqli_real_escape_string($conn, $_POST['condition']);
                    $damages = mysqli_real_escape_string($conn, $_POST['damages']);
                    $popularity = mysqli_real_escape_string($conn, $_POST['popularity']);
                    $price = floatval($_POST['price']);
                    $rent_price = floatval($_POST['rent_price']);
                    $stock = intval($_POST['stock']);
                    $description = mysqli_real_escape_string($conn, $_POST['description']);
                    
                    // Simplified file upload approach for editing
                    $cover_image_query = "";
                    $cover_image = $book_data['cover_image']; // Keep existing cover by default
                    
                    if (!empty($_FILES['cover_image']['name'])) {
                        $new_cover = direct_upload_image($_FILES['cover_image']);
                        if (!empty($new_cover)) {
                            $cover_image = $new_cover;
                            $cover_image_query = ", cover_image = ?";
                        }
                    }
                    
                    // Update book in database
                    if (!empty($cover_image_query)) {
                        $query = "UPDATE books SET title = ?, author = ?, ISBN = ?, genre = ?, theme = ?, 
                                  book_type = ?, `condition` = ?, damages = ?, popularity = ?, 
                                  price = ?, rent_price = ?, stock = ?, description = ?, cover_image = ? 
                                  WHERE book_id = ? AND user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("sssssssssddissii", $title, $author, $isbn, $genre, $theme, 
                                          $book_type, $condition, $damages, $popularity, 
                                          $price, $rent_price, $stock, $description, $cover_image, $book_id, $userId);
                    } else {
                        $query = "UPDATE books SET title = ?, author = ?, ISBN = ?, genre = ?, theme = ?, 
                                  book_type = ?, `condition` = ?, damages = ?, popularity = ?, 
                                  price = ?, rent_price = ?, stock = ?, description = ? 
                                  WHERE book_id = ? AND user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("sssssssssddisii", $title, $author, $isbn, $genre, $theme, 
                                         $book_type, $condition, $damages, $popularity, 
                                         $price, $rent_price, $stock, $description, $book_id, $userId);
                    }
                    
                    if ($stmt->execute()) {
                        $success_message = "Book updated successfully!";
                    } else {
                        $error_message = "Error updating book: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    $error_message = "You don't have permission to edit this book.";
                }
            } else {
                $error_message = "Book not found.";
            }
            
            $check_stmt->close();
        }
        
        // Delete book
        elseif ($_POST['action'] === 'delete' && isset($_POST['book_id'])) {
            $book_id = intval($_POST['book_id']);
            
            // Delete the book if it belongs to the current user
            $query = "DELETE FROM books WHERE book_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $book_id, $userId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success_message = "Book deleted successfully!";
                } else {
                    $error_message = "You don't have permission to delete this book or the book doesn't exist.";
                }
            } else {
                $error_message = "Error deleting book: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

// Fetch books owned by this user
$query = "SELECT * FROM books WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch genre list for dropdown
$genre_query = "SELECT DISTINCT genre FROM books";
$genre_result = $conn->query($genre_query);
$genres = [];
while ($row = $genre_result->fetch_assoc()) {
    if (!empty($row['genre'])) {
        $genres[] = $row['genre'];
    }
}

// Fetch theme list for dropdown
$theme_query = "SELECT DISTINCT theme FROM books";
$theme_result = $conn->query($theme_query);
$themes = [];
while ($row = $theme_result->fetch_assoc()) {
    if (!empty($row['theme'])) {
        $themes[] = $row['theme'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - BookWagon</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <style>
 :root {
    --primary-color: #6366f1;
    --primary-light: #818cf8;
    --primary-dark: #4f46e5;
    --secondary-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
    --sidebar-width: 260px;
    --topbar-height: 70px;
    --card-border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
        
body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: #374151;
        }
        
        .sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary-color) 100%);
    box-shadow: var(--shadow-lg);
    padding-top: var(--topbar-height);
    z-index: 1000;
    transition: all 0.3s ease;
}

.sidebar-logo {
    height: var(--topbar-height);
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    background-color: rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    padding: 12px 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
    margin-bottom: 5px;
    border-radius: 0 50px 50px 0;
}

.sidebar-menu li.active, 
.sidebar-menu li:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    padding-left: 30px;
}

.sidebar-menu li i {
    width: 24px;
    text-align: center;
}

.sidebar-menu a {
    color: inherit;
    text-decoration: none;
    font-weight: 500;
}

/* Main content and topbar */
.main-content {
    margin-left: var(--sidebar-width);
    padding: 20px;
    min-height: 100vh;
}

.topbar {
    height: var(--topbar-height);
    background-color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
    box-shadow: var(--shadow-sm);
    position: fixed;
    top: 0;
    left: var(--sidebar-width);
    right: 0;
    z-index: 999;
}

.search-bar {
    position: relative;
    flex: 1;
    max-width: 400px;
    margin-right: 20px;
}

.search-bar input {
    width: 100%;
    padding: 10px 20px;
    padding-left: 40px;
    border-radius: 50px;
    border: 1px solid #e5e7eb;
    background-color: #f9fafb;
    transition: all 0.3s ease;
}

.search-bar input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    outline: none;
}

.search-bar i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.topbar-icons {
    display: flex;
    align-items: center;
    gap: 20px;
}

.icon-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f9fafb;
    color: #4b5563;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.icon-btn:hover {
    background-color: #f3f4f6;
    color: var(--primary-color);
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background-color: var(--primary-light);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
}

.user-role {
    font-size: 12px;
    color: #6b7280;
}

.content-wrapper {
    margin-top: calc(var(--topbar-height) + 20px);
    padding: 10px;
}

/* Responsive tweaks */
@media (max-width: 991px) {
    :root {
        --sidebar-width: 70px;
    }
    
    .sidebar {
        overflow: hidden;
    }
    
    .sidebar-menu li span,
    .sidebar-logo span {
        display: none;
    }
    
    .sidebar-menu li {
        justify-content: center;
        padding: 12px;
    }
    
    .sidebar-menu li.active, 
    .sidebar-menu li:hover {
        padding-left: 12px;
    }
    
    .sidebar-menu li i {
        margin-right: 0;
        font-size: 20px;
    }
}

@media (max-width: 767px) {
    .topbar {
        padding: 0 15px;
    }
    
    .search-bar {
        max-width: 200px;
    }
    
    .user-info {
        display: none;
    }
}
.content-wrapper {
    margin-top: calc(var(--topbar-height) + 20px);
    padding: 10px;
}

/* Responsive tweaks */
@media (max-width: 991px) {
    :root {
        --sidebar-width: 70px;
    }
    
    .sidebar {
        overflow: hidden;
    }
    
    .sidebar-menu li span,
    .sidebar-logo span {
        display: none;
    }
    
    .sidebar-menu li {
        justify-content: center;
        padding: 12px;
    }
    
    .sidebar-menu li.active, 
    .sidebar-menu li:hover {
        padding-left: 12px;
    }
    
    .sidebar-menu li i {
        margin-right: 0;
        font-size: 20px;
    }
}

@media (max-width: 767px) {
    .topbar {
        padding: 0 15px;
    }
    
    .search-bar {
        max-width: 200px;
    }
    
    .user-info {
        display: none;
    }
}
        .content-wrapper {
            margin-top: var(--topbar-height);
            padding: 20px;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .table-responsive {
            background-color: white;
        }
        
        .table thead {
            background-color: #f8f9fa;
        }
        
        .action-dropdown {
            cursor: pointer;
        }
        
        .book-thumbnail {
            width: 50px;
            height: 70px;
            object-fit: cover;
            margin-right: 10px;
        }
        
        /* Modal styles */
        .modal-content {
            border-radius: 8px;
            border: none;
        }
        
        .modal-header {
            background-color: #f8f9fa;
        }
        
        /* Grid view styles */
        .book-grid {
            display: flex;
            flex-wrap: wrap;
        }
        
        .object-fit-cover {
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->

    <div class="sidebar">
    <div class="sidebar-logo">
        <img src="images/logo.png" alt="BookWagon" height="40">
        <span class="ms-2 text-white fw-bold fs-5">BookWagon</span>
    </div>
    <ul class="sidebar-menu">
        <li>
            <i class="fas fa-th-large"></i>
            <span><a href="dashboard.php" class="text-decoration-none text-inherit">Dashboard</a></span>
        </li>
        <li class="active">
            <i class="fas fa-book"></i>
            <span>Manage Books</span>
        </li>
        <li>
            <i class="fas fa-shopping-cart"></i>
            <span><a href="order.php" class="text-decoration-none text-inherit">Orders</a></span>
        </li>
        <li>
            <i class="fas fa-exchange-alt"></i>
            <span><a href="rentals.php" class="text-decoration-none text-inherit">Rentals</a></span>
        </li>
        <li>
            <i class="fas fa-undo-alt"></i>
            <span><a href="rental_request.php" class="text-decoration-none text-inherit">Return Requests</a></span>
        </li>
        <li>
            <i class="fas fa-user-friends"></i>
            <span><a href="renter.php" class="text-decoration-none text-inherit">Customers</a></span>
        </li>
        <li>
            <i class="fas fa-chart-line"></i>
            <span><a href="reports.php" class="text-decoration-none text-inherit">Reports</a></span>
        </li>
        <li>
            <i class="fas fa-cog"></i>
            <span><a href="settings.php" class="text-decoration-none text-inherit">Settings</a></span>
        </li>
    </ul>
</div>

    <!-- Topbar -->
    <div class="topbar">
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search returns..." id="returnSearch">
    </div>
    <div class="topbar-icons">
        <a href="dashboard.php" class="nav-link" title="Home">Home</a>
        <button class="icon-btn" title="Notifications">
            <i class="fas fa-bell"></i>
        </button>
        <button class="icon-btn" title="Messages">
            <i class="fas fa-envelope"></i>
        </button>
        <div class="user-profile">
            <div class="avatar">
                <?php echo substr(isset($_SESSION['firstname']) ? $_SESSION['firstname'] : $_SESSION['email'], 0, 1); ?>
            </div>
            <div class="user-info">
                <div class="user-name">
                    <?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] . ' ' . ($_SESSION['lastname'] ?? '') : $_SESSION['email']; ?>
                </div>
                <div class="user-role">Seller</div>
            </div>
        </div>
    </div>
</div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <div class="container-fluid">
                <!-- Alert Messages -->
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-12">
                        <h2 class="mb-4">Manage Books</h2>
                        
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Book List</h5>
                                <div class="d-flex">
                                    <div class="btn-group me-3">
                                        <button class="btn btn-outline-secondary active" id="tableViewBtn">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" id="gridViewBtn">
                                            <i class="fas fa-th-large"></i>
                                        </button>
                                    </div>
                                    <div class="input-group me-3" style="width: 300px;">
                                        <input type="text" class="form-control" id="searchBooks" placeholder="Search books...">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                                        <i class="fas fa-plus me-2"></i>Add New Book
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                                </th>
                                                <th>Book</th>
                                                <th>ISBN</th>
                                                <th>Genre</th>
                                                <th>Type</th>
                                                <th>Condition</th>
                                                <th>Price</th>
                                                <th>Rent Price</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($books)): ?>
                                            <tr>
                                                <td colspan="11" class="text-center py-5">
                                                    <p>No books found. Add some books to get started.</p>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($books as $book): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input book-select" value="<?php echo $book['book_id']; ?>">
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?php echo !empty($book['cover_image']) ? $book['cover_image'] : 'img/default-book-cover.jpg'; ?>" 
                                                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                                                 class="book-thumbnail">
                                                            <div>
                                                                <div><?php echo htmlspecialchars($book['title']); ?></div>
                                                                <small class="text-muted">By <?php echo htmlspecialchars($book['author']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($book['ISBN'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($book['genre']); ?></td>
                                                    <!-- Display Book Type -->
                                                    <td><?php echo htmlspecialchars($book['book_type'] ?? 'Paperback'); ?></td>
                                                    <!-- Display Condition with tooltip for damages -->
                                                    <td>
                                                        <?php if (!empty($book['damages'])): ?>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($book['damages']); ?>">
                                                            <?php echo htmlspecialchars($book['condition'] ?? 'New'); ?> <i class="fas fa-info-circle text-warning"></i>
                                                        </span>
                                                        <?php else: ?>
                                                            <?php echo htmlspecialchars($book['condition'] ?? 'New'); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>₱<?php echo number_format($book['price'], 2); ?></td>
                                                    <td>₱<?php echo number_format($book['rent_price'] ?? 0, 2); ?></td>
                                                    <td>
                                                        <?php if ($book['stock'] <= 5): ?>
                                                            <span class="text-danger"><?php echo $book['stock']; ?></span>
                                                        <?php else: ?>
                                                            <?php echo $book['stock']; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($book['stock'] > 0): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Out of Stock</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <a href="#" class="action-dropdown" data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item edit-book" href="#" 
                                                                       data-bs-toggle="modal" 
                                                                       data-bs-target="#editBookModal" 
                                                                       data-book-id="<?php echo $book['book_id']; ?>">
                                                                        <i class="fas fa-edit me-2"></i>Edit
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item delete-book" href="#" 
                                                                       data-bs-toggle="modal" 
                                                                       data-bs-target="#deleteBookModal" 
                                                                       data-book-id="<?php echo $book['book_id']; ?>"
                                                                       data-book-title="<?php echo htmlspecialchars($book['title']); ?>">
                                                                        <i class="fas fa-trash me-2"></i>Delete
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Book Grid View (Alternative to Table View) -->
                                <div class="row book-grid" id="bookGridView" style="display: none;">
                                    <?php if (empty($books)): ?>
                                    <div class="col-12 text-center py-5">
                                        <p>No books found. Add some books to get started.</p>
                                    </div>
                                    <?php else: ?>
                                        <?php foreach ($books as $book): ?>
                                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                                            <div class="card h-100">
                                                <div class="card-img-top position-relative" style="height: 200px; overflow: hidden;">
                                                    <img src="<?php echo !empty($book['cover_image']) ? $book['cover_image'] : 'img/default-book-cover.jpg'; ?>" 
                                                         alt="<?php echo htmlspecialchars($book['title']); ?>"
                                                         class="w-100 h-100 object-fit-cover">
                                                    
                                                    <!-- Book Type Badge -->
                                                    <span class="position-absolute top-0 start-0 badge bg-info m-2">
                                                        <?php echo htmlspecialchars($book['book_type'] ?? 'Paperback'); ?>
                                                    </span>
                                                    
                                                    <!-- Condition Badge -->
                                                    <span class="position-absolute top-0 end-0 badge 
                                                        <?php 
                                                        $conditionClass = 'bg-success';
                                                        switch($book['condition'] ?? 'New') {
                                                            case 'New':
                                                                $conditionClass = 'bg-success';
                                                                break;
                                                            case 'Like New':
                                                                $conditionClass = 'bg-success';
                                                                break;
                                                            case 'Very Good':
                                                                $conditionClass = 'bg-info';
                                                                break;
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
                                                        echo $conditionClass;
                                                        ?> m-2">
                                                        <?php echo htmlspecialchars($book['condition'] ?? 'New'); ?>
                                                    </span>
                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title text-truncate"><?php echo htmlspecialchars($book['title']); ?></h5>
                                                    <p class="card-text text-muted mb-1">By <?php echo htmlspecialchars($book['author']); ?></p>
                                                    <p class="card-text mb-1">
                                                        <small class="text-muted">ISBN: <?php echo htmlspecialchars($book['ISBN'] ?? 'N/A'); ?></small>
                                                    </p>
                                                    <p class="card-text mb-1">
                                                        <small class="text-muted">Genre: <?php echo htmlspecialchars($book['genre']); ?></small>
                                                    </p>
                                                    
                                                    <!-- Damages Note (if any) -->
                                                    <?php if (!empty($book['damages'])): ?>
                                                    <p class="card-text mb-1">
                                                        <small class="text-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> 
                                                            <?php echo htmlspecialchars(mb_strimwidth($book['damages'], 0, 30, "...")); ?>
                                                        </small>
                                                    </p>
                                                    <?php endif; ?>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                        <div>
                                                            <p class="mb-0 fw-bold">₱<?php echo number_format($book['price'], 2); ?></p>
                                                            <small class="text-muted">Rent: ₱<?php echo number_format($book['rent_price'] ?? 0, 2); ?>/wk</small>
                                                        </div>
                                                        <div>
                                                            <span class="badge <?php echo $book['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                                <?php echo $book['stock'] > 0 ? 'In Stock: ' . $book['stock'] : 'Out of Stock'; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-white border-0">
                                                    <div class="d-flex justify-content-end">
                                                        <button class="btn btn-sm btn-outline-primary me-2 edit-book" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editBookModal" 
                                                                data-book-id="<?php echo $book['book_id']; ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger delete-book" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteBookModal" 
                                                                data-book-id="<?php echo $book['book_id']; ?>"
                                                                data-book-title="<?php echo htmlspecialchars($book['title']); ?>">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
                    <h5 class="modal-title" id="addBookModalLabel">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="manage_books.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="author" class="form-label">Author</label>
                                    <input type="text" class="form-control" id="author" name="author" required>
                                </div>
                                <div class="mb-3">
                                    <label for="isbn" class="form-label">ISBN</label>
                                    <input type="text" class="form-control" id="isbn" name="isbn" required>
                                </div>
                                <div class="mb-3">
                                    <label for="genre" class="form-label">Genre</label>
                                    <select class="form-select" id="genre" name="genre" required onchange="updateThemeOptions('genre', 'theme')">
                                        <option value="">Select Genre</option>
                                        <option value="Fiction">Fiction</option>
                                        <option value="Non-Fiction">Non-Fiction</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="theme" class="form-label">Theme</label>
                                    <select class="form-select" id="theme" name="theme" required disabled>
                                        <option value="">Select Genre First</option>
                                    </select>
                                </div>
                                <!-- New Field: Book Type -->
                                <div class="mb-3">
                                    <label for="book_type" class="form-label">Book Type</label>
                                    <select class="form-select" id="book_type" name="book_type" required>
                                        <option value="Paperback">Paperback</option>
                                        <option value="Hardcover">Hardcover</option>
                                        <option value="E-book">E-book</option>
                                        <option value="Audiobook">Audiobook</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- New Field: Condition -->
                                <div class="mb-3">
                                    <label for="condition" class="form-label">Condition</label>
                                    <select class="form-select" id="condition" name="condition" required>
                                        <option value="New">New</option>
                                        <option value="Like New">Like New</option>
                                        <option value="Very Good">Very Good</option>
                                        <option value="Good">Good</option>
                                        <option value="Fair">Fair</option>
                                        <option value="Poor">Poor</option>
                                    </select>
                                </div>
                                <!-- New Field: Damages -->
                                <div class="mb-3">
                                    <label for="damages" class="form-label">Damages (if any)</label>
                                    <textarea class="form-control" id="damages" name="damages" rows="2" placeholder="Describe any damages or defects..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (Whole book) (₱)</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="rent_price" class="form-label">Rent Price (Per week) (₱)</label>
                                    <input type="number" class="form-control" id="rent_price" name="rent_price" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Cover Image</label>
                                    <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                                    <div class="form-text">Recommended size: 400x600 pixels</div>
                                </div>
                                <!-- Popularity is hidden and will be set by the system -->
                                <input type="hidden" name="popularity" value="New Releases">
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Book Modal -->
    <div class="modal fade" id="editBookModal" tabindex="-1" aria-labelledby="editBookModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBookModalLabel">Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBookForm" action="manage_books.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="book_id" id="edit_book_id">
                    <div class="modal-body">
                        <!-- Form fields will be populated via JavaScript -->
                        <div class="spinner-border text-primary" role="status" id="editBookLoader">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div id="editBookContent" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="edit_title" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_author" class="form-label">Author</label>
                                        <input type="text" class="form-control" id="edit_author" name="author" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="edit_isbn" name="isbn" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_genre" class="form-label">Genre</label>
                                        <input type="text" class="form-control" id="edit_genre" name="genre" list="genreList" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_theme" class="form-label">Theme</label>
                                        <input type="text" class="form-control" id="edit_theme" name="theme" list="themeList" required>
                                    </div>
                                    <!-- New Field: Book Type -->
                                    <div class="mb-3">
                                        <label for="edit_book_type" class="form-label">Book Type</label>
                                        <select class="form-select" id="edit_book_type" name="book_type" required>
                                            <option value="Paperback">Paperback</option>
                                            <option value="Hardcover">Hardcover</option>
                                            <option value="E-book">E-book</option>
                                            <option value="Audiobook">Audiobook</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- New Field: Condition -->
                                    <div class="mb-3">
                                        <label for="edit_condition" class="form-label">Condition</label>
                                        <select class="form-select" id="edit_condition" name="condition" required>
                                            <option value="New">New</option>
                                            <option value="Like New">Like New</option>
                                            <option value="Very Good">Very Good</option>
                                            <option value="Good">Good</option>
                                            <option value="Fair">Fair</option>
                                            <option value="Poor">Poor</option>
                                        </select>
                                    </div>
                                    <!-- New Field: Damages -->
                                    <div class="mb-3">
                                        <label for="edit_damages" class="form-label">Damages (if any)</label>
                                        <textarea class="form-control" id="edit_damages" name="damages" rows="2" placeholder="Describe any damages or defects..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_price" class="form-label">Price (₱)</label>
                                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_rent_price" class="form-label">Rent Price (₱)</label>
                                        <input type="number" class="form-control" id="edit_rent_price" name="rent_price" step="0.01" min="0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_stock" class="form-label">Stock</label>
                                        <input type="number" class="form-control" id="edit_stock" name="stock" min="0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_cover_image" class="form-label">Cover Image</label>
                                        <input type="file" class="form-control" id="edit_cover_image" name="cover_image" accept="image/*">
                                        <div class="form-text">Leave empty to keep current image</div>
                                        <div id="current_cover_preview" class="mt-2"></div>
                                    </div>
                                    <!-- Popularity is hidden and will maintain its current value -->
                                    <input type="hidden" name="popularity" id="edit_popularity">
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="edit_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Book Modal -->
    <div class="modal fade" id="deleteBookModal" tabindex="-1" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBookModalLabel">Delete Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete "<span id="delete-book-title"></span>"? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form action="manage_books.php" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="book_id" id="delete_book_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Datalists for edit form -->
    <datalist id="genreList">
        <?php foreach ($genres as $genre): ?>
            <option value="<?php echo htmlspecialchars($genre); ?>">
        <?php endforeach; ?>
    </datalist>
    
    <datalist id="themeList">
        <?php foreach ($themes as $theme): ?>
            <option value="<?php echo htmlspecialchars($theme); ?>">
        <?php endforeach; ?>
    </datalist>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Fiction and Non-Fiction theme options
        const themeOptions = {
            "Fiction": [
                "Fantasy", 
                "Science Fiction", 
                "Mystery", 
                "Thriller", 
                "Suspense", 
                "Romance", 
                "Historical Fiction", 
                "Horror", 
                "Literary Fiction", 
                "Adventure", 
                "Dystopian", 
                "Paranormal", 
                "Magical Realism", 
                "Urban Fantasy", 
                "Contemporary Fiction", 
                "Crime Fiction", 
                "Drama", 
                "Western", 
                "Satire", 
                "Gothic Fiction"
            ],
            "Non-Fiction": [
                "Biography",
                "Autobiography",
                "Memoir",
                "Self-Help",
                "True Crime",
                "History",
                "Science",
                "Nature",
                "Travel",
                "Philosophy",
                "Psychology",
                "Religion/Spirituality",
                "Politics",
                "Essays",
                "Journalism",
                "Art",
                "Music",
                "Business",
                "Health & Wellness",
                "Cookbooks",
                "Education",
                "Parenting",
                "Sports",
                "Technology",
                "Finance"
            ]
        };
        
        // Function to update theme dropdown based on genre selection
        function updateThemeOptions(genreId, themeId) {
            const genreSelect = document.getElementById(genreId);
            const themeSelect = document.getElementById(themeId);
            
            // Clear current options
            themeSelect.innerHTML = '';
            
            const selectedGenre = genreSelect.value;
            
            if (selectedGenre === '') {
                // If no genre selected, disable theme dropdown
                themeSelect.disabled = true;
                themeSelect.innerHTML = '<option value="">Select Genre First</option>';
                return;
            }
            
            // Enable theme dropdown
            themeSelect.disabled = false;
            
            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select Theme';
            themeSelect.appendChild(defaultOption);
            
            // Add options based on selected genre
            const options = themeOptions[selectedGenre] || [];
            options.forEach(theme => {
                const option = document.createElement('option');
                option.value = theme;
                option.textContent = theme;
                themeSelect.appendChild(option);
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips for damage info icons
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Show/hide damages field based on condition selection
            const conditionSelect = document.getElementById('condition');
            const damagesField = document.getElementById('damages').closest('.mb-3');
            
            if (conditionSelect && damagesField) {
                // Initial state check
                updateDamagesVisibility(conditionSelect.value);
                
                // Listen for changes
                conditionSelect.addEventListener('change', function() {
                    updateDamagesVisibility(this.value);
                });
            }
            
            // Same for edit modal
            const editConditionSelect = document.getElementById('edit_condition');
            const editDamagesField = document.getElementById('edit_damages').closest('.mb-3');
            
            if (editConditionSelect && editDamagesField) {
                editConditionSelect.addEventListener('change', function() {
                    updateDamagesVisibility(this.value, true);
                });
            }
            
            // Function to show/hide damages field based on condition
            function updateDamagesVisibility(condition, isEdit = false) {
                const damagesField = isEdit 
                    ? document.getElementById('edit_damages').closest('.mb-3')
                    : document.getElementById('damages').closest('.mb-3');
                
                // Show damages field only for non-new conditions
                if (condition === 'New') {
                    damagesField.style.display = 'none';
                    if (isEdit) {
                        document.getElementById('edit_damages').value = '';
                    } else {
                        document.getElementById('damages').value = '';
                    }
                } else {
                    damagesField.style.display = 'block';
                }
            }
            
            // Edit Book Modal
            const editBookModal = document.getElementById('editBookModal');
            const editBookLoader = document.getElementById('editBookLoader');
            const editBookContent = document.getElementById('editBookContent');
            
            // Handle Edit Book button clicks
            editBookModal.addEventListener('show.bs.modal', function(event) {
                // Button that triggered the modal
                const button = event.relatedTarget;
                // Extract book ID
                const bookId = button.getAttribute('data-book-id');
                
                // Set the book ID in the form
                document.getElementById('edit_book_id').value = bookId;
                
                // Show loader
                editBookLoader.style.display = 'block';
                editBookContent.style.display = 'none';
                
                // Fetch book details via AJAX
                fetch('get_book.php?id=' + bookId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const book = data.book;
                            
                            // Populate form fields
                            document.getElementById('edit_title').value = book.title;
                            document.getElementById('edit_author').value = book.author;
                            document.getElementById('edit_isbn').value = book.ISBN || '';
                            
                            // Set genre and update theme options
                            const genreSelect = document.getElementById('edit_genre');
                            genreSelect.value = book.genre;
                            
                            // Set theme
                            const themeSelect = document.getElementById('edit_theme');
                            themeSelect.value = book.theme;
                            
                            // Set new fields
                            document.getElementById('edit_book_type').value = book.book_type || 'Paperback';
                            document.getElementById('edit_condition').value = book.condition || 'New';
                            document.getElementById('edit_damages').value = book.damages || '';
                            
                            // Update damages visibility based on condition
                            updateDamagesVisibility(book.condition || 'New', true);
                            
                            document.getElementById('edit_popularity').value = book.popularity || 'New Releases';
                            document.getElementById('edit_price').value = book.price;
                            document.getElementById('edit_rent_price').value = book.rent_price || 0;
                            document.getElementById('edit_stock').value = book.stock;
                            document.getElementById('edit_description').value = book.description;
                            
                            // Show current cover image preview if available
                            const coverPreview = document.getElementById('current_cover_preview');
                            if (book.cover_image) {
                                coverPreview.innerHTML = `
                                    <img src="${book.cover_image}" alt="Current cover" style="max-height: 100px; max-width: 100%;" class="img-thumbnail">
                                    <p class="mt-1 mb-0 small">Current cover image</p>
                                `;
                            } else {
                                coverPreview.innerHTML = '<p class="text-muted">No cover image</p>';
                            }
                            
                            // Hide loader and show content
                            editBookLoader.style.display = 'none';
                            editBookContent.style.display = 'block';
                        } else {
                            // Handle error
                            alert('Error loading book details: ' + data.message);
                            const modal = bootstrap.Modal.getInstance(editBookModal);
                            modal.hide();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while loading book details.');
                        const modal = bootstrap.Modal.getInstance(editBookModal);
                        modal.hide();
                    });
            });
            
            // Delete Book Modal
            const deleteBookModal = document.getElementById('deleteBookModal');
            deleteBookModal.addEventListener('show.bs.modal', function(event) {
                // Button that triggered the modal
                const button = event.relatedTarget;
                
                // Extract book info
                const bookId = button.getAttribute('data-book-id');
                const bookTitle = button.getAttribute('data-book-title');
                
                // Update the modal content
                document.getElementById('delete-book-title').textContent = bookTitle;
                document.getElementById('delete_book_id').value = bookId;
            });
            
            // Select All Checkbox
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('.book-select').forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                });
            }
            
            // Search functionality
            const searchBooks = document.getElementById('searchBooks');
            if (searchBooks) {
                searchBooks.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    const bookRows = document.querySelectorAll('tbody tr');
                    const bookCards = document.querySelectorAll('.book-grid .col-md-6');
                    
                    // Search in table view
                    bookRows.forEach(row => {
                        // Skip empty state row
                        if (row.querySelector('td[colspan]')) return;
                        
                        const title = row.querySelector('.d-flex .text-muted')?.parentElement?.firstElementChild?.textContent.toLowerCase() || '';
                        const author = row.querySelector('.d-flex .text-muted')?.textContent.toLowerCase() || '';
                        const isbn = row.cells[2]?.textContent.toLowerCase() || '';
                        const genre = row.cells[3]?.textContent.toLowerCase() || '';
                        const type = row.cells[4]?.textContent.toLowerCase() || '';
                        const condition = row.cells[5]?.textContent.toLowerCase() || '';
                        
                        if (title.includes(searchTerm) || 
                            author.includes(searchTerm) || 
                            isbn.includes(searchTerm) || 
                            genre.includes(searchTerm) ||
                            type.includes(searchTerm) ||
                            condition.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    // Search in grid view
                    bookCards.forEach(card => {
                        const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                        const author = card.querySelector('.card-text.text-muted')?.textContent.toLowerCase() || '';
                        const isbn = card.querySelector('small:nth-of-type(1)')?.textContent.toLowerCase() || '';
                        const genre = card.querySelector('small:nth-of-type(2)')?.textContent.toLowerCase() || '';
                        const type = card.querySelector('.badge.bg-info')?.textContent.toLowerCase() || '';
                        const condition = card.querySelector('.badge:not(.bg-info):not(.bg-success):not(.bg-danger)')?.textContent.toLowerCase() || '';
                        
                        if (title.includes(searchTerm) || 
                            author.includes(searchTerm) || 
                            isbn.includes(searchTerm) || 
                            genre.includes(searchTerm) ||
                            type.includes(searchTerm) ||
                            condition.includes(searchTerm)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    
                    // Check if there are any visible rows
                    const visibleRows = Array.from(bookRows).filter(row => row.style.display !== 'none');
                    const tbody = document.querySelector('tbody');
                    
                    // Show "No results found" if all rows are hidden
                    if (visibleRows.length === 0 && searchTerm !== '' && !document.getElementById('no-results-row')) {
                        const noResultsRow = document.createElement('tr');
                        noResultsRow.id = 'no-results-row';
                        noResultsRow.innerHTML = `
                            <td colspan="11" class="text-center py-3">
                                <p>No books found matching "${searchTerm}"</p>
                            </td>
                        `;
                        tbody.appendChild(noResultsRow);
                    } else if ((visibleRows.length > 0 || searchTerm === '') && document.getElementById('no-results-row')) {
                        document.getElementById('no-results-row').remove();
                    }
                });
            }
            
            // View toggling (Table/Grid)
            const tableViewBtn = document.getElementById('tableViewBtn');
            const gridViewBtn = document.getElementById('gridViewBtn');
            const tableView = document.querySelector('.table-responsive');
            const gridView = document.getElementById('bookGridView');

            if (tableViewBtn && gridViewBtn && tableView && gridView) {
                // Table view (default)
                tableViewBtn.addEventListener('click', function() {
                    tableView.style.display = 'block';
                    gridView.style.display = 'none';
                    tableViewBtn.classList.add('active');
                    gridViewBtn.classList.remove('active');
                    
                    // Save preference to localStorage
                    localStorage.setItem('bookwagon_view_preference', 'table');
                });
                
                // Grid view
                gridViewBtn.addEventListener('click', function() {
                    tableView.style.display = 'none';
                    gridView.style.display = 'flex';
                    gridViewBtn.classList.add('active');
                    tableViewBtn.classList.remove('active');
                    
                    // Save preference to localStorage
                    localStorage.setItem('bookwagon_view_preference', 'grid');
                });
                
                // Load saved preference (if any)
                const savedViewPreference = localStorage.getItem('bookwagon_view_preference');
                if (savedViewPreference === 'grid') {
                    gridViewBtn.click();
                }
            }
        });
    </script>
    
</body>
</html>