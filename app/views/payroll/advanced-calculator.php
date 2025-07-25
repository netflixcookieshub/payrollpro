<?php 
$title = 'Advanced Salary Calculator - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/payroll" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Advanced Salary Calculator</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Calculate salary with formulas, pro-rata, LOP, and TDS
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Calculator Form -->
        <div class="space-y-6">
            <!-- Employee Selection -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Employee & Period</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
                            <select name="employee_id" id="employee_id" required class="form-select">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee['id']; ?>" 
                                            data-basic="<?php echo $employee['basic_salary'] ?? 0; ?>"
                                            data-join-date="<?php echo $employee['join_date']; ?>">
                                        <?php echo htmlspecialchars($employee['emp_code'] . ' - ' . $employee['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="period_id" class="block text-sm font-medium text-gray-700 mb-2">Payroll Period *</label>
                            <select name="period_id" id="period_id" required class="form-select">
                                <option value="">Select Period</option>
                                <?php foreach ($periods as $period): ?>
                                    <option value="<?php echo $period['id']; ?>"
                                            data-start="<?php echo $period['start_date']; ?>"
                                            data-end="<?php echo $period['end_date']; ?>"
                                            data-fy="<?php echo $period['financial_year']; ?>">
                                        <?php echo htmlspecialchars($period['period_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance & LOP -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Attendance Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="working_days" class="block text-sm font-medium text-gray-700 mb-2">Working Days</label>
                            <input type="number" id="working_days" class="form-input" value="22" min="1" max="31">
                        </div>
                        <div>
                            <label for="present_days" class="block text-sm font-medium text-gray-700 mb-2">Present Days</label>
                            <input type="number" id="present_days" class="form-input" value="22" min="0" max="31">
                        </div>
                        <div>
                            <label for="lop_days" class="block text-sm font-medium text-gray-700 mb-2">LOP Days</label>
                            <input type="number" id="lop_days" class="form-input" value="0" min="0" max="31" readonly>
                        </div>
                        <div>
                            <label for="overtime_hours" class="block text-sm font-medium text-gray-700 mb-2">Overtime Hours</label>
                            <input type="number" id="overtime_hours" class="form-input" value="0" min="0" step="0.5">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pro-rata Settings -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Pro-rata Calculation</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="apply_pro_rata" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Apply Pro-rata Calculation</span>
                        </label>
                        
                        <div id="pro-rata-details" class="hidden grid grid-cols-2 gap-4">
                            <div>
                                <label for="actual_start_date" class="block text-sm font-medium text-gray-700 mb-2">Actual Start Date</label>
                                <input type="date" id="actual_start_date" class="form-input">
                            </div>
                            <div>
                                <label for="actual_end_date" class="block text-sm font-medium text-gray-700 mb-2">Actual End Date</label>
                                <input type="date" id="actual_end_date" class="form-input">
                            </div>
                        </div>
                        
                        <div id="pro-rata-factor" class="text-sm text-gray-600 hidden">
                            Pro-rata Factor: <span id="pro-rata-value" class="font-medium">100%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculate Button -->
            <div class="text-center">
                <button onclick="calculateAdvancedSalary()" class="btn btn-primary btn-lg">
                    <i class="fas fa-calculator mr-2"></i>
                    Calculate Salary
                </button>
            </div>
        </div>

        <!-- Results Panel -->
        <div class="space-y-6">
            <!-- Earnings Breakdown -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 text-green-700">Earnings Breakdown</h3>
                </div>
                <div class="p-6">
                    <div id="earnings-breakdown" class="space-y-3">
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-calculator text-4xl mb-4"></i>
                            <p>Select employee and period to calculate</p>
                        </div>
                    </div>
                    <div class="border-t pt-4 mt-4">
                        <div class="flex justify-between items-center font-semibold text-lg">
                            <span>Total Earnings:</span>
                            <span id="total-earnings" class="text-green-600">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions Breakdown -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 text-red-700">Deductions Breakdown</h3>
                </div>
                <div class="p-6">
                    <div id="deductions-breakdown" class="space-y-3">
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-minus-circle text-4xl mb-4"></i>
                            <p>Deductions will appear here</p>
                        </div>
                    </div>
                    <div class="border-t pt-4 mt-4">
                        <div class="flex justify-between items-center font-semibold text-lg">
                            <span>Total Deductions:</span>
                            <span id="total-deductions" class="text-red-600">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Net Salary -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Net Salary</h3>
                </div>
                <div class="p-6 text-center">
                    <div class="text-4xl font-bold text-blue-600 mb-2" id="net-salary">₹0.00</div>
                    <div class="text-sm text-gray-500" id="net-salary-words">Zero Rupees Only</div>
                    
                    <div class="mt-6 grid grid-cols-3 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900" id="gross-salary">₹0</div>
                            <div class="text-gray-500">Gross Salary</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900" id="take-home">₹0</div>
                            <div class="text-gray-500">Take Home</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900" id="annual-ctc">₹0</div>
                            <div class="text-gray-500">Annual CTC</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TDS Details -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">TDS Calculation</h3>
                </div>
                <div class="p-6">
                    <div id="tds-details" class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Annual Gross:</span>
                            <span id="annual-gross">₹0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Taxable Income:</span>
                            <span id="taxable-income">₹0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Annual Tax:</span>
                            <span id="annual-tax">₹0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Previous TDS:</span>
                            <span id="previous-tds">₹0</span>
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span>Monthly TDS:</span>
                            <span id="monthly-tds">₹0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate LOP days
document.getElementById('working_days').addEventListener('input', calculateLOP);
document.getElementById('present_days').addEventListener('input', calculateLOP);

function calculateLOP() {
    const workingDays = parseInt(document.getElementById('working_days').value) || 0;
    const presentDays = parseInt(document.getElementById('present_days').value) || 0;
    const lopDays = Math.max(0, workingDays - presentDays);
    
    document.getElementById('lop_days').value = lopDays;
}

// Show/hide pro-rata details
document.getElementById('apply_pro_rata').addEventListener('change', function() {
    const details = document.getElementById('pro-rata-details');
    const factor = document.getElementById('pro-rata-factor');
    
    if (this.checked) {
        details.classList.remove('hidden');
        factor.classList.remove('hidden');
        calculateProRataFactor();
    } else {
        details.classList.add('hidden');
        factor.classList.add('hidden');
    }
});

// Calculate pro-rata factor
document.getElementById('actual_start_date').addEventListener('change', calculateProRataFactor);
document.getElementById('actual_end_date').addEventListener('change', calculateProRataFactor);

function calculateProRataFactor() {
    const startDate = new Date(document.getElementById('actual_start_date').value);
    const endDate = new Date(document.getElementById('actual_end_date').value);
    
    if (startDate && endDate && startDate <= endDate) {
        const totalDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        const periodSelect = document.getElementById('period_id');
        const selectedOption = periodSelect.options[periodSelect.selectedIndex];
        
        if (selectedOption.value) {
            const periodStart = new Date(selectedOption.dataset.start);
            const periodEnd = new Date(selectedOption.dataset.end);
            const periodDays = Math.ceil((periodEnd - periodStart) / (1000 * 60 * 60 * 24)) + 1;
            
            const factor = (totalDays / periodDays) * 100;
            document.getElementById('pro-rata-value').textContent = factor.toFixed(2) + '%';
        }
    }
}

// Advanced salary calculation
function calculateAdvancedSalary() {
    const employeeId = document.getElementById('employee_id').value;
    const periodId = document.getElementById('period_id').value;
    
    if (!employeeId || !periodId) {
        showMessage('Please select employee and period', 'error');
        return;
    }
    
    const calculationData = {
        employee_id: employeeId,
        period_id: periodId,
        working_days: document.getElementById('working_days').value,
        present_days: document.getElementById('present_days').value,
        lop_days: document.getElementById('lop_days').value,
        overtime_hours: document.getElementById('overtime_hours').value,
        apply_pro_rata: document.getElementById('apply_pro_rata').checked,
        actual_start_date: document.getElementById('actual_start_date').value,
        actual_end_date: document.getElementById('actual_end_date').value
    };
    
    showLoading();
    
    fetch('/payroll/calculate-salary?' + new URLSearchParams(calculationData))
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                displayCalculationResults(data.calculation);
                showMessage('Salary calculated successfully', 'success');
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('An error occurred during calculation', 'error');
        });
}

function displayCalculationResults(calculation) {
    // Display earnings
    const earningsDiv = document.getElementById('earnings-breakdown');
    earningsDiv.innerHTML = '';
    
    let totalEarnings = 0;
    calculation.earnings.forEach(earning => {
        totalEarnings += earning.amount;
        
        const earningDiv = document.createElement('div');
        earningDiv.className = 'flex justify-between items-center py-2 border-b border-gray-100';
        earningDiv.innerHTML = `
            <div>
                <span class="text-sm font-medium text-gray-900">${earning.name}</span>
                ${earning.formula ? `<div class="text-xs text-gray-500">Formula: ${earning.formula}</div>` : ''}
            </div>
            <span class="text-sm font-medium text-green-600">₹${earning.amount.toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
        `;
        earningsDiv.appendChild(earningDiv);
    });
    
    // Display deductions
    const deductionsDiv = document.getElementById('deductions-breakdown');
    deductionsDiv.innerHTML = '';
    
    let totalDeductions = 0;
    calculation.deductions.forEach(deduction => {
        totalDeductions += deduction.amount;
        
        const deductionDiv = document.createElement('div');
        deductionDiv.className = 'flex justify-between items-center py-2 border-b border-gray-100';
        deductionDiv.innerHTML = `
            <div>
                <span class="text-sm font-medium text-gray-900">${deduction.name}</span>
                ${deduction.calculation_note ? `<div class="text-xs text-gray-500">${deduction.calculation_note}</div>` : ''}
            </div>
            <span class="text-sm font-medium text-red-600">₹${deduction.amount.toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
        `;
        deductionsDiv.appendChild(deductionDiv);
    });
    
    // Update totals
    const netSalary = totalEarnings - totalDeductions;
    
    document.getElementById('total-earnings').textContent = '₹' + totalEarnings.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('total-deductions').textContent = '₹' + totalDeductions.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('net-salary').textContent = '₹' + netSalary.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('gross-salary').textContent = '₹' + totalEarnings.toLocaleString('en-IN');
    document.getElementById('take-home').textContent = '₹' + netSalary.toLocaleString('en-IN');
    document.getElementById('annual-ctc').textContent = '₹' + (totalEarnings * 12).toLocaleString('en-IN');
    
    // Update TDS details
    if (calculation.tds_details) {
        const tds = calculation.tds_details;
        document.getElementById('annual-gross').textContent = '₹' + tds.annual_gross.toLocaleString('en-IN');
        document.getElementById('taxable-income').textContent = '₹' + tds.taxable_income.toLocaleString('en-IN');
        document.getElementById('annual-tax').textContent = '₹' + tds.annual_tax.toLocaleString('en-IN');
        document.getElementById('previous-tds').textContent = '₹' + tds.previous_tds.toLocaleString('en-IN');
        document.getElementById('monthly-tds').textContent = '₹' + tds.monthly_tds.toLocaleString('en-IN', {minimumFractionDigits: 2});
    }
    
    // Convert number to words (simplified)
    document.getElementById('net-salary-words').textContent = numberToWords(netSalary) + ' Rupees Only';
}

// Simplified number to words conversion
function numberToWords(num) {
    if (num === 0) return 'Zero';
    
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
    const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    
    function convertHundreds(n) {
        let result = '';
        
        if (n >= 100) {
            result += ones[Math.floor(n / 100)] + ' Hundred ';
            n %= 100;
        }
        
        if (n >= 20) {
            result += tens[Math.floor(n / 10)] + ' ';
            n %= 10;
        } else if (n >= 10) {
            result += teens[n - 10] + ' ';
            return result;
        }
        
        if (n > 0) {
            result += ones[n] + ' ';
        }
        
        return result;
    }
    
    let result = '';
    
    if (num >= 10000000) {
        result += convertHundreds(Math.floor(num / 10000000)) + 'Crore ';
        num %= 10000000;
    }
    
    if (num >= 100000) {
        result += convertHundreds(Math.floor(num / 100000)) + 'Lakh ';
        num %= 100000;
    }
    
    if (num >= 1000) {
        result += convertHundreds(Math.floor(num / 1000)) + 'Thousand ';
        num %= 1000;
    }
    
    if (num > 0) {
        result += convertHundreds(num);
    }
    
    return result.trim();
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>