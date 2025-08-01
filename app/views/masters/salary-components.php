<?php 
$title = 'Salary Components - Master Data';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Salary Components</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage salary components, formulas, and calculation rules
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Add Component
            </button>
        </div>
    </div>

    <!-- Components List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Component
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Formula
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
                    <?php if (!empty($components)): ?>
                        <?php foreach ($components as $component): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($component['name']); ?>
                                        <?php if ($component['is_mandatory']): ?>
                                            <span class="ml-1 text-red-500">*</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($component['code']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($component['type']) {
                                            case 'earning': echo 'bg-green-100 text-green-800'; break;
                                            case 'deduction': echo 'bg-red-100 text-red-800'; break;
                                            case 'reimbursement': echo 'bg-blue-100 text-blue-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($component['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if (!empty($component['formula'])): ?>
                                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">
                                            <?php echo htmlspecialchars($component['formula']); ?>
                                        </code>
                                    <?php else: ?>
                                        <span class="text-gray-400">Manual</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex flex-wrap gap-1">
                                        <?php if ($component['is_taxable']): ?>
                                            <span class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Tax</span>
                                        <?php endif; ?>
                                        <?php if ($component['is_pf_applicable']): ?>
                                            <span class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded bg-purple-100 text-purple-800">PF</span>
                                        <?php endif; ?>
                                        <?php if ($component['is_esi_applicable']): ?>
                                            <span class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded bg-indigo-100 text-indigo-800">ESI</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editComponent(<?php echo $component['id']; ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteComponent(<?php echo $component['id']; ?>)" 
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
                                    <i class="fas fa-calculator text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No salary components found</p>
                                    <p class="text-sm">Create your first salary component to get started</p>
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Component
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

<!-- Create/Edit Component Modal -->
<div id="component-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Add Salary Component</h3>
            <form id="component-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="component_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Component Name *</label>
                        <input type="text" name="name" id="name" required
                               class="form-input" placeholder="e.g., Basic Salary">
                    </div>
                    
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Component Code *</label>
                        <input type="text" name="code" id="code" required
                               class="form-input" placeholder="e.g., BASIC" maxlength="20">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                        <select name="type" id="type" required class="form-select">
                            <option value="">Select Type</option>
                            <option value="earning">Earning</option>
                            <option value="deduction">Deduction</option>
                            <option value="reimbursement">Reimbursement</option>
                            <option value="variable">Variable</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                        <input type="number" name="display_order" id="display_order"
                               class="form-input" placeholder="1" min="1">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="formula" class="block text-sm font-medium text-gray-700 mb-2">Formula</label>
                    <input type="text" name="formula" id="formula"
                           class="form-input" placeholder="e.g., BASIC * 0.4">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for manual entry. Use component codes for calculations.</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_mandatory" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Mandatory</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_taxable" class="form-checkbox" checked>
                            <span class="ml-2 text-sm text-gray-700">Taxable</span>
                        </label>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_pf_applicable" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">PF Applicable</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_esi_applicable" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">ESI Applicable</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeComponentModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submit-text">Add Component</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Salary Component';
    document.getElementById('submit-text').textContent = 'Add Component';
    document.getElementById('component-form').reset();
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('component_id').value = '';
    document.querySelector('input[name="is_taxable"]').checked = true;
    document.getElementById('component-modal').classList.remove('hidden');
}

function editComponent(componentId) {
    document.getElementById('modal-title').textContent = 'Edit Salary Component';
    document.getElementById('submit-text').textContent = 'Update Component';
    document.querySelector('input[name="action"]').value = 'update';
    document.getElementById('component_id').value = componentId;
    document.getElementById('component-modal').classList.remove('hidden');
    
    // In a real implementation, you would fetch and populate the form data
}

function closeComponentModal() {
    document.getElementById('component-modal').classList.add('hidden');
}

function deleteComponent(componentId) {
    if (confirm('Are you sure you want to delete this salary component? This action cannot be undone.')) {
        showLoading();
        
        fetch('/salary-components', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                id: componentId,
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
            showMessage('An error occurred while deleting the component', 'error');
        });
    }
}

// Form submission
document.getElementById('component-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Convert checkboxes to boolean values
    data.is_mandatory = formData.has('is_mandatory') ? 1 : 0;
    data.is_taxable = formData.has('is_taxable') ? 1 : 0;
    data.is_pf_applicable = formData.has('is_pf_applicable') ? 1 : 0;
    data.is_esi_applicable = formData.has('is_esi_applicable') ? 1 : 0;
    
    showLoading();
    
    fetch('/salary-components', {
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
            closeComponentModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showMessage(data.errors[field], 'error');
                });
            } else {
                showMessage(data.message || 'Failed to save component', 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving the component', 'error');
    });
});

// Auto-generate component code from name
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