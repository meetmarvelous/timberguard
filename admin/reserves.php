<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin or forest manager login
require_login();
if (!has_role('admin') && !has_role('forest manager')) {
    $_SESSION['message'] = "You don't have permission to access this page.";
    $_SESSION['message_type'] = "error";
    redirect('index.php');
}

$page_title = "Manage Forest Reserves";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Admin Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Manage Reserves', 'url' => '', 'active' => true]
];

// Handle form submission for adding/editing reserves
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_reserve'])) {
        $reserve_name = sanitize_input($_POST['reserve_name']);
        $location = sanitize_input($_POST['location']);
        $description = sanitize_input($_POST['description']);
        
        // Validation
        $errors = [];
        
        if (empty($reserve_name)) {
            $errors[] = "Reserve name is required.";
        }
        
        if (empty($location)) {
            $errors[] = "Location is required.";
        }
        
        if (empty($errors)) {
            global $conn;
            $sql = "INSERT INTO forest_reserves (reserve_name, location, description, created_at) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $reserve_name, $location, $description);
            
            if ($stmt->execute()) {
                // Log activity
                log_activity($_SESSION['user_id'], 'reserve_add', 'Added new forest reserve: ' . $reserve_name);
                
                $_SESSION['message'] = "Forest reserve added successfully.";
                $_SESSION['message_type'] = "success";
                redirect('admin/reserves.php');
            } else {
                $errors[] = "Failed to add reserve. Please try again.";
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['message'] = implode("<br>", $errors);
            $_SESSION['message_type'] = "error";
        }
    }
    elseif (isset($_POST['edit_reserve'])) {
        $id = intval($_POST['id']);
        $reserve_name = sanitize_input($_POST['reserve_name']);
        $location = sanitize_input($_POST['location']);
        $description = sanitize_input($_POST['description']);
        
        // Validation
        $errors = [];
        
        if (empty($reserve_name)) {
            $errors[] = "Reserve name is required.";
        }
        
        if (empty($location)) {
            $errors[] = "Location is required.";
        }
        
        if (empty($errors)) {
            global $conn;
            $sql = "UPDATE forest_reserves 
                    SET reserve_name = ?, location = ?, description = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $reserve_name, $location, $description, $id);
            
            if ($stmt->execute()) {
                // Log activity
                log_activity($_SESSION['user_id'], 'reserve_edit', 'Edited forest reserve: ' . $reserve_name);
                
                $_SESSION['message'] = "Forest reserve updated successfully.";
                $_SESSION['message_type'] = "success";
                redirect('admin/reserves.php');
            } else {
                $errors[] = "Failed to update reserve. Please try again.";
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['message'] = implode("<br>", $errors);
            $_SESSION['message_type'] = "error";
        }
    }
}

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    global $conn;
    // First get the reserve name for logging
    $sql = "SELECT reserve_name FROM forest_reserves WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $reserve = $result->fetch_assoc();
        $reserve_name = $reserve['reserve_name'];
        
        // Delete the reserve
        $sql = "DELETE FROM forest_reserves WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Log activity
            log_activity($_SESSION['user_id'], 'reserve_delete', 'Deleted forest reserve: ' . $reserve_name);
            
            $_SESSION['message'] = "Forest reserve deleted successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Failed to delete forest reserve.";
            $_SESSION['message_type'] = "error";
        }
    }
    
    redirect('admin/reserves.php');
}

// Get all forest reserves
global $conn;
$sql = "SELECT * FROM forest_reserves ORDER BY created_at DESC";
$reserves = $conn->query($sql);
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="display-5 fw-bold">Forest Reserves</h1>
            <p class="lead">Manage forest reserves and their information</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addReserveModal">
                <i class="fas fa-plus me-2"></i>Add New Reserve
            </button>
        </div>
    </div>
    
    <?php if ($reserves->num_rows > 0): ?>
        <div class="card border-0 shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Reserve Name</th>
                                <th>Location</th>
                                <th>Tree Count</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($reserve = $reserves->fetch_assoc()): ?>
                                <?php
                                // Get tree count for this reserve
                                $sql = "SELECT 
                                            COUNT(*) as total,
                                            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                                            SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold
                                        FROM trees 
                                        WHERE reserve_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $reserve['id']);
                                $stmt->execute();
                                $tree_count = $stmt->get_result()->fetch_assoc();
                                ?>
                                <tr>
                                    <td><?php echo str_pad($reserve['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <strong><?php echo $reserve['reserve_name']; ?></strong>
                                        <div class="text-muted small"><?php echo substr($reserve['description'], 0, 100) . (strlen($reserve['description']) > 100 ? '...' : ''); ?></div>
                                    </td>
                                    <td><?php echo $reserve['location']; ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $tree_count['available']; ?> Available</span>
                                        <span class="badge bg-danger"><?php echo $tree_count['sold']; ?> Sold</span>
                                        <span class="text-muted">(<?php echo $tree_count['total']; ?> Total)</span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($reserve['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    data-bs-toggle="modal" data-bs-target="#editReserveModal<?php echo $reserve['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="reserves.php?delete=<?php echo $reserve['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this forest reserve? This action cannot be undone.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Edit Reserve Modal -->
                                <div class="modal fade" id="editReserveModal<?php echo $reserve['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Forest Reserve</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="reserves.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="edit_reserve" value="1">
                                                    <input type="hidden" name="id" value="<?php echo $reserve['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="reserve_name_<?php echo $reserve['id']; ?>" class="form-label">Reserve Name</label>
                                                        <input type="text" class="form-control" id="reserve_name_<?php echo $reserve['id']; ?>" 
                                                               name="reserve_name" value="<?php echo $reserve['reserve_name']; ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="location_<?php echo $reserve['id']; ?>" class="form-label">Location</label>
                                                        <input type="text" class="form-control" id="location_<?php echo $reserve['id']; ?>" 
                                                               name="location" value="<?php echo $reserve['location']; ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="description_<?php echo $reserve['id']; ?>" class="form-label">Description</label>
                                                        <textarea class="form-control" id="description_<?php echo $reserve['id']; ?>" 
                                                                  name="description" rows="3"><?php echo $reserve['description']; ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-success">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-leaf fa-3x text-muted mb-3"></i>
                <h4>No forest reserves found</h4>
                <p class="text-muted mb-4">Get started by adding your first forest reserve.</p>
                <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addReserveModal">
                    <i class="fas fa-plus me-2"></i>Add First Reserve
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Reserve Modal -->
<div class="modal fade" id="addReserveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Forest Reserve</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="reserves.php">
                <div class="modal-body">
                    <input type="hidden" name="add_reserve" value="1">
                    
                    <div class="mb-3">
                        <label for="reserve_name" class="form-label">Reserve Name</label>
                        <input type="text" class="form-control" id="reserve_name" name="reserve_name" 
                               placeholder="Enter reserve name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               placeholder="Enter reserve location" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Enter reserve description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Add Reserve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>