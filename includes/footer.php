</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-4 mb-4">
                <h5><i class="fas fa-tree me-2"></i><?php echo SITE_NAME; ?></h5>
                <p class="text-white-50">A comprehensive forestry management system for sustainable timber trading and illegal logging monitoring.</p>
                <div class="social-icons mt-3">
                    <a href="#" class="text-white me-2" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-2" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-2" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>index.php" class="text-white-50 text-decoration-none hover-white">Home</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>reserve_list.php" class="text-white-50 text-decoration-none hover-white">Forest Reserves</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>about.php" class="text-white-50 text-decoration-none hover-white">About Us</a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>contact.php" class="text-white-50 text-decoration-none hover-white">Contact</a></li>
                    <?php if (is_logged_in()): ?>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>user/index.php" class="text-white-50 text-decoration-none hover-white">User Dashboard</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>login.php" class="text-white-50 text-decoration-none hover-white">Login</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>register.php" class="text-white-50 text-decoration-none hover-white">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-6 col-md-4 mb-4">
                <h5>Contact Us</h5>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-white"></i>University of Ibadan, Nigeria</li>
                    <li class="mb-2"><i class="fas fa-phone me-2 text-white"></i>+234 806 833 8665</li>
                    <li class="mb-2"><i class="fas fa-envelope me-2 text-white"></i>info@timberguard.com.ng</li>
                </ul>
            </div>
        </div>
        <hr class="bg-secondary my-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="text-white-50 mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
            <div class="col-12 col-md-6 text-center text-md-end">
                <a href="#" class="text-white-50 text-decoration-none me-3 hover-white">Privacy Policy</a>
                <a href="#" class="text-white-50 text-decoration-none hover-white">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>

<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

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
    
    // Handle form submissions with AJAX where needed
    document.addEventListener('DOMContentLoaded', function() {
        // Example for payment form
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                // Add any pre-submission validation here
                const termsCheckbox = document.getElementById('terms');
                if (termsCheckbox && !termsCheckbox.checked) {
                    alert('You must accept the terms and conditions to proceed.');
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
</script>
</body>
</html>