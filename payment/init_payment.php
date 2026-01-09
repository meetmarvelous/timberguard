<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
require_login();

// Check if payment details are in session
if (!isset($_SESSION['payment'])) {
    $_SESSION['message'] = "No payment details found. Please start from the checkout page.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

// Get payment details from session
$payment = $_SESSION['payment'];
$tree_id = $payment['tree_id'];
$user_id = $payment['user_id'];
$reserve_id = $payment['reserve_id'];
$payment_code = $payment['payment_code'];
$amount = $payment['amount'];
$message = $payment['message'] ?? '';

// Get tree details
global $conn;
$sql = "SELECT * FROM trees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tree_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['message'] = "Tree not found.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$tree = $result->fetch_assoc();

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['message'] = "User not found.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$user = $result->fetch_assoc();

// Amount in kobo (Paystack requires amount in kobo)
$amount_kobo = $amount * 100;

// Generate unique reference
$reference = 'TGPAY' . time() . mt_rand(1000, 9999);

// Initialize Paystack payment
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'email' => $user['email'],
        'amount' => $amount_kobo,
        'reference' => $reference,
        'callback_url' => BASE_URL . 'payment/verify_payment.php',
        'metadata' => [
            'tree_id' => $tree_id,
            'user_id' => $user_id,
            'reserve_id' => $reserve_id,
            'payment_code' => $payment_code,
            'message' => $message
        ]
    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Content-Type: application/json"
    ],
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    $_SESSION['message'] = "cURL Error #:" . $err;
    $_SESSION['message_type'] = "error";
    redirect('checkout.php?tree_id=' . $tree_id);
}

$transaction_data = json_decode($response, true);

if (!$transaction_data['status']) {
    $_SESSION['message'] = "Payment initialization failed: " . $transaction_data['message'];
    $_SESSION['message_type'] = "error";
    redirect('checkout.php?tree_id=' . $tree_id);
}

// Store transaction details
$transaction_url = $transaction_data['data']['authorization_url'];
$access_code = $transaction_data['data']['access_code'];

// Save transaction to database
$sql = "INSERT INTO transactions (tree_id, user_id, reserve_id, payment_code, amount, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'failed', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiisd", $tree_id, $user_id, $reserve_id, $payment_code, $amount);

if (!$stmt->execute()) {
    $_SESSION['message'] = "Failed to create transaction record.";
    $_SESSION['message_type'] = "error";
    redirect('checkout.php?tree_id=' . $tree_id);
}

// Redirect to Paystack payment page
redirect($transaction_url);
?>