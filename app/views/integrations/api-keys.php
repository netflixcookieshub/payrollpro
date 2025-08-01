<?php 
$title = 'API Key Management - System Integrations';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">API Key Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage API keys for external system access and integrations
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Generate API Key
            </button>
        </div>
    </div>

    <!-- API Keys List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Key Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Permissions
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Last Used
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($api_keys)): ?>
                        <?php foreach ($api_keys as $key): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($key['name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach (explode(',', $key['permissions']) as $permission): ?>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?php echo trim($permission); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $key['last_used'] ? date('M j, Y H:i', strtotime($key['last_used'])) : 'Never'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php echo $key['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($key['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M j, Y', strtotime($key['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewUsage(<?php echo $key['id']; ?>)" 
                                                class="text-blue-600 hover:text-blue-900" title="View Usage">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>
                                        <?php if ($key['status'] === 'active'): ?>
                                            <button onclick="revokeKey(<?php echo $key['id']; ?>)" 
                                                    class="text-red-600 hover:text-red-900" title="Revoke">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-key text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No API keys found</p>
                                    <p class="text-sm">Generate your first API key to enable external integrations</p>
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Generate API Key
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- API Documentation -->
    <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">API Documentation</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Available Endpoints</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800 mr-2">GET</span>
                            <code>/api/v1/employees</code>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800 mr-2">POST</span>
                            <code>/api/v1/employees</code>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800 mr-2">GET</span>
                            <code>/api/v1/attendance</code>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800 mr-2">POST</span>
                            <code>/api/v1/attendance</code>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800 mr-2">GET</span>
                            <code>/api/v1/payroll</code>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Authentication</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>Include the API key in the Authorization header:</p>
                        <code class="block bg-gray-100 p-2 rounded">
                            Authorization: Bearer YOUR_API_KEY
                        </code>
                        <p class="mt-4">Example request:</p>
                        <code class="block bg-gray-100 p-2 rounded text-xs">
                            curl -H "Authorization: Bearer YOUR_API_KEY" \<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;<?php echo BASE_URL; ?>/api/v1/employees
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create API Key Modal -->
<div id="create-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Generate API Key</h3>
            <form id="api-key-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label for="key_name" class="block text-sm font-medium text-gray-700 mb-2">Key Name *</label>
                    <input type="text" name="key_name" id="key_name" required
                           class="form-input" placeholder="e.g., Mobile App Integration">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions *</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="permissions[]" value="read" class="form-checkbox" checked>
                            <span class="ml-2 text-sm text-gray-700">Read Access</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="permissions[]" value="write" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Write Access</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="permissions[]" value="delete" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Delete Access</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeCreateModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Key</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- API Key Display Modal -->
<div id="key-display-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">API Key Generated</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your API Key</label>
                <div class="flex items-center">
                    <input type="text" id="generated-key" readonly class="form-input flex-1 font-mono text-sm">
                    <button onclick="copyKey()" class="ml-2 btn btn-outline btn-sm">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800">Important!</h4>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>This API key will only be shown once. Please copy and store it securely.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="closeKeyDisplayModal()" class="btn btn-primary">I've Saved the Key</button>
            </div>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('create-modal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('create-modal').classList.add('hidden');
    document.getElementById('api-key-form').reset();
}

function closeKeyDisplayModal() {
    document.getElementById('key-display-modal').classList.add('hidden');
    location.reload();
}

function copyKey() {
    const keyInput = document.getElementById('generated-key');
    keyInput.select();
    document.execCommand('copy');
    showMessage('API key copied to clipboard', 'success');
}

function revokeKey(keyId) {
    if (confirm('Are you sure you want to revoke this API key? This action cannot be undone.')) {
        showLoading();
        
        fetch('/integrations/api-keys', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'revoke',
                key_id: keyId,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage('API key revoked successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Failed to revoke API key', 'error');
        });
    }
}

function viewUsage(keyId) {
    // Implementation for viewing API key usage statistics
    showMessage('Usage statistics feature coming soon', 'info');
}

// Form submission
document.getElementById('api-key-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const permissions = formData.getAll('permissions[]');
    
    if (permissions.length === 0) {
        showMessage('Please select at least one permission', 'error');
        return;
    }
    
    const data = {
        key_name: formData.get('key_name'),
        permissions: permissions.join(','),
        csrf_token: formData.get('csrf_token')
    };
    
    showLoading();
    
    fetch('/integrations/api-keys', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'generate',
            ...data
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            document.getElementById('generated-key').value = data.api_key;
            closeCreateModal();
            document.getElementById('key-display-modal').classList.remove('hidden');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Failed to generate API key', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>