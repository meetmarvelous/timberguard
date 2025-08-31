<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login
require_login();

$page_title = "My Transactions";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'My Transactions', 'url' => '', 'active' => true]
];

// Get user transactions
global $conn;
$user_id = $_SESSION['user_id'];

// Fix: Use prepared statement properly
$sql = "SELECT t.*, r.reserve_name, trs.species, trs.volume 
        FROM transactions t
        JOIN forest_reserves r ON t.reserve_id = r.id
        JOIN trees trs ON t.tree_id = trs.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result();

// Get transaction statistics
$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as spent
        FROM transactions
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="display-5 fw-bold">My Transactions</h1>
            <p class="lead">View your timber purchase history</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="index.php" class="btn btn-success btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
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
                    <h3><?php echo format_currency($stats['spent']); ?></h3>
                    <p>Total Spent</p>
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
    
    <?php if ($transactions->num_rows > 0): ?>
        <div class="card border-0 shadow">
            <div class="card-body p-0">
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
                            <?php while ($transaction = $transactions->fetch_assoc()): ?>
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
                <p class="text-muted">You haven't made any transactions yet.</p>
                <a href="../reserve_list.php" class="btn btn-success">Browse Timber Inventory</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>