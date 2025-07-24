<?php 
$title = 'Add Loan - Payroll System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/loans" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add Employee Loan</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Create a new loan record for an employee
                </p>
            </div>
        </div>
    </div>

    <!-- Loan Form -->
    <form method="POST" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <!-- Loan Details -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Loan Details</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
                        <select name="employee_id" id="employee_id" required class="form-select">
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo htmlspecialchars($employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['employee_id'])): ?>
                            <p class="error-message"><?php echo $errors['employee_id']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="loan_type_id" class="block text-sm font-medium text-gray-700 mb-2">Loan Type *</label>
                        <select name="loan_type_id" id="loan_type_id" required class="form-select">
                            <option value="">Select Loan Type</option>
                            <?php foreach ($loan_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" 
                                        data-max-amount="<?php echo $type['max_amount']; ?>"
                                        data-interest-rate="<?php echo $type['interest_rate']; ?>"
                                        data-max-tenure="<?php echo $type['max_tenure_months']; ?>">
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['loan_type_id'])): ?>
                            <p class="error-message"><?php echo $errors['loan_type_id']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="loan_amount" class="block text-sm font-medium text-gray-700 mb-2">Loan Amount *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input type="number" name="loan_amount" id="loan_amount" required
                                   class="pl-8 form-input" step="0.01" placeholder="0.00">
                        </div>
                        <p id="max-amount-hint" class="text-xs text-gray-500 mt-1 hidden"></p>
                        <?php if (isset($errors['loan_amount'])): ?>
                            <p class="error-message"><?php echo $errors['loan_amount']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (% per annum)</label>
                        <input type="number" name="interest_rate" id="interest_rate"
                               class="form-input" step="0.01" placeholder="0.00">
                    </div>
                    
                    <div>
                        <label for="tenure_months" class="block text-sm font-medium text-gray-700 mb-2">Tenure (Months) *</label>
                        <input type="number" name="tenure_months" id="tenure_months" required
                               class="form-input" min="1" placeholder="12">
                        <p id="max-tenure-hint" class="text-xs text-gray-500 mt-1 hidden"></p>
                        <?php if (isset($errors['tenure_months'])): ?>
                            <p class="error-message"><?php echo $errors['tenure_months']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="disbursed_date" class="block text-sm font-medium text-gray-700 mb-2">Disbursement Date *</label>
                        <input type="date" name="disbursed_date" id="disbursed_date" required
                               value="<?php echo date('Y-m-d'); ?>" class="form-input">
                        <?php if (isset($errors['disbursed_date'])): ?>
                            <p class="error-message"><?php echo $errors['disbursed_date']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- EMI Calculation -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">EMI Calculation</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Monthly EMI</p>
                        <p id="calculated-emi" class="text-2xl font-bold text-blue-600">₹0.00</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Total Amount</p>
                        <p id="total-amount" class="text-2xl font-bold text-gray-900">₹0.00</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Total Interest</p>
                        <p id="total-interest" class="text-2xl font-bold text-red-600">₹0.00</p>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <button type="button" onclick="calculateEMI()" class="btn btn-outline">
                        <i class="fas fa-calculator mr-2"></i>
                        Calculate EMI
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="/loans" class="btn btn-outline">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Create Loan
            </button>
        </div>
    </form>
</div>

<script>
// Update loan type details when selection changes
document.getElementById('loan_type_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const maxAmount = selectedOption.dataset.maxAmount;
    const interestRate = selectedOption.dataset.interestRate;
    const maxTenure = selectedOption.dataset.maxTenure;
    
    // Update hints
    const maxAmountHint = document.getElementById('max-amount-hint');
    const maxTenureHint = document.getElementById('max-tenure-hint');
    
    if (maxAmount) {
        maxAmountHint.textContent = `Maximum amount: ₹${parseFloat(maxAmount).toLocaleString('en-IN')}`;
        maxAmountHint.classList.remove('hidden');
    } else {
        maxAmountHint.classList.add('hidden');
    }
    
    if (maxTenure) {
        maxTenureHint.textContent = `Maximum tenure: ${maxTenure} months`;
        maxTenureHint.classList.remove('hidden');
        document.getElementById('tenure_months').max = maxTenure;
    } else {
        maxTenureHint.classList.add('hidden');
    }
    
    // Set default interest rate
    if (interestRate) {
        document.getElementById('interest_rate').value = interestRate;
    }
    
    // Auto-calculate EMI
    calculateEMI();
});

// Auto-calculate EMI when values change
document.getElementById('loan_amount').addEventListener('input', calculateEMI);
document.getElementById('interest_rate').addEventListener('input', calculateEMI);
document.getElementById('tenure_months').addEventListener('input', calculateEMI);

function calculateEMI() {
    const loanAmount = parseFloat(document.getElementById('loan_amount').value) || 0;
    const interestRate = parseFloat(document.getElementById('interest_rate').value) || 0;
    const tenureMonths = parseInt(document.getElementById('tenure_months').value) || 0;
    
    if (loanAmount > 0 && tenureMonths > 0) {
        fetch('/loans/calculate-emi', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                loan_amount: loanAmount,
                interest_rate: interestRate,
                tenure_months: tenureMonths
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('calculated-emi').textContent = '₹' + data.emi_amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
                document.getElementById('total-amount').textContent = '₹' + data.total_amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
                document.getElementById('total-interest').textContent = '₹' + data.total_interest.toLocaleString('en-IN', {minimumFractionDigits: 2});
            }
        })
        .catch(error => {
            console.error('Error calculating EMI:', error);
        });
    } else {
        document.getElementById('calculated-emi').textContent = '₹0.00';
        document.getElementById('total-amount').textContent = '₹0.00';
        document.getElementById('total-interest').textContent = '₹0.00';
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const loanAmount = parseFloat(document.getElementById('loan_amount').value);
    const selectedLoanType = document.getElementById('loan_type_id').selectedOptions[0];
    const maxAmount = parseFloat(selectedLoanType.dataset.maxAmount);
    
    if (maxAmount && loanAmount > maxAmount) {
        e.preventDefault();
        showMessage(`Loan amount cannot exceed ₹${maxAmount.toLocaleString('en-IN')}`, 'error');
        return;
    }
    
    const tenureMonths = parseInt(document.getElementById('tenure_months').value);
    const maxTenure = parseInt(selectedLoanType.dataset.maxTenure);
    
    if (maxTenure && tenureMonths > maxTenure) {
        e.preventDefault();
        showMessage(`Tenure cannot exceed ${maxTenure} months`, 'error');
        return;
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>