<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login
require_login();

$page_title = "Profile";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Profile', 'url' => '', 'active' => true]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $current_password = sanitize_input($_POST['current_password']);
    $new_password = sanitize_input($_POST['new_password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    
    // Validation
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
        // Build update query
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
        
        // Add user_id to parameters
        $params[] = $_SESSION['user_id'];
        $types .= "i";
        
        if (!empty($update_fields)) {
            $sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                
                // Log activity
                log_activity($_SESSION['user_id'], 'profile_update', 'Updated profile information');
                
                $_SESSION['message'] = "Profile updated successfully.";
                $_SESSION['message_type'] = "success";
                redirect('user/profile.php');
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

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-success text-white py-4">
                    <h4 class="mb-0 text-white"><i class="fas fa-user me-2"></i>My Profile</h4>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="profile.php">
                        <div class="row mb-4">
                            <div class="col-md-12 text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <div class="bg-success text-white rounded-circle" style="width: 120px; height: 120px; line-height: 120px; font-size: 3rem;">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <div class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle p-2">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                </div>
                                <h5 class="mt-3 mb-1"><?php echo $user['name']; ?></h5>
                                <p class="text-muted"><?php echo $user['role']; ?> Account</p>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                       value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       value="<?php echo $user['email']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="alert alert-info mb-4">
                                                    <i class="fas fa-info-circle me-2"></i>Leave password fields empty if you don't want to change your password.
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="current_password" class="form-label">Current Password</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="new_password" class="form-label">New Password</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>