<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    die("Unauthorized access");
}

// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bookwagon_db";

// Create connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Get seller ID from request
$seller_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($seller_id <= 0) {
    die("Invalid seller ID");
}

try {
    // Fetch seller details
    $sql = "SELECT 
            u.id as user_id,
            u.firstName,
            u.lastName,
            u.middleInitial,
            u.email,
            sd.*,
            sa.street_address,
            sa.city,
            sa.state,
            sa.postal_code,
            sa.country
            FROM users u
            JOIN seller_details sd ON u.id = sd.user_id
            LEFT JOIN seller_addresses sa ON sd.id = sa.seller_id
            WHERE sd.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $seller = $result->fetch_assoc();
    
    if (!$seller) {
        die("Seller not found");
    }
    
    // Fetch seller IDs
    $sql = "SELECT * FROM seller_ids WHERE seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $ids_result = $stmt->get_result();
    $ids = $ids_result->fetch_all(MYSQLI_ASSOC);
    
    // Fetch seller selfies
    $sql = "SELECT * FROM seller_selfies WHERE seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $selfies_result = $stmt->get_result();
    $selfies = $selfies_result->fetch_all(MYSQLI_ASSOC);
    
    // Output seller details
    ?>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">Personal Information</h6>
                <table class="table table-bordered">
                    <tr>
                        <th>Name</th>
                        <td><?php echo htmlspecialchars($seller['firstName'] . ' ' . $seller['middleInitial'] . ' ' . $seller['lastName']); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo htmlspecialchars($seller['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?php echo htmlspecialchars($seller['phone_number']); ?></td>
                    </tr>
                    <tr>
                        <th>Social Media</th>
                        <td><?php echo htmlspecialchars($seller['social_media_link'] ?? 'Not provided'); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">Address Information</h6>
                <table class="table table-bordered">
                    <tr>
                        <th>Street Address</th>
                        <td><?php echo htmlspecialchars($seller['street_address'] ?? 'Not provided'); ?></td>
                    </tr>
                    <tr>
                        <th>City</th>
                        <td><?php echo htmlspecialchars($seller['city'] ?? 'Not provided'); ?></td>
                    </tr>
                    <tr>
                        <th>State</th>
                        <td><?php echo htmlspecialchars($seller['state'] ?? 'Not provided'); ?></td>
                    </tr>
                    <tr>
                        <th>Postal Code</th>
                        <td><?php echo htmlspecialchars($seller['postal_code'] ?? 'Not provided'); ?></td>
                    </tr>
                    <tr>
                        <th>Country</th>
                        <td><?php echo htmlspecialchars($seller['country'] ?? 'Not provided'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <h6 class="mb-3">Identification Documents</h6>
                <div class="row">
                    <?php foreach ($ids as $id): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6><?php echo htmlspecialchars(ucfirst($id['id_type']) . ' ID: ' . $id['id_name']); ?></h6>
                                <img src="<?php echo htmlspecialchars($id['id_image_path']); ?>" class="img-fluid" alt="ID Document">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($selfies)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <h6 class="mb-3">Verification Selfies</h6>
                <div class="row">
                    <?php foreach ($selfies as $selfie): ?>
                    <div class="col-md-4 mb-3">
                        <img src="<?php echo htmlspecialchars($selfie['selfie_path']); ?>" class="img-fluid" alt="Verification Selfie">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($seller['verification_status'] === 'pending'): ?>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button class="btn btn-success me-2" onclick="updateStatus(<?php echo $seller_id; ?>, 'approved')">
                    <i class="fas fa-check"></i> Approve Seller
                </button>
                <button class="btn btn-danger" onclick="updateStatus(<?php echo $seller_id; ?>, 'rejected')">
                    <i class="fas fa-times"></i> Reject Seller
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?> 