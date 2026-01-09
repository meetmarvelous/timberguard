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

<div class="container py-4 py-md-5">
    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="display-6 fw-bold mb-1">Admin Dashboard</h1>
            <p class="lead text-muted mb-0">Manage forest reserves, timber inventory, and user transactions</p>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card dashboard-stat dashboard-stat-forest h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-leaf" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $total_reserves; ?></h3>
                    <p>Forest Reserves</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-stat dashboard-stat-trees h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-tree" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $available_trees; ?></h3>
                    <p>Available Trees</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-stat dashboard-stat-sold h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $sold_trees; ?></h3>
                    <p>Sold Trees</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-stat dashboard-stat-warning h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $pending_reports; ?></h3>
                    <p>Pending Reports</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Main Content Column -->
        <div class="col-12 col-lg-8">
            <!-- Recent Transactions Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success" aria-hidden="true"></i>Recent Transactions</h5>
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
                                        <th class="hide-mobile">Reserve</th>
                                        <th class="hide-mobile">Tree ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="hide-mobile">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($transaction['payment_code']); ?></code></td>
                                            <td><?php echo htmlspecialchars($transaction['user_name']); ?></td>
                                            <td class="hide-mobile"><?php echo htmlspecialchars($transaction['reserve_name']); ?></td>
                                            <td class="hide-mobile"><?php echo str_pad($transaction['tree_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><strong><?php echo format_currency($transaction['amount']); ?></strong></td>
                                            <td>
                                                <?php if ($transaction['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Failed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="hide-mobile"><?php echo date('M j, Y', strtotime($transaction['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt" aria-hidden="true"></i>
                            <h4>No transactions yet</h4>
                            <p>Transactions will appear here once customers make purchases.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Reports Card -->
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning" aria-hidden="true"></i>Recent Illegal Reports</h5>
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
                                        <th class="hide-mobile">Coordinates</th>
                                        <th class="hide-mobile">Description</th>
                                        <th>Status</th>
                                        <th class="hide-mobile">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($report = $recent_reports->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                                            <td><?php echo htmlspecialchars($report['reserve_name']); ?></td>
                                            <td class="hide-mobile">
                                                <small><?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?></small>
                                            </td>
                                            <td class="hide-mobile">
                                                <div class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($report['description']); ?>">
                                                    <?php echo htmlspecialchars($report['description']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($report['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Resolved</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="hide-mobile"><?php echo date('M j, Y', strtotime($report['date_reported'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                            <h4>No reports yet</h4>
                            <p>Illegal logging reports will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Column -->
        <div class="col-12 col-lg-4">
            <!-- Transaction Statistics Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-success" aria-hidden="true"></i>Transaction Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Transactions:</span>
                        <span class="fw-bold"><?php echo $total_transactions; ?></span>
                    </div>
                    <?php if ($total_transactions > 0): ?>
                    <div class="progress mb-4" style="height: 12px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo round(($completed_transactions / $total_transactions) * 100); ?>%" aria-valuenow="<?php echo round(($completed_transactions / $total_transactions) * 100); ?>" aria-valuemin="0" aria-valuemax="100">
                        </div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo round(($failed_transactions / $total_transactions) * 100); ?>%" aria-valuenow="<?php echo round(($failed_transactions / $total_transactions) * 100); ?>" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-success"><i class="fas fa-check-circle me-1" aria-hidden="true"></i> Completed:</span>
                        <span class="fw-bold"><?php echo $completed_transactions; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-danger"><i class="fas fa-times-circle me-1" aria-hidden="true"></i> Failed:</span>
                        <span class="fw-bold"><?php echo $failed_transactions; ?></span>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3"><i class="fas fa-money-bill-wave me-2 text-success" aria-hidden="true"></i>Revenue Summary</h6>
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
                        <span class="fw-bold text-success"><?php echo format_currency($total_revenue); ?></span>
                    </div>
                    
                    <?php if ($revenue_by_reserve->num_rows > 0): ?>
                        <h6 class="mt-4 mb-3 text-muted">Top Reserves:</h6>
                        <?php while ($reserve = $revenue_by_reserve->fetch_assoc()): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($reserve['reserve_name']); ?></span>
                                <span class="fw-bold"><?php echo format_currency($reserve['revenue']); ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card border-0 shadow quick-actions">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2 text-success" aria-hidden="true"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="reserves.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-leaf" aria-hidden="true"></i>
                                <span>Reserves</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="trees.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-tree" aria-hidden="true"></i>
                                <span>Trees</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="transactions.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-receipt" aria-hidden="true"></i>
                                <span>Transactions</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="reports.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-flag" aria-hidden="true"></i>
                                <span>Reports</span>
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="profile.php" class="btn btn-success w-100">
                                <i class="fas fa-user-cog" aria-hidden="true"></i>
                                <span>Manage Profile</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>