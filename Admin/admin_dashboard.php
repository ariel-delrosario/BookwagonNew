<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BookWagon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 15px 30px;
            margin-bottom: 30px;
        }
        .welcome-msg {
            margin: 0;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .dashboard-content {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .action-btns {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .action-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .action-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="welcome-msg">Hi, <?php echo htmlspecialchars($_SESSION["admin_username"]); ?></h1>
        <a href="logout.php" class="logout-btn">Sign Out</a>
    </div>
    
    <div class="dashboard-container">
        <div class="dashboard-content">
            <h2>BookWagon Admin Dashboard</h2>
            <p>Welcome to the admin dashboard. Here you can manage all aspects of your BookWagon site.</p>
            
            <div class="action-btns">
                <a href="#" class="action-btn">Manage Books</a>
                <a href="#" class="action-btn">Manage Users</a>
                <a href="admin_seller_requests.php" class="action-btn">Manage Sellers</a>
                <a href="#" class="action-btn">Manage Orders</a>
                <a href="#" class="action-btn">View Reports</a>
                <a href="#" class="action-btn">Site Settings</a>
            </div>
        </div>
    </div>
</body>
</html>