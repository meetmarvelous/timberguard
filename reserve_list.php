<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = "Forest Reserves";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Forest Reserves', 'url' => '', 'active' => true]
];

// Get all forest reserves
global $conn;
$sql = "SELECT * FROM forest_reserves ORDER BY reserve_name";
$reserves = $conn->query($sql);

// Get tree count for each reserve
$tree_counts = [];
$sql = "SELECT reserve_id, 
               SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
               SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
               COUNT(*) as total
        FROM trees 
        GROUP BY reserve_id";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $tree_counts[$row['reserve_id']] = $row;
}
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h1 class="display-4 fw-bold mb-4">Our Forest Reserves</h1>
            <p class="lead">Explore our sustainably managed forest reserves across Nigeria</p>
        </div>
    </div>
    
    <?php if ($reserves->num_rows > 0): ?>
        <div class="row">
            <?php while ($reserve = $reserves->fetch_assoc()): ?>
                <?php 
                $count = $tree_counts[$reserve['id']] ?? ['available' => 0, 'sold' => 0, 'total' => 0];
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow">
                        <div class="position-relative">
                            <img src="<?php echo BASE_URL; ?>assets/img/placeholder/forest.jpg" class="card-img-top" alt="<?php echo $reserve['reserve_name']; ?>" style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 start-0 bg-success text-white px-3 py-2 m-3 rounded">
                                <i class="fas fa-leaf me-1"></i> <?php echo $count['available']; ?> Trees
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $reserve['reserve_name']; ?></h5>
                            <p class="card-text text-muted"><i class="fas fa-map-marker-alt me-2"></i> <?php echo $reserve['location']; ?></p>
                            
                            <div class="mb-3">
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $count['total'] > 0 ? ($count['available'] / $count['total']) * 100 : 0; ?>%" title="<?php echo $count['available']; ?> available"></div>
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $count['total'] > 0 ? ($count['sold'] / $count['total']) * 100 : 0; ?>%" title="<?php echo $count['sold']; ?> sold"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-success"><?php echo $count['available']; ?> Available</small>
                                    <small class="text-danger"><?php echo $count['sold']; ?> Sold</small>
                                </div>
                            </div>
                            
                            <p class="card-text"><?php echo substr($reserve['description'], 0, 120) . (strlen($reserve['description']) > 120 ? '...' : ''); ?></p>
                            
                            <a href="reserve.php?id=<?php echo $reserve['id']; ?>" class="btn btn-success w-100">
                                <i class="fas fa-leaf me-2"></i>View Trees
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-leaf fa-3x text-muted mb-3"></i>
                <h4>No forest reserves found</h4>
                <p class="text-muted">No forest reserves have been added yet.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>