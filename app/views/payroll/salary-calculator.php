<?php 
$title = 'Salary Calculator - Payroll Management System';
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
                <h1 class="text-3xl font-bold text-gray-900">Salary Calculator</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Calculate salary with real-time formula evaluation and statutory deductions
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Calculator Input -->
        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="basic_salary" class="block text-sm font-medium text-gray-700 mb-2">Basic Salary *</label>
                            <input type="number" id="basic_salary" class="form-input" 
                                   placeholder="Enter basic salary" min="1000" step="100">
                        </div>
                        
                        <div>
                            <label for="employee_type" class="block text-sm font-medium text-gray-700 mb-2">Employee Type</label>
                            <select id="employee_type" class="form-select">
                                <option value="regular">Regular Employee</option>
                                <option value="contract">Contract Employee</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings Components -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 text-green-700">Earnings</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-900">House Rent Allowance (HRA)</label>
                                <p class="text-xs text-gray-500">Formula: BASIC * 0.4</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-green-600 mr-2">₹</span>
                                <input type="number" id="hra" class="form-input w-24 text-right" readonly>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Transport Allowance</label>
                                <p class="text-xs text-gray-500">Fixed: ₹1,600</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-green-600 mr-2">₹</span>
                                <input type="number" id="transport_allowance" class="form-input w-24 text-right" value="1600">
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Medical Allowance</label>
                                <p class="text-xs text-gray-500">Fixed: ₹1,250</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-green-600 mr-2">₹</span>
                                <input type="number" id="medical_allowance" class="form-input w-24 text-right" value="1250">
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Special Allowance</label>
                                <p class="text-xs text-gray-500">Optional</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-green-600 mr-2">₹</span>
                                <input type="number" id="special_allowance" class="form-input w-24 text-right" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deductions -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 text-red-700">Deductions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Provident Fund (PF)</label>
                                <p class="text-xs text-gray-500">12% of Basic (max ₹15,000)</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-red-600 mr-2">₹</span>
                                <input type="number" id="pf" class="form-input w-24 text-right" readonly>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-900">ESI Contribution</label>
                                <p class="text-xs text-gray-500">0.75% if gross ≤ ₹21,000</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-red-600 mr-2">₹</span>
                                <input type="number" id="esi" class="form-input w-24 text-right" readonly>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Professional Tax</label>
                                <p class="text-xs text-gray-500">As per state rules</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-red-600 mr-2">₹</span>
                                <input type="number" id="professional_tax" class="form-input w-24 text-right" readonly>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Income Tax (TDS)</label>
                                <p class="text-xs text-gray-500">As per IT slabs</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-red-600 mr-2">₹</span>
                                <input type="number" id="income_tax" class="form-input w-24 text-right" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calculate Button -->
            <div class="text-center">
                <button onclick="calculateSalary()" class="btn btn-primary btn-lg">
                    <i class="fas fa-calculator mr-2"></i>
                    Calculate Salary
                </button>
            </div>
        </div>

        <!-- Results Panel -->
        <div class="space-y-6">
            <!-- Summary -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Salary Summary</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600" id="total-earnings-display">₹0</div>
                            <div class="text-sm text-gray-500">Total Earnings</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600" id="total-deductions-display">₹0</div>
                            <div class="text-sm text-gray-500">Total Deductions</div>
                        </div>
                        
                        <div class="text-center border-t pt-4">
                            <div class="text-4xl font-bold text-blue-600" id="net-salary-display">₹0</div>
                            <div class="text-sm text-gray-500">Net Salary</div>
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900" id="annual-ctc">₹0</div>
                            <div class="text-gray-500">Annual CTC</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900" id="take-home">₹0</div>
                            <div class="text-gray-500">Take Home</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breakdown Chart -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Salary Breakdown</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Basic Salary</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div id="basic-bar" class="bg-blue-500 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <span id="basic-amount" class="text-sm font-medium w-16 text-right">₹0</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">HRA</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div id="hra-bar" class="bg-green-500 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <span id="hra-amount" class="text-sm font-medium w-16 text-right">₹0</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Other Allowances</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div id="allowances-bar" class="bg-yellow-500 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <span id="allowances-amount" class="text-sm font-medium w-16 text-right">₹0</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Deductions</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div id="deductions-bar" class="bg-red-500 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <span id="deductions-amount" class="text-sm font-medium w-16 text-right">₹0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Information -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Tax Information</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Annual Gross:</span>
                            <span id="annual-gross">₹0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Standard Deduction:</span>
                            <span>₹50,000</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Taxable Income:</span>
                            <span id="taxable-income">₹0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Annual Tax:</span>
                            <span id="annual-tax">₹0</span>
                        </div>
                        <div class="flex justify-between font-semibold border-t pt-2">
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
// Auto-calculate when basic salary changes
document.getElementById('basic_salary').addEventListener('input', function() {
    const basic = parseFloat(this.value) || 0;
    
    // Calculate HRA (40% of basic)
    const hra = basic * 0.4;
    document.getElementById('hra').value = hra;
    
    // Auto-calculate other components
    calculateSalary();
});

// Calculate all other components when any input changes
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', calculateSalary);
});

function calculateSalary() {
    const basic = parseFloat(document.getElementById('basic_salary').value) || 0;
    const hra = parseFloat(document.getElementById('hra').value) || 0;
    const transportAllowance = parseFloat(document.getElementById('transport_allowance').value) || 0;
    const medicalAllowance = parseFloat(document.getElementById('medical_allowance').value) || 0;
    const specialAllowance = parseFloat(document.getElementById('special_allowance').value) || 0;
    
    if (basic === 0) {
        resetCalculations();
        return;
    }
    
    // Calculate earnings
    const totalEarnings = basic + hra + transportAllowance + medicalAllowance + specialAllowance;
    
    // Calculate deductions
    const pf = Math.min(basic * 0.12, 1800); // 12% of basic, max ₹1,800 (for ₹15,000 ceiling)
    const esi = totalEarnings <= 21000 ? totalEarnings * 0.0075 : 0;
    const professionalTax = calculatePT(totalEarnings);
    const incomeTax = calculateTDS(totalEarnings);
    
    const totalDeductions = pf + esi + professionalTax + incomeTax;
    const netSalary = totalEarnings - totalDeductions;
    
    // Update deduction fields
    document.getElementById('pf').value = pf.toFixed(2);
    document.getElementById('esi').value = esi.toFixed(2);
    document.getElementById('professional_tax').value = professionalTax.toFixed(2);
    document.getElementById('income_tax').value = incomeTax.toFixed(2);
    
    // Update summary
    document.getElementById('total-earnings-display').textContent = '₹' + totalEarnings.toLocaleString('en-IN');
    document.getElementById('total-deductions-display').textContent = '₹' + totalDeductions.toLocaleString('en-IN');
    document.getElementById('net-salary-display').textContent = '₹' + netSalary.toLocaleString('en-IN');
    document.getElementById('annual-ctc').textContent = '₹' + (totalEarnings * 12).toLocaleString('en-IN');
    document.getElementById('take-home').textContent = '₹' + netSalary.toLocaleString('en-IN');
    
    // Update breakdown bars
    updateBreakdownBars(basic, hra, transportAllowance + medicalAllowance + specialAllowance, totalDeductions, totalEarnings);
    
    // Update tax information
    updateTaxInformation(totalEarnings, incomeTax);
}

function calculatePT(grossSalary) {
    if (grossSalary > 21000) return 200;
    if (grossSalary > 15000) return 150;
    if (grossSalary > 10000) return 100;
    return 0;
}

function calculateTDS(monthlyGross) {
    const annualGross = monthlyGross * 12;
    const standardDeduction = 50000;
    const taxableIncome = Math.max(0, annualGross - standardDeduction);
    
    let annualTax = 0;
    
    // Tax slabs for FY 2024-25
    if (taxableIncome > 300000) {
        if (taxableIncome <= 700000) {
            annualTax += (taxableIncome - 300000) * 0.05;
        } else if (taxableIncome <= 1000000) {
            annualTax += 400000 * 0.05 + (taxableIncome - 700000) * 0.10;
        } else if (taxableIncome <= 1200000) {
            annualTax += 400000 * 0.05 + 300000 * 0.10 + (taxableIncome - 1000000) * 0.15;
        } else if (taxableIncome <= 1500000) {
            annualTax += 400000 * 0.05 + 300000 * 0.10 + 200000 * 0.15 + (taxableIncome - 1200000) * 0.20;
        } else {
            annualTax += 400000 * 0.05 + 300000 * 0.10 + 200000 * 0.15 + 300000 * 0.20 + (taxableIncome - 1500000) * 0.30;
        }
    }
    
    // Add cess (4%)
    annualTax += annualTax * 0.04;
    
    return annualTax / 12; // Monthly TDS
}

function updateBreakdownBars(basic, hra, allowances, deductions, total) {
    const basicPercent = (basic / total) * 100;
    const hraPercent = (hra / total) * 100;
    const allowancesPercent = (allowances / total) * 100;
    const deductionsPercent = (deductions / total) * 100;
    
    document.getElementById('basic-bar').style.width = basicPercent + '%';
    document.getElementById('hra-bar').style.width = hraPercent + '%';
    document.getElementById('allowances-bar').style.width = allowancesPercent + '%';
    document.getElementById('deductions-bar').style.width = deductionsPercent + '%';
    
    document.getElementById('basic-amount').textContent = '₹' + basic.toLocaleString('en-IN');
    document.getElementById('hra-amount').textContent = '₹' + hra.toLocaleString('en-IN');
    document.getElementById('allowances-amount').textContent = '₹' + allowances.toLocaleString('en-IN');
    document.getElementById('deductions-amount').textContent = '₹' + deductions.toLocaleString('en-IN');
}

function updateTaxInformation(monthlyGross, monthlyTDS) {
    const annualGross = monthlyGross * 12;
    const standardDeduction = 50000;
    const taxableIncome = Math.max(0, annualGross - standardDeduction);
    const annualTax = monthlyTDS * 12;
    
    document.getElementById('annual-gross').textContent = '₹' + annualGross.toLocaleString('en-IN');
    document.getElementById('taxable-income').textContent = '₹' + taxableIncome.toLocaleString('en-IN');
    document.getElementById('annual-tax').textContent = '₹' + annualTax.toLocaleString('en-IN');
    document.getElementById('monthly-tds').textContent = '₹' + monthlyTDS.toLocaleString('en-IN', {minimumFractionDigits: 2});
}

function resetCalculations() {
    document.getElementById('hra').value = 0;
    document.getElementById('pf').value = 0;
    document.getElementById('esi').value = 0;
    document.getElementById('professional_tax').value = 0;
    document.getElementById('income_tax').value = 0;
    
    document.getElementById('total-earnings-display').textContent = '₹0';
    document.getElementById('total-deductions-display').textContent = '₹0';
    document.getElementById('net-salary-display').textContent = '₹0';
    document.getElementById('annual-ctc').textContent = '₹0';
    document.getElementById('take-home').textContent = '₹0';
    
    // Reset bars
    document.querySelectorAll('[id$="-bar"]').forEach(bar => {
        bar.style.width = '0%';
    });
    
    document.querySelectorAll('[id$="-amount"]').forEach(amount => {
        amount.textContent = '₹0';
    });
}

// Initialize with sample calculation
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('basic_salary').value = 30000;
    calculateSalary();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>