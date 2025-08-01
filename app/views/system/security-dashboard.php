<?php 
$title = 'Security Dashboard - System Administration';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Security Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">
            Monitor system security, access logs, and threat detection
        </p>
    </div>

    <!-- Security Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Security Score</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo $security_score ?? 85; ?>%</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sign-in-alt text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Login Success Rate</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $login_success_rate ?? 95; ?>%</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Suspicious Activities</p>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo count($suspicious_activities ?? []); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ban text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Blocked IPs</p>
                    <p class="text-2xl font-bold text-red-600"><?php echo count($blocked_ips ?? []); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Alerts -->
    <?php if (!empty($security_alerts)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Security Alerts</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($security_alerts as $alert): ?>
                            <li><?php echo htmlspecialchars($alert['message']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Security Events -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Security Events</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <?php if (!empty($recent_events)): ?>
                        <?php foreach ($recent_events as $event): ?>
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                                        <?php 
                                        switch($event['event_type']) {
                                            case 'login_success': echo 'bg-green-100'; break;
                                            case 'login_failure': echo 'bg-red-100'; break;
                                            case 'session_expired': echo 'bg-yellow-100'; break;
                                            default: echo 'bg-gray-100';
                                        }
                                        ?>">
                                        <i class="fas fa-<?php 
                                            switch($event['event_type']) {
                                                case 'login_success': echo 'check text-green-600'; break;
                                                case 'login_failure': echo 'times text-red-600'; break;
                                                case 'session_expired': echo 'clock text-yellow-600'; break;
                                                default: echo 'info text-gray-600';
                                            }
                                        ?> text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo ucwords(str_replace('_', ' ', $event['event_type'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        IP: <?php echo htmlspecialchars($event['ip_address']); ?>
                                        <?php if ($event['user_id']): ?>
                                            • User ID: <?php echo $event['user_id']; ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        <?php echo date('M j, Y H:i:s', strtotime($event['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No recent security events</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Blocked IPs -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Blocked IP Addresses</h3>
                    <button onclick="openBlockIPModal()" class="btn btn-outline btn-sm">
                        <i class="fas fa-ban mr-2"></i>Block IP
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <?php if (!empty($blocked_ips)): ?>
                        <?php foreach ($blocked_ips as $ip): ?>
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($ip['ip_address']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($ip['reason']); ?></p>
                                    <p class="text-xs text-gray-400">
                                        Blocked until: <?php echo date('M j, Y H:i', strtotime($ip['blocked_until'])); ?>
                                    </p>
                                </div>
                                <button onclick="unblockIP('<?php echo $ip['ip_address']; ?>')" 
                                        class="text-red-600 hover:text-red-900" title="Unblock">
                                    <i class="fas fa-unlock"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No blocked IP addresses</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Actions -->
    <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Security Actions</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button onclick="generateSecurityReport()" class="btn btn-outline">
                    <i class="fas fa-file-alt mr-2"></i>
                    Generate Security Report
                </button>
                <button onclick="scanForThreats()" class="btn btn-outline">
                    <i class="fas fa-search mr-2"></i>
                    Scan for Threats
                </button>
                <button onclick="exportSecurityLogs()" class="btn btn-outline">
                    <i class="fas fa-download mr-2"></i>
                    Export Security Logs
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div id="block-ip-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Block IP Address</h3>
            <form id="block-ip-form">
                <div class="mb-4">
                    <label for="ip-address" class="block text-sm font-medium text-gray-700 mb-2">IP Address *</label>
                    <input type="text" name="ip_address" id="ip-address" required
                           class="form-input" placeholder="192.168.1.1">
                </div>
                
                <div class="mb-4">
                    <label for="block-reason" class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
                    <textarea name="reason" id="block-reason" required rows="3"
                              class="form-textarea" placeholder="Reason for blocking this IP"></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="block-duration" class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                    <select name="duration" id="block-duration" class="form-select">
                        <option value="3600">1 Hour</option>
                        <option value="86400">24 Hours</option>
                        <option value="604800">7 Days</option>
                        <option value="2592000">30 Days</option>
                        <option value="0">Permanent</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeBlockIPModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban mr-2"></i>Block IP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBlockIPModal() {
    document.getElementById('block-ip-modal').classList.remove('hidden');
}

function closeBlockIPModal() {
    document.getElementById('block-ip-modal').classList.add('hidden');
    document.getElementById('block-ip-form').reset();
}

function unblockIP(ipAddress) {
    if (confirm(`Are you sure you want to unblock IP address ${ipAddress}?`)) {
        showLoading();
        
        fetch('/system/unblock-ip', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ip_address: ipAddress,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage('IP address unblocked successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Failed to unblock IP address', 'error');
        });
    }
}

function generateSecurityReport() {
    window.location.href = '/system/security-report?format=pdf';
}

function scanForThreats() {
    showLoading();
    
    fetch('/system/scan-threats', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            csrf_token: '<?php echo $csrf_token; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage(`Threat scan completed. Found ${data.threats_found} potential threats.`, 'info');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Threat scan failed', 'error');
    });
}

function exportSecurityLogs() {
    window.location.href = '/system/export-security-logs?format=csv';
}

// Form submission
document.getElementById('block-ip-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.csrf_token = '<?php echo $csrf_token; ?>';
    
    showLoading();
    
    fetch('/system/block-ip', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage('IP address blocked successfully', 'success');
            closeBlockIPModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Failed to block IP address', 'error');
    });
});

// Auto-refresh security events every 30 seconds
setInterval(function() {
    fetch('/system/security-events')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update security events display
                updateSecurityEvents(data.events);
            }
        })
        .catch(error => console.error('Error refreshing security events:', error));
}, 30000);
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>