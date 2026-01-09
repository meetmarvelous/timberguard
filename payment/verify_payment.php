<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Get payment reference from URL
$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    $_SESSION['message'] = "No payment reference provided.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

// Verify payment with Paystack
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY
    ],
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    $_SESSION['message'] = "cURL Error #:" . $err;
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$verify_data = json_decode($response, true);

if (!$verify_data['status']) {
    $_SESSION['message'] = "Payment verification failed: " . $verify_data['message'];
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

// Check if payment was successful
if ($verify_data['data']['status'] !== 'success') {
    $_SESSION['message'] = "Payment was not successful.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

// Get transaction metadata
$metadata = $verify_data['data']['metadata'];
$tree_id = $metadata['tree_id'];
$user_id = $metadata['user_id'];
$reserve_id = $metadata['reserve_id'];
$payment_code = $metadata['payment_code'];
$message = $metadata['message'] ?? '';

// Update transaction status
global $conn;
$sql = "UPDATE transactions 
        SET status = 'completed' 
        WHERE payment_code = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $payment_code, $user_id);

if (!$stmt->execute()) {
    $_SESSION['message'] = "Failed to update transaction status.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

// Update tree status to sold
$sql = "UPDATE trees SET status = 'sold' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tree_id);

if (!$stmt->execute()) {
    $_SESSION['message'] = "Failed to update tree status.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

// Log activity
log_activity($user_id, 'payment_success', 'Payment successful for tree ID: ' . $tree_id);

// Clear payment session data
unset($_SESSION['payment']);

// Set success message
$_SESSION['message'] = "Payment successful! Your tree has been reserved.";
$_SESSION['message_type'] = "success";

// Redirect to transactions page
redirect('user/transactions.php');
?>