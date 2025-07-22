<?php 
$title = 'Salary Register - Reports';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/reports" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Salary Register</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate comprehensive salary reports for payroll periods
                </p>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Report Filters</h3>
        </div>
        <div class="p-6">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="period_id" class="block text-sm font-medium text-gray-700 mb-2">Payroll Period *</label>
                        <select name="period_id" id="period_id" required class="form-select">
                            <option value="">Select Period</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo $period['id']; ?>" 
                                        <?php echo (isset($_GET['period']) && $_GET['period'] == $period['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($period['period_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                        <select name="format" id="format" class="form-select">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
                
                <!-- Advanced Options -->
                <div class="border-t pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Report Options</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_summary" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Include Summary</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_deductions" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Include Deductions Breakdown</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_bank_details" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Include Bank Details</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Preview -->
    <div id="report-preview" class="bg-white shadow-sm rounded-lg border border-gray-200 hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Report Preview</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="printReport()" class="btn btn-outline btn-sm">
                        <i class="fas fa-print mr-2"></i>
                        Print
                    </button>
                    <button onclick="exportReport()" class="btn btn-primary btn-sm">
                        <i class="fas fa-download mr-2"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div id="report-content">
                <!-- Report content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Sample Report Structure -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Sample Report Structure</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Emp Code</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Designation</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Basic</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">HRA</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Allowances</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Gross</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">PF</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">TDS</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Deductions</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net Pay</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="text-sm">
                            <td class="px-3 py-2 text-gray-500">EMP001</td>
                            <td class="px-3 py-2 text-gray-500">John Doe</td>
                            <td class="px-3 py-2 text-gray-500">IT</td>
                            <td class="px-3 py-2 text-gray-500">Software Engineer</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹30,000</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹12,000</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹2,850</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹44,850</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹3,600</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹2,500</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹6,300</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900">₹38,550</td>
                        </tr>
                        <tr class="text-sm">
                            <td class="px-3 py-2 text-gray-500">EMP002</td>
                            <td class="px-3 py-2 text-gray-500">Jane Smith</td>
                            <td class="px-3 py-2 text-gray-500">IT</td>
                            <td class="px-3 py-2 text-gray-500">Senior Software Engineer</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹45,000</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹18,000</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹2,850</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹65,850</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹5,400</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹5,200</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹10,800</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900">₹55,050</td>
                        </tr>
                        <tr class="bg-gray-50 font-semibold text-sm">
                            <td colspan="7" class="px-3 py-2 text-right">Total:</td>
                            <td class="px-3 py-2 text-right">₹1,10,700</td>
                            <td class="px-3 py-2 text-right">₹9,000</td>
                            <td class="px-3 py-2 text-right">₹7,700</td>
                            <td class="px-3 py-2 text-right">₹17,100</td>
                            <td class="px-3 py-2 text-right">₹93,600</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Form submission for report generation
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const periodId = formData.get('period_id');
    
    if (!periodId) {
        showMessage('Please select a payroll period', 'error');
        return;
    }
    
    showLoading();
    
    // Convert FormData to JSON
    const data = Object.fromEntries(formData);
    
    fetch('/reports/salary-register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        hideLoading();
        
        if (response.ok) {
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => {
                    if (data.success) {
                        showMessage('Report generated successfully', 'success');
                        // Show preview or download link
                    } else {
                        showMessage(data.message || 'Failed to generate report', 'error');
                    }
                });
            } else {
                // File download
                const filename = `salary_register_${new Date().toISOString().split('T')[0]}.${data.format}`;
                
                return response.blob().then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    showMessage('Report downloaded successfully', 'success');
                });
            }
        } else {
            showMessage('Failed to generate report', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while generating the report', 'error');
    });
});

function printReport() {
    window.print();
}

function exportReport() {
    // Trigger form submission with current settings
    document.querySelector('form').dispatchEvent(new Event('submit'));
}

// Auto-generate report if period is provided in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const periodParam = urlParams.get('period');
    const autoParam = urlParams.get('auto');
    
    if (periodParam) {
        document.getElementById('period_id').value = periodParam;
        
        if (autoParam === '1') {
            // Auto-generate report
            setTimeout(() => {
                document.querySelector('form').dispatchEvent(new Event('submit'));
            }, 500);
        }
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>