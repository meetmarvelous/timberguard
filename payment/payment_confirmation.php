<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login
require_login();

// Check if we're processing a payment
if (!isset($_SESSION['payment_processing'])) {
    $_SESSION['message'] = "No payment in progress. Please start a new transaction.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$payment = $_SESSION['payment_processing'];
$reference = $_GET['reference'] ?? '';
$status = $_GET['status'] ?? 'failed';

// In a real implementation, you would verify the payment with Paystack
// For now, we'll simulate the verification

if ($status === 'success') {
    // Update tree status to sold
    global $conn;
    $sql = "UPDATE trees SET status = 'sold' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $payment['tree_id']);
    $stmt->execute();
    
    // Create transaction record
    $sql = "INSERT INTO transactions (tree_id, user_id, reserve_id, payment_code, amount, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'completed', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisd", 
        $payment['tree_id'],
        $payment['user_id'],
        $payment['reserve_id'],
        $payment['payment_code'],
        $payment['amount']
    );
    $stmt->execute();
    
    // Send notification to forest manager
    $subject = "New Timber Purchase - Payment Completed";
    $message = "A new timber purchase has been completed.\n\n";
    $message .= "Payment Code: " . $payment['payment_code'] . "\n";
    $message .= "Tree ID: " . str_pad($payment['tree_id'], 6, '0', STR_PAD_LEFT) . "\n";
    $message .= "Amount: " . format_currency($payment['amount']) . "\n";
    $message .= "Customer: " . $_SESSION['name'] . "\n";
    $message .= "Email: " . $_SESSION['email'] . "\n";
    $message .= "Date: " . date('F j, Y, g:i a') . "\n\n";
    $message .= "Special Instructions: " . ($payment['message'] ?: 'None');
    
    send_email_notification(FOREST_MANAGER_EMAIL, $subject, nl2br($message));
    
    // Clear payment session
    unset($_SESSION['payment']);
    unset($_SESSION['payment_processing']);
    
    // Set success message
    $_SESSION['message'] = "Payment successful! Your timber purchase has been confirmed.";
    $_SESSION['message_type'] = "success";
    
    // Redirect to receipt
    redirect('user/receipt.php?payment_code=' . $payment['payment_code']);
} else {
    // Payment failed
    // In a real implementation, you might want to log the failure
    
    // Clear payment session
    unset($_SESSION['payment']);
    unset($_SESSION['payment_processing']);
    
    // Set error message
    $_SESSION['message'] = "Payment failed. Please try again or contact support.";
    $_SESSION['message_type'] = "error";
    
    // Create failed transaction record
    $sql = "INSERT INTO transactions (tree_id, user_id, reserve_id, payment_code, amount, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'failed', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisd", 
        $payment['tree_id'],
        $payment['user_id'],
        $payment['reserve_id'],
        $payment['payment_code'],
        $payment['amount']
    );
    $stmt->execute();
    
    // Redirect to forest reserve list
    redirect('reserve_list.php');
}
?>