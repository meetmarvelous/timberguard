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
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-4 fw-bold mb-4">Our Forest Reserves</h1>
            <p class="lead">Explore our network of sustainably managed forest reserves across Nigeria</p>
        </div>
    </div>
    
    <?php if ($reserves->num_rows > 0): ?>
        <div class="row">
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
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="row g-0 h-100">
                            <div class="col-md-4">
                                <img src="<?php echo BASE_URL; ?>assets/img/placeholder/forest.jpg" class="img-fluid h-100 rounded-start" style="object-fit: cover;" alt="<?php echo $reserve['reserve_name']; ?>">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body d-flex flex-column">
                                    <div>
                                        <h5 class="card-title"><?php echo $reserve['reserve_name']; ?></h5>
                                        <p class="card-text text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $reserve['location']; ?></p>
                                        <p class="card-text"><?php echo substr($reserve['description'], 0, 150) . (strlen($reserve['description']) > 150 ? '...' : ''); ?></p>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="badge bg-success"><i class="fas fa-tree me-1"></i> <?php echo $tree_count['available']; ?> Available</span>
                                            <span class="badge bg-danger"><i class="fas fa-tree me-1"></i> <?php echo $tree_count['sold']; ?> Sold</span>
                                        </div>
                                        <a href="reserve.php?id=<?php echo $reserve['id']; ?>" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-leaf me-1"></i> Explore Reserve
                                        </a>
                                    </div>
                                </div>
                            </div>
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
                <p class="text-muted">No forest reserves have been added to the system yet.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>