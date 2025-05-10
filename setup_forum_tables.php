<?php
// Database setup script for forum tables
include("connect.php");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create forum_categories table
$createCategoriesTable = "CREATE TABLE IF NOT EXISTS forum_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(50) DEFAULT 'fa-comments',
    color VARCHAR(20) DEFAULT '#6c757d',
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($createCategoriesTable) === TRUE) {
    echo "Table forum_categories created successfully<br>";
} else {
    echo "Error creating forum_categories table: " . $conn->error . "<br>";
}

// Create forum_posts table
$createPostsTable = "CREATE TABLE IF NOT EXISTS forum_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    tags VARCHAR(255) NULL,
    views INT DEFAULT 0,
    is_pinned TINYINT(1) DEFAULT 0,
    status ENUM('active', 'closed', 'hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES forum_categories(category_id) ON DELETE CASCADE
)";

if ($conn->query($createPostsTable) === TRUE) {
    echo "Table forum_posts created successfully<br>";
} else {
    echo "Error creating forum_posts table: " . $conn->error . "<br>";
}

// Create forum_comments table
$createCommentsTable = "CREATE TABLE IF NOT EXISTS forum_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES forum_comments(comment_id) ON DELETE SET NULL
)";

if ($conn->query($createCommentsTable) === TRUE) {
    echo "Table forum_comments created successfully<br>";
} else {
    echo "Error creating forum_comments table: " . $conn->error . "<br>";
}

// Create forum_user_interactions table (for likes, bookmarks, etc.)
$createInteractionsTable = "CREATE TABLE IF NOT EXISTS forum_user_interactions (
    interaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NULL,
    comment_id INT NULL,
    interaction_type ENUM('like', 'bookmark', 'report') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES forum_comments(comment_id) ON DELETE CASCADE
)";

if ($conn->query($createInteractionsTable) === TRUE) {
    echo "Table forum_user_interactions created successfully<br>";
} else {
    echo "Error creating forum_user_interactions table: " . $conn->error . "<br>";
}

// Insert default categories
$categories = [
    ['name' => 'Book Discussions', 'description' => 'General book discussions and topics', 'icon' => 'fa-book', 'color' => '#3498db', 'order_index' => 1],
    ['name' => 'Reading Recommendations', 'description' => 'Ask for and provide book recommendations', 'icon' => 'fa-thumbs-up', 'color' => '#2ecc71', 'order_index' => 2],
    ['name' => 'Book Reviews', 'description' => 'Share and discuss book reviews', 'icon' => 'fa-star', 'color' => '#f39c12', 'order_index' => 3],
    ['name' => 'Writing Corner', 'description' => 'For writers and aspiring authors', 'icon' => 'fa-pen', 'color' => '#3498db', 'order_index' => 4],
    ['name' => 'Book Clubs', 'description' => 'Book club discussions and meeting arrangements', 'icon' => 'fa-users', 'color' => '#e74c3c', 'order_index' => 5],
    ['name' => 'Literary Events', 'description' => 'Upcoming literary events and festivals', 'icon' => 'fa-calendar-alt', 'color' => '#6c757d', 'order_index' => 6]
];

// Check if categories exist before inserting
$checkCategories = $conn->query("SELECT COUNT(*) as count FROM forum_categories");
$categoryCount = $checkCategories->fetch_assoc()['count'];

if ($categoryCount == 0) {
    foreach ($categories as $category) {
        $insertCategory = $conn->prepare("INSERT INTO forum_categories (name, description, icon, color, order_index) VALUES (?, ?, ?, ?, ?)");
        $insertCategory->bind_param("ssssi", $category['name'], $category['description'], $category['icon'], $category['color'], $category['order_index']);
        
        if ($insertCategory->execute()) {
            echo "Added category: " . $category['name'] . "<br>";
        } else {
            echo "Error adding category: " . $insertCategory->error . "<br>";
        }
        
        $insertCategory->close();
    }
} else {
    echo "Categories already exist. Skipping insertion.<br>";
}

// Insert a sample post for testing
$checkPosts = $conn->query("SELECT COUNT(*) as count FROM forum_posts");
$postCount = $checkPosts->fetch_assoc()['count'];

if ($postCount == 0) {
    // Get a user ID (admin or first user)
    $getUserQuery = "SELECT id FROM users LIMIT 1";
    $userResult = $conn->query($getUserQuery);
    
    if ($userResult && $userResult->num_rows > 0) {
        $userId = $userResult->fetch_assoc()['id'];
        
        $samplePost = $conn->prepare("INSERT INTO forum_posts (category_id, user_id, title, content, tags) VALUES (?, ?, ?, ?, ?)");
        $categoryId = 2; // Book Reviews category
        $title = "Mysteries of Sherlock Holmes";
        $content = "A bit cliff hanger 7/10. I really enjoyed reading this classic detective story. The plot twists were amazing!";
        $tags = "fiction,mystery,detective";
        
        $samplePost->bind_param("iisss", $categoryId, $userId, $title, $content, $tags);
        
        if ($samplePost->execute()) {
            echo "Added sample post for testing<br>";
        } else {
            echo "Error adding sample post: " . $samplePost->error . "<br>";
        }
        
        $samplePost->close();
    } else {
        echo "No users found to create sample post<br>";
    }
} else {
    echo "Posts already exist. Skipping sample post creation.<br>";
}

echo "<br>Database setup complete! <a href='explore.php'>Go to Forum</a>";

$conn->close();
?> 