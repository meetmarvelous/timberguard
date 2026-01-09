<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Check if reserve ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid reserve selection.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$reserve_id = intval($_GET['id']);

// Get reserve details
global $conn;
$sql = "SELECT * FROM forest_reserves WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reserve_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "The selected forest reserve does not exist.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$reserve = $result->fetch_assoc();

// Get available trees from this reserve
$sql = "SELECT * 
        FROM trees 
        WHERE reserve_id = ? AND status = 'available'
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reserve_id);
$stmt->execute();
$available_trees = $stmt->get_result();

// Get sold trees from this reserve
$sql = "SELECT * 
        FROM trees 
        WHERE reserve_id = ? AND status = 'sold'
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reserve_id);
$stmt->execute();
$sold_trees = $stmt->get_result();

// Get tree statistics
$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold
        FROM trees 
        WHERE reserve_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reserve_id);
$stmt->execute();
$tree_stats = $stmt->get_result()->fetch_assoc();

$page_title = $reserve['reserve_name'];
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Forest Reserves', 'url' => 'reserve_list.php', 'active' => false],
    ['title' => $reserve['reserve_name'], 'url' => '', 'active' => true]
];
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold"><?php echo $reserve['reserve_name']; ?></h1>
            <p class="lead"><?php echo $reserve['description']; ?></p>
            <div class="d-flex gap-2 mt-3">
                <span class="badge bg-success"><i class="fas fa-tree me-1"></i> <?php echo $tree_stats['available']; ?> Available Trees</span>
                <span class="badge bg-primary"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $reserve['location']; ?></span>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <?php if (is_logged_in() && has_role('customer')): ?>
                <a href="#" class="btn btn-success btn-lg px-4" data-bs-toggle="modal" data-bs-target="#reportModal">
                    <i class="fas fa-bullhorn me-2"></i>Report Illegal Activity
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-success btn-lg px-4">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Report
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <ul class="nav nav-tabs" id="reserveTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="available-tab" data-bs-toggle="tab" data-bs-target="#available" type="button" role="tab" aria-controls="available" aria-selected="true">
                        <i class="fas fa-check-circle me-1 text-success"></i>Available Trees (<?php echo $tree_stats['available']; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sold-tab" data-bs-toggle="tab" data-bs-target="#sold" type="button" role="tab" aria-controls="sold" aria-selected="false">
                        <i class="fas fa-times-circle me-1 text-danger"></i>Sold Trees (<?php echo $tree_stats['sold']; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="false">
                        <i class="fas fa-info-circle me-1"></i>Reserve Information
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="reserveTabsContent">
        <!-- Available Trees Tab -->
        <div class="tab-pane fade show active" id="available" role="tabpanel" aria-labelledby="available-tab">
            <?php if ($available_trees->num_rows > 0): ?>
                <div class="row">
                    <?php while ($tree = $available_trees->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card tree-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-success">Available</span>
                                        <span class="text-muted"><i class="fas fa-tree me-1"></i> <em><?php echo $tree['species']; ?></em> </span>
                                    </div>
                                    <h5 class="card-title mb-3">Tree ID: <?php echo str_pad($tree['id'], 6, '0', STR_PAD_LEFT); ?></h5>

                                    <div class="mb-3">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="50%">MHT (m):</th>
                                                <td><?php echo $tree['MTH']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>THT (m):</th>
                                                <td><?php echo $tree['THT']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>DBH (cm):</th>
                                                <td><?php echo $tree['DBH'] * 100; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Basal Area (m²):</th>
                                                <td><?php echo $tree['basal_area']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Volume (m³):</th>
                                                <td><?php echo $tree['volume']; ?></td>
                                            </tr>
                                            <!-- Added Latitude and Longitude between Volume and Price -->
                                            <tr>
                                                <th>Coordinates:</th>
                                                <td>
                                                    <span class="d-block">Lat: <?php echo $tree['latitude']; ?></span>
                                                    <span class="d-block">Long: <?php echo $tree['longitude']; ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-success">Price:</th>
                                                <td class="text-success fw-bold"><?php echo format_currency(calculate_tree_price($tree['volume'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <?php if (is_logged_in() && has_role('customer')): ?>
                                        <a href="checkout.php?tree_id=<?php echo $tree['id']; ?>&reserve_id=<?php echo $reserve_id; ?>" class="btn btn-success w-100">
                                            <i class="fas fa-shopping-cart me-2"></i>Buy Now
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-success w-100">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Currently, there are no available trees in this reserve.
                </div>
            <?php endif; ?>
        </div>

        <!-- Sold Trees Tab -->
        <div class="tab-pane fade" id="sold" role="tabpanel" aria-labelledby="sold-tab">
            <?php if ($sold_trees->num_rows > 0): ?>
                <div class="row">
                    <?php while ($tree = $sold_trees->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card tree-card h-100" style="opacity: 0.7;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-danger">Sold</span>
                                        <span class="text-muted"><i class="fas fa-tree me-1"></i> <?php echo $tree['species']; ?></span>
                                    </div>
                                    <h5 class="card-title mb-3">Tree ID: <?php echo str_pad($tree['id'], 6, '0', STR_PAD_LEFT); ?></h5>

                                    <div class="mb-3">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="50%">MHT (m):</th>
                                                <td><?php echo $tree['MTH']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>THT (m):</th>
                                                <td><?php echo $tree['THT']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>DBH (cm):</th>
                                                <td><?php echo $tree['DBH'] * 100; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Basal Area (m²):</th>
                                                <td><?php echo $tree['basal_area']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Volume (m³):</th>
                                                <td><?php echo $tree['volume']; ?></td>
                                            </tr>
                                            <!-- Added Latitude and Longitude between Volume and Price -->
                                            <tr>
                                                <th>Coordinates:</th>
                                                <td>
                                                    <span class="d-block">Lat: <?php echo $tree['latitude']; ?></span>
                                                    <span class="d-block">Long: <?php echo $tree['longitude']; ?></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <button class="btn btn-outline-secondary w-100" disabled>
                                        <i class="fas fa-times-circle me-2"></i>Sold
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No trees have been sold from this reserve yet.
                </div>
            <?php endif; ?>
        </div>

        <!-- Reserve Information Tab -->
        <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4"><i class="fas fa-info-circle me-2 text-success"></i>Reserve Details</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Reserve Name:</th>
                                    <td><?php echo $reserve['reserve_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td><?php echo $reserve['location']; ?></td>
                                </tr>
                                <tr>
                                    <th>Established:</th>
                                    <td><?php echo date('F j, Y', strtotime($reserve['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Managed By:</th>
                                    <td>University of Ibadan Forestry Department</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4"><i class="fas fa-chart-line me-2 text-success"></i>Forest Statistics</h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="dashboard-stat available">
                                        <i class="fas fa-tree"></i>
                                        <h3><?php echo $tree_stats['available']; ?></h3>
                                        <p>Available Trees</p>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="dashboard-stat sold">
                                        <i class="fas fa-tree"></i>
                                        <h3><?php echo $tree_stats['sold']; ?></h3>
                                        <p>Sold Trees</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="dashboard-stat">
                                        <i class="fas fa-ruler-combined"></i>
                                        <h3>18,168</h3>
                                        <p>Hectares</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="dashboard-stat">
                                        <i class="fas fa-seedling"></i>
                                        <h3>200+</h3>
                                        <p>Tree Species</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="fas fa-map-marked-alt me-2 text-success"></i>Location Map</h5>
                    <div class="ratio ratio-16x9">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="border-radius: 8px;">
                            <p class="text-muted"><i class="fas fa-map-marked-alt me-2"></i>Map will be displayed here (GIS integration)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Illegal Activity Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Illegal Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="user/report.php">
                <div class="modal-body">
                    <input type="hidden" name="reserve_id" value="<?php echo $reserve_id; ?>">

                    <div class="mb-3">
                        <label for="reporter_name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="reporter_name" name="reporter_name"
                            value="<?php echo $_SESSION['name']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="coordinates" class="form-label">Coordinates (Latitude, Longitude)</label>
                        <input type="text" class="form-control" id="coordinates" name="coordinates"
                            placeholder="e.g., 7.562858, 5.205806" required>
                        <div class="form-text">You can use Google Maps to find the exact coordinates of the location.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description of Activity</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                            placeholder="Describe the suspicious activity in detail..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>