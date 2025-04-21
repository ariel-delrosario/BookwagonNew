<?php
// Initialize the session
session_start();
 
// Check if the user is logged in as admin, if not then redirect to admin login page
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Include database connection
require_once "db_connect.php";

// Process approval/rejection actions
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $seller_id = $_GET['id'];
    
    if($action == 'approve') {
        // Get the user_id for this seller
        $user_query = "SELECT user_id FROM sellers WHERE id = ?";
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()) {
            $user_id = $row['user_id'];
            
            // Start a transaction
            $conn->begin_transaction();
            
            try {
                // Update seller status to approved
                $update_seller = "UPDATE sellers SET status = 'approved' WHERE id = ?";
                $stmt = $conn->prepare($update_seller);
                $stmt->bind_param("i", $seller_id);
                $stmt->execute();
                
                // Update user type to seller
                $update_user = "UPDATE users SET usertype = 'seller' WHERE id = ?";
                $stmt = $conn->prepare($update_user);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $success_message = "Seller request approved successfully!";
            } catch (Exception $e) {
                // Roll back transaction on error
                $conn->rollback();
                $error_message = "Error: " . $e->getMessage();
            }
        }
    } elseif($action == 'reject') {
        // Update seller status to rejected
        $update_seller = "UPDATE sellers SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($update_seller);
        $stmt->bind_param("i", $seller_id);
        
        if($stmt->execute()) {
            $success_message = "Seller request rejected.";
        } else {
            $error_message = "Error updating record: " . $conn->error;
        }
    }
}

// Fetch all seller requests
$sql = "SELECT s.*, u.email, u.username FROM sellers s 
        JOIN users u ON s.user_id = u.id 
        ORDER BY s.created_at DESC";
$result = $conn->query($sql);
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Seller Requests - BookWagon Admin</title>
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
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
        }
        .status-pending {
            background-color: #fef9e7;
            color: #f39c12;
        }
        .status-approved {
            background-color: #e9f7ef;
            color: #27ae60;
        }
        .status-rejected {
            background-color: #fdedec;
            color: #e74c3c;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            margin-right: 5px;
            display: inline-block;
        }
        .btn-approve {
            background-color: #27ae60;
        }
        .btn-approve:hover {
            background-color: #219955;
        }
        .btn-reject {
            background-color: #e74c3c;
        }
        .btn-reject:hover {
            background-color: #c0392b;
        }
        .btn-view {
            background-color: #3498db;
        }
        .btn-view:hover {
            background-color: #2980b9;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #777;
        }
        .shop-logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="welcome-msg">Manage Seller Requests</h1>
        <a href="logout.php" class="logout-btn">Sign Out</a>
    </div>
    
    <div class="dashboard-container">
        <div class="dashboard-content">
            <h2>Seller Requests</h2>
            
            <?php if(isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Shop Name</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php if(!empty($row['shop_logo']) && file_exists($row['shop_logo'])): ?>
                                        <img src="<?php echo $row['shop_logo']; ?>" class="shop-logo" alt="Shop Logo">
                                    <?php else: ?>
                                        <span>No logo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['shop_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if($row['status'] == 'pending'): ?>
                                        <span class="status status-pending">Pending</span>
                                    <?php elseif($row['status'] == 'approved'): ?>
                                        <span class="status status-approved">Approved</span>
                                    <?php elseif($row['status'] == 'rejected'): ?>
                                        <span class="status status-rejected">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="admin_view_seller_request.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view">View</a>
                                    
                                    <?php if($row['status'] == 'pending'): ?>
                                        <a href="admin_seller_requests.php?action=approve&id=<?php echo $row['id']; ?>" class="action-btn btn-approve" onclick="return confirm('Are you sure you want to approve this seller?')">Approve</a>
                                        <a href="admin_seller_requests.php?action=reject&id=<?php echo $row['id']; ?>" class="action-btn btn-reject" onclick="return confirm('Are you sure you want to reject this seller?')">Reject</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No seller requests found</h3>
                    <p>There are currently no seller requests to review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>