<?php
require_once 'db.php';

// Sanitize input data
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Generate unique booking number
function generate_booking_number() {
    return 'TGBK' . mt_rand(100000, 999999);
}

// Format currency
function format_currency($amount) {
    return '₦' . number_format($amount, 2);
}

// Show alert message
function show_alert($message, $type = 'success') {
    $alert_types = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $class = isset($alert_types[$type]) ? $alert_types[$type] : 'alert-info';
    
    return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

// Send email notification
function send_email_notification($to, $subject, $message) {
    // In a real application, you would use PHPMailer or similar
    // For now, we'll simulate email sending
    
    $headers = "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
    $headers .= "Reply-To: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // For development, we'll just log the email
    error_log("EMAIL TO: $to\nSUBJECT: $subject\nMESSAGE: $message");
    
    // In production, uncomment this:
    // return mail($to, $subject, $message, $headers);
    return true;
}

// Calculate tree price (simplified for now)
function calculate_tree_price($volume) {
    // Base price per cubic meter
    $base_price = 5000;
    
    // Calculate price based on volume
    $price = $volume * $base_price;
    
    // Round to nearest 100
    return round($price / 100) * 100;
}

// Check if tree is available
function is_tree_available($tree_id) {
    global $conn;
    
    $sql = "SELECT status FROM trees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tree_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $tree = $result->fetch_assoc();
        return $tree['status'] === 'available';
    }
    
    return false;
}
?>