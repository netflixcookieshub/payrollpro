<?php 
$title = 'System Dashboard - Administration';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">System Administration</h1>
        <p class="mt-1 text-sm text-gray-500">
            Monitor system health, manage backups, and configure security settings
        </p>
    </div>

    <!-- System Health Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-server text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">System Status</p>
                    <p class="text-lg font-bold text-green-600">Healthy</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-database text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Database Size</p>
                    <p class="text-lg font-bold text-gray-900"><?php echo $stats['database_size'] ?? 'N/A'; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hdd text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Storage Used</p>
                    <p class="text-lg font-bold text-gray-900"><?php echo $stats['storage_used'] ?? 'N/A'; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Users</p>
                    <p class="text-lg font-bold text-gray-900"><?php echo $stats['total_users'] ?? 0; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-blue-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-medium text-gray-900">Security</h3>
            </div>
            <p class="text-sm text-gray-500 mb-4">Monitor security events and manage access controls</p>
            <a href="/system/security" class="btn btn-outline btn-sm w-full">
                <i class="fas fa-eye mr-2"></i>
                View Security Dashboard
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-database text-green-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-medium text-gray-900">Backups</h3>
            </div>
            <p class="text-sm text-gray-500 mb-4">Manage system backups and data recovery</p>
            <a href="/system/backup-manager" class="btn btn-outline btn-sm w-full">
                <i class="fas fa-download mr-2"></i>
                Manage Backups
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tools text-yellow-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-medium text-gray-900">Maintenance</h3>
            </div>
            <p class="text-sm text-gray-500 mb-4">Perform system maintenance and optimization</p>
            <button onclick="openMaintenanceModal()" class="btn btn-outline btn-sm w-full">
                <i class="fas fa-wrench mr-2"></i>
                Run Maintenance
            </button>
        </div>
    </div>

    <!-- System Information -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">System Information</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Application Details</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Version:</span>
                            <span class="font-medium"><?php echo APP_VERSION; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">PHP Version:</span>
                            <span class="font-medium"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Database:</span>
                            <span class="font-medium">MySQL</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Timezone:</span>
                            <span class="font-medium"><?php echo date_default_timezone_get(); ?></span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">System Resources</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Memory Usage:</span>
                            <span class="font-medium"><?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?> MB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Memory Limit:</span>
                            <span class="font-medium"><?php echo ini_get('memory_limit'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Max Execution Time:</span>
                            <span class="font-medium"><?php echo ini_get('max_execution_time'); ?>s</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Upload Max Size:</span>
                            <span class="font-medium"><?php echo ini_get('upload_max_filesize'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Modal -->
<div id="maintenance-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">System Maintenance</h3>
            <form id="maintenance-form">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Maintenance Tasks</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="tasks[]" value="cleanup_logs" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Cleanup Old Logs</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="tasks[]" value="optimize_database" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Optimize Database</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="tasks[]" value="clear_cache" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Clear Application Cache</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="tasks[]" value="cleanup_sessions" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Cleanup Expired Sessions</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeMaintenanceModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Run Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openMaintenanceModal() {
    document.getElementById('maintenance-modal').classList.remove('hidden');
}

function closeMaintenanceModal() {
    document.getElementById('maintenance-modal').classList.add('hidden');
    document.getElementById('maintenance-form').reset();
}

// Maintenance form submission
document.getElementById('maintenance-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const tasks = formData.getAll('tasks[]');
    
    if (tasks.length === 0) {
        showMessage('Please select at least one maintenance task', 'error');
        return;
    }
    
    if (confirm('Are you sure you want to run the selected maintenance tasks?')) {
        showLoading();
        
        fetch('/system/maintenance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                tasks: tasks,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                let message = 'Maintenance completed:\n';
                Object.keys(data.results).forEach(task => {
                    const result = data.results[task];
                    message += `• ${task}: ${result.success ? 'Success' : 'Failed'}\n`;
                });
                alert(message);
                closeMaintenanceModal();
            } else {
                showMessage('Maintenance failed', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Maintenance failed', 'error');
        });
    }
});

// Auto-refresh system stats every 30 seconds
setInterval(function() {
    fetch('/system/health')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update health indicators
                updateSystemHealth(data.health);
            }
        })
        .catch(error => console.error('Error refreshing system health:', error));
}, 30000);

function updateSystemHealth(health) {
    // Update system health indicators
    console.log('System health updated:', health);
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>