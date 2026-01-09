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

$page_title = "Admin Profile";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Admin Dashboard', 'url' => 'admin/index.php', 'active' => false],
    ['title' => 'Profile', 'url' => '', 'active' => true]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Check if email already exists (for other users)
    global $conn;
    $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Email already exists.";
    }
    
    // Password change validation
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password.";
        }
        
        if (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
        
        // Verify current password
        if (!empty($current_password)) {
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (!password_verify($current_password, $user['password'])) {
                    $errors[] = "Current password is incorrect.";
                }
            }
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        $update_fields = [];
        $params = [];
        $types = "";
        
        if (!empty($name)) {
            $update_fields[] = "name = ?";
            $params[] = $name;
            $types .= "s";
        }
        
        if (!empty($email)) {
            $update_fields[] = "email = ?";
            $params[] = $email;
            $types .= "s";
        }
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_fields[] = "password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }
        
        $params[] = $_SESSION['user_id'];
        $types .= "i";
        
        if (!empty($update_fields)) {
            $sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                
                log_activity($_SESSION['user_id'], 'profile_update', 'Updated profile information');
                
                $_SESSION['message'] = "Profile updated successfully.";
                $_SESSION['message_type'] = "success";
                redirect('admin/profile.php');
            } else {
                $errors[] = "Failed to update profile. Please try again.";
            }
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
    }
}

// Get current user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card border-0 shadow">
                <div class="card-header text-white py-4" style="background: linear-gradient(135deg, #2e7d32, #1b5e20);">
                    <h4 class="mb-0 text-white"><i class="fas fa-user-cog me-2" aria-hidden="true"></i>Admin Profile</h4>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="profile.php">
                        <!-- Profile Avatar -->
                        <div class="text-center mb-4">
                            <div class="profile-avatar mx-auto mb-3">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                <div class="profile-avatar-badge">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </div>
                            </div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                            <span class="badge bg-success"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                        </div>
                        
                        <!-- Profile Info Section -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <!-- Password Change Section -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-header bg-transparent border-0 py-3">
                                <h5 class="mb-0"><i class="fas fa-key me-2 text-success" aria-hidden="true"></i>Change Password</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
                                    <i class="fas fa-info-circle mt-1" aria-hidden="true"></i>
                                    <span>Leave password fields empty if you don't want to change your password.</span>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" 
                                               autocomplete="current-password">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" 
                                               minlength="8" autocomplete="new-password">
                                        <div class="form-text">Minimum 8 characters</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2" aria-hidden="true"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Account Info Card -->
            <div class="card border-0 shadow mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-success" aria-hidden="true"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <label class="text-muted small text-uppercase">Role</label>
                            <p class="mb-0 fw-bold"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="text-muted small text-uppercase">Member Since</label>
                            <p class="mb-0"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="text-muted small text-uppercase">User ID</label>
                            <p class="mb-0"><code><?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>