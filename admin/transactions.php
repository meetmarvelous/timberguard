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

$page_title = "Transaction History";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Admin Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Transaction History', 'url' => '', 'active' => true]
];

// Get all transactions with user and tree details
global $conn;
$sql = "SELECT t.*, u.name as user_name, u.email as user_email, r.reserve_name, trs.species, trs.volume 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN forest_reserves r ON t.reserve_id = r.id
        JOIN trees trs ON t.tree_id = trs.id
        ORDER BY t.created_at DESC";
$transactions = $conn->query($sql);

// Get transaction statistics
$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as revenue
        FROM transactions";
$stats = $conn->query($sql)->fetch_assoc();

// Get transactions by reserve
$sql = "SELECT r.reserve_name, 
               COUNT(*) as total_transactions,
               SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_transactions,
               SUM(CASE WHEN t.status = 'completed' THEN t.amount ELSE 0 END) as revenue
        FROM transactions t
        JOIN forest_reserves r ON t.reserve_id = r.id
        GROUP BY r.id
        ORDER BY revenue DESC";
$transactions_by_reserve = $conn->query($sql);
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="display-5 fw-bold">Transaction History</h1>
            <p class="lead">View and manage all timber transactions</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-success btn-lg" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Report
            </button>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #4caf50, #2e7d32);">
                <div class="card-body">
                    <i class="fas fa-receipt fa-3x mb-3"></i>
                    <h3><?php echo $stats['completed']; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #f44336, #b71c1c);">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-3x mb-3"></i>
                    <h3><?php echo $stats['failed']; ?></h3>
                    <p>Failed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #2196f3, #0d47a1);">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                    <h3><?php echo format_currency($stats['revenue']); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #8bc34a, #558b2f);">
                <div class="card-body">
                    <i class="fas fa-receipt fa-3x mb-3"></i>
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Transactions</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <?php if ($transactions->num_rows > 0): ?>
                <div class="card border-0 shadow">
                    <div class="card-body p-0">
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $transaction['payment_code']; ?></td>
                                            <td>
                                                <strong><?php echo $transaction['user_name']; ?></strong>
                                                <div class="text-muted small"><?php echo $transaction['user_email']; ?></div>
                                            </td>
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
                                                <a href="receipt.php?payment_code=<?php echo $transaction['payment_code']; ?>" 
                                                   class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="View Receipt">
                                                    <i class="fas fa-receipt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h4>No transactions found</h4>
                        <p class="text-muted">No transactions have been made yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-success"></i>Revenue by Reserve</h5>
                </div>
                <div class="card-body">
                    <?php if ($transactions_by_reserve->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($reserve = $transactions_by_reserve->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo $reserve['reserve_name']; ?></strong>
                                        <div class="text-muted small">
                                            <?php echo $reserve['completed_transactions']; ?> completed
                                        </div>
                                    </div>
                                    <span class="fw-bold"><?php echo format_currency($reserve['revenue']); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No transaction data available.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-success"></i>Transaction Information</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">This section displays all transactions made through the TimberGuard platform.</p>
                    
                    <h6 class="mt-4 mb-3">Key Features:</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <div>View all completed and failed transactions</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <div>Track revenue by forest reserve</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <div>Generate printable reports for accounting purposes</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                            <div>View detailed transaction receipts</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>                        