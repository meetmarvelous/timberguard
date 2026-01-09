<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login
require_login();

$page_title = "Report Illegal Activity";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Report Illegal Activity', 'url' => '', 'active' => true]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reporter_name = sanitize_input($_POST['reporter_name']);
    $description = sanitize_input($_POST['description']);
    $coordinates = sanitize_input($_POST['coordinates']);
    $reserve_id = intval($_POST['reserve_id']);
    
    // Validation
    $errors = [];
    
    if (empty($reporter_name)) {
        $errors[] = "Your name is required.";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    
    if (empty($coordinates)) {
        $errors[] = "Coordinates are required.";
    }
    
    if (empty($errors)) {
        global $conn;
        
        $sql = "INSERT INTO illegal_reports (reporter_name, description, coordinates, reserve_id, date_reported, status)
                VALUES (?, ?, ?, ?, NOW(), 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $reporter_name, $description, $coordinates, $reserve_id);
        
        if ($stmt->execute()) {
            // Log activity
            log_activity($_SESSION['user_id'], 'report', 'Illegal logging report submitted');
            
            // Send notification to forest manager
            $subject = "New Illegal Logging Report - Action Required";
            $message = "A new illegal logging report has been submitted.\n\n";
            $message .= "Reporter: " . $reporter_name . "\n";
            $message .= "Reserve: Idanre Forest Reserve\n";
            $message .= "Coordinates: " . $coordinates . "\n";
            $message .= "Description: " . $description . "\n";
            $message .= "Date Reported: " . date('F j, Y, g:i a') . "\n";
            
            send_email_notification(FOREST_MANAGER_EMAIL, $subject, nl2br($message));
            
            $_SESSION['message'] = "Your report has been submitted successfully. Thank you for helping protect our forests!";
            $_SESSION['message_type'] = "success";
            redirect('user/report.php');
        } else {
            $errors[] = "Failed to submit report. Please try again.";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
    }
}

// Get forest reserves
global $conn;
$sql = "SELECT * FROM forest_reserves";
$reserves = $conn->query($sql);
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow">
                <div class="card-header bg-success text-white py-4">
                    <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Report Illegal Logging Activity</h4>
                </div>
                <div class="card-body p-5">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>Help us protect our forests by reporting any suspicious activities. All reports are confidential and will be investigated by forest management.
                    </div>
                    
                    <form method="POST" action="report.php">
                        <div class="mb-4">
                            <label for="reporter_name" class="form-label">Your Name</label>
                            <input type="text" class="form-control form-control-lg" id="reporter_name" name="reporter_name" 
                                   value="<?php echo $_SESSION['name']; ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="reserve_id" class="form-label">Forest Reserve</label>
                            <select class="form-select form-select-lg" id="reserve_id" name="reserve_id" required>
                                <option value="">Select a forest reserve</option>
                                <?php while ($reserve = $reserves->fetch_assoc()): ?>
                                    <option value="<?php echo $reserve['id']; ?>" <?php echo ($reserve['id'] == 1) ? 'selected' : ''; ?>>
                                        <?php echo $reserve['reserve_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="coordinates" class="form-label">Coordinates (Latitude, Longitude)</label>
                            <input type="text" class="form-control form-control-lg" id="coordinates" name="coordinates" 
                                   placeholder="e.g., 7.4404, 3.9075" required>
                            <div class="form-text">You can use Google Maps to find the exact coordinates of the location.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="form-label">Description of Activity</label>
                            <textarea class="form-control form-control-lg" id="description" name="description" rows="5" 
                                      placeholder="Describe the suspicious activity in detail..." required></textarea>
                            <div class="form-text">Include details such as number of people, vehicles, equipment used, and time of observation.</div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confidential" name="confidential" checked>
                                <label class="form-check-label" for="confidential">
                                    I understand that my report will be kept confidential
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="mt-4">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3"><i class="fas fa-shield-alt me-2 text-success"></i>Confidentiality Notice</h5>
                        <p class="card-text">All reports are treated with the utmost confidentiality. Your identity will not be disclosed to anyone outside the forest management team. We take illegal logging very seriously and appreciate your help in protecting our natural resources.</p>
                        
                        <h5 class="card-title mt-4 mb-3"><i class="fas fa-map-marked-alt me-2 text-success"></i>Finding Coordinates</h5>
                        <p class="card-text">To find the exact coordinates of a location:</p>
                        <ol>
                            <li>Open Google Maps on your computer or mobile device</li>
                            <li>Right-click on the location you want to report</li>
                            <li>Select "What's here?" from the menu</li>
                            <li>The coordinates will appear at the bottom of the screen</li>
                            <li>Enter these coordinates in the format: latitude, longitude (e.g., 7.4404, 3.9075)</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>