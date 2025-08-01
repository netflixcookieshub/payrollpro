<?php 
$title = 'System Integrations - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">System Integrations</h1>
        <p class="mt-1 text-sm text-gray-500">
            Connect with external systems and manage data synchronization
        </p>
    </div>

    <!-- Integration Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php foreach ($integrations as $key => $integration): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-plug text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900"><?php echo $integration['name']; ?></h3>
                            <p class="text-sm text-gray-500"><?php echo $integration['type']; ?></p>
                        </div>
                    </div>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                        <?php echo $integration['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                        <?php echo ucfirst($integration['status']); ?>
                    </span>
                </div>
                
                <p class="text-sm text-gray-600 mb-4"><?php echo $integration['description']; ?></p>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button onclick="testIntegration('<?php echo $key; ?>')" 
                                class="btn btn-outline btn-sm">
                            <i class="fas fa-check-circle mr-1"></i>Test
                        </button>
                        <button onclick="syncIntegration('<?php echo $key; ?>')" 
                                class="btn btn-outline btn-sm">
                            <i class="fas fa-sync mr-1"></i>Sync
                        </button>
                    </div>
                    <a href="/integrations/configure/<?php echo $key; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-cog mr-1"></i>Configure
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-upload text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Bulk Import</h3>
                    <p class="text-sm text-gray-500">Import employee data</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="/integrations/import" class="text-green-600 hover:text-green-800 text-sm font-medium">
                    Import Data →
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-download text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Bulk Export</h3>
                    <p class="text-sm text-gray-500">Export system data</p>
                </div>
            </div>
            <div class="mt-4">
                <button onclick="openExportModal()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Export Data →
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-key text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">API Keys</h3>
                    <p class="text-sm text-gray-500">Manage API access</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="/integrations/api-keys" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                    Manage Keys →
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-webhook text-orange-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Webhooks</h3>
                    <p class="text-sm text-gray-500">Real-time data sync</p>
                </div>
            </div>
            <div class="mt-4">
                <button onclick="showWebhookInfo()" class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                    View Endpoints →
                </button>
            </div>
        </div>
    </div>

    <!-- Active Integrations -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Active Integrations</h3>
        </div>
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Integration
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Last Sync
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($active_integrations)): ?>
                        <?php foreach ($active_integrations as $integration): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($integration['name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($integration['type']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $integration['last_sync'] ? date('M j, Y H:i', strtotime($integration['last_sync'])) : 'Never'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php echo $integration['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($integration['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="testIntegration('<?php echo $integration['type']; ?>')" 
                                                class="text-blue-600 hover:text-blue-900" title="Test">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button onclick="syncIntegration('<?php echo $integration['type']; ?>')" 
                                                class="text-green-600 hover:text-green-900" title="Sync">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <a href="/integrations/configure/<?php echo $integration['type']; ?>" 
                                           class="text-indigo-600 hover:text-indigo-900" title="Configure">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-plug text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No active integrations</p>
                                    <p class="text-sm">Configure integrations to connect with external systems</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="export-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Export Data</h3>
            <form id="export-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Tables</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="tables[]" value="employees" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Employees</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="tables[]" value="departments" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Departments</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="tables[]" value="salary_components" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Salary Components</span>
                        </label>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="export_format" class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                    <select name="format" id="export_format" class="form-select">
                        <option value="json">JSON</option>
                        <option value="csv">CSV</option>
                        <option value="xml">XML</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeExportModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function testIntegration(type) {
    showLoading();
    
    fetch(`/integrations/test/${type}`, {
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
        showMessage('Test failed', 'error');
    });
}

function syncIntegration(type) {
    if (confirm('Are you sure you want to sync data? This may take some time.')) {
        showLoading();
        
        fetch(`/integrations/sync/${type}`, {
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
            showMessage('Sync failed', 'error');
        });
    }
}

function openExportModal() {
    document.getElementById('export-modal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('export-modal').classList.add('hidden');
}

function showWebhookInfo() {
    const webhookUrls = [
        'Attendance: /integrations/webhook/attendance',
        'HRMS: /integrations/webhook/hrms',
        'Banking: /integrations/webhook/banking'
    ];
    
    alert('Webhook Endpoints:\n\n' + webhookUrls.join('\n'));
}

// Export form submission
document.getElementById('export-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const tables = formData.getAll('tables[]');
    const format = formData.get('format');
    
    if (tables.length === 0) {
        showMessage('Please select at least one table', 'warning');
        return;
    }
    
    const params = new URLSearchParams();
    tables.forEach(table => params.append('tables[]', table));
    params.append('format', format);
    
    window.location.href = `/integrations/export-data?${params.toString()}`;
    closeExportModal();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>