/**
 * Payroll-specific JavaScript functions
 */

// Payroll processing utilities
window.PayrollUtils = {
    
    // Format currency for Indian locale
    formatCurrency: function(amount) {
        return '₹' + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },
    
    // Calculate net salary
    calculateNetSalary: function(earnings, deductions) {
        const totalEarnings = earnings.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0);
        const totalDeductions = deductions.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0);
        return totalEarnings - totalDeductions;
    },
    
    // Validate salary component formula
    validateFormula: function(formula, availableComponents) {
        if (!formula || formula.trim() === '') {
            return { valid: true };
        }
        
        // Check for valid operators and component codes
        const allowedPattern = /^[A-Z_0-9\+\-\*\/\(\)\.\s]+$/;
        
        if (!allowedPattern.test(formula)) {
            return { valid: false, message: 'Formula contains invalid characters' };
        }
        
        // Check for balanced parentheses
        const openCount = (formula.match(/\(/g) || []).length;
        const closeCount = (formula.match(/\)/g) || []).length;
        
        if (openCount !== closeCount) {
            return { valid: false, message: 'Unbalanced parentheses in formula' };
        }
        
        // Check if all component codes exist
        const componentCodes = formula.match(/[A-Z_]+/g) || [];
        const invalidCodes = componentCodes.filter(code => !availableComponents.includes(code));
        
        if (invalidCodes.length > 0) {
            return { valid: false, message: 'Invalid component codes: ' + invalidCodes.join(', ') };
        }
        
        return { valid: true };
    },
    
    // Calculate pro-rata salary
    calculateProRata: function(amount, totalDays, actualDays) {
        return (amount / totalDays) * actualDays;
    },
    
    // Calculate working days between dates
    calculateWorkingDays: function(startDate, endDate, holidays = []) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        let workingDays = 0;
        
        while (start <= end) {
            const dayOfWeek = start.getDay();
            const dateString = start.toISOString().split('T')[0];
            
            // Skip weekends (0 = Sunday, 6 = Saturday)
            if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                // Skip holidays
                if (!holidays.includes(dateString)) {
                    workingDays++;
                }
            }
            
            start.setDate(start.getDate() + 1);
        }
        
        return workingDays;
    },
    
    // Generate payroll period name
    generatePeriodName: function(startDate) {
        const date = new Date(startDate);
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        return monthNames[date.getMonth()] + ' ' + date.getFullYear();
    },
    
    // Validate payroll period dates
    validatePeriodDates: function(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (start >= end) {
            return { valid: false, message: 'End date must be after start date' };
        }
        
        const daysDiff = (end - start) / (1000 * 60 * 60 * 24);
        
        if (daysDiff > 31) {
            return { valid: false, message: 'Period cannot exceed 31 days' };
        }
        
        if (daysDiff < 1) {
            return { valid: false, message: 'Period must be at least 1 day' };
        }
        
        return { valid: true };
    },
    
    // Calculate EMI
    calculateEMI: function(principal, rate, tenure) {
        if (rate === 0) {
            return principal / tenure;
        }
        
        const monthlyRate = rate / (12 * 100);
        const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, tenure)) / 
                   (Math.pow(1 + monthlyRate, tenure) - 1);
        
        return Math.round(emi * 100) / 100;
    },
    
    // Format employee name for display
    formatEmployeeName: function(employee) {
        return `${employee.emp_code} - ${employee.first_name} ${employee.last_name}`;
    },
    
    // Get financial year from date
    getFinancialYear: function(date = null) {
        const currentDate = date ? new Date(date) : new Date();
        const currentMonth = currentDate.getMonth() + 1;
        const currentYear = currentDate.getFullYear();
        
        if (currentMonth >= 4) {
            return currentYear + '-' + (currentYear + 1);
        } else {
            return (currentYear - 1) + '-' + currentYear;
        }
    },
    
    // Validate PAN number
    validatePAN: function(pan) {
        const panPattern = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
        return panPattern.test(pan);
    },
    
    // Validate Aadhaar number
    validateAadhaar: function(aadhaar) {
        const cleanAadhaar = aadhaar.replace(/\s/g, '');
        return cleanAadhaar.length === 12 && /^\d+$/.test(cleanAadhaar);
    },
    
    // Format Aadhaar number with spaces
    formatAadhaar: function(aadhaar) {
        const clean = aadhaar.replace(/\D/g, '');
        if (clean.length <= 4) return clean;
        if (clean.length <= 8) return clean.slice(0, 4) + ' ' + clean.slice(4);
        return clean.slice(0, 4) + ' ' + clean.slice(4, 8) + ' ' + clean.slice(8, 12);
    },
    
    // Validate IFSC code
    validateIFSC: function(ifsc) {
        const ifscPattern = /^[A-Z]{4}0[A-Z0-9]{6}$/;
        return ifscPattern.test(ifsc);
    },
    
    // Show confirmation dialog for critical actions
    confirmAction: function(message, callback) {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
        }
    },
    
    // Debounce function for search inputs
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Auto-save form data to localStorage
    autoSave: function(formId, key) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        // Load saved data
        const savedData = localStorage.getItem(key);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(fieldName => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    if (field && field.type !== 'password') {
                        field.value = data[fieldName];
                    }
                });
            } catch (e) {
                console.error('Error loading saved form data:', e);
            }
        }
        
        // Save data on input
        form.addEventListener('input', this.debounce(() => {
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (!key.includes('csrf_token') && !key.includes('password')) {
                    data[key] = value;
                }
            }
            
            localStorage.setItem(key, JSON.stringify(data));
        }, 1000));
        
        // Clear saved data on successful submit
        form.addEventListener('submit', () => {
            setTimeout(() => {
                localStorage.removeItem(key);
            }, 2000);
        });
    },
    
    // Initialize tooltips
    initTooltips: function() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-black rounded shadow-lg';
                tooltip.textContent = this.dataset.tooltip;
                tooltip.style.bottom = '100%';
                tooltip.style.left = '50%';
                tooltip.style.transform = 'translateX(-50%)';
                tooltip.style.marginBottom = '5px';
                
                this.style.position = 'relative';
                this.appendChild(tooltip);
            });
            
            element.addEventListener('mouseleave', function() {
                const tooltip = this.querySelector('.absolute.z-50');
                if (tooltip) {
                    tooltip.remove();
                }
            });
        });
    }
};

// Initialize payroll utilities when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    PayrollUtils.initTooltips();
    
    // Auto-format currency inputs
    document.querySelectorAll('input[data-currency]').forEach(input => {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
        });
    });
    
    // Auto-format PAN inputs
    document.querySelectorAll('input[name*="pan"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
    
    // Auto-format Aadhaar inputs
    document.querySelectorAll('input[name*="aadhaar"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = PayrollUtils.formatAadhaar(this.value);
        });
    });
    
    // Auto-format IFSC inputs
    document.querySelectorAll('input[name*="ifsc"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
});

// Export for use in other scripts
window.PayrollUtils = PayrollUtils;