<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login
require_login();

// Check if payment details are in session
if (!isset($_SESSION['payment'])) {
    $_SESSION['message'] = "Invalid payment request. Please try again.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$payment = $_SESSION['payment'];
$tree_id = $payment['tree_id'];
$user_id = $payment['user_id'];
$reserve_id = $payment['reserve_id'];
$payment_code = $payment['payment_code'];
$amount = $payment['amount'];
$message = $payment['message'];

// Verify tree is still available
if (!is_tree_available($tree_id)) {
    unset($_SESSION['payment']);
    $_SESSION['message'] = "Sorry, this tree is no longer available.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

// Convert amount to kobo (Paystack uses smallest currency unit)
$amount_kobo = $amount * 100;

// Get user details
global $conn;
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Initialize Paystack payment
$reference = 'TGPAY' . time() . rand(1000, 9999);

// In a real implementation, you would use the Paystack PHP library
// For now, we'll simulate the payment process

// For development, we'll redirect to a mock payment confirmation
// In production, you would use the Paystack API

// Store payment details for confirmation
$_SESSION['payment_processing'] = [
    'reference' => $reference,
    'tree_id' => $tree_id,
    'user_id' => $user_id,
    'reserve_id' => $reserve_id,
    'payment_code' => $payment_code,
    'amount' => $amount,
    'amount_kobo' => $amount_kobo,
    'message' => $message
];

// Redirect to payment confirmation (simulated)
redirect('payment/payment_confirmation.php?reference=' . $reference . '&status=success');
?>