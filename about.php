<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = "About Us";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'About Us', 'url' => '', 'active' => true]
];
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h1 class="display-4 fw-bold mb-4">About <?php echo SITE_NAME; ?></h1>
            <p class="lead">Revolutionizing forest management through technology and sustainability</p>
        </div>
    </div>
    
    <div class="row align-items-center mb-5">
        <div class="col-md-6 mb-4 mb-md-0">
            <img src="<?php echo BASE_URL; ?>assets/img/placeholder/forest.jpg" class="img-fluid rounded-3 shadow" alt="Forest Conservation">
        </div>
        <div class="col-md-6">
            <h2 class="section-title d-inline-block mb-4">Our Mission</h2>
            <p class="fs-5">At <?php echo SITE_NAME; ?>, we're dedicated to creating a sustainable future for Nigeria's forests through innovative technology and responsible management practices.</p>
            <p>Our mission is to balance economic development with environmental conservation by providing a transparent, secure platform for timber trading while actively protecting our forest reserves from illegal activities.</p>
            
            <div class="mt-5">
                <h4 class="mb-4">Our Core Values:</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="core-value-card d-flex align-items-center">
                            <div class="core-value-icon-wrapper">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="core-value-content">
                                <h6 class="mb-1">Sustainability</h6>
                                <p class="small mb-0">We prioritize long-term forest health over short-term gains.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="core-value-card d-flex align-items-center">
                            <div class="core-value-icon-wrapper">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="core-value-content">
                                <h6 class="mb-1">Security</h6>
                                <p class="small mb-0">Our platform ensures secure transactions and data protection.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="core-value-card d-flex align-items-center">
                            <div class="core-value-icon-wrapper">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="core-value-content">
                                <h6 class="mb-1">Transparency</h6>
                                <p class="small mb-0">We provide clear information about timber sources and pricing.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="core-value-card d-flex align-items-center">
                            <div class="core-value-icon-wrapper">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="core-value-content">
                                <h6 class="mb-1">Community</h6>
                                <p class="small mb-0">We work with local communities to ensure fair benefits.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-12">
            <h2 class="section-title d-inline-block mb-4">Our Story</h2>
            <p class="fs-5">Founded in partnership with the University of Ibadan's Forestry Department, <?php echo SITE_NAME; ?> was created to address the challenges facing Nigeria's forest reserves.</p>
            <p>Illegal logging, lack of transparency in timber trading, and inadequate monitoring systems have threatened our valuable forest resources for decades. Our team of forestry experts, technologists, and conservationists came together to develop a comprehensive solution that combines cutting-edge technology with sustainable forestry practices.</p>
            <p>Today, <?php echo SITE_NAME; ?> serves as a model for responsible forest management, connecting timber buyers with sustainably harvested wood while providing forest rangers with the tools they need to protect our natural heritage.</p>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h2 class="section-title d-inline-block mb-4">Forest Conservation Impact</h2>
        </div>
        <div class="col-md-3 mb-4">
            <div class="text-center">
                <div class="display-4 fw-bold text-success">65%</div>
                <h5>Reduction in Illegal Logging</h5>
                <p class="text-muted">Since implementing our reporting system</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="text-center">
                <div class="display-4 fw-bold text-success">18,168</div>
                <h5>Hectares Protected</h5>
                <p class="text-muted">Across Nigeria's forest reserves</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="text-center">
                <div class="display-4 fw-bold text-success">200+</div>
                <h5>Tree Species Preserved</h5>
                <p class="text-muted">Through sustainable harvesting practices</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="text-center">
                <div class="display-4 fw-bold text-success">5,000+</div>
                <h5>Community Members Engaged</h5>
                <p class="text-muted">In conservation and monitoring efforts</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class="fas fa-bullhorn me-2 text-success"></i>Reporting System</h4>
                    <p>Our real-time illegal logging reporting system empowers citizens to protect our forests. With just a few clicks, anyone can report suspicious activities, which are immediately routed to forest management teams for investigation.</p>
                    <p>This community-driven approach has proven highly effective, with over 95% of reported incidents receiving a response within 24 hours.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class="fas fa-shopping-cart me-2 text-success"></i>Timber Marketplace</h4>
                    <p>Our secure timber trading platform connects buyers with sustainably harvested wood from certified forest reserves. Each tree is carefully documented with detailed specifications, ensuring transparency and accountability throughout the supply chain.</p>
                    <p>All transactions are securely processed, and a portion of each sale is reinvested in forest conservation and community development programs.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>