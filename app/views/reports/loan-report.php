<?php 
$title = 'Loan Report - Reports';
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
                <h1 class="text-3xl font-bold text-gray-900">Loan Report</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate comprehensive loan and EMI reports
                </p>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Report Parameters</h3>
        </div>
        <div class="p-6">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <label for="as_of_date" class="block text-sm font-medium text-gray-700 mb-2">As of Date *</label>
                        <input type="date" name="as_of_date" id="as_of_date" required 
                               value="<?php echo date('Y-m-d'); ?>" class="form-input">
                    </div>
                    
                    <div>
                        <label for="loan_type_id" class="block text-sm font-medium text-gray-700 mb-2">Loan Type</label>
                        <select name="loan_type_id" id="loan_type_id" class="form-select">
                            <option value="">All Loan Types</option>
                            <?php foreach ($loan_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                        <select name="format" id="format" class="form-select">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
                
                <div class="border-t pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Report Options</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_closed" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Include Closed Loans</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_summary" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Include Summary</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_emi_schedule" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Include EMI Schedule</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sample Report Structure -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Sample Loan Report</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Emp Code</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Loan Type</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Loan Amount</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">EMI Amount</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="text-sm">
                            <td class="px-3 py-2 text-gray-500">EMP001</td>
                            <td class="px-3 py-2 text-gray-500">John Doe</td>
                            <td class="px-3 py-2 text-gray-500">Personal Loan</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹1,00,000</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹4,707</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹85,000</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                        </tr>
                        <tr class="text-sm">
                            <td class="px-3 py-2 text-gray-500">EMP003</td>
                            <td class="px-3 py-2 text-gray-500">Mike Johnson</td>
                            <td class="px-3 py-2 text-gray-500">Home Loan</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹20,00,000</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹24,538</td>
                            <td class="px-3 py-2 text-right text-gray-500">₹19,00,000</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                        </tr>
                        <tr class="bg-gray-50 font-semibold text-sm">
                            <td colspan="3" class="px-3 py-2 text-right">Total:</td>
                            <td class="px-3 py-2 text-right">₹21,00,000</td>
                            <td class="px-3 py-2 text-right">₹29,245</td>
                            <td class="px-3 py-2 text-right">₹19,85,000</td>
                            <td class="px-3 py-2"></td>
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
    const asOfDate = formData.get('as_of_date');
    
    if (!asOfDate) {
        showMessage('Please select as of date', 'error');
        return;
    }
    
    showLoading();
    
    // Convert FormData to JSON
    const data = Object.fromEntries(formData);
    
    fetch('/reports/loan-report', {
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
                    } else {
                        showMessage(data.message || 'Failed to generate report', 'error');
                    }
                });
            } else {
                // File download
                const filename = `loan_report_${new Date().toISOString().split('T')[0]}.${data.format}`;
                
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
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>