<?php 
$title = 'Tax Calculator - Payroll System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/tax" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Income Tax Calculator</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Calculate income tax liability for FY <?php echo $current_fy; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Calculator Form -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tax Calculation</h3>
            </div>
            <div class="p-6">
                <form id="tax-calculator-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="financial_year" value="<?php echo $current_fy; ?>">
                    
                    <div class="space-y-6">
                        <div>
                            <label for="annual_income" class="block text-sm font-medium text-gray-700 mb-2">Annual Income *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" name="annual_income" id="annual_income" required
                                       class="pl-8 form-input" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        
                        <div>
                            <label for="deductions" class="block text-sm font-medium text-gray-700 mb-2">Total Deductions (80C, 80D, etc.)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" name="deductions" id="deductions"
                                       class="pl-8 form-input" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calculator mr-2"></i>
                                Calculate Tax
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tax Calculation Result -->
        <div id="tax-result" class="bg-white shadow-sm rounded-lg border border-gray-200 hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tax Calculation Result</h3>
            </div>
            <div class="p-6">
                <div id="tax-summary" class="space-y-4">
                    <!-- Tax calculation results will be displayed here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Slabs Reference -->
    <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Income Tax Slabs for FY <?php echo $current_fy; ?></h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Income Slab
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tax Rate
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Up to ₹3,00,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Nil</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹3,00,001 - ₹7,00,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5%</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹7,00,001 - ₹10,00,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">10%</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹10,00,001 - ₹12,00,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">15%</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹12,00,001 - ₹15,00,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">20%</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Above ₹15,00,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">30%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-sm text-gray-600">
                <p><strong>Note:</strong> Additional surcharge and cess may apply based on income level.</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    <li>Surcharge: 10% if income > ₹50 lakhs, 15% if income > ₹1 crore</li>
                    <li>Health and Education Cess: 4% on (tax + surcharge)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('tax-calculator-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    showLoading();
    
    fetch('/tax/calculate', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            displayTaxResult(data.calculation);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while calculating tax', 'error');
    });
});

function displayTaxResult(calculation) {
    const resultDiv = document.getElementById('tax-result');
    const summaryDiv = document.getElementById('tax-summary');
    
    let html = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-gray-600">Annual Income</p>
                <p class="text-xl font-bold text-blue-600">₹${calculation.annual_income.toLocaleString('en-IN')}</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-gray-600">Total Deductions</p>
                <p class="text-xl font-bold text-green-600">₹${calculation.deductions.toLocaleString('en-IN')}</p>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <p class="text-sm text-gray-600">Taxable Income</p>
                <p class="text-xl font-bold text-yellow-600">₹${calculation.taxable_income.toLocaleString('en-IN')}</p>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-lg">
                <p class="text-sm text-gray-600">Total Tax</p>
                <p class="text-xl font-bold text-red-600">₹${calculation.total_tax.toLocaleString('en-IN')}</p>
            </div>
        </div>
        
        <div class="mb-6">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Tax Breakdown</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Slab</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Taxable Amount</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Tax</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    calculation.tax_breakdown.forEach(slab => {
        html += `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">${slab.slab}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${slab.rate}</td>
                <td class="px-4 py-2 text-sm text-gray-900 text-right">₹${slab.taxable_amount.toLocaleString('en-IN')}</td>
                <td class="px-4 py-2 text-sm text-gray-900 text-right">₹${slab.tax_amount.toLocaleString('en-IN')}</td>
            </tr>
        `;
    });
    
    html += `
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span>Basic Tax:</span>
                    <span>₹${calculation.basic_tax.toLocaleString('en-IN')}</span>
                </div>
                <div class="flex justify-between">
                    <span>Surcharge:</span>
                    <span>₹${calculation.surcharge.toLocaleString('en-IN')}</span>
                </div>
                <div class="flex justify-between">
                    <span>Health & Education Cess:</span>
                    <span>₹${calculation.cess.toLocaleString('en-IN')}</span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>Monthly TDS:</span>
                    <span>₹${calculation.monthly_tds.toLocaleString('en-IN')}</span>
                </div>
            </div>
        </div>
    `;
    
    summaryDiv.innerHTML = html;
    resultDiv.classList.remove('hidden');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>