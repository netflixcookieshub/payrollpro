<?php 
$title = 'Tax Slabs - Master Data';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Tax Slab Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Configure income tax slabs for financial year <?php echo $current_fy; ?>
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Add Tax Slab
            </button>
        </div>
    </div>

    <!-- Tax Slabs List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Income Range
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tax Rate
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Surcharge
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cess
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($tax_slabs)): ?>
                        <?php foreach ($tax_slabs as $slab): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($slab['min_amount']); ?> - 
                                    <?php echo $slab['max_amount'] ? '₹' . number_format($slab['max_amount']) : 'Above'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $slab['tax_rate']; ?>%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $slab['surcharge_rate']; ?>%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $slab['cess_rate']; ?>%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editTaxSlab(<?php echo $slab['id']; ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteTaxSlab(<?php echo $slab['id']; ?>)" 
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
                                    <i class="fas fa-percentage text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No tax slabs found for <?php echo $current_fy; ?></p>
                                    <p class="text-sm">Add tax slabs to enable TDS calculations</p>
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Tax Slab
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

<!-- Create/Edit Tax Slab Modal -->
<div id="tax-slab-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Add Tax Slab</h3>
            <form id="tax-slab-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="tax_slab_id">
                
                <div class="mb-4">
                    <label for="financial_year" class="block text-sm font-medium text-gray-700 mb-2">Financial Year *</label>
                    <input type="text" name="financial_year" id="financial_year" required
                           value="<?php echo $current_fy; ?>" class="form-input" readonly>
                </div>
                
                <div class="mb-4">
                    <label for="min_amount" class="block text-sm font-medium text-gray-700 mb-2">Minimum Amount *</label>
                    <input type="number" name="min_amount" id="min_amount" required
                           class="form-input" step="0.01" min="0">
                </div>
                
                <div class="mb-4">
                    <label for="max_amount" class="block text-sm font-medium text-gray-700 mb-2">Maximum Amount</label>
                    <input type="number" name="max_amount" id="max_amount"
                           class="form-input" step="0.01" min="0" placeholder="Leave empty for no upper limit">
                </div>
                
                <div class="mb-4">
                    <label for="tax_rate" class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%) *</label>
                    <input type="number" name="tax_rate" id="tax_rate" required
                           class="form-input" step="0.01" min="0" max="100">
                </div>
                
                <div class="mb-4">
                    <label for="surcharge_rate" class="block text-sm font-medium text-gray-700 mb-2">Surcharge Rate (%)</label>
                    <input type="number" name="surcharge_rate" id="surcharge_rate"
                           class="form-input" step="0.01" min="0" max="100" value="0">
                </div>
                
                <div class="mb-6">
                    <label for="cess_rate" class="block text-sm font-medium text-gray-700 mb-2">Cess Rate (%)</label>
                    <input type="number" name="cess_rate" id="cess_rate"
                           class="form-input" step="0.01" min="0" max="100" value="4">
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeTaxSlabModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submit-text">Add Tax Slab</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Tax Slab';
    document.getElementById('submit-text').textContent = 'Add Tax Slab';
    document.getElementById('tax-slab-form').reset();
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('tax_slab_id').value = '';
    document.getElementById('financial_year').value = '<?php echo $current_fy; ?>';
    document.getElementById('cess_rate').value = '4';
    document.getElementById('tax-slab-modal').classList.remove('hidden');
}

function editTaxSlab(taxSlabId) {
    document.getElementById('modal-title').textContent = 'Edit Tax Slab';
    document.getElementById('submit-text').textContent = 'Update Tax Slab';
    document.querySelector('input[name="action"]').value = 'update';
    document.getElementById('tax_slab_id').value = taxSlabId;
    document.getElementById('tax-slab-modal').classList.remove('hidden');
    
    // In a real implementation, you would fetch and populate the form data
}

function closeTaxSlabModal() {
    document.getElementById('tax-slab-modal').classList.add('hidden');
}

function deleteTaxSlab(taxSlabId) {
    if (confirm('Are you sure you want to delete this tax slab?')) {
        showLoading();
        
        fetch('/tax-slabs', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                id: taxSlabId,
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
            showMessage('An error occurred while deleting the tax slab', 'error');
        });
    }
}

// Form submission
document.getElementById('tax-slab-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/tax-slabs', {
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
            closeTaxSlabModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showMessage(data.errors[field], 'error');
                });
            } else {
                showMessage(data.message || 'Failed to save tax slab', 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving the tax slab', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>