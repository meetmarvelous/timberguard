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
    ['title' => 'Admin Dashboard', 'url' => 'admin/index.php', 'active' => false],
    ['title' => 'Manage Reserves', 'url' => '', 'active' => true]
];

// Handle form submission for adding/editing reserves
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_reserve'])) {
        $reserve_name = sanitize_input($_POST['reserve_name']);
        $location = sanitize_input($_POST['location']);
        $description = sanitize_input($_POST['description']);
        
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
    $sql = "SELECT reserve_name FROM forest_reserves WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $reserve = $result->fetch_assoc();
        $reserve_name = $reserve['reserve_name'];
        
        $sql = "DELETE FROM forest_reserves WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
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

<div class="container py-4 py-md-5">
    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="display-6 fw-bold mb-1">Forest Reserves</h1>
            <p class="lead text-muted mb-0">Manage forest reserves and their information</p>
        </div>
        <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addReserveModal">
            <i class="fas fa-plus me-2" aria-hidden="true"></i>Add New Reserve
        </button>
    </div>
    
    <?php if ($reserves->num_rows > 0): ?>
        <div class="card border-0 shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="reservesTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Reserve Name</th>
                                <th class="hide-mobile">Location</th>
                                <th>Tree Count</th>
                                <th class="hide-mobile">Date Added</th>
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
                                    <td><code><?php echo str_pad($reserve['id'], 4, '0', STR_PAD_LEFT); ?></code></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($reserve['reserve_name']); ?></strong>
                                        <div class="text-muted small text-truncate" style="max-width: 200px;">
                                            <?php echo htmlspecialchars(substr($reserve['description'], 0, 80)) . (strlen($reserve['description']) > 80 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td class="hide-mobile"><?php echo htmlspecialchars($reserve['location']); ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="badge bg-success"><?php echo $tree_count['available'] ?: 0; ?> Available</span>
                                            <span class="badge bg-danger"><?php echo $tree_count['sold'] ?: 0; ?> Sold</span>
                                        </div>
                                        <small class="text-muted">(<?php echo $tree_count['total'] ?: 0; ?> Total)</small>
                                    </td>
                                    <td class="hide-mobile"><?php echo date('M j, Y', strtotime($reserve['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="trees.php?reserve_id=<?php echo $reserve['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               data-bs-toggle="tooltip" title="View Trees"
                                               aria-label="View trees in <?php echo htmlspecialchars($reserve['reserve_name']); ?>">
                                                <i class="fas fa-tree" aria-hidden="true"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    data-bs-toggle="modal" data-bs-target="#editReserveModal<?php echo $reserve['id']; ?>"
                                                    aria-label="Edit <?php echo htmlspecialchars($reserve['reserve_name']); ?>">
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                            </button>
                                            <a href="reserves.php?delete=<?php echo $reserve['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this forest reserve? This will also delete all associated trees. This action cannot be undone.');"
                                               aria-label="Delete <?php echo htmlspecialchars($reserve['reserve_name']); ?>">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Edit Reserve Modal -->
                                <div class="modal fade" id="editReserveModal<?php echo $reserve['id']; ?>" tabindex="-1" aria-labelledby="editReserveModalLabel<?php echo $reserve['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editReserveModalLabel<?php echo $reserve['id']; ?>">Edit Forest Reserve</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="reserves.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="edit_reserve" value="1">
                                                    <input type="hidden" name="id" value="<?php echo $reserve['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="reserve_name_<?php echo $reserve['id']; ?>" class="form-label">Reserve Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="reserve_name_<?php echo $reserve['id']; ?>" 
                                                               name="reserve_name" value="<?php echo htmlspecialchars($reserve['reserve_name']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="location_<?php echo $reserve['id']; ?>" class="form-label">Location <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="location_<?php echo $reserve['id']; ?>" 
                                                               name="location" value="<?php echo htmlspecialchars($reserve['location']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="description_<?php echo $reserve['id']; ?>" class="form-label">Description</label>
                                                        <textarea class="form-control" id="description_<?php echo $reserve['id']; ?>" 
                                                                  name="description" rows="4"><?php echo htmlspecialchars($reserve['description']); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-2" aria-hidden="true"></i>Save Changes</button>
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
            <div class="card-body empty-state">
                <i class="fas fa-leaf" aria-hidden="true"></i>
                <h4>No forest reserves found</h4>
                <p>Get started by adding your first forest reserve.</p>
                <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addReserveModal">
                    <i class="fas fa-plus me-2" aria-hidden="true"></i>Add First Reserve
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Reserve Modal -->
<div class="modal fade" id="addReserveModal" tabindex="-1" aria-labelledby="addReserveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReserveModalLabel">Add New Forest Reserve</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="reserves.php">
                <div class="modal-body">
                    <input type="hidden" name="add_reserve" value="1">
                    
                    <div class="mb-3">
                        <label for="reserve_name" class="form-label">Reserve Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reserve_name" name="reserve_name" 
                               placeholder="Enter reserve name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location" 
                               placeholder="Enter reserve location" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Enter reserve description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-plus me-2" aria-hidden="true"></i>Add Reserve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$extra_scripts = "
<script>
    $(document).ready(function() {
        $('#reservesTable').DataTable({
            order: [[4, 'desc']],
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [5] }
            ],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search reserves...'
            }
        });
    });
</script>
";
include '../includes/footer.php'; 
?>