<?php 
$title = 'Leave Types - Master Data';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Leave Type Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage different types of leaves and their policies
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Add Leave Type
            </button>
        </div>
    </div>

    <!-- Leave Types List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Leave Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Max Days/Year
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Properties
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($leave_types)): ?>
                        <?php foreach ($leave_types as $leaveType): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($leaveType['name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($leaveType['code']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $leaveType['max_days_per_year'] ?? 'Unlimited'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex flex-wrap gap-1">
                                        <?php if ($leaveType['is_paid']): ?>
                                            <span class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800">Paid</span>
                                        <?php else: ?>
                                            <span class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded bg-red-100 text-red-800">Unpaid</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($leaveType['carry_forward']): ?>
                                            <span class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-800">Carry Forward</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editLeaveType(<?php echo $leaveType['id']; ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteLeaveType(<?php echo $leaveType['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-calendar-minus text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No leave types found</p>
                                    <p class="text-sm">Create your first leave type to get started</p>
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Leave Type
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Leave Type Modal -->
<div id="leave-type-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Add Leave Type</h3>
            <form id="leave-type-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="leave_type_id">
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Leave Type Name *</label>
                    <input type="text" name="name" id="name" required
                           class="form-input" placeholder="e.g., Casual Leave">
                </div>
                
                <div class="mb-4">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Leave Code *</label>
                    <input type="text" name="code" id="code" required
                           class="form-input" placeholder="e.g., CL" maxlength="20">
                </div>
                
                <div class="mb-4">
                    <label for="max_days_per_year" class="block text-sm font-medium text-gray-700 mb-2">Max Days Per Year</label>
                    <input type="number" name="max_days_per_year" id="max_days_per_year"
                           class="form-input" placeholder="Leave empty for unlimited" min="1">
                </div>
                
                <div class="mb-6 space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_paid" id="is_paid" class="form-checkbox" checked>
                        <span class="ml-2 text-sm text-gray-700">Paid Leave</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="carry_forward" id="carry_forward" class="form-checkbox">
                        <span class="ml-2 text-sm text-gray-700">Allow Carry Forward</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeLeaveTypeModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submit-text">Add Leave Type</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Leave Type';
    document.getElementById('submit-text').textContent = 'Add Leave Type';
    document.getElementById('leave-type-form').reset();
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('leave_type_id').value = '';
    document.getElementById('is_paid').checked = true;
    document.getElementById('leave-type-modal').classList.remove('hidden');
}

function editLeaveType(leaveTypeId) {
    document.getElementById('modal-title').textContent = 'Edit Leave Type';
    document.getElementById('submit-text').textContent = 'Update Leave Type';
    document.querySelector('input[name="action"]').value = 'update';
    document.getElementById('leave_type_id').value = leaveTypeId;
    document.getElementById('leave-type-modal').classList.remove('hidden');
    
    // In a real implementation, you would fetch and populate the form data
}

function closeLeaveTypeModal() {
    document.getElementById('leave-type-modal').classList.add('hidden');
}

function deleteLeaveType(leaveTypeId) {
    if (confirm('Are you sure you want to delete this leave type?')) {
        showLoading();
        
        fetch('/leave-types', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                id: leaveTypeId,
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
            showMessage('An error occurred while deleting the leave type', 'error');
        });
    }
}

// Form submission
document.getElementById('leave-type-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Convert checkboxes to boolean values
    data.is_paid = formData.has('is_paid') ? 1 : 0;
    data.carry_forward = formData.has('carry_forward') ? 1 : 0;
    
    showLoading();
    
    fetch('/leave-types', {
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
            showMessage(data.message, 'success');
            closeLeaveTypeModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showMessage(data.errors[field], 'error');
                });
            } else {
                showMessage(data.message || 'Failed to save leave type', 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving the leave type', 'error');
    });
});

// Auto-generate leave code from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value.trim();
    if (name && !document.getElementById('code').value) {
        const words = name.split(' ');
        let code = '';
        words.forEach(word => {
            if (word.length > 0) {
                code += word.charAt(0).toUpperCase();
            }
        });
        document.getElementById('code').value = code.substring(0, 20);
    }
});

// Convert code to uppercase
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>