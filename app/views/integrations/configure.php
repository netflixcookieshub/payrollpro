<?php 
$title = 'Configure Integration - ' . ucfirst($integration);
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/integrations" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Configure <?php echo ucfirst($integration); ?> Integration</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Set up connection parameters and authentication details
                </p>
            </div>
        </div>
    </div>

    <!-- Configuration Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Integration Settings</h3>
        </div>
        <div class="p-6">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <?php switch($integration): case 'hrms': ?>
                    <!-- HRMS Configuration -->
                    <div class="space-y-6">
                        <div>
                            <label for="api_url" class="block text-sm font-medium text-gray-700 mb-2">API URL *</label>
                            <input type="url" name="api_url" id="api_url" required
                                   value="<?php echo $config['api_url'] ?? ''; ?>"
                                   class="form-input" placeholder="https://api.hrms.com/v1">
                        </div>
                        
                        <div>
                            <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">API Key *</label>
                            <input type="password" name="api_key" id="api_key" required
                                   value="<?php echo $config['api_key'] ?? ''; ?>"
                                   class="form-input" placeholder="Your HRMS API key">
                        </div>
                        
                        <div>
                            <label for="sync_frequency" class="block text-sm font-medium text-gray-700 mb-2">Sync Frequency</label>
                            <select name="sync_frequency" id="sync_frequency" class="form-select">
                                <option value="hourly" <?php echo ($config['sync_frequency'] ?? '') === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                                <option value="daily" <?php echo ($config['sync_frequency'] ?? '') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="weekly" <?php echo ($config['sync_frequency'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                <option value="manual" <?php echo ($config['sync_frequency'] ?? '') === 'manual' ? 'selected' : ''; ?>>Manual</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="auto_create_employees" class="form-checkbox" 
                                       <?php echo ($config['auto_create_employees'] ?? false) ? 'checked' : ''; ?>>
                                <span class="ml-2 text-sm text-gray-700">Auto-create new employees</span>
                            </label>
                        </div>
                    </div>
                <?php break; case 'banking': ?>
                    <!-- Banking Configuration -->
                    <div class="space-y-6">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name *</label>
                            <select name="bank_name" id="bank_name" required class="form-select">
                                <option value="">Select Bank</option>
                                <option value="sbi" <?php echo ($config['bank_name'] ?? '') === 'sbi' ? 'selected' : ''; ?>>State Bank of India</option>
                                <option value="hdfc" <?php echo ($config['bank_name'] ?? '') === 'hdfc' ? 'selected' : ''; ?>>HDFC Bank</option>
                                <option value="icici" <?php echo ($config['bank_name'] ?? '') === 'icici' ? 'selected' : ''; ?>>ICICI Bank</option>
                                <option value="axis" <?php echo ($config['bank_name'] ?? '') === 'axis' ? 'selected' : ''; ?>>Axis Bank</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">Company Account Number *</label>
                            <input type="text" name="account_number" id="account_number" required
                                   value="<?php echo $config['account_number'] ?? ''; ?>"
                                   class="form-input" placeholder="Company bank account number">
                        </div>
                        
                        <div>
                            <label for="ifsc_code" class="block text-sm font-medium text-gray-700 mb-2">IFSC Code *</label>
                            <input type="text" name="ifsc_code" id="ifsc_code" required
                                   value="<?php echo $config['ifsc_code'] ?? ''; ?>"
                                   class="form-input" placeholder="SBIN0001234">
                        </div>
                        
                        <div>
                            <label for="transfer_mode" class="block text-sm font-medium text-gray-700 mb-2">Transfer Mode</label>
                            <select name="transfer_mode" id="transfer_mode" class="form-select">
                                <option value="neft" <?php echo ($config['transfer_mode'] ?? '') === 'neft' ? 'selected' : ''; ?>>NEFT</option>
                                <option value="rtgs" <?php echo ($config['transfer_mode'] ?? '') === 'rtgs' ? 'selected' : ''; ?>>RTGS</option>
                                <option value="imps" <?php echo ($config['transfer_mode'] ?? '') === 'imps' ? 'selected' : ''; ?>>IMPS</option>
                            </select>
                        </div>
                    </div>
                <?php break; case 'email': ?>
                    <!-- Email Configuration -->
                    <div class="space-y-6">
                        <div>
                            <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-2">SMTP Host *</label>
                            <input type="text" name="smtp_host" id="smtp_host" required
                                   value="<?php echo $config['smtp_host'] ?? ''; ?>"
                                   class="form-input" placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-2">SMTP Port *</label>
                                <input type="number" name="smtp_port" id="smtp_port" required
                                       value="<?php echo $config['smtp_port'] ?? '587'; ?>"
                                       class="form-input" placeholder="587">
                            </div>
                            
                            <div>
                                <label for="encryption" class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                                <select name="encryption" id="encryption" class="form-select">
                                    <option value="tls" <?php echo ($config['encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo ($config['encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="none" <?php echo ($config['encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                            <input type="email" name="username" id="username" required
                                   value="<?php echo $config['username'] ?? ''; ?>"
                                   class="form-input" placeholder="your-email@gmail.com">
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                            <input type="password" name="password" id="password" required
                                   value="<?php echo $config['password'] ?? ''; ?>"
                                   class="form-input" placeholder="Your email password">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="from_email" class="block text-sm font-medium text-gray-700 mb-2">From Email *</label>
                                <input type="email" name="from_email" id="from_email" required
                                       value="<?php echo $config['from_email'] ?? ''; ?>"
                                       class="form-input" placeholder="noreply@company.com">
                            </div>
                            
                            <div>
                                <label for="from_name" class="block text-sm font-medium text-gray-700 mb-2">From Name *</label>
                                <input type="text" name="from_name" id="from_name" required
                                       value="<?php echo $config['from_name'] ?? ''; ?>"
                                       class="form-input" placeholder="Company HR">
                            </div>
                        </div>
                    </div>
                <?php break; case 'attendance': ?>
                    <!-- Attendance Device Configuration -->
                    <div class="space-y-6">
                        <div>
                            <label for="device_type" class="block text-sm font-medium text-gray-700 mb-2">Device Type *</label>
                            <select name="device_type" id="device_type" required class="form-select">
                                <option value="">Select Device Type</option>
                                <option value="zkteco" <?php echo ($config['device_type'] ?? '') === 'zkteco' ? 'selected' : ''; ?>>ZKTeco</option>
                                <option value="essl" <?php echo ($config['device_type'] ?? '') === 'essl' ? 'selected' : ''; ?>>ESSL</option>
                                <option value="realtime" <?php echo ($config['device_type'] ?? '') === 'realtime' ? 'selected' : ''; ?>>Realtime</option>
                                <option value="generic" <?php echo ($config['device_type'] ?? '') === 'generic' ? 'selected' : ''; ?>>Generic</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="device_ip" class="block text-sm font-medium text-gray-700 mb-2">Device IP Address *</label>
                            <input type="text" name="device_ip" id="device_ip" required
                                   value="<?php echo $config['device_ip'] ?? ''; ?>"
                                   class="form-input" placeholder="192.168.1.100">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="device_port" class="block text-sm font-medium text-gray-700 mb-2">Port</label>
                                <input type="number" name="device_port" id="device_port"
                                       value="<?php echo $config['device_port'] ?? '4370'; ?>"
                                       class="form-input" placeholder="4370">
                            </div>
                            
                            <div>
                                <label for="sync_interval" class="block text-sm font-medium text-gray-700 mb-2">Sync Interval (minutes)</label>
                                <input type="number" name="sync_interval" id="sync_interval"
                                       value="<?php echo $config['sync_interval'] ?? '15'; ?>"
                                       class="form-input" placeholder="15">
                            </div>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="auto_sync" class="form-checkbox" 
                                       <?php echo ($config['auto_sync'] ?? false) ? 'checked' : ''; ?>>
                                <span class="ml-2 text-sm text-gray-700">Enable automatic synchronization</span>
                            </label>
                        </div>
                    </div>
                <?php break; default: ?>
                    <!-- Generic Configuration -->
                    <div class="space-y-6">
                        <div>
                            <label for="api_url" class="block text-sm font-medium text-gray-700 mb-2">API URL</label>
                            <input type="url" name="api_url" id="api_url"
                                   value="<?php echo $config['api_url'] ?? ''; ?>"
                                   class="form-input" placeholder="https://api.example.com">
                        </div>
                        
                        <div>
                            <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                            <input type="password" name="api_key" id="api_key"
                                   value="<?php echo $config['api_key'] ?? ''; ?>"
                                   class="form-input" placeholder="Your API key">
                        </div>
                        
                        <div>
                            <label for="webhook_secret" class="block text-sm font-medium text-gray-700 mb-2">Webhook Secret</label>
                            <input type="password" name="webhook_secret" id="webhook_secret"
                                   value="<?php echo $config['webhook_secret'] ?? ''; ?>"
                                   class="form-input" placeholder="Webhook verification secret">
                        </div>
                    </div>
                <?php endswitch; ?>
                
                <!-- Common Settings -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">General Settings</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="enabled" class="form-checkbox" 
                                       <?php echo ($config['enabled'] ?? false) ? 'checked' : ''; ?>>
                                <span class="ml-2 text-sm text-gray-700">Enable this integration</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="log_requests" class="form-checkbox" 
                                       <?php echo ($config['log_requests'] ?? true) ? 'checked' : ''; ?>>
                                <span class="ml-2 text-sm text-gray-700">Log all requests and responses</span>
                            </label>
                        </div>
                        
                        <div>
                            <label for="timeout" class="block text-sm font-medium text-gray-700 mb-2">Request Timeout (seconds)</label>
                            <input type="number" name="timeout" id="timeout"
                                   value="<?php echo $config['timeout'] ?? '30'; ?>"
                                   class="form-input" min="5" max="300">
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="mt-8 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button type="button" onclick="testConnection()" class="btn btn-outline">
                            <i class="fas fa-check-circle mr-2"></i>
                            Test Connection
                        </button>
                        <button type="button" onclick="generateWebhookUrl()" class="btn btn-outline">
                            <i class="fas fa-link mr-2"></i>
                            Generate Webhook URL
                        </button>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="/integrations" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Save Configuration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Integration Documentation -->
    <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Integration Documentation</h3>
        </div>
        <div class="p-6">
            <?php switch($integration): case 'hrms': ?>
                <div class="prose text-sm text-gray-600">
                    <h4>HRMS Integration Setup</h4>
                    <p>This integration allows automatic synchronization of employee data between your HRMS and the payroll system.</p>
                    <ul>
                        <li><strong>API URL:</strong> The base URL of your HRMS API endpoint</li>
                        <li><strong>API Key:</strong> Authentication key provided by your HRMS vendor</li>
                        <li><strong>Sync Frequency:</strong> How often to sync data automatically</li>
                        <li><strong>Webhook URL:</strong> <code><?php echo BASE_URL; ?>/integrations/webhook/hrms</code></li>
                    </ul>
                </div>
            <?php break; case 'banking': ?>
                <div class="prose text-sm text-gray-600">
                    <h4>Banking Integration Setup</h4>
                    <p>Configure direct salary transfer to employee bank accounts.</p>
                    <ul>
                        <li><strong>Bank Name:</strong> Select your company's primary bank</li>
                        <li><strong>Account Number:</strong> Company account from which salaries will be transferred</li>
                        <li><strong>Transfer Mode:</strong> NEFT for amounts up to ₹2 lakhs, RTGS for higher amounts</li>
                    </ul>
                </div>
            <?php break; case 'email': ?>
                <div class="prose text-sm text-gray-600">
                    <h4>Email Integration Setup</h4>
                    <p>Configure SMTP settings for sending payslips and notifications via email.</p>
                    <ul>
                        <li><strong>SMTP Host:</strong> Your email provider's SMTP server</li>
                        <li><strong>Port:</strong> Usually 587 for TLS or 465 for SSL</li>
                        <li><strong>Authentication:</strong> Your email credentials</li>
                    </ul>
                </div>
            <?php break; case 'attendance': ?>
                <div class="prose text-sm text-gray-600">
                    <h4>Attendance Device Integration</h4>
                    <p>Connect with biometric attendance devices for automatic data sync.</p>
                    <ul>
                        <li><strong>Device IP:</strong> Network IP address of your attendance device</li>
                        <li><strong>Port:</strong> Communication port (usually 4370 for ZKTeco)</li>
                        <li><strong>Sync Interval:</strong> How frequently to pull attendance data</li>
                    </ul>
                </div>
            <?php endswitch; ?>
        </div>
    </div>
</div>

<script>
function testConnection() {
    showLoading();
    
    fetch(`/integrations/test/<?php echo $integration; ?>`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage(data.message, 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Connection test failed', 'error');
    });
}

function generateWebhookUrl() {
    const webhookUrl = `<?php echo BASE_URL; ?>/integrations/webhook/<?php echo $integration; ?>`;
    
    // Copy to clipboard
    navigator.clipboard.writeText(webhookUrl).then(() => {
        showMessage('Webhook URL copied to clipboard', 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = webhookUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showMessage('Webhook URL copied to clipboard', 'success');
    });
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let hasErrors = false;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-300');
            hasErrors = true;
        } else {
            field.classList.remove('border-red-300');
        }
    });
    
    if (hasErrors) {
        e.preventDefault();
        showMessage('Please fill in all required fields', 'error');
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>