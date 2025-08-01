<?php 
$title = 'Loan Types - Master Data';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Loan Type Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Configure different types of loans with terms and conditions
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Add Loan Type
            </button>
        </div>
    </div>

    <!-- Loan Types List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Loan Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Max Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Interest Rate
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Max Tenure
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Active Loans
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($loan_types)): ?>
                        <?php foreach ($loan_types as $loanType): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($loanType['name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($loanType['code']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $loanType['max_amount'] ? '₹' . number_format($loanType['max_amount'], 0) : 'No Limit'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $loanType['interest_rate']; ?>% p.a.
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $loanType['max_tenure_months'] ? $loanType['max_tenure_months'] . ' months' : 'No Limit'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $loanType['active_loans']; ?> loans
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editLoanType(<?php echo $loanType['id']; ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteLoanType(<?php echo $loanType['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-hand-holding-usd text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No loan types found</p>
                                    <p class="text-sm">Create your first loan type to get started</p>
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Loan Type
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

<!-- Create/Edit Loan Type Modal -->
<div id="loan-type-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Add Loan Type</h3>
            <form id="loan-type-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="loan_type_id">
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Loan Type Name *</label>
                    <input type="text" name="name" id="name" required
                           class="form-input" placeholder="e.g., Personal Loan">
                </div>
                
                <div class="mb-4">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Loan Code *</label>
                    <input type="text" name="code" id="code" required
                           class="form-input" placeholder="e.g., PL" maxlength="20">
                </div>
                
                <div class="mb-4">
                    <label for="max_amount" class="block text-sm font-medium text-gray-700 mb-2">Maximum Amount</label>
                    <input type="number" name="max_amount" id="max_amount"
                           class="form-input" placeholder="Leave empty for no limit" min="1000" step="1000">
                </div>
                
                <div class="mb-4">
                    <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (% p.a.)</label>
                    <input type="number" name="interest_rate" id="interest_rate"
                           class="form-input" placeholder="0" min="0" max="50" step="0.01">
                </div>
                
                <div class="mb-6">
                    <label for="max_tenure_months" class="block text-sm font-medium text-gray-700 mb-2">Maximum Tenure (Months)</label>
                    <input type="number" name="max_tenure_months" id="max_tenure_months"
                           class="form-input" placeholder="Leave empty for no limit" min="1" max="360">
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeLoanTypeModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submit-text">Add Loan Type</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Loan Type';
    document.getElementById('submit-text').textContent = 'Add Loan Type';
    document.getElementById('loan-type-form').reset();
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('loan_type_id').value = '';
    document.getElementById('loan-type-modal').classList.remove('hidden');
}

function editLoanType(loanTypeId) {
    document.getElementById('modal-title').textContent = 'Edit Loan Type';
    document.getElementById('submit-text').textContent = 'Update Loan Type';
    document.querySelector('input[name="action"]').value = 'update';
    document.getElementById('loan_type_id').value = loanTypeId;
    document.getElementById('loan-type-modal').classList.remove('hidden');
    
    // In a real implementation, you would fetch and populate the form data
}

function closeLoanTypeModal() {
    document.getElementById('loan-type-modal').classList.add('hidden');
}

function deleteLoanType(loanTypeId) {
    if (confirm('Are you sure you want to delete this loan type?')) {
        showLoading();
        
        fetch('/loan-types', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                id: loanTypeId,
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
            showMessage('An error occurred while deleting the loan type', 'error');
        });
    }
}

// Form submission
document.getElementById('loan-type-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/loan-types', {
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
            closeLoanTypeModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showMessage(data.errors[field], 'error');
                });
            } else {
                showMessage(data.message || 'Failed to save loan type', 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving the loan type', 'error');
    });
});

// Auto-generate loan code from name
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