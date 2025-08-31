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
    ['title' => 'Admin Dashboard', 'url' => 'index.php', 'active' => false],
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
        // Log activity
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

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="display-5 fw-bold">Illegal Logging Reports</h1>
            <p class="lead">Manage and respond to illegal logging reports</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-success btn-lg" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Report
            </button>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #ff9800, #e65100);">
                <div class="card-body">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p>Pending Reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #2196f3, #0d47a1);">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h3><?php echo $stats['resolved']; ?></h3>
                    <p>Resolved Reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card dashboard-stat h-100" style="background: linear-gradient(135deg, #4caf50, #2e7d32);">
                <div class="card-body">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Reports</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <?php if ($reports->num_rows > 0): ?>
                <div class="card border-0 shadow">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Reporter</th>
                                        <th>Reserve</th>
                                        <th>Coordinates</th>
                                        <th>Status</th>
                                        <th>Date Reported</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($report = $reports->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo str_pad($report['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo $report['reporter_name']; ?></td>
                                            <td><?php echo $report['reserve_name']; ?></td>
                                            <td>
                                                <a href="https://www.google.com/maps?q=<?php echo $report['coordinates']; ?>" 
                                                   target="_blank" class="text-decoration-none">
                                                    <?php echo $report['coordinates']; ?>
                                                    <i class="fas fa-external-link-alt ms-1"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($report['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Resolved</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($report['date_reported'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        data-bs-toggle="modal" data-bs-target="#viewReportModal<?php echo $report['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- View Report Modal -->
                                        <div class="modal fade" id="viewReportModal<?php echo $report['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Illegal Logging Report #<?php echo str_pad($report['id'], 4, '0', STR_PAD_LEFT); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <strong>Reporter:</strong> <?php echo $report['reporter_name']; ?>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Reserve:</strong> <?php echo $report['reserve_name']; ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <strong>Coordinates:</strong> 
                                                                <a href="https://www.google.com/maps?q=<?php echo $report['coordinates']; ?>" 
                                                                   target="_blank" class="text-decoration-none">
                                                                    <?php echo $report['coordinates']; ?>
                                                                    <i class="fas fa-external-link-alt ms-1"></i>
                                                                </a>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Date Reported:</strong> <?php echo date('F j, Y, g:i a', strtotime($report['date_reported'])); ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <strong>Description:</strong>
                                                            <p class="bg-light p-3 rounded"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <strong>Status:</strong>
                                                            <span class="badge <?php echo ($report['status'] === 'pending') ? 'bg-warning' : 'bg-success'; ?> ms-2">
                                                                <?php echo ucfirst($report['status']); ?>
                                                            </span>
                                                        </div>
                                                        
                                                        <?php if (!empty($report['resolution_notes'])): ?>
                                                            <div class="mb-3">
                                                                <strong>Resolution Notes:</strong>
                                                                <p class="bg-light p-3 rounded"><?php echo nl2br(htmlspecialchars($report['resolution_notes'])); ?></p>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <hr>
                                                        
                                                        <form method="POST" action="reports.php">
                                                            <input type="hidden" name="update_status" value="1">
                                                            <input type="hidden" name="id" value="<?php echo $report['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="status_<?php echo $report['id']; ?>" class="form-label">Update Status</label>
                                                                <select class="form-select" id="status_<?php echo $report['id']; ?>" name="status" required>
                                                                    <option value="pending" <?php echo ($report['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                                    <option value="resolved" <?php echo ($report['status'] === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="resolution_notes_<?php echo $report['id']; ?>" class="form-label">Resolution Notes</label>
                                                                <textarea class="form-control" id="resolution_notes_<?php echo $report['id']; ?>" 
                                                                          name="resolution_notes" rows="3"><?php echo $report['resolution_notes']; ?></textarea>
                                                            </div>
                                                            
                                                            <div class="d-grid">
                                                                <button type="submit" class="btn btn-success">Update Status</button>
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
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                        <h4>No reports found</h4>
                        <p class="text-muted">No illegal logging reports have been submitted yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-success"></i>Reports by Reserve</h5>
                </div>
                <div class="card-body">
                    <?php if ($reports_by_reserve->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($reserve = $reports_by_reserve->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo $reserve['reserve_name']; ?></strong>
                                        <div class="text-muted small">
                                            <?php echo $reserve['pending_reports']; ?> pending, <?php echo $reserve['resolved_reports']; ?> resolved
                                        </div>
                                    </div>
                                    <span class="fw-bold"><?php echo $reserve['total_reports']; ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No report data available.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-success"></i>Reporting Information</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">This section displays all illegal logging reports submitted through the TimberGuard platform.</p>
                    
                    <h6 class="mt-4 mb-3">Response Protocol:</h6>
                    <ol class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-start">
                            <span class="me-2">1.</span>
                            <div>Review all pending reports daily</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <span class="me-2">2.</span>
                            <div>Verify the reported location and details</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <span class="me-2">3.</span>
                            <div>Dispatch forest rangers to investigate</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <span class="me-2">4.</span>
                            <div>Update report status and add resolution notes</div>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <span class="me-2">5.</span>
                            <div>Contact the reporter if additional information is needed</div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>