<?php 
$title = 'Add Loan - Payroll Management System';
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
                <h1 class="text-3xl font-bold text-gray-900">Add New Loan</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Create a new employee loan with EMI calculations
                </p>
            </div>
        </div>
    </div>

    <!-- Loan Form -->
    <form method="POST" class="space-y-6">
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
                                    <?php echo htmlspecialchars($employee['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                    </div>
                    
                    <div>
                        <label for="loan_amount" class="block text-sm font-medium text-gray-700 mb-2">Loan Amount *</label>
                        <input type="number" name="loan_amount" id="loan_amount" required 
                               class="form-input" step="0.01" min="1000">
                        <p id="max-amount-hint" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    
                    <div>
                        <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (% per annum)</label>
                        <input type="number" name="interest_rate" id="interest_rate" 
                               class="form-input" step="0.01" min="0" max="50">
                    </div>
                    
                    <div>
                        <label for="tenure_months" class="block text-sm font-medium text-gray-700 mb-2">Tenure (Months) *</label>
                        <input type="number" name="tenure_months" id="tenure_months" required 
                               class="form-input" min="1" max="360">
                        <p id="max-tenure-hint" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    
                    <div>
                        <label for="disbursed_date" class="block text-sm font-medium text-gray-700 mb-2">Disbursement Date *</label>
                        <input type="date" name="disbursed_date" id="disbursed_date" required 
                               value="<?php echo date('Y-m-d'); ?>" class="form-input">
                    </div>
                    
                    <div>
                        <label for="first_emi_date" class="block text-sm font-medium text-gray-700 mb-2">First EMI Date</label>
                        <input type="date" name="first_emi_date" id="first_emi_date" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        <!-- EMI Calculator -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">EMI Calculator</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Monthly EMI</label>
                        <div id="calculated-emi" class="text-3xl font-bold text-blue-600">₹0</div>
                    </div>
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Total Interest</label>
                        <div id="total-interest" class="text-3xl font-bold text-orange-600">₹0</div>
                    </div>
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Total Amount</label>
                        <div id="total-amount" class="text-3xl font-bold text-green-600">₹0</div>
                    </div>
                </div>
                
                <div class="mt-6">
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
// Auto-populate fields when loan type changes
document.getElementById('loan_type_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (selectedOption.value) {
        const maxAmount = selectedOption.dataset.maxAmount;
        const interestRate = selectedOption.dataset.interestRate;
        const maxTenure = selectedOption.dataset.maxTenure;
        
        document.getElementById('interest_rate').value = interestRate;
        document.getElementById('max-amount-hint').textContent = maxAmount ? `Maximum: ₹${parseFloat(maxAmount).toLocaleString('en-IN')}` : '';
        document.getElementById('max-tenure-hint').textContent = maxTenure ? `Maximum: ${maxTenure} months` : '';
        
        // Set max attributes
        if (maxAmount) {
            document.getElementById('loan_amount').setAttribute('max', maxAmount);
        }
        if (maxTenure) {
            document.getElementById('tenure_months').setAttribute('max', maxTenure);
        }
    } else {
        document.getElementById('interest_rate').value = '';
        document.getElementById('max-amount-hint').textContent = '';
        document.getElementById('max-tenure-hint').textContent = '';
    }
    
    calculateEMI();
});

// Auto-calculate first EMI date when disbursement date changes
document.getElementById('disbursed_date').addEventListener('change', function() {
    if (this.value) {
        const disbursedDate = new Date(this.value);
        const firstEmiDate = new Date(disbursedDate);
        firstEmiDate.setMonth(firstEmiDate.getMonth() + 1);
        
        document.getElementById('first_emi_date').value = firstEmiDate.toISOString().split('T')[0];
    }
});

// Calculate EMI when values change
document.getElementById('loan_amount').addEventListener('input', calculateEMI);
document.getElementById('interest_rate').addEventListener('input', calculateEMI);
document.getElementById('tenure_months').addEventListener('input', calculateEMI);

function calculateEMI() {
    const principal = parseFloat(document.getElementById('loan_amount').value) || 0;
    const annualRate = parseFloat(document.getElementById('interest_rate').value) || 0;
    const tenureMonths = parseInt(document.getElementById('tenure_months').value) || 0;
    
    if (principal <= 0 || tenureMonths <= 0) {
        document.getElementById('calculated-emi').textContent = '₹0';
        document.getElementById('total-interest').textContent = '₹0';
        document.getElementById('total-amount').textContent = '₹0';
        return;
    }
    
    let emi = 0;
    let totalAmount = 0;
    let totalInterest = 0;
    
    if (annualRate === 0) {
        emi = principal / tenureMonths;
        totalAmount = principal;
        totalInterest = 0;
    } else {
        const monthlyRate = annualRate / (12 * 100);
        emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, tenureMonths)) / 
              (Math.pow(1 + monthlyRate, tenureMonths) - 1);
        totalAmount = emi * tenureMonths;
        totalInterest = totalAmount - principal;
    }
    
    document.getElementById('calculated-emi').textContent = '₹' + emi.toLocaleString('en-IN', {maximumFractionDigits: 2});
    document.getElementById('total-interest').textContent = '₹' + totalInterest.toLocaleString('en-IN', {maximumFractionDigits: 2});
    document.getElementById('total-amount').textContent = '₹' + totalAmount.toLocaleString('en-IN', {maximumFractionDigits: 2});
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const loanAmount = parseFloat(document.getElementById('loan_amount').value);
    const maxAmount = parseFloat(document.getElementById('loan_amount').getAttribute('max'));
    
    if (maxAmount && loanAmount > maxAmount) {
        e.preventDefault();
        showMessage('Loan amount exceeds maximum limit for selected loan type', 'error');
        return;
    }
    
    const tenureMonths = parseInt(document.getElementById('tenure_months').value);
    const maxTenure = parseInt(document.getElementById('tenure_months').getAttribute('max'));
    
    if (maxTenure && tenureMonths > maxTenure) {
        e.preventDefault();
        showMessage('Tenure exceeds maximum limit for selected loan type', 'error');
        return;
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>