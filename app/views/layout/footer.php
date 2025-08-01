<!-- Footer -->
    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <p class="text-sm text-gray-500">
                        © <?php echo date('Y'); ?> PayrollPro. All rights reserved.
                    </p>
                </div>
                <div class="flex items-center space-x-6">
                    <span class="text-sm text-gray-500">
                        Version <?php echo APP_VERSION; ?>
                    </span>
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-question-circle mr-1"></i>Help
                    </a>
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-info-circle mr-1"></i>About
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700">Loading...</span>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="flash-messages" class="fixed top-4 right-4 z-50"></div>

    <!-- Common JavaScript -->
    <script src="/public/js/app.js"></script>
    
    <!-- Page-specific scripts -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Global utility functions
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }
        
        function showLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.remove('hidden');
            }
        }
        
        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.add('hidden');
            }
        }
        
        function showMessage(message, type = 'info') {
            const container = document.getElementById('flash-messages');
            if (!container) return;
            
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
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `mb-4 p-4 rounded-lg border ${colors[type]} fade-in`;
            messageDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${icons[type]} mr-2"></i>
                    <span class="flex-1">${message}</span>
                    <button type="button" onclick="this.closest('div').remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(messageDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 5000);
        }
        
        // Form validation helper
        function validateForm(form) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-300');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-300');
                }
            });
            
            return isValid;
        }
        
        // Number formatting
        function formatCurrency(amount) {
            return '₹' + parseFloat(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Date formatting
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
        }
    </script>
</body>
</html>