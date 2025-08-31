<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require login for checkout
require_login();

// Check if tree ID is provided
if (!isset($_GET['tree_id']) || empty($_GET['tree_id'])) {
    $_SESSION['message'] = "Invalid tree selection. Please select a tree to purchase.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$tree_id = intval($_GET['tree_id']);

// Verify tree exists and is available
global $conn;
$sql = "SELECT t.*, r.reserve_name, r.id as reserve_id 
        FROM trees t 
        JOIN forest_reserves r ON t.reserve_id = r.id 
        WHERE t.id = ? AND t.status = 'available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tree_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "The selected tree is not available for purchase.";
    $_SESSION['message_type'] = "error";
    redirect('reserve_list.php');
}

$tree = $result->fetch_assoc();
$price = calculate_tree_price($tree['volume']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate terms acceptance
    if (!isset($_POST['terms'])) {
        $_SESSION['message'] = "You must accept the terms and conditions to proceed.";
        $_SESSION['message_type'] = "error";
        redirect($_SERVER['REQUEST_URI']);
        exit();
    }
    
    // Generate payment code
    $payment_code = 'TGPAY' . mt_rand(100000, 999999);
    
    // Store payment details in session
    $_SESSION['payment'] = [
        'tree_id' => $tree_id,
        'user_id' => $_SESSION['user_id'],
        'reserve_id' => $tree['reserve_id'],
        'payment_code' => $payment_code,
        'amount' => $price,
        'message' => sanitize_input($_POST['message'] ?? '')
    ];
    
    // Redirect to payment initialization
    redirect('payment/init_payment.php');
}
?>
<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Checkout</h4>
                </div>
                <div class="card-body p-4">
                    <form id="paymentForm" method="POST" action="checkout.php?tree_id=<?php echo $tree_id; ?>">
                        <input type="hidden" name="tree_id" value="<?php echo $tree_id; ?>">
                        
                        <div class="mb-4">
                            <h5 class="mb-3">Tree Details</h5>
                            <div class="card tree-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-success">Available</span>
                                        <span class="text-muted"><i class="fas fa-tree me-1"></i> <?php echo $tree['species']; ?></span>
                                    </div>
                                    <h5 class="card-title mb-3">Tree ID: <?php echo str_pad($tree['id'], 6, '0', STR_PAD_LEFT); ?></h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>MHT (m):</strong> <?php echo $tree['MTH']; ?></p>
                                            <p><strong>THT (m):</strong> <?php echo $tree['THT']; ?></p>
                                            <p><strong>DBH (m):</strong> <?php echo $tree['DBH']; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Basal Area (m²):</strong> <?php echo $tree['basal_area']; ?></p>
                                            <p><strong>Volume (m³):</strong> <?php echo $tree['volume']; ?></p>
                                            <p class="text-success fw-bold fs-5">Price: <?php echo format_currency($price); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="mb-3">Additional Information</h5>
                            <div class="form-group mb-3">
                                <label for="message" class="form-label">Special Instructions or Message</label>
                                <textarea class="form-control" id="message" name="message" rows="3" placeholder="Any special instructions for harvesting or delivery?"></textarea>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I accept the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a> for timber purchase.
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Payment (<?php echo format_currency($price); ?>)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Tree ID:</td>
                                <td class="text-end"><?php echo str_pad($tree['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            </tr>
                            <tr>
                                <td>Species:</td>
                                <td class="text-end"><?php echo $tree['species']; ?></td>
                            </tr>
                            <tr>
                                <td>Volume (m³):</td>
                                <td class="text-end"><?php echo $tree['volume']; ?></td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Total Amount:</td>
                                <td class="text-end"><?php echo format_currency($price); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>Payment will be processed securely through Paystack.
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Secure Checkout</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-3">Your payment information is encrypted and secure. We never store your payment details.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <i class="fab fa-cc-visa fa-2x text-muted"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-muted"></i>
                        <i class="fab fa-cc-paypal fa-2x text-muted"></i>
                        <i class="fas fa-lock fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Payment Terms</h6>
                <p>Full payment is required at the time of purchase. All prices are quoted in Nigerian Naira (₦).</p>
                
                <h6>2. Harvesting and Collection</h6>
                <p>Timber must be harvested and collected within 14 days of purchase confirmation. The buyer is responsible for arranging harvesting and transportation.</p>
                
                <h6>3. Cancellation Policy</h6>
                <p>Purchases can be cancelled within 24 hours of payment for a full refund. After 24 hours, cancellations will incur a 10% administrative fee.</p>
                
                <h6>4. Sustainable Harvesting</h6>
                <p>All harvesting must comply with sustainable forestry practices and regulations set by the University of Ibadan Forestry Department.</p>
                
                <h6>5. Liability</h6>
                <p>The University of Ibadan Forestry Department is not liable for any damage or injury occurring during harvesting or transportation of purchased timber.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>