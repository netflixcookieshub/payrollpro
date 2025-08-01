<?php 
$title = 'Record Payment - ' . $loan['first_name'] . ' ' . $loan['last_name'];
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-2xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/loans/<?php echo $loan['id']; ?>" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Record Loan Payment</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Record payment for <?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Loan Summary -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Loan Summary</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">
                        ₹<?php echo number_format($loan['loan_amount'], 2); ?>
                    </div>
                    <div class="text-sm text-gray-500">Original Amount</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        ₹<?php echo number_format($loan['emi_amount'], 2); ?>
                    </div>
                    <div class="text-sm text-gray-500">Monthly EMI</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">
                        ₹<?php echo number_format($loan['outstanding_amount'], 2); ?>
                    </div>
                    <div class="text-sm text-gray-500">Outstanding</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Payment Details</h3>
        </div>
        <div class="p-6">
            <form id="payment-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="space-y-6">
                    <div>
                        <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount *</label>
                        <input type="number" name="payment_amount" id="payment_amount" required 
                               class="form-input" step="0.01" min="1" 
                               max="<?php echo $loan['outstanding_amount']; ?>"
                               value="<?php echo $loan['emi_amount']; ?>">
                        <p class="text-xs text-gray-500 mt-1">
                            Maximum: ₹<?php echo number_format($loan['outstanding_amount'], 2); ?>
                        </p>
                    </div>
                    
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date *</label>
                        <input type="date" name="payment_date" id="payment_date" required 
                               value="<?php echo date('Y-m-d'); ?>" class="form-input">
                    </div>
                    
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            <option value="salary_deduction">Salary Deduction</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input type="text" name="reference_number" id="reference_number" 
                               class="form-input" placeholder="Transaction/Cheque number">
                    </div>
                    
                    <div>
                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                        <textarea name="remarks" id="remarks" rows="3" class="form-textarea" 
                                  placeholder="Optional payment remarks"></textarea>
                    </div>
                </div>
                
                <!-- Payment Summary -->
                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Payment Summary</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Amount:</span>
                            <span id="summary-amount" class="font-medium">₹<?php echo number_format($loan['emi_amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Outstanding After Payment:</span>
                            <span id="summary-outstanding" class="font-medium text-red-600">
                                ₹<?php echo number_format($loan['outstanding_amount'] - $loan['emi_amount'], 2); ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Remaining EMIs:</span>
                            <span id="summary-emis" class="font-medium">
                                <?php echo ceil(($loan['outstanding_amount'] - $loan['emi_amount']) / $loan['emi_amount']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end space-x-4">
                    <a href="/loans/<?php echo $loan['id']; ?>" class="btn btn-outline">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update summary when payment amount changes
document.getElementById('payment_amount').addEventListener('input', function() {
    const paymentAmount = parseFloat(this.value) || 0;
    const outstandingAmount = <?php echo $loan['outstanding_amount']; ?>;
    const emiAmount = <?php echo $loan['emi_amount']; ?>;
    
    const newOutstanding = Math.max(0, outstandingAmount - paymentAmount);
    const remainingEmis = newOutstanding > 0 ? Math.ceil(newOutstanding / emiAmount) : 0;
    
    document.getElementById('summary-amount').textContent = '₹' + paymentAmount.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('summary-outstanding').textContent = '₹' + newOutstanding.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('summary-emis').textContent = remainingEmis;
});

// Form submission
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/loans/<?php echo $loan['id']; ?>/payment', {
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
            setTimeout(() => {
                window.location.href = '/loans/<?php echo $loan['id']; ?>';
            }, 2000);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while recording payment', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>