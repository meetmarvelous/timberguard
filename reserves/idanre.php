<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$page_title = "Idanre Forest Reserve - Timber Inventory";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Forest Reserves', 'url' => '#', 'active' => false],
    ['title' => 'Idanre Forest Reserve', 'url' => '', 'active' => true]
];

// Get available trees from Idanre Forest Reserve
global $conn;
$forest_reserve_id = 1; // Assuming Idanre is the first reserve

$sql = "SELECT t.*, r.reserve_name 
        FROM trees t 
        JOIN forest_reserves r ON t.reserve_id = r.id 
        WHERE t.reserve_id = ? AND t.status = 'available'
        ORDER BY t.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $forest_reserve_id);
$stmt->execute();
$result = $stmt->get_result();
$trees = [];
while ($row = $result->fetch_assoc()) {
    $trees[] = $row;
}

// Get reserve details
$sql = "SELECT * FROM forest_reserves WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $forest_reserve_id);
$stmt->execute();
$reserve = $stmt->get_result()->fetch_assoc();
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold"><?php echo $reserve['reserve_name']; ?></h1>
            <p class="lead"><?php echo $reserve['description']; ?></p>
            <div class="d-flex gap-2 mt-3">
                <span class="badge bg-success"><i class="fas fa-tree me-1"></i> <?php echo count($trees); ?> Available Trees</span>
                <span class="badge bg-primary"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $reserve['location']; ?></span>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="#" class="btn btn-success btn-lg px-4">
                <i class="fas fa-question-circle me-2"></i>Report Illegal Activity
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <ul class="nav nav-tabs" id="reserveTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="available-tab" data-bs-toggle="tab" data-bs-target="#available" type="button" role="tab" aria-controls="available" aria-selected="true">
                        <i class="fas fa-check-circle me-1 text-success"></i>Available Trees
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sold-tab" data-bs-toggle="tab" data-bs-target="#sold" type="button" role="tab" aria-controls="sold" aria-selected="false">
                        <i class="fas fa-times-circle me-1 text-danger"></i>Sold Trees
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
            <?php if (empty($trees)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Currently, there are no available trees in this reserve.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($trees as $tree): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card tree-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-success">Available</span>
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
                                                <th>DBH (m):</th>
                                                <td><?php echo $tree['DBH']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Basal Area (m²):</th>
                                                <td><?php echo $tree['basal_area']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Volume (m³):</th>
                                                <td><?php echo $tree['volume']; ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-success">Price:</th>
                                                <td class="text-success fw-bold"><?php echo format_currency(calculate_tree_price($tree['volume'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <?php if (is_logged_in() && has_role('customer')): ?>
                                        <a href="checkout.php?tree_id=<?php echo $tree['id']; ?>" class="btn btn-success w-100">
                                            <i class="fas fa-shopping-cart me-2"></i>Buy Now
                                        </a>
                                    <?php else: ?>
                                        <a href="../login.php" class="btn btn-success w-100">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sold Trees Tab -->
        <div class="tab-pane fade" id="sold" role="tabpanel" aria-labelledby="sold-tab">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>This section will display sold trees once transactions are completed.
            </div>
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
                                        <h3><?php echo count($trees); ?></h3>
                                        <p>Available Trees</p>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="dashboard-stat sold">
                                        <i class="fas fa-tree"></i>
                                        <h3>0</h3>
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

<?php include '../includes/footer.php'; ?>