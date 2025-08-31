// Main JavaScript file for the forestry management system

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Confirm delete action
    window.confirmDelete = function(message) {
        return confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.');
    };
    
    // Handle form submissions with validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check for terms and conditions checkbox
            const termsCheckbox = form.querySelector('input[name="terms"]');
            if (termsCheckbox && !termsCheckbox.checked) {
                e.preventDefault();
                alert('You must accept the terms and conditions to proceed.');
                return false;
            }
            
            // Add loading indicator for payment forms
            if (form.classList.contains('payment-form')) {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="loading"></span> Processing...';
                submitBtn.disabled = true;
                
                // Reset button after 3 seconds if needed
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }, 3000);
            }
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Handle print functionality
    const printButtons = document.querySelectorAll('.btn-print');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Handle back button
    const backButtons = document.querySelectorAll('.btn-back');
    backButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.history.back();
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Handle modal events with proper timing
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            // Focus first input in modal after it's fully shown
            setTimeout(() => {
                const firstInput = this.querySelector('input, textarea, select');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 100);
        });
        
        modal.addEventListener('show.bs.modal', function() {
            // Prevent multiple modals from opening simultaneously
            document.querySelectorAll('.modal.show').forEach(otherModal => {
                if (otherModal !== this) {
                    const bsModal = bootstrap.Modal.getInstance(otherModal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
            });
        });
    });
    
    // Handle tab navigation
    const tabs = document.querySelectorAll('.nav-tabs a');
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const tabTrigger = new bootstrap.Tab(this);
            tabTrigger.show();
        });
    });
    
    // Handle collapse elements
    const collapses = document.querySelectorAll('.collapse');
    collapses.forEach(collapse => {
        collapse.addEventListener('shown.bs.collapse', function() {
            const trigger = document.querySelector(`[data-bs-target="#${this.id}"]`) ||
                          document.querySelector(`[href="#${this.id}"]`);
            if (trigger) {
                trigger.querySelector('i').classList.remove('fa-plus');
                trigger.querySelector('i').classList.add('fa-minus');
            }
        });
        
        collapse.addEventListener('hidden.bs.collapse', function() {
            const trigger = document.querySelector(`[data-bs-target="#${this.id}"]`) ||
                          document.querySelector(`[href="#${this.id}"]`);
            if (trigger) {
                trigger.querySelector('i').classList.remove('fa-minus');
                trigger.querySelector('i').classList.add('fa-plus');
            }
        });
    });
    
    // Handle file uploads preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector(`[data-preview="${input.id}"]`);
                    if (preview) {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Handle search functionality
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const searchResults = document.querySelector(this.dataset.results);
            if (searchResults) {
                const items = searchResults.querySelectorAll('.search-item');
                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }
        });
    });
    
    // Fix for modal blinking issue - prevent multiple event bindings
    document.addEventListener('click', function(e) {
        const target = e.target;
        if (target.matches('[data-bs-toggle="modal"]')) {
            // Prevent default behavior if needed
            e.preventDefault();
            
            // Get modal target
            const modalTarget = target.getAttribute('data-bs-target') || target.getAttribute('href');
            if (modalTarget) {
                const modal = document.querySelector(modalTarget);
                if (modal) {
                    // Check if modal is already open
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.show();
                    } else {
                        new bootstrap.Modal(modal).show();
                    }
                }
            }
        }
    });
});

// Utility functions
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(notification);
        bsAlert.close();
    }, 5000);
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Error:', e.error);
    // In production, you might want to send this to an error tracking service
});

// Prevent multiple form submissions
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            // Disable submit button to prevent multiple submissions
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                
                // Re-enable after 3 seconds if still disabled
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }, 3000);
            }
        });
    });
});