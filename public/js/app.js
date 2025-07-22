/**
 * Main application JavaScript
 */

// Global app object
window.PayrollApp = {
    baseUrl: window.location.origin,
    
    // Initialize application
    init: function() {
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupAjaxDefaults();
    },
    
    // Setup global event listeners
    setupEventListeners: function() {
        // Close flash messages
        document.addEventListener('click', function(e) {
            if (e.target.matches('.flash-close')) {
                e.target.closest('.flash-message').remove();
            }
        });
        
        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-confirm]')) {
                if (!confirm(e.target.dataset.confirm)) {
                    e.preventDefault();
                }
            }
        });
        
        // Auto-submit forms on select change
        document.addEventListener('change', function(e) {
            if (e.target.matches('[data-auto-submit]')) {
                e.target.closest('form').submit();
            }
        });
    },
    
    // Setup form validation
    setupFormValidation: function() {
        // Add validation styles
        const style = document.createElement('style');
        style.textContent = `
            .field-error { border-color: #dc2626 !important; }
            .error-message { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; }
        `;
        document.head.appendChild(style);
    },
    
    // Setup AJAX defaults
    setupAjaxDefaults: function() {
        // Add CSRF token to all AJAX requests
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            this.csrfToken = token.getAttribute('content');
        }
    },
    
    // Show flash message
    showFlashMessage: function(message, type = 'info', duration = 5000) {
        const container = document.getElementById('flash-messages') || this.createFlashContainer();
        
        const colors = {
            success: 'bg-green-100 text-green-800 border-green-200',
            error: 'bg-red-100 text-red-800 border-red-200',
            warning: 'bg-yellow-100 text-yellow-800 border-yellow-200',
            info: 'bg-blue-100 text-blue-800 border-blue-200'
        };
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        const flashDiv = document.createElement('div');
        flashDiv.className = `flash-message mb-4 p-4 rounded-lg border ${colors[type]} animate-slide-down`;
        flashDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${icons[type]} mr-2"></i>
                <span class="flex-1">${message}</span>
                <button type="button" class="flash-close ml-4 text-current opacity-70 hover:opacity-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        container.appendChild(flashDiv);
        
        if (duration > 0) {
            setTimeout(() => {
                if (flashDiv.parentNode) {
                    flashDiv.remove();
                }
            }, duration);
        }
    },
    
    // Create flash message container
    createFlashContainer: function() {
        const container = document.createElement('div');
        container.id = 'flash-messages';
        container.className = 'fixed top-4 right-4 z-50 max-w-sm';
        document.body.appendChild(container);
        return container;
    },
    
    // Show loading overlay
    showLoading: function() {
        document.getElementById('loading-overlay').classList.remove('hidden');
    },
    
    // Hide loading overlay
    hideLoading: function() {
        document.getElementById('loading-overlay').classList.add('hidden');
    },
    
    // Make AJAX request
    ajax: function(options) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        // Add CSRF token for non-GET requests
        if (options.method !== 'GET' && this.csrfToken) {
            if (options.body && typeof options.body === 'object') {
                options.body.csrf_token = this.csrfToken;
            }
            defaults.headers['X-CSRF-Token'] = this.csrfToken;
        }
        
        const config = Object.assign({}, defaults, options);
        
        if (config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }
        
        return fetch(config.url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                this.showFlashMessage('An error occurred. Please try again.', 'error');
                throw error;
            });
    },
    
    // Form validation helpers
    validateForm: function(formElement) {
        const errors = {};
        const formData = new FormData(formElement);
        
        // Clear previous errors
        formElement.querySelectorAll('.field-error').forEach(el => {
            el.classList.remove('field-error');
        });
        formElement.querySelectorAll('.error-message').forEach(el => {
            el.remove();
        });
        
        // Validate required fields
        formElement.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                errors[field.name] = 'This field is required';
                this.showFieldError(field, errors[field.name]);
            }
        });
        
        // Validate email fields
        formElement.querySelectorAll('input[type="email"]').forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                errors[field.name] = 'Please enter a valid email address';
                this.showFieldError(field, errors[field.name]);
            }
        });
        
        return Object.keys(errors).length === 0;
    },
    
    // Show field error
    showFieldError: function(field, message) {
        field.classList.add('field-error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    },
    
    // Email validation
    isValidEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Format currency
    formatCurrency: function(amount, currency = '₹') {
        return currency + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },
    
    // Format date
    formatDate: function(dateString, format = 'MMM DD, YYYY') {
        const date = new Date(dateString);
        const options = {};
        
        if (format === 'MMM DD, YYYY') {
            options.year = 'numeric';
            options.month = 'short';
            options.day = '2-digit';
        }
        
        return date.toLocaleDateString('en-US', options);
    },
    
    // Toggle element visibility
    toggle: function(selector) {
        const element = document.querySelector(selector);
        if (element) {
            element.classList.toggle('hidden');
        }
    },
    
    // Confirm action
    confirm: function(message, callback) {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
        }
    },
    
    // Debounce function
    debounce: function(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            
            if (callNow) func.apply(context, args);
        };
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    PayrollApp.init();
});

// Utility functions
function showLoading() {
    PayrollApp.showLoading();
}

function hideLoading() {
    PayrollApp.hideLoading();
}

function showMessage(message, type) {
    PayrollApp.showFlashMessage(message, type);
}

// Add animation styles
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }
        
        .transition-all {
            transition: all 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.05);
        }
        
        .loading-spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});