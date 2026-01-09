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
    ['title' => 'Admin Dashboard', 'url' => 'admin/index.php', 'active' => false],
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

<div class="container py-4 py-md-5">
    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="display-6 fw-bold mb-1">Transaction History</h1>
            <p class="lead text-muted mb-0">View and manage all timber transactions</p>
        </div>
        <button type="button" class="btn btn-success btn-lg" onclick="window.print()">
            <i class="fas fa-print me-2" aria-hidden="true"></i>Print Report
        </button>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card dashboard-stat dashboard-stat-forest h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $stats['completed'] ?: 0; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-stat dashboard-stat-sold h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-times-circle" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $stats['failed'] ?: 0; ?></h3>
                    <p>Failed</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-stat dashboard-stat-reports h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-money-bill-wave" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo format_currency($stats['revenue'] ?: 0); ?></h3>
                    <p>Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card dashboard-stat dashboard-stat-trees h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-receipt" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $stats['total'] ?: 0; ?></h3>
                    <p>Total</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Main Content Column -->
        <div class="col-12 col-lg-8">
            <?php if ($transactions->num_rows > 0): ?>
                <div class="card border-0 shadow">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="transactionsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Code</th>
                                        <th>Customer</th>
                                        <th class="hide-mobile">Reserve</th>
                                        <th class="hide-mobile">Tree ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="hide-mobile">Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($transaction['payment_code']); ?></code></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($transaction['user_name']); ?></strong>
                                                <div class="text-muted small hide-mobile"><?php echo htmlspecialchars($transaction['user_email']); ?></div>
                                            </td>
                                            <td class="hide-mobile"><?php echo htmlspecialchars($transaction['reserve_name']); ?></td>
                                            <td class="hide-mobile"><code><?php echo str_pad($transaction['tree_id'], 6, '0', STR_PAD_LEFT); ?></code></td>
                                            <td><strong class="text-success"><?php echo format_currency($transaction['amount']); ?></strong></td>
                                            <td>
                                                <?php if ($transaction['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Failed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="hide-mobile"><?php echo date('M j, Y', strtotime($transaction['created_at'])); ?></td>
                                            <td>
                                                <a href="../payment/receipt.php?payment_code=<?php echo urlencode($transaction['payment_code']); ?>" 
                                                   class="btn btn-sm btn-outline-success" 
                                                   data-bs-toggle="tooltip" title="View Receipt"
                                                   aria-label="View receipt for payment <?php echo htmlspecialchars($transaction['payment_code']); ?>">
                                                    <i class="fas fa-receipt" aria-hidden="true"></i>
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
                    <div class="card-body empty-state">
                        <i class="fas fa-receipt" aria-hidden="true"></i>
                        <h4>No transactions found</h4>
                        <p>No transactions have been made yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar Column -->
        <div class="col-12 col-lg-4">
            <!-- Revenue by Reserve Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-success" aria-hidden="true"></i>Revenue by Reserve</h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($transactions_by_reserve->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($reserve = $transactions_by_reserve->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                    <div>
                                        <strong><?php echo htmlspecialchars($reserve['reserve_name']); ?></strong>
                                        <div class="text-muted small">
                                            <?php echo $reserve['completed_transactions']; ?> completed transactions
                                        </div>
                                    </div>
                                    <span class="fw-bold text-success"><?php echo format_currency($reserve['revenue']); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <p class="mb-0">No transaction data available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Transaction Info Card -->
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-success" aria-hidden="true"></i>Transaction Information</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">This section displays all transactions made through the TimberGuard platform.</p>
                    
                    <h6 class="text-uppercase text-muted small mb-3">Key Features</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start mb-3">
                            <i class="fas fa-check-circle text-success me-3 mt-1" aria-hidden="true"></i>
                            <span>View all completed and failed transactions</span>
                        </li>
                        <li class="d-flex align-items-start mb-3">
                            <i class="fas fa-check-circle text-success me-3 mt-1" aria-hidden="true"></i>
                            <span>Track revenue by forest reserve</span>
                        </li>
                        <li class="d-flex align-items-start mb-3">
                            <i class="fas fa-check-circle text-success me-3 mt-1" aria-hidden="true"></i>
                            <span>Generate printable reports for accounting</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-3 mt-1" aria-hidden="true"></i>
                            <span>View detailed transaction receipts</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_scripts = "
<script>
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            order: [[6, 'desc']],
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [7] }
            ],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search transactions...'
            }
        });
    });
</script>
";
include '../includes/footer.php'; 
?>