<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = "Welcome to " . SITE_NAME;
$hide_banner = true;
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-3 fw-bold mb-4">Sustainable Forestry Management</h1>
        <p class="lead text-white mb-5">Track, trade, and protect our precious forest resources with our comprehensive management system</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="reserve_list.php" class="btn btn-light btn-lg px-4 py-3 fs-5">
                <i class="fas fa-leaf me-2"></i>Browse Timber
            </a>
            <a href="<?php echo is_logged_in() ? 'user/report.php' : 'login.php'; ?>" class="btn btn-outline-light btn-lg px-4 py-3 fs-5">
                <i class="fas fa-bullhorn me-2"></i>Report Illegal Activity
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-md-12 text-center">
                <h2 class="section-title d-inline-block">Our Key Features</h2>
                <p class="lead">Comprehensive tools for sustainable forest management</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="p-3 bg-light rounded-circle d-inline-block">
                                <i class="fas fa-tree fa-3x text-success"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-3">Timber Trading</h4>
                        <p class="card-text">Browse available trees, view detailed specifications, and securely purchase timber from designated forest reserves.</p>
                        <a href="reserve_list.php" class="btn btn-outline-success mt-3">Explore Timber <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="p-3 bg-light rounded-circle d-inline-block">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-3">Illegal Logging Reports</h4>
                        <p class="card-text">Report suspicious activities with location details to help protect our forests from illegal logging operations.</p>
                        <a href="<?php echo is_logged_in() ? 'user/report.php' : 'login.php'; ?>" class="btn btn-outline-warning mt-3">Report Activity <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="p-3 bg-light rounded-circle d-inline-block">
                                <i class="fas fa-chart-line fa-3x text-primary"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-3">Forest Monitoring</h4>
                        <p class="card-text">Track forest health, tree growth, and reserve status with our comprehensive monitoring tools and analytics.</p>
                        <a href="about.php" class="btn btn-outline-primary mt-3">Learn More <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 text-center">
                <div class="display-4 fw-bold text-success">12</div>
                <h4>Forest Reserves</h4>
                <p>Managed across Nigeria</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="display-4 fw-bold text-success">5,231</div>
                <h4>Trees Available</h4>
                <p>Ready for sustainable harvesting</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="display-4 fw-bold text-success">247</div>
                <h4>Illegal Reports</h4>
                <p>Processed this year</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="display-4 fw-bold text-success">98%</div>
                <h4>Success Rate</h4>
                <p>In combating illegal logging</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Reserve Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-md-12 text-center">
                <h2 class="section-title d-inline-block">Featured Forest Reserve</h2>
                <p class="lead">Discover Idanre Forest Reserve</p>
            </div>
        </div>
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <img src="<?php echo BASE_URL; ?>assets/img/placeholder/forest.jpg" class="img-fluid rounded-3 shadow" alt="Idanre Forest Reserve">
                    <div class="position-absolute top-0 start-0 bg-success text-white px-3 py-2 m-3 rounded">
                        <i class="fas fa-check-circle me-1"></i> Sustainably Managed
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h3 class="mb-4">Idanre Forest Reserve</h3>
                <p class="lead mb-4">Located in Ondo State, Nigeria, Idanre Forest Reserve is one of the most biodiverse forest ecosystems in the region, covering approximately 18,168 hectares of land.</p>
                
                <div class="mb-4">
                    <h5 class="mb-3">Key Features:</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i> Home to over 200 tree species
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i> Sustainable timber harvesting program
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i> Active conservation and monitoring
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i> Community engagement initiatives
                        </li>
                    </ul>
                </div>
                
                <a href="reserve_list.php" class="btn btn-success btn-lg px-4 py-3">
                    <i class="fas fa-leaf me-2"></i> Explore Timber Inventory
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-md-12 text-center">
                <h2 class="section-title d-inline-block">What Our Users Say</h2>
                <p class="lead">Trusted by forest managers, researchers, and timber traders</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-quote-left fa-2x text-success"></i>
                        </div>
                        <p class="card-text fst-italic">"TimberGuard has revolutionized how we manage our forest resources. The real-time reporting system has helped us reduce illegal logging incidents by 65% in just one year."</p>
                        <div class="d-flex align-items-center mt-4">
                            <div class="flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name=John+Doe&background=4CAF50&color=fff" class="rounded-circle" width="50" height="50" alt="User">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">John Doe</h6>
                                <small class="text-muted">Forest Manager, Idanre Reserve</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-quote-left fa-2x text-success"></i>
                        </div>
                        <p class="card-text fst-italic">"As a timber trader, I appreciate the transparency and ease of purchasing available trees. The payment system is secure and the documentation is thorough."</p>
                        <div class="d-flex align-items-center mt-4">
                            <div class="flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name=Jane+Smith&background=4CAF50&color=fff" class="rounded-circle" width="50" height="50" alt="User">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Jane Smith</h6>
                                <small class="text-muted">Timber Trader, Lagos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-quote-left fa-2x text-success"></i>
                        </div>
                        <p class="card-text fst-italic">"The platform has made it easy for us to monitor tree growth and forest health. The data visualization tools are incredibly helpful for our research."</p>
                        <div class="d-flex align-items-center mt-4">
                            <div class="flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name=Dr.+Adeola&background=4CAF50&color=fff" class="rounded-circle" width="50" height="50" alt="User">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Dr. Adeola Johnson</h6>
                                <small class="text-muted">Forest Researcher, UI</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>