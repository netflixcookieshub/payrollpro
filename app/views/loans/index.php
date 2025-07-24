<?php 
$title = 'Loan Management - Payroll System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Loan Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage employee loans, EMIs, and repayment tracking
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="/loans/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Add Loan
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($filters['search']); ?>" 
                           class="form-input" placeholder="Employee name or code">
                </div>
                
                <div>
                    <label for="loan_type" class="block text-sm font-medium text-gray-700 mb-2">Loan Type</label>
                    <select name="loan_type" id="loan_type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach ($loan_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>" 
                                    <?php echo $filters['loanType'] == $type['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="closed" <?php echo $filters['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="defaulted" <?php echo $filters['status'] === 'defaulted' ? 'selected' : ''; ?>>Defaulted</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full btn btn-primary">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loans List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Loan Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Loan Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            EMI Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Outstanding
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
                    <?php if (!empty($loans)): ?>
                        <?php foreach ($loans as $loan): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($loan['emp_code']); ?> • 
                                        <?php echo htmlspecialchars($loan['department_name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($loan['loan_type_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($loan['loan_amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($loan['emi_amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($loan['outstanding_amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($loan['status']) {
                                            case 'active': echo 'bg-green-100 text-green-800'; break;
                                            case 'closed': echo 'bg-gray-100 text-gray-800'; break;
                                            case 'defaulted': echo 'bg-red-100 text-red-800'; break;
                                        }
                                        ?>">
                                        <?php echo ucfirst($loan['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="/loans/<?php echo $loan['id']; ?>" class="text-primary-600 hover:text-primary-900" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($loan['status'] === 'active'): ?>
                                            <a href="/loans/<?php echo $loan['id']; ?>/edit" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="closeLoan(<?php echo $loan['id']; ?>)" class="text-orange-600 hover:text-orange-900" title="Close Loan">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-hand-holding-usd text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No loans found</p>
                                    <p class="text-sm">Start by adding employee loans</p>
                                    <a href="/loans/create" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Loan
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Close Loan Modal -->
<div id="close-loan-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Close Loan</h3>
            <form id="close-loan-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="loan_id" id="close_loan_id">
                
                <div class="mb-4">
                    <label for="closure_amount" class="block text-sm font-medium text-gray-700 mb-2">Closure Amount *</label>
                    <input type="number" name="closure_amount" id="closure_amount" required
                           class="form-input" step="0.01" placeholder="0.00">
                </div>
                
                <div class="mb-4">
                    <label for="closure_date" class="block text-sm font-medium text-gray-700 mb-2">Closure Date *</label>
                    <input type="date" name="closure_date" id="closure_date" required
                           value="<?php echo date('Y-m-d'); ?>" class="form-input">
                </div>
                
                <div class="mb-6">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                    <textarea name="remarks" id="closure_remarks" rows="3" class="form-textarea" 
                              placeholder="Reason for closure"></textarea>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Close Loan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function closeLoan(loanId) {
    document.getElementById('close_loan_id').value = loanId;
    document.getElementById('close-loan-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('close-loan-modal').classList.add('hidden');
}

// Close loan form submission
document.getElementById('close-loan-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const loanId = formData.get('loan_id');
    
    showLoading();
    
    fetch(`/loans/${loanId}/close`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage(data.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while closing the loan', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>