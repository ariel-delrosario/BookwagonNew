<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bookwagon_db";

// Create connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sellers - BookWagon Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f8a100;
            --secondary-color: #f8f9fa;
            --accent-blue: #5b6bff;
            --bg-cream: #faebc8;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            min-height: 100vh;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.5rem;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--bg-cream);
            color: var(--primary-color);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .dropdown-item:hover {
            background-color: var(--bg-cream);
            color: var(--primary-color);
        }

        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            padding: 1rem;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <img src="images/logo.png" alt="BookWagon" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#manageUsers">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                            <div class="collapse show" id="manageUsers">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="manage_sellers.php">Sellers</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <h2 class="mb-4">Manage Sellers</h2>
                
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Seller Applications</h5>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <label class="me-2">From:</label>
                                    <input type="date" class="form-control form-control-sm" id="dateFrom">
                                </div>
                                <div class="me-3">
                                    <label class="me-2">To:</label>
                                    <input type="date" class="form-control form-control-sm" id="dateTo">
                                </div>
                                <div>
                                    <label class="me-2">Show:</label>
                                    <select class="form-select form-select-sm" id="entriesPerPage">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="sellersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch sellers with their details
                                    $sql = "SELECT 
                                            u.id,
                                            u.firstName,
                                            u.lastName,
                                            u.email,
                                            sd.phone_number,
                                            sd.verification_status,
                                            sd.created_at,
                                            sd.id as seller_detail_id
                                           FROM users u
                                           JOIN seller_details sd ON u.id = sd.user_id
                                           ORDER BY sd.created_at DESC";
                                    
                                    try {
                                        $result = $conn->query($sql);
                                        
                                        if ($result->num_rows > 0) {
                                            while($row = $result->fetch_assoc()) {
                                                $status_class = '';
                                                switch($row['verification_status']) {
                                                    case 'pending':
                                                        $status_class = 'text-warning';
                                                        break;
                                                    case 'approved':
                                                        $status_class = 'text-success';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'text-danger';
                                                        break;
                                                }
                                                
                                                echo "<tr class='seller-row' data-date='" . date('Y-m-d', strtotime($row['created_at'])) . "'>";
                                                echo "<td>" . $row['id'] . "</td>";
                                                echo "<td>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                                                echo "<td class='" . $status_class . "'>" . ucfirst($row['verification_status']) . "</td>";
                                                echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                                echo "<td>";
                                                echo "<button class='btn btn-info btn-sm me-2' onclick='viewDetails(" . $row['seller_detail_id'] . ")'><i class='fas fa-eye'></i> View</button>";
                                                
                                                if ($row['verification_status'] === 'pending') {
                                                    echo "<button class='btn btn-success btn-sm me-2' onclick='updateStatus(" . $row['seller_detail_id'] . ", \"approved\")'><i class='fas fa-check'></i> Approve</button>";
                                                    echo "<button class='btn btn-danger btn-sm' onclick='updateStatus(" . $row['seller_detail_id'] . ", \"rejected\")'><i class='fas fa-times'></i> Reject</button>";
                                                }
                                                
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>No sellers found</td></tr>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . $e->getMessage() . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="showing-entries">
                                    Showing <span id="showingStart">1</span> to <span id="showingEnd">10</span> of <span id="totalEntries">0</span> entries
                                </div>
                                <div class="pagination-container">
                                    <button class="btn btn-sm btn-outline-secondary me-2" id="prevPage" disabled>Previous</button>
                                    <button class="btn btn-sm btn-outline-secondary" id="nextPage">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seller Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="sellerDetailsContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    let currentPage = 1;
    let rowsPerPage = 10;
    let filteredRows = [];
    
    function initializeTable() {
        const rows = document.querySelectorAll('.seller-row');
        filteredRows = Array.from(rows);
        updatePagination();
        showPage(1);
    }

    function filterByDate() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const rows = document.querySelectorAll('.seller-row');
        
        filteredRows = Array.from(rows).filter(row => {
            const rowDate = row.dataset.date;
            if (!dateFrom && !dateTo) return true;
            if (dateFrom && !dateTo) return rowDate >= dateFrom;
            if (!dateFrom && dateTo) return rowDate <= dateTo;
            return rowDate >= dateFrom && rowDate <= dateTo;
        });
        
        currentPage = 1;
        updatePagination();
        showPage(1);
    }

    function updatePagination() {
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        
        document.getElementById('totalEntries').textContent = totalRows;
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage >= totalPages;
        
        const start = (currentPage - 1) * rowsPerPage + 1;
        const end = Math.min(start + rowsPerPage - 1, totalRows);
        document.getElementById('showingStart').textContent = totalRows === 0 ? 0 : start;
        document.getElementById('showingEnd').textContent = end;
    }

    function showPage(page) {
        currentPage = page;
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        // Hide all rows
        document.querySelectorAll('.seller-row').forEach(row => {
            row.style.display = 'none';
        });
        
        // Show only rows for current page
        filteredRows.slice(start, end).forEach(row => {
            row.style.display = '';
        });
        
        updatePagination();
    }

    // Event Listeners
    document.getElementById('dateFrom').addEventListener('change', filterByDate);
    document.getElementById('dateTo').addEventListener('change', filterByDate);
    document.getElementById('entriesPerPage').addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        updatePagination();
        showPage(1);
    });
    
    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) showPage(currentPage - 1);
    });
    
    document.getElementById('nextPage').addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) showPage(currentPage + 1);
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', initializeTable);

    // Existing functions
    function viewDetails(sellerId) {
        const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
        const contentDiv = document.getElementById('sellerDetailsContent');
        contentDiv.innerHTML = 'Loading...';
        modal.show();

        fetch(`get_seller_details.php?id=${sellerId}`)
            .then(response => response.text())
            .then(data => {
                contentDiv.innerHTML = data;
            })
            .catch(error => {
                contentDiv.innerHTML = `Error loading details: ${error}`;
            });
    }

    function updateStatus(sellerId, status) {
        if (!confirm(`Are you sure you want to ${status} this seller?`)) {
            return;
        }

        fetch('update_seller_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `seller_id=${sellerId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error updating status: ' + error);
        });
    }
    </script>
</body>
</html> 