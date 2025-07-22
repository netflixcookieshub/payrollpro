<?php 
$title = 'Departments - Master Data';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Department Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage company departments and organizational structure
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Add Department
            </button>
        </div>
    </div>

    <!-- Departments List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department Head
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employees
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $department): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($department['name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($department['code']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($department['first_name']): ?>
                                        <?php echo htmlspecialchars($department['first_name'] . ' ' . $department['last_name']); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $department['employee_count']; ?> employees
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editDepartment(<?php echo $department['id']; ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteDepartment(<?php echo $department['id']; ?>)" 
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
                                    <i class="fas fa-building text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No departments found</p>
                                    <p class="text-sm">Create your first department to get started</p>
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Department
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

<!-- Create/Edit Department Modal -->
<div id="department-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Add Department</h3>
            <form id="department-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="department_id">
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Department Name *</label>
                    <input type="text" name="name" id="name" required
                           class="form-input" placeholder="e.g., Human Resources">
                </div>
                
                <div class="mb-4">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Department Code *</label>
                    <input type="text" name="code" id="code" required
                           class="form-input" placeholder="e.g., HR" maxlength="10">
                </div>
                
                <div class="mb-6">
                    <label for="head_id" class="block text-sm font-medium text-gray-700 mb-2">Department Head</label>
                    <select name="head_id" id="head_id" class="form-select">
                        <option value="">Select Department Head</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?php echo $employee['id']; ?>">
                                <?php echo htmlspecialchars($employee['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeDepartmentModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submit-text">Add Department</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Department';
    document.getElementById('submit-text').textContent = 'Add Department';
    document.getElementById('department-form').reset();
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('department_id').value = '';
    document.getElementById('department-modal').classList.remove('hidden');
}

function editDepartment(departmentId) {
    // In a real implementation, you would fetch department data
    document.getElementById('modal-title').textContent = 'Edit Department';
    document.getElementById('submit-text').textContent = 'Update Department';
    document.querySelector('input[name="action"]').value = 'update';
    document.getElementById('department_id').value = departmentId;
    document.getElementById('department-modal').classList.remove('hidden');
    
    // Fetch and populate department data
    // This would be implemented with an AJAX call
}

function closeDepartmentModal() {
    document.getElementById('department-modal').classList.add('hidden');
}

function deleteDepartment(departmentId) {
    if (confirm('Are you sure you want to delete this department? This action cannot be undone.')) {
        showLoading();
        
        fetch('/departments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                id: departmentId,
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
            showMessage('An error occurred while deleting the department', 'error');
        });
    }
}

// Form submission
document.getElementById('department-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/departments', {
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
            closeDepartmentModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showMessage(data.errors[field], 'error');
                });
            } else {
                showMessage(data.message || 'Failed to save department', 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving the department', 'error');
    });
});

// Auto-generate department code from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value.trim();
    if (name && !document.getElementById('code').value) {
        // Generate code from first letters of words
        const words = name.split(' ');
        let code = '';
        words.forEach(word => {
            if (word.length > 0) {
                code += word.charAt(0).toUpperCase();
            }
        });
        document.getElementById('code').value = code.substring(0, 10);
    }
});

// Convert code to uppercase
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>