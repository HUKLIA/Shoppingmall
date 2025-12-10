/**
 * ShoppingMall - Main JavaScript
 * Client-side interactions and utilities
 */

document.addEventListener('DOMContentLoaded', function() {

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Quantity controls (for non-form based)
    const quantityControls = document.querySelectorAll('.quantity-control');
    quantityControls.forEach(function(control) {
        const input = control.querySelector('input[type="text"], input[type="number"]');
        const minusBtn = control.querySelector('button:first-child');
        const plusBtn = control.querySelector('button:last-child');

        if (input && minusBtn && plusBtn) {
            minusBtn.addEventListener('click', function(e) {
                if (!this.closest('form')) {
                    e.preventDefault();
                    let value = parseInt(input.value) || 1;
                    if (value > 1) {
                        input.value = value - 1;
                    }
                }
            });

            plusBtn.addEventListener('click', function(e) {
                if (!this.closest('form')) {
                    e.preventDefault();
                    let value = parseInt(input.value) || 1;
                    let max = parseInt(input.max) || 99;
                    if (value < max) {
                        input.value = value + 1;
                    }
                }
            });
        }
    });

    // Mobile menu toggle (for future enhancement)
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Form validation feedback
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.focus();
                }
            }
        });
    });

    // Clear form errors on input
    const formInputs = document.querySelectorAll('.form-control');
    formInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            this.classList.remove('error');
        });
    });

});

// Format price helper
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

// Debounce helper for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
