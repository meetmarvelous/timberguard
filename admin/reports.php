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

$page_title = "Illegal Logging Reports";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Admin Dashboard', 'url' => 'admin/index.php', 'active' => false],
    ['title' => 'Illegal Reports', 'url' => '', 'active' => true]
];

// Handle form submission for updating report status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $status = sanitize_input($_POST['status']);
    $resolution_notes = sanitize_input($_POST['resolution_notes']);
    
    global $conn;
    $sql = "UPDATE illegal_reports 
            SET status = ?, resolution_notes = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $resolution_notes, $id);
    
    if ($stmt->execute()) {
        log_activity($_SESSION['user_id'], 'report_update', 'Updated illegal logging report status (ID: ' . $id . ')');
        $_SESSION['message'] = "Report status updated successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to update report status.";
        $_SESSION['message_type'] = "error";
    }
    
    redirect('admin/reports.php');
}

// Get all illegal reports with reserve information
global $conn;
$sql = "SELECT ir.*, r.reserve_name 
        FROM illegal_reports ir
        JOIN forest_reserves r ON ir.reserve_id = r.id
        ORDER BY ir.date_reported DESC";
$reports = $conn->query($sql);

// Get report statistics
$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
        FROM illegal_reports";
$stats = $conn->query($sql)->fetch_assoc();

// Get reports by reserve
$sql = "SELECT r.reserve_name, 
               COUNT(*) as total_reports,
               SUM(CASE WHEN ir.status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
               SUM(CASE WHEN ir.status = 'resolved' THEN 1 ELSE 0 END) as resolved_reports
        FROM illegal_reports ir
        JOIN forest_reserves r ON ir.reserve_id = r.id
        GROUP BY r.id
        ORDER BY total_reports DESC";
$reports_by_reserve = $conn->query($sql);
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4 py-md-5">
    <!-- Page Header -->
    <div class="admin-page-header mb-4">
        <div>
            <h1 class="display-6 fw-bold mb-1">Illegal Logging Reports</h1>
            <p class="lead text-muted mb-0">Manage and respond to illegal logging reports</p>
        </div>
        <button type="button" class="btn btn-success btn-lg" onclick="window.print()">
            <i class="fas fa-print me-2" aria-hidden="true"></i>Print Report
        </button>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card dashboard-stat dashboard-stat-warning h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $stats['pending'] ?: 0; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card dashboard-stat dashboard-stat-forest h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo $stats['resolved'] ?: 0; ?></h3>
                    <p>Resolved</p>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card dashboard-stat dashboard-stat-reports h-100">
                <div class="card-body">
                    <div class="dashboard-stat-icon-wrapper">
                        <i class="fas fa-file-alt" aria-hidden="true"></i>
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
            <?php if ($reports->num_rows > 0): ?>
                <div class="card border-0 shadow">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="reportsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Reporter</th>
                                        <th>Reserve</th>
                                        <th class="hide-mobile">Coordinates</th>
                                        <th>Status</th>
                                        <th class="hide-mobile">Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($report = $reports->fetch_assoc()): ?>
                                        <tr>
                                            <td><code><?php echo str_pad($report['id'], 4, '0', STR_PAD_LEFT); ?></code></td>
                                            <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                                            <td><?php echo htmlspecialchars($report['reserve_name']); ?></td>
                                            <td class="hide-mobile">
                                                <a href="https://www.google.com/maps?q=<?php echo $report['latitude']; ?>,<?php echo $report['longitude']; ?>" 
                                                   target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                                    <small><?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?></small>
                                                    <i class="fas fa-external-link-alt ms-1 small" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($report['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Resolved</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="hide-mobile"><?php echo date('M j, Y', strtotime($report['date_reported'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        data-bs-toggle="modal" data-bs-target="#viewReportModal<?php echo $report['id']; ?>"
                                                        aria-label="View report details">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- View Report Modal -->
                                        <div class="modal fade" id="viewReportModal<?php echo $report['id']; ?>" tabindex="-1" aria-labelledby="viewReportModalLabel<?php echo $report['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewReportModalLabel<?php echo $report['id']; ?>">
                                                            Report #<?php echo str_pad($report['id'], 4, '0', STR_PAD_LEFT); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Report Details -->
                                                        <div class="row g-3 mb-4">
                                                            <div class="col-6 col-md-3">
                                                                <label class="text-muted small text-uppercase">Reporter</label>
                                                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($report['reporter_name']); ?></p>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="text-muted small text-uppercase">Reserve</label>
                                                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($report['reserve_name']); ?></p>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="text-muted small text-uppercase">Date Reported</label>
                                                                <p class="mb-0"><?php echo date('M j, Y', strtotime($report['date_reported'])); ?></p>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="text-muted small text-uppercase">Status</label>
                                                                <p class="mb-0">
                                                                    <span class="badge <?php echo ($report['status'] === 'pending') ? 'bg-warning' : 'bg-success'; ?>">
                                                                        <?php echo ucfirst($report['status']); ?>
                                                                    </span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-4">
                                                            <label class="text-muted small text-uppercase">Location</label>
                                                            <p class="mb-2">
                                                                <a href="https://www.google.com/maps?q=<?php echo $report['latitude']; ?>,<?php echo $report['longitude']; ?>" 
                                                                   target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-map-marker-alt me-2" aria-hidden="true"></i>
                                                                    <?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?>
                                                                    <i class="fas fa-external-link-alt ms-2" aria-hidden="true"></i>
                                                                </a>
                                                            </p>
                                                        </div>
                                                        
                                                        <div class="mb-4">
                                                            <label class="text-muted small text-uppercase">Description</label>
                                                            <div class="bg-light p-3 rounded">
                                                                <?php echo nl2br(htmlspecialchars($report['description'])); ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if (!empty($report['resolution_notes'])): ?>
                                                            <div class="mb-4">
                                                                <label class="text-muted small text-uppercase">Resolution Notes</label>
                                                                <div class="bg-light p-3 rounded">
                                                                    <?php echo nl2br(htmlspecialchars($report['resolution_notes'])); ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <hr>
                                                        
                                                        <!-- Update Status Form -->
                                                        <h6 class="mb-3"><i class="fas fa-edit me-2 text-success" aria-hidden="true"></i>Update Status</h6>
                                                        <form method="POST" action="reports.php">
                                                            <input type="hidden" name="update_status" value="1">
                                                            <input type="hidden" name="id" value="<?php echo $report['id']; ?>">
                                                            
                                                            <div class="row g-3">
                                                                <div class="col-12 col-md-4">
                                                                    <label for="status_<?php echo $report['id']; ?>" class="form-label">Status</label>
                                                                    <select class="form-select" id="status_<?php echo $report['id']; ?>" name="status" required>
                                                                        <option value="pending" <?php echo ($report['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                                        <option value="resolved" <?php echo ($report['status'] === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 col-md-8">
                                                                    <label for="resolution_notes_<?php echo $report['id']; ?>" class="form-label">Resolution Notes</label>
                                                                    <textarea class="form-control" id="resolution_notes_<?php echo $report['id']; ?>" 
                                                                              name="resolution_notes" rows="2" placeholder="Add notes about the resolution..."><?php echo htmlspecialchars($report['resolution_notes'] ?? ''); ?></textarea>
                                                                </div>
                                                                <div class="col-12">
                                                                    <button type="submit" class="btn btn-success w-100">
                                                                        <i class="fas fa-save me-2" aria-hidden="true"></i>Update Status
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow">
                    <div class="card-body empty-state">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        <h4>No reports found</h4>
                        <p>No illegal logging reports have been submitted yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar Column -->
        <div class="col-12 col-lg-4">
            <!-- Reports by Reserve Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-success" aria-hidden="true"></i>Reports by Reserve</h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($reports_by_reserve->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($reserve = $reports_by_reserve->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                    <div>
                                        <strong><?php echo htmlspecialchars($reserve['reserve_name']); ?></strong>
                                        <div class="text-muted small">
                                            <?php echo $reserve['pending_reports']; ?> pending, <?php echo $reserve['resolved_reports']; ?> resolved
                                        </div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo $reserve['total_reports']; ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <p class="mb-0">No report data available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Response Protocol Card -->
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-success" aria-hidden="true"></i>Response Protocol</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Follow these steps when handling illegal logging reports:</p>
                    <ol class="small ps-3 mb-0">
                        <li class="mb-2">Verify the location using the coordinates provided.</li>
                        <li class="mb-2">Dispatch a ranger team to investigate the site.</li>
                        <li class="mb-2">Document findings and take photos if possible.</li>
                        <li class="mb-2">Update the report status to "Resolved" and add detailed notes.</li>
                        <li>If confirmed illegal activity, escalate to local law enforcement.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_scripts = "
<script>
    $(document).ready(function() {
        $('#reportsTable').DataTable({
            order: [[5, 'desc']],
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [6] }
            ],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search reports...'
            }
        });
    });
</script>
";
include '../includes/footer.php'; 
?>