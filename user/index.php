<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login
require_login();

$page_title = "User Dashboard";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Dashboard', 'url' => '', 'active' => true]
];

// Get user stats
global $conn;
$user_id = $_SESSION['user_id'];

// Total transactions
$sql = "SELECT COUNT(*) as total FROM transactions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_transactions = $stmt->get_result()->fetch_assoc()['total'];

// Completed transactions
$sql = "SELECT COUNT(*) as completed FROM transactions WHERE user_id = ? AND status = 'completed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_transactions = $stmt->get_result()->fetch_assoc()['completed'];

// Failed transactions
$sql = "SELECT COUNT(*) as failed FROM transactions WHERE user_id = ? AND status = 'failed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$failed_transactions = $stmt->get_result()->fetch_assoc()['failed'];

// Recent transactions
$sql = "SELECT t.*, tr.reserve_name, trs.species, trs.volume 
        FROM transactions t
        JOIN trees trs ON t.tree_id = trs.id
        JOIN forest_reserves tr ON t.reserve_id = tr.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_transactions = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-5 fw-bold">Welcome back, <?php echo $_SESSION['name']; ?></h1>
            <p class="lead">Manage your timber transactions and reports from your dashboard</p>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card dashboard-stat dashboard-stat-success h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $completed_transactions; ?></h3>
                    <p>Completed Transactions</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card dashboard-stat dashboard-stat-danger h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-times-circle" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $failed_transactions; ?></h3>
                    <p>Failed Transactions</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card dashboard-stat dashboard-stat-forest h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-receipt" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $total_transactions; ?></h3>
                    <p>Total Transactions</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Browse Inventory Card (Always Visible) -->
            <div class="card border-0 shadow mb-4 bg-success text-white overflow-hidden position-relative">
                <div class="card-body p-4 position-relative" style="z-index: 1;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold mb-2 text-white">Looking for Timber?</h4>
                            <p class="mb-3 text-white-50">Browse our extensive inventory of high-quality timber from sustainable forest reserves.</p>
                            <a href="../reserve_list.php" class="btn btn-light text-success fw-bold">
                                <i class="fas fa-tree me-2"></i>Browse Timber Inventory
                            </a>
                        </div>
                        <div class="col-md-4 d-none d-md-block text-end">
                            <i class="fas fa-shopping-basket fa-6x text-white" style="opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
                <!-- Decorative background circle -->
                <div class="position-absolute top-0 end-0 translate-middle-y" style="width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; margin-top: -50px; margin-right: -50px;"></div>
            </div>

            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-success"></i>Recent Transactions</h5>
                    <?php if ($recent_transactions->num_rows > 0): ?>
                    <a href="transactions.php" class="btn btn-sm btn-outline-success">View All</a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if ($recent_transactions->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Code</th>
                                        <th>Reserve</th>
                                        <th>Tree ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $transaction['payment_code']; ?></td>
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
                                            <td>
                                                <a href="receipt.php?payment_code=<?php echo $transaction['payment_code']; ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="View Receipt">
                                                    <i class="fas fa-receipt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 80px; height: 80px;">
                                    <i class="fas fa-receipt fa-3x text-muted"></i>
                                </div>
                            </div>
                            <h5 class="text-muted">No transactions yet</h5>
                            <p class="text-muted mb-4">Your purchase history will appear here once you make your first purchase.</p>
                            <a href="../reserve_list.php" class="btn btn-outline-success">
                                <i class="fas fa-search me-2"></i>Find Trees to Buy
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2 text-success"></i>Illegal Logging Reports</h5>
                </div>
                <div class="card-body">
                    <p>Help protect our forests by reporting any suspicious activities.</p>
                    <div class="d-grid">
                        <a href="report.php" class="btn btn-success btn-lg">
                            <i class="fas fa-exclamation-triangle me-2"></i>Report Illegal Activity
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="mb-3">Recent Reports</h6>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Suspicious Activity at Idanre Reserve</h6>
                                    <small class="text-muted">2 days ago</small>
                                </div>
                                <p class="mb-1">Reported unauthorized logging near coordinates X: 7.5, Y: 5.2</p>
                                <small class="text-success"><i class="fas fa-circle me-1"></i> Pending</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Illegal Logging Activity</h6>
                                    <small class="text-muted">1 week ago</small>
                                </div>
                                <p class="mb-1">Multiple trucks observed removing timber without authorization</p>
                                <small class="text-success"><i class="fas fa-circle me-1"></i> Pending</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user me-2 text-success"></i>Account Summary</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="bg-success text-white rounded-circle" style="width: 100px; height: 100px; line-height: 100px; font-size: 2rem;">
                                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                            </div>
                        </div>
                        <h5 class="mt-3 mb-1"><?php echo $_SESSION['name']; ?></h5>
                        <p class="text-muted"><?php echo $_SESSION['email']; ?></p>
                        <span class="badge bg-success">Customer</span>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user me-2"></i>Profile Settings</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="transactions.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-receipt me-2"></i>Transaction History</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $total_transactions; ?></span>
                        </a>
                        <a href="report.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-exclamation-triangle me-2"></i>Report Activity</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-success"></i>Quick Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <div>Always check the tree details before purchasing</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <div>Harvesting must be completed within 14 days of purchase</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <div>Report any suspicious activities to help protect our forests</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <div>Contact forest management for any special harvesting requirements</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>