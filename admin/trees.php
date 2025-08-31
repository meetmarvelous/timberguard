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

$page_title = "Manage Trees";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Admin Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Manage Trees', 'url' => '', 'active' => true]
];

// Get reserve ID from URL parameter if provided
$reserve_id = isset($_GET['reserve_id']) ? intval($_GET['reserve_id']) : null;

// Handle form submission for adding/editing trees
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_tree'])) {
        $reserve_id = intval($_POST['reserve_id']);
        $species = sanitize_input($_POST['species']);
        $MTH = floatval($_POST['MTH']);
        $THT = floatval($_POST['THT']);
        $DBH = floatval($_POST['DBH']);
        $basal_area = floatval($_POST['basal_area']);
        $volume = floatval($_POST['volume']);
        $status = sanitize_input($_POST['status']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        
        // Validation
        $errors = [];
        
        if (empty($reserve_id)) {
            $errors[] = "Reserve is required.";
        }
        
        if (empty($species)) {
            $errors[] = "Species is required.";
        }
        
        if (empty($errors)) {
            global $conn;
            $sql = "INSERT INTO trees (reserve_id, species, MTH, THT, DBH, basal_area, volume, latitude, longitude, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isddddddds", $reserve_id, $species, $MTH, $THT, $DBH, $basal_area, $volume, $latitude, $longitude, $status);
            
            if ($stmt->execute()) {
                // Log activity
                log_activity($_SESSION['user_id'], 'tree_add', 'Added new tree: ' . $species . ' (ID: ' . $conn->insert_id . ')');
                
                $_SESSION['message'] = "Tree added successfully.";
                $_SESSION['message_type'] = "success";
                redirect('admin/trees.php' . ($reserve_id ? '?reserve_id=' . $reserve_id : ''));
            } else {
                $errors[] = "Failed to add tree. Please try again.";
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['message'] = implode("<br>", $errors);
            $_SESSION['message_type'] = "error";
        }
    }
    elseif (isset($_POST['edit_tree'])) {
        $id = intval($_POST['id']);
        $reserve_id = intval($_POST['reserve_id']);
        $species = sanitize_input($_POST['species']);
        $MTH = floatval($_POST['MTH']);
        $THT = floatval($_POST['THT']);
        $DBH = floatval($_POST['DBH']);
        $basal_area = floatval($_POST['basal_area']);
        $volume = floatval($_POST['volume']);
        $status = sanitize_input($_POST['status']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        
        // Validation
        $errors = [];
        
        if (empty($reserve_id)) {
            $errors[] = "Reserve is required.";
        }
        
        if (empty($species)) {
            $errors[] = "Species is required.";
        }
        
        if (empty($errors)) {
            global $conn;
            $sql = "UPDATE trees 
                    SET reserve_id = ?, species = ?, MTH = ?, THT = ?, DBH = ?, basal_area = ?, volume = ?, latitude = ?, longitude = ?, status = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isdddddddsi", $reserve_id, $species, $MTH, $THT, $DBH, $basal_area, $volume, $latitude, $longitude, $status, $id);
            
            if ($stmt->execute()) {
                // Log activity
                log_activity($_SESSION['user_id'], 'tree_edit', 'Edited tree: ' . $species . ' (ID: ' . $id . ')');
                
                $_SESSION['message'] = "Tree updated successfully.";
                $_SESSION['message_type'] = "success";
                redirect('admin/trees.php' . ($reserve_id ? '?reserve_id=' . $reserve_id : ''));
            } else {
                $errors[] = "Failed to update tree. Please try again.";
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
    // First get tree details for logging
    $sql = "SELECT t.id, t.species, r.reserve_name 
            FROM trees t
            JOIN forest_reserves r ON t.reserve_id = r.id
            WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tree = $result->fetch_assoc();
        
        // Delete the tree
        $sql = "DELETE FROM trees WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Log activity
            log_activity($_SESSION['user_id'], 'tree_delete', 'Deleted tree: ' . $tree['species'] . ' from ' . $tree['reserve_name'] . ' (ID: ' . $id . ')');
            
            $_SESSION['message'] = "Tree deleted successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Failed to delete tree.";
            $_SESSION['message_type'] = "error";
        }
    }
    
    redirect('admin/trees.php' . ($reserve_id ? '?reserve_id=' . $reserve_id : ''));
}

// Build the query based on filter
$sql = "SELECT t.*, r.reserve_name 
        FROM trees t
        JOIN forest_reserves r ON t.reserve_id = r.id";

$where_clauses = [];
$params = [];
$types = "";

if ($reserve_id) {
    $where_clauses[] = "t.reserve_id = ?";
    $params[] = $reserve_id;
    $types .= "i";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$trees = $stmt->get_result();

// Get all forest reserves for dropdown
$sql = "SELECT * FROM forest_reserves ORDER BY reserve_name";
$reserves = $conn->query($sql);
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="display-5 fw-bold">Tree Inventory</h1>
            <p class="lead">Manage tree inventory across all forest reserves</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addTreeModal">
                <i class="fas fa-plus me-2"></i>Add New Tree
            </button>
        </div>
    </div>
    
    <!-- Filter by Reserve -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0"><i class="fas fa-filter me-2 text-success"></i>Filter by Reserve</h5>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="reserveFilter" onchange="window.location.href='trees.php?reserve_id=' + this.value">
                                <option value="">All Reserves</option>
                                <?php
                                $reserves->data_seek(0);
                                while ($reserve = $reserves->fetch_assoc()): ?>
                                    <option value="<?php echo $reserve['id']; ?>" <?php echo ($reserve_id == $reserve['id']) ? 'selected' : ''; ?>>
                                        <?php echo $reserve['reserve_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($trees->num_rows > 0): ?>
        <div class="card border-0 shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Reserve</th>
                                <th>Species</th>
                                <th>MHT (m)</th>
                                <th>THT (m)</th>
                                <th>DBH (m)</th>
                                <th>Volume (m³)</th>
                                <th>Coordinates</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($tree = $trees->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo str_pad($tree['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo $tree['reserve_name']; ?></td>
                                    <td><?php echo $tree['species']; ?></td>
                                    <td><?php echo $tree['MTH']; ?></td>
                                    <td><?php echo $tree['THT']; ?></td>
                                    <td><?php echo $tree['DBH']; ?></td>
                                    <td><?php echo $tree['volume']; ?></td>
                                    <td><?php echo $tree['latitude']; ?>, <?php echo $tree['longitude']; ?></td>
                                    <td>
                                        <?php if ($tree['status'] === 'available'): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Sold</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    data-bs-toggle="modal" data-bs-target="#editTreeModal<?php echo $tree['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="trees.php?delete=<?php echo $tree['id']; ?><?php echo $reserve_id ? '&reserve_id=' . $reserve_id : ''; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this tree? This action cannot be undone.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Edit Tree Modal -->
                                <div class="modal fade" id="editTreeModal<?php echo $tree['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Tree - <?php echo $tree['species']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="trees.php<?php echo $reserve_id ? '?reserve_id=' . $reserve_id : ''; ?>">
                                                <div class="modal-body">
                                                    <input type="hidden" name="edit_tree" value="1">
                                                    <input type="hidden" name="id" value="<?php echo $tree['id']; ?>">
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="reserve_id_<?php echo $tree['id']; ?>" class="form-label">Forest Reserve</label>
                                                            <select class="form-select" id="reserve_id_<?php echo $tree['id']; ?>" name="reserve_id" required>
                                                                <option value="">Select a reserve</option>
                                                                <?php
                                                                $reserves->data_seek(0);
                                                                while ($reserve = $reserves->fetch_assoc()): ?>
                                                                    <option value="<?php echo $reserve['id']; ?>" <?php echo ($tree['reserve_id'] == $reserve['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo $reserve['reserve_name']; ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="species_<?php echo $tree['id']; ?>" class="form-label">Species</label>
                                                            <input type="text" class="form-control" id="species_<?php echo $tree['id']; ?>" 
                                                                   name="species" value="<?php echo $tree['species']; ?>" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="MTH_<?php echo $tree['id']; ?>" class="form-label">MHT (m)</label>
                                                            <input type="number" step="0.01" class="form-control" id="MTH_<?php echo $tree['id']; ?>" 
                                                                   name="MTH" value="<?php echo $tree['MTH']; ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="THT_<?php echo $tree['id']; ?>" class="form-label">THT (m)</label>
                                                            <input type="number" step="0.01" class="form-control" id="THT_<?php echo $tree['id']; ?>" 
                                                                   name="THT" value="<?php echo $tree['THT']; ?>" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="DBH_<?php echo $tree['id']; ?>" class="form-label">DBH (m)</label>
                                                            <input type="number" step="0.01" class="form-control" id="DBH_<?php echo $tree['id']; ?>" 
                                                                   name="DBH" value="<?php echo $tree['DBH']; ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="basal_area_<?php echo $tree['id']; ?>" class="form-label">Basal Area (m²)</label>
                                                            <input type="number" step="0.0001" class="form-control" id="basal_area_<?php echo $tree['id']; ?>" 
                                                                   name="basal_area" value="<?php echo $tree['basal_area']; ?>" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="volume_<?php echo $tree['id']; ?>" class="form-label">Volume (m³)</label>
                                                            <input type="number" step="0.0001" class="form-control" id="volume_<?php echo $tree['id']; ?>" 
                                                                   name="volume" value="<?php echo $tree['volume']; ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="status_<?php echo $tree['id']; ?>" class="form-label">Status</label>
                                                            <select class="form-select" id="status_<?php echo $tree['id']; ?>" name="status" required>
                                                                <option value="available" <?php echo ($tree['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                                                <option value="sold" <?php echo ($tree['status'] === 'sold') ? 'selected' : ''; ?>>Sold</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="latitude_<?php echo $tree['id']; ?>" class="form-label">Latitude</label>
                                                            <input type="number" step="0.00000001" class="form-control" id="latitude_<?php echo $tree['id']; ?>" 
                                                                   name="latitude" value="<?php echo $tree['latitude']; ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="longitude_<?php echo $tree['id']; ?>" class="form-label">Longitude</label>
                                                            <input type="number" step="0.00000001" class="form-control" id="longitude_<?php echo $tree['id']; ?>" 
                                                                   name="longitude" value="<?php echo $tree['longitude']; ?>" required>
                                                        </div>
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
                <i class="fas fa-tree fa-3x text-muted mb-3"></i>
                <h4>No trees found</h4>
                <p class="text-muted mb-4">
                    <?php if ($reserve_id): ?>
                        No trees found in <?php echo $reserves->data_seek(0); $reserves->fetch_assoc()['reserve_name']; ?>.
                    <?php else: ?>
                        No trees have been added yet.
                    <?php endif; ?>
                </p>
                <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addTreeModal">
                    <i class="fas fa-plus me-2"></i>Add First Tree
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Tree Modal -->
<div class="modal fade" id="addTreeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Tree</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="trees.php<?php echo $reserve_id ? '?reserve_id=' . $reserve_id : ''; ?>">
                <div class="modal-body">
                    <input type="hidden" name="add_tree" value="1">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reserve_id" class="form-label">Forest Reserve</label>
                            <select class="form-select" id="reserve_id" name="reserve_id" required>
                                <option value="">Select a reserve</option>
                                <?php
                                $reserves->data_seek(0);
                                while ($reserve = $reserves->fetch_assoc()): ?>
                                    <option value="<?php echo $reserve['id']; ?>" <?php echo ($reserve_id == $reserve['id']) ? 'selected' : ''; ?>>
                                        <?php echo $reserve['reserve_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="species" class="form-label">Species</label>
                            <input type="text" class="form-control" id="species" name="species" 
                                   placeholder="Enter tree species" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="MTH" class="form-label">MHT (m)</label>
                            <input type="number" step="0.01" class="form-control" id="MTH" name="MTH" 
                                   placeholder="Enter MHT" required>
                        </div>
                        <div class="col-md-6">
                            <label for="THT" class="form-label">THT (m)</label>
                            <input type="number" step="0.01" class="form-control" id="THT" name="THT" 
                                   placeholder="Enter THT" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="DBH" class="form-label">DBH (m)</label>
                            <input type="number" step="0.01" class="form-control" id="DBH" name="DBH" 
                                   placeholder="Enter DBH" required>
                        </div>
                        <div class="col-md-6">
                            <label for="basal_area" class="form-label">Basal Area (m²)</label>
                            <input type="number" step="0.0001" class="form-control" id="basal_area" name="basal_area" 
                                   placeholder="Enter basal area" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="volume" class="form-label">Volume (m³)</label>
                            <input type="number" step="0.0001" class="form-control" id="volume" name="volume" 
                                   placeholder="Enter volume" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="available">Available</option>
                                <option value="sold">Sold</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="number" step="0.00000001" class="form-control" id="latitude" name="latitude" 
                                   placeholder="Enter latitude" required>
                        </div>
                        <div class="col-md-6">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="number" step="0.00000001" class="form-control" id="longitude" name="longitude" 
                                   placeholder="Enter longitude" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Add Tree</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>