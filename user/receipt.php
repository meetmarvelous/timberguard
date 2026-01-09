<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login
require_login();

// Check if payment code is provided
if (!isset($_GET['payment_code']) || empty($_GET['payment_code'])) {
    $_SESSION['message'] = "Invalid receipt request.";
    $_SESSION['message_type'] = "error";
    redirect('user/transactions.php');
}

$payment_code = sanitize_input($_GET['payment_code']);

// Get transaction details
global $conn;
$sql = "SELECT t.*, u.name as user_name, u.email as user_email, r.reserve_name, trs.species, trs.volume, trs.MTH, trs.THT, trs.DBH, trs.basal_area
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN forest_reserves r ON t.reserve_id = r.id
        JOIN trees trs ON t.tree_id = trs.id
        WHERE t.payment_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $payment_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Receipt not found.";
    $_SESSION['message_type'] = "error";
    redirect('user/transactions.php');
}

$transaction = $result->fetch_assoc();
$price = calculate_tree_price($transaction['volume']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - <?php echo $payment_code; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2e7d32;
        }
        
        .receipt-logo {
            width: 80px;
            height: 80px;
            background: #2e7d32;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
        }
        
        .receipt-title {
            color: #2e7d32;
            margin: 0;
            font-size: 2rem;
        }
        
        .receipt-subtitle {
            color: #666;
            margin: 5px 0 0;
            font-size: 1.1rem;
        }
        
        .receipt-details {
            margin: 20px 0;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
        }
        
        .detail-label {
            width: 180px;
            font-weight: bold;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
            color: #333;
        }
        
        .tree-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .tree-info h5 {
            margin-top: 0;
            color: #2e7d32;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .tree-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .tree-spec {
            display: flex;
            justify-content: space-between;
        }
        
        .tree-spec-label {
            font-weight: bold;
            color: #555;
        }
        
        .tree-spec-value {
            color: #333;
        }
        
        .amount-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2e7d32;
            text-align: right;
            margin: 20px 0;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 0.9rem;
            font-style: italic;
        }
        
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn-print {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin: 0 10px;
        }
        
        .btn-print:hover {
            background: #1b5e20;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin: 0 10px;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="receipt-logo">
                <i class="fas fa-tree"></i>
            </div>
            <h1 class="receipt-title"><?php echo SITE_NAME; ?></h1>
            <p class="receipt-subtitle">Forest Management & Timber Trading System</p>
        </div>
        
        <div class="receipt-details">
            <div class="detail-row">
                <div class="detail-label">Payment Code:</div>
                <div class="detail-value"><?php echo $transaction['payment_code']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Date & Time:</div>
                <div class="detail-value"><?php echo date('F j, Y \a\t g:i A', strtotime($transaction['created_at'])); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <?php echo $transaction['status']; ?>
                    <span class="status-badge status-<?php echo $transaction['status']; ?>">
                        <?php echo ucfirst($transaction['status']); ?>
                    </span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Customer Name:</div>
                <div class="detail-value"><?php echo $transaction['user_name']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Customer Email:</div>
                <div class="detail-value"><?php echo $transaction['user_email']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Forest Reserve:</div>
                <div class="detail-value"><?php echo $transaction['reserve_name']; ?></div>
            </div>
        </div>
        
        <div class="tree-info">
            <h5>Tree Information</h5>
            <div class="tree-specs">
                <div class="tree-spec">
                    <span class="tree-spec-label">Tree ID:</span>
                    <span class="tree-spec-value"><?php echo str_pad($transaction['tree_id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="tree-spec">
                    <span class="tree-spec-label">Species:</span>
                    <span class="tree-spec-value"><?php echo $transaction['species']; ?></span>
                </div>
                <div class="tree-spec">
                    <span class="tree-spec-label">MHT (m):</span>
                    <span class="tree-spec-value"><?php echo $transaction['MTH']; ?></span>
                </div>
                <div class="tree-spec">
                    <span class="tree-spec-label">THT (m):</span>
                    <span class="tree-spec-value"><?php echo $transaction['THT']; ?></span>
                </div>
                <div class="tree-spec">
                    <span class="tree-spec-label">DBH (m):</span>
                    <span class="tree-spec-value"><?php echo $transaction['DBH']; ?></span>
                </div>
                <div class="tree-spec">
                    <span class="tree-spec-label">Basal Area (m²):</span>
                    <span class="tree-spec-value"><?php echo $transaction['basal_area']; ?></span>
                </div>
                <div class="tree-spec">
                    <span class="tree-spec-label">Volume (m³):</span>
                    <span class="tree-spec-value"><?php echo $transaction['volume']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="amount-total">
            Amount Paid: <?php echo format_currency($transaction['amount']); ?>
        </div>
        
        <div class="footer-note">
            This receipt serves as proof of payment for the timber purchase. Please keep it for your records.
        </div>
    </div>
    
    <div class="action-buttons no-print">
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print me-2"></i>Print Receipt
        </button>
        <button onclick="window.history.back()" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Transactions
        </button>
    </div>
</body>
</html>