<?php 
$title = 'Designations - Master Data';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Designation Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage job positions and organizational hierarchy
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Add Designation
            </button>
        </div>
    </div>

    <!-- Designations List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Designation
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Grade
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
                    <?php if (!empty($designations)): ?>
                        <?php foreach ($designations as $designation): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($designation['name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($designation['code']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($designation['department_name'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($designation['grade'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $designation['employee_count']; ?> employees
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editDesignation(<?php echo $designation['id']; ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteDesignation(<?php echo $designation['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-user-tie text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No designations found</p>
                                    <p class="text-sm">Create your first designation to get started</p>
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Designation
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

<!-- Create/Edit Designation Modal -->
<div id="designation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Add Designation</h3>
            <form id="designation-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="designation_id">
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Designation Name *</label>
                    <input type="text" name="name" id="name" required
                           class="form-input" placeholder="e.g., Software Engineer">
                </div>
                
                <div class="mb-4">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Designation Code *</label>
                    <input type="text" name="code" id="code" required
                           class="form-input" placeholder="e.g., SWE" maxlength="10">
                </div>
                
                <div class="mb-4">
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                    <select name="department_id" id="department_id" required class="form-select">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>">
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label for="grade" class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                    <input type="text" name="grade" id="grade"
                           class="form-input" placeholder="e.g., T2">
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeDesignationModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submit-text">Add Designation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Designation';
    document.getElementById('submit-text').textContent = 'Add Designation';
    document.getElementById('designation-form').reset();
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('designation_id').value = '';
    document.getElementById('designation-modal').classList.remove('hidden');
}

function editDesignation(designationId) {
    document.getElementById('modal-title').textContent = 'Edit Designation';
    document.getElementById('submit-text').textContent = 'Update Designation';
    document.querySelector('input[name="action"]').value = 'update';
    document.getElementById('designation_id').value = designationId;
    document.getElementById('designation-modal').classList.remove('hidden');
    
    // In a real implementation, you would fetch and populate the form data
}

function closeDesignationModal() {
    document.getElementById('designation-modal').classList.add('hidden');
}

function deleteDesignation(designationId) {
    if (confirm('Are you sure you want to delete this designation? This action cannot be undone.')) {
        showLoading();
        
        fetch('/designations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                id: designationId,
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
            showMessage('An error occurred while deleting the designation', 'error');
        });
    }
}

// Form submission
document.getElementById('designation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/designations', {
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
            closeDesignationModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showMessage(data.errors[field], 'error');
                });
            } else {
                showMessage(data.message || 'Failed to save designation', 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving the designation', 'error');
    });
});

// Auto-generate designation code from name
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
        document.getElementById('code').value = code.substring(0, 10);
    }
});

// Convert code to uppercase
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>