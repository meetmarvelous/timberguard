</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5><i class="fas fa-tree me-2"></i><?php echo SITE_NAME; ?></h5>
                <p>A comprehensive forestry management system for sustainable timber trading and illegal logging monitoring.</p>
                <div class="social-icons mt-3">
                    <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>index.php" class="text-white text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>reserve_list.php" class="text-white text-decoration-none">Forest Reserves</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>about.php" class="text-white text-decoration-none">About Us</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>contact.php" class="text-white text-decoration-none">Contact</a></li>
                    <?php if (is_logged_in()): ?>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>user/index.php" class="text-white text-decoration-none">User Dashboard</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>login.php" class="text-white text-decoration-none">Login</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>register.php" class="text-white text-decoration-none">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> University of Ibadan, Nigeria</li>
                    <li class="mb-2"><i class="fas fa-phone me-2"></i> +234 800 000 0000</li>
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@timberguard.com</li>
                </ul>
            </div>
        </div>
        <hr class="bg-light">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="text-white text-decoration-none me-3">Privacy Policy</a>
                <a href="#" class="text-white text-decoration-none">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>

<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    
    // Confirm delete action
    function confirmDelete(message) {
        return confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.');
    }
    
    // FIX MODAL ISSUES - Prevent blinking and double scrollbar
    document.addEventListener('DOMContentLoaded', function() {
        // Fix for modal double scrollbar issue
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                // Ensure body doesn't have multiple overflow settings
                document.body.style.overflow = 'auto';
                document.body.style.paddingRight = '0';
            });
        });
        
        // Handle form submissions with AJAX where needed
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                // Add any pre-submission validation here
                const termsCheckbox = document.getElementById('terms');
                if (!termsCheckbox.checked) {
                    alert('You must accept the terms and conditions to proceed.');
                    e.preventDefault();
                    return false;
                }
            });
        }
        
        // Fix for modal backdrop issues
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(trigger => {
            trigger.addEventListener('click', function() {
                // Make sure any existing modals are properly closed
                const openModals = document.querySelectorAll('.modal.show');
                openModals.forEach(modal => {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });
            });
        });
    });
</script>
</body>
</html>