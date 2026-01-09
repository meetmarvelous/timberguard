<?php
require_once 'db.php';
require_once 'functions.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check user role
function has_role($role) {
    if (!is_logged_in()) {
        return false;
    }
    
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user['role'] === $role;
    }
    
    return false;
}

// Redirect to login page if not authenticated
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

// Redirect to a specific page
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Store redirect URL for after login
function store_redirect_url() {
    if (isset($_SESSION['redirect_url'])) {
        unset($_SESSION['redirect_url']);
    }
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
}

// Get redirect URL after login
function get_redirect_url() {
    if (isset($_SESSION['redirect_url'])) {
        $url = $_SESSION['redirect_url'];
        unset($_SESSION['redirect_url']);
        return $url;
    }
    return 'index.php';
}

// Login user
function login_user($email, $password) {
    global $conn;
    
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Log activity
            log_activity($user['id'], 'login', 'User logged in');
            
            return true;
        }
    }
    
    return false;
}

// Logout user
function logout_user() {
    if (is_logged_in()) {
        // Log activity
        log_activity($_SESSION['user_id'], 'logout', 'User logged out');
        
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
    }
}

// Log user activity
function log_activity($user_id, $action, $description) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $sql = "INSERT INTO logs (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $action, $description, $ip_address, $user_agent);
    $stmt->execute();
}
?>