<?php 
$title = 'Salary Structure - ' . $employee['first_name'] . ' ' . $employee['last_name'];
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/employees/<?php echo $employee['id']; ?>" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Salary Structure</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Configure salary components for <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Employee Info Card -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <?php if (!empty($employee['photo'])): ?>
                        <img class="h-16 w-16 rounded-full object-cover" src="/uploads/<?php echo htmlspecialchars($employee['photo']); ?>" alt="">
                    <?php else: ?>
                        <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-xl font-medium text-gray-600">
                                <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                    </h3>
                    <p class="text-sm text-gray-500">
                        <?php echo htmlspecialchars($employee['emp_code']); ?> • 
                        <?php echo htmlspecialchars($employee['designation_name']); ?> • 
                        <?php echo htmlspecialchars($employee['department_name']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Structure Form -->
    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Salary Components</h3>
                    <div>
                        <label for="effective_date" class="block text-sm font-medium text-gray-700 mb-1">Effective Date</label>
                        <input type="date" name="effective_date" id="effective_date" 
                               value="<?php echo date('Y-m-d'); ?>" 
                               class="form-input">
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Earnings -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-green-700 mb-4">Earnings</h4>
                    <div class="space-y-4">
                        <?php foreach ($salary_components as $component): ?>
                            <?php if ($component['type'] === 'earning'): ?>
                                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($component['name']); ?>
                                            <?php if ($component['is_mandatory']): ?>
                                                <span class="text-red-500">*</span>
                                            <?php endif; ?>
                                        </label>
                                        <p class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($component['code']); ?>
                                            <?php if (!empty($component['formula'])): ?>
                                                • Formula: <?php echo htmlspecialchars($component['formula']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <span class="text-green-600 mr-2">₹</span>
                                            <input type="number" 
                                                   name="components[<?php echo $component['id']; ?>]" 
                                                   value="<?php echo $current_amounts[$component['id']] ?? ''; ?>"
                                                   class="form-input w-32 text-right" 
                                                   step="0.01" 
                                                   placeholder="0.00"
                                                   <?php echo $component['is_mandatory'] ? 'required' : ''; ?>>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Deductions -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-red-700 mb-4">Deductions</h4>
                    <div class="space-y-4">
                        <?php foreach ($salary_components as $component): ?>
                            <?php if ($component['type'] === 'deduction'): ?>
                                <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($component['name']); ?>
                                            <?php if ($component['is_mandatory']): ?>
                                                <span class="text-red-500">*</span>
                                            <?php endif; ?>
                                        </label>
                                        <p class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($component['code']); ?>
                                            <?php if (!empty($component['formula'])): ?>
                                                • Formula: <?php echo htmlspecialchars($component['formula']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <span class="text-red-600 mr-2">₹</span>
                                            <input type="number" 
                                                   name="components[<?php echo $component['id']; ?>]" 
                                                   value="<?php echo $current_amounts[$component['id']] ?? ''; ?>"
                                                   class="form-input w-32 text-right" 
                                                   step="0.01" 
                                                   placeholder="0.00"
                                                   <?php echo $component['is_mandatory'] ? 'required' : ''; ?>>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Reimbursements -->
                <?php 
                $hasReimbursements = false;
                foreach ($salary_components as $component) {
                    if ($component['type'] === 'reimbursement') {
                        $hasReimbursements = true;
                        break;
                    }
                }
                ?>
                
                <?php if ($hasReimbursements): ?>
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-blue-700 mb-4">Reimbursements</h4>
                    <div class="space-y-4">
                        <?php foreach ($salary_components as $component): ?>
                            <?php if ($component['type'] === 'reimbursement'): ?>
                                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($component['name']); ?>
                                        </label>
                                        <p class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($component['code']); ?>
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <span class="text-blue-600 mr-2">₹</span>
                                            <input type="number" 
                                                   name="components[<?php echo $component['id']; ?>]" 
                                                   value="<?php echo $current_amounts[$component['id']] ?? ''; ?>"
                                                   class="form-input w-32 text-right" 
                                                   step="0.01" 
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Summary -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Salary Summary</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Total Earnings</p>
                            <p id="total-earnings" class="text-2xl font-bold text-green-600">₹0.00</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Total Deductions</p>
                            <p id="total-deductions" class="text-2xl font-bold text-red-600">₹0.00</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Net Salary</p>
                            <p id="net-salary" class="text-2xl font-bold text-blue-600">₹0.00</p>
                        </div>
                    </div>
                    
                    <!-- Formula Validation -->
                    <div class="mt-6 border-t pt-4">
                        <h5 class="text-sm font-semibold text-gray-900 mb-2">Formula Validation</h5>
                        <div id="formula-validation" class="text-sm text-gray-600">
                            All formulas will be validated when you save the structure.
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="mt-4 flex items-center justify-between">
                        <button type="button" onclick="validateFormulas()" class="btn btn-outline btn-sm">
                            <i class="fas fa-check-circle mr-2"></i>
                            Validate Formulas
                        </button>
                        <button type="button" onclick="previewCalculation()" class="btn btn-outline btn-sm">
                            <i class="fas fa-calculator mr-2"></i>
                            Preview Calculation
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="/employees/<?php echo $employee['id']; ?>" class="btn btn-outline">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Save Salary Structure
            </button>
        </div>
    </form>
</div>

<script>
// Calculate totals when amounts change
function calculateTotals() {
    let totalEarnings = 0;
    let totalDeductions = 0;
    
    // Calculate earnings
    document.querySelectorAll('.bg-green-50 input[type="number"]').forEach(input => {
        const value = parseFloat(input.value) || 0;
        totalEarnings += value;
    });
    
    // Calculate deductions
    document.querySelectorAll('.bg-red-50 input[type="number"]').forEach(input => {
        const value = parseFloat(input.value) || 0;
        totalDeductions += value;
    });
    
    const netSalary = totalEarnings - totalDeductions;
    
    // Update display
    document.getElementById('total-earnings').textContent = '₹' + totalEarnings.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('total-deductions').textContent = '₹' + totalDeductions.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('net-salary').textContent = '₹' + netSalary.toLocaleString('en-IN', {minimumFractionDigits: 2});
}

// Validate formulas
function validateFormulas() {
    showLoading();
    
    const formulas = [];
    document.querySelectorAll('[data-formula]').forEach(element => {
        const formula = element.dataset.formula;
        if (formula) {
            formulas.push({
                component: element.dataset.component,
                formula: formula
            });
        }
    });
    
    fetch('/api/validate-formulas', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            formulas: formulas,
            csrf_token: '<?php echo $csrf_token; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            document.getElementById('formula-validation').innerHTML = 
                '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>All formulas are valid</span>';
        } else {
            document.getElementById('formula-validation').innerHTML = 
                '<span class="text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>' + data.message + '</span>';
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Error validating formulas', 'error');
    });
}

// Preview calculation
function previewCalculation() {
    const components = {};
    document.querySelectorAll('input[name^="components["]').forEach(input => {
        const componentId = input.name.match(/\[(\d+)\]/)[1];
        const amount = parseFloat(input.value) || 0;
        if (amount > 0) {
            components[componentId] = amount;
        }
    });
    
    if (Object.keys(components).length === 0) {
        showMessage('Please enter at least one component amount', 'warning');
        return;
    }
    
    showLoading();
    
    fetch('/api/preview-salary-calculation', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            employee_id: <?php echo $employee['id']; ?>,
            components: components,
            csrf_token: '<?php echo $csrf_token; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showCalculationPreview(data.calculation);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Error previewing calculation', 'error');
    });
}

function showCalculationPreview(calculation) {
    // Create modal or update existing display with calculation details
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Salary Calculation Preview</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Gross Earnings:</span>
                        <span class="font-medium">₹${calculation.gross_earnings.toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total Deductions:</span>
                        <span class="font-medium">₹${calculation.total_deductions.toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2 font-semibold">
                        <span>Net Salary:</span>
                        <span>₹${calculation.net_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button onclick="this.closest('.fixed').remove()" class="btn btn-primary">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Add event listeners to all amount inputs
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', calculateTotals);
});

// Calculate initial totals
calculateTotals();

// Form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const netSalary = parseFloat(document.getElementById('net-salary').textContent.replace('₹', '').replace(/,/g, ''));
    
    if (netSalary <= 0) {
        e.preventDefault();
        showMessage('Net salary must be greater than zero', 'error');
        return;
    }
    
    showLoading();
});

// Auto-calculate formula-based components
document.addEventListener('DOMContentLoaded', function() {
    // This would implement formula calculation logic
    // For now, we'll just show the current values
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>