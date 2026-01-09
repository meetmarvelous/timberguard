<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = "Contact Us";
$breadcrumb = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Contact Us', 'url' => '', 'active' => true]
];
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h1 class="display-4 fw-bold mb-4">Get In Touch</h1>
            <p class="lead">Have questions about our forest management system or need to report an issue?</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body p-5">
                    <h2 class="section-title d-inline-block mb-4">Send Us a Message</h2>
                    
                    <form>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control form-control-lg" id="name" placeholder="Enter your name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control form-control-lg" id="subject" placeholder="Enter subject" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control form-control-lg" id="message" rows="6" placeholder="Enter your message" required></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-5">
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-5">
                    <h2 class="section-title d-inline-block mb-4">Contact Information</h2>
                    
                    <div class="contact-info-item">
                        <div class="contact-icon-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-content">
                            <h5>Address</h5>
                            <p>University of Ibadan<br>Forestry Department<br>Ibadan, Oyo State<br>Nigeria</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="contact-icon-wrapper">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-content">
                            <h5>Phone</h5>
                            <p>+234 800 000 0000<br>+234 800 000 0001</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="contact-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-content">
                            <h5>Email</h5>
                            <p>info@timberguard.com<br>support@timberguard.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item mb-0">
                        <div class="contact-icon-wrapper">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-content">
                            <h5>Office Hours</h5>
                            <p>Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: 9:00 AM - 1:00 PM<br>Sunday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow">
                <div class="card-body p-5">
                    <h5 class="mb-4">Emergency Reporting</h5>
                    <p class="mb-3">For urgent illegal logging reports, please contact our 24/7 emergency hotline:</p>
                    <div class="d-flex align-items-center">
                        <div class="emergency-contact-wrapper">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fs-5">+234 800 000 0002</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card border-0 shadow">
                <div class="card-body p-0">
                    <div class="ratio ratio-16x9">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="border-radius: 8px;">
                            <p class="text-muted"><i class="fas fa-map-marked-alt me-2"></i>Map will be displayed here (GIS integration)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>