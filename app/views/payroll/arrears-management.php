<?php 
$title = 'Arrears Management - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Arrears Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage employee arrears and pending payments
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <button onclick="openCreateModal()" class="btn btn-outline">
                <i class="fas fa-plus mr-2"></i>
                Add Arrears
            </button>
            <button onclick="bulkApprove()" class="btn btn-primary">
                <i class="fas fa-check mr-2"></i>
                Bulk Approve
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Arrears</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['pending_count'] ?? 0; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Approved</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['approved_count'] ?? 0; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Amount</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($stats['pending_amount'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hand-holding-usd text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Processed Amount</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($stats['processed_amount'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Arrears List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Pending Arrears</h3>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="select-all" class="form-checkbox">
                    <label for="select-all" class="text-sm text-gray-700">Select All</label>
                </div>
            </div>
        </div>
        
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="form-checkbox">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Component
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Period
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
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
                    <?php if (!empty($arrears)): ?>
                        <?php foreach ($arrears as $arrear): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="arrear_ids[]" value="<?php echo $arrear['id']; ?>" class="form-checkbox arrear-checkbox">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($arrear['first_name'] . ' ' . $arrear['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($arrear['emp_code']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($arrear['component_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($arrear['period_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($arrear['amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($arrear['status']) {
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'approved': echo 'bg-green-100 text-green-800'; break;
                                            case 'processed': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'rejected': echo 'bg-red-100 text-red-800'; break;
                                        }
                                        ?>">
                                        <?php echo ucfirst($arrear['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($arrear['status'] === 'pending'): ?>
                                            <button onclick="approveArrears(<?php echo $arrear['id']; ?>)" 
                                                    class="text-green-600 hover:text-green-900" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="rejectArrears(<?php echo $arrear['id']; ?>)" 
                                                    class="text-red-600 hover:text-red-900" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="viewDetails(<?php echo $arrear['id']; ?>)" 
                                                class="text-blue-600 hover:text-blue-900" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-money-bill-wave text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No pending arrears</p>
                                    <p class="text-sm">All arrears have been processed</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Arrears Modal -->
<div id="create-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Arrears</h3>
            <form id="arrears-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-4">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
                    <select name="employee_id" id="employee_id" required class="form-select">
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?php echo $employee['id']; ?>">
                                <?php echo htmlspecialchars($employee['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="component_id" class="block text-sm font-medium text-gray-700 mb-2">Component *</label>
                    <select name="component_id" id="component_id" required class="form-select">
                        <option value="">Select Component</option>
                        <?php foreach ($components as $component): ?>
                            <option value="<?php echo $component['id']; ?>">
                                <?php echo htmlspecialchars($component['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="effective_period_id" class="block text-sm font-medium text-gray-700 mb-2">Effective Period *</label>
                    <select name="effective_period_id" id="effective_period_id" required class="form-select">
                        <option value="">Select Period</option>
                        <?php foreach ($periods as $period): ?>
                            <option value="<?php echo $period['id']; ?>">
                                <?php echo htmlspecialchars($period['period_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                    <input type="number" name="amount" id="amount" required 
                           class="form-input" step="0.01" min="0.01">
                </div>
                
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" id="description" required rows="3" 
                              class="form-textarea" placeholder="Reason for arrears"></textarea>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeCreateModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Arrears</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('create-modal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('create-modal').classList.add('hidden');
    document.getElementById('arrears-form').reset();
}

function approveArrears(id) {
    if (confirm('Are you sure you want to approve this arrears?')) {
        showLoading();
        
        fetch('/payroll/approve-arrears', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                id: id,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('An error occurred', 'error');
        });
    }
}

function rejectArrears(id) {
    const reason = prompt('Please enter rejection reason:');
    if (reason) {
        showLoading();
        
        fetch('/payroll/reject-arrears', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                id: id,
                reason: reason,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('An error occurred', 'error');
        });
    }
}

function bulkApprove() {
    const selectedIds = [];
    document.querySelectorAll('.arrear-checkbox:checked').forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });
    
    if (selectedIds.length === 0) {
        showMessage('Please select at least one arrears to approve', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to approve ${selectedIds.length} arrears?`)) {
        showLoading();
        
        fetch('/payroll/bulk-approve-arrears', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ids: selectedIds,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage(`${data.count} arrears approved successfully`, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('An error occurred', 'error');
        });
    }
}

// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.arrear-checkbox').forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Form submission
document.getElementById('arrears-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/payroll/create-arrears', {
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
            showMessage('Arrears created successfully', 'success');
            closeCreateModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showMessage(data.errors[field], 'error');
                });
            } else {
                showMessage(data.message, 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>