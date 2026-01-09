<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin or forest manager login
require_login();
if (!has_role('admin') && !has_role('forest manager')) {
    $_SESSION['message'] = "You don't have permission to access the admin dashboard.";
    $_SESSION['message_type'] = "error";
    redirect('index.php');
}

$page_title = "Admin Dashboard";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Admin Dashboard', 'url' => '', 'active' => true]
];

// Get dashboard statistics
global $conn;

// Total reserves
$sql = "SELECT COUNT(*) as total FROM forest_reserves";
$total_reserves = $conn->query($sql)->fetch_assoc()['total'];

// Total trees
$sql = "SELECT COUNT(*) as total FROM trees";
$total_trees = $conn->query($sql)->fetch_assoc()['total'];

// Available trees
$sql = "SELECT COUNT(*) as total FROM trees WHERE status = 'available'";
$available_trees = $conn->query($sql)->fetch_assoc()['total'];

// Sold trees
$sql = "SELECT COUNT(*) as total FROM trees WHERE status = 'sold'";
$sold_trees = $conn->query($sql)->fetch_assoc()['total'];

// Total transactions
$sql = "SELECT COUNT(*) as total FROM transactions";
$total_transactions = $conn->query($sql)->fetch_assoc()['total'];

// Completed transactions
$sql = "SELECT COUNT(*) as total FROM transactions WHERE status = 'completed'";
$completed_transactions = $conn->query($sql)->fetch_assoc()['total'];

// Failed transactions
$sql = "SELECT COUNT(*) as total FROM transactions WHERE status = 'failed'";
$failed_transactions = $conn->query($sql)->fetch_assoc()['total'];

// Total reports
$sql = "SELECT COUNT(*) as total FROM illegal_reports";
$total_reports = $conn->query($sql)->fetch_assoc()['total'];

// Pending reports
$sql = "SELECT COUNT(*) as total FROM illegal_reports WHERE status = 'pending'";
$pending_reports = $conn->query($sql)->fetch_assoc()['total'];

// Recent transactions
$sql = "SELECT t.*, u.name as user_name, r.reserve_name, trs.species, trs.volume 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN forest_reserves r ON t.reserve_id = r.id
        JOIN trees trs ON t.tree_id = trs.id
        ORDER BY t.created_at DESC
        LIMIT 5";
$recent_transactions = $conn->query($sql);

// Recent reports
$sql = "SELECT ir.*, r.reserve_name 
        FROM illegal_reports ir
        JOIN forest_reserves r ON ir.reserve_id = r.id
        ORDER BY ir.date_reported DESC
        LIMIT 5";
$recent_reports = $conn->query($sql);
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-5 fw-bold">Admin Dashboard</h1>
            <p class="lead">Manage forest reserves, timber inventory, and user transactions</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #4caf50, #2e7d32);">
                <div class="card-body">
                    <i class="fas fa-leaf fa-3x mb-3"></i>
                    <h3><?php echo $total_reserves; ?></h3>
                    <p>Forest Reserves</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #8bc34a, #558b2f);">
                <div class="card-body">
                    <i class="fas fa-tree fa-3x mb-3"></i>
                    <h3><?php echo $available_trees; ?></h3>
                    <p>Available Trees</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #f44336, #b71c1c);">
                <div class="card-body">
                    <i class="fas fa-tree fa-3x mb-3"></i>
                    <h3><?php echo $sold_trees; ?></h3>
                    <p>Sold Trees</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #2196f3, #0d47a1);">
                <div class="card-body">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h3><?php echo $pending_reports; ?></h3>
                    <p>Pending Reports</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success"></i>Recent Transactions</h5>
                    <a href="transactions.php" class="btn btn-sm btn-outline-success">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($recent_transactions->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Code</th>
                                        <th>Customer</th>
                                        <th>Reserve</th>
                                        <th>Tree ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $transaction['payment_code']; ?></td>
                                            <td><?php echo $transaction['user_name']; ?></td>
                                            <td><?php echo $transaction['reserve_name']; ?></td>
                                            <td><?php echo str_pad($transaction['tree_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo format_currency($transaction['amount']); ?></td>
                                            <td>
                                                <?php if ($transaction['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Failed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($transaction['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-receipt fa-3x mb-3"></i>
                            <p>No transactions yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-success"></i>Recent Illegal Reports</h5>
                    <a href="reports.php" class="btn btn-sm btn-outline-success">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($recent_reports->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reporter</th>
                                        <th>Reserve</th>
                                        <th>Coordinates</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($report = $recent_reports->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $report['reporter_name']; ?></td>
                                            <td><?php echo $report['reserve_name']; ?></td>
                                            <td><?php echo $report['coordinates']; ?></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="<?php echo $report['description']; ?>">
                                                    <?php echo $report['description']; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($report['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Resolved</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($report['date_reported'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <p>No illegal reports yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-success"></i>Transaction Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Transactions:</span>
                        <span class="fw-bold"><?php echo $total_transactions; ?></span>
                    </div>
                    <div class="progress mb-4" style="height: 20px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $completed_transactions ? round(($completed_transactions / $total_transactions) * 100) : 0; ?>%">
                            <?php echo $completed_transactions ? round(($completed_transactions / $total_transactions) * 100) : 0; ?>%
                        </div>
                        <div class="progress-bar bg-danger" style="width: <?php echo $failed_transactions ? round(($failed_transactions / $total_transactions) * 100) : 0; ?>%">
                            <?php echo $failed_transactions ? round(($failed_transactions / $total_transactions) * 100) : 0; ?>%
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> Completed:</span>
                        <span class="fw-bold"><?php echo $completed_transactions; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-danger"><i class="fas fa-times-circle me-1"></i> Failed:</span>
                        <span class="fw-bold"><?php echo $failed_transactions; ?></span>
                    </div>
                    
                    <h6 class="mb-3"><i class="fas fa-money-bill-wave me-2 text-success"></i>Revenue Summary</h6>
                    <?php
                    // Total revenue
                    $sql = "SELECT SUM(amount) as total_revenue FROM transactions WHERE status = 'completed'";
                    $total_revenue = $conn->query($sql)->fetch_assoc()['total_revenue'] ?: 0;
                    
                    // Revenue by reserve
                    $sql = "SELECT r.reserve_name, SUM(t.amount) as revenue 
                            FROM transactions t
                            JOIN forest_reserves r ON t.reserve_id = r.id
                            WHERE t.status = 'completed'
                            GROUP BY r.id
                            ORDER BY revenue DESC
                            LIMIT 3";
                    $revenue_by_reserve = $conn->query($sql);
                    ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Revenue:</span>
                        <span class="fw-bold"><?php echo format_currency($total_revenue); ?></span>
                    </div>
                    
                    <?php if ($revenue_by_reserve->num_rows > 0): ?>
                        <h6 class="mt-4 mb-3">Top Reserves:</h6>
                        <?php while ($reserve = $revenue_by_reserve->fetch_assoc()): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo $reserve['reserve_name']; ?>:</span>
                                <span><?php echo format_currency($reserve['revenue']); ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-cog me-2 text-success"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="reserves.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-leaf me-2"></i>Manage Reserves
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="trees.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-tree me-2"></i>Manage Trees
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="transactions.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-receipt me-2"></i>View Transactions
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="reports.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>View Reports
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="profile.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-user-cog me-2"></i>Manage Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>