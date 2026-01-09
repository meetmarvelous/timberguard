<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = "Login";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Login', 'url' => '', 'active' => true]
];

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    
    if (login_user($email, $password)) {
        $redirect_url = get_redirect_url();
        redirect($redirect_url);
    } else {
        $_SESSION['message'] = "Invalid email or password.";
        $_SESSION['message_type'] = "error";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow">
                <div class="card-header bg-success text-white text-center py-4">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login to Your Account</h4>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="login.php">
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Login</button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p>Don't have an account? <a href="register.php" class="text-success">Register here</a></p>
                        <p><a href="#" class="text-success">Forgot your password?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>