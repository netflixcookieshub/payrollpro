<?php 
$title = 'Bank Transfer Report - Reports';
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
                <h1 class="text-3xl font-bold text-gray-900">Bank Transfer File</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate bank-ready salary transfer files
                </p>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Transfer File Parameters</h3>
        </div>
        <div class="p-6">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="period_id" class="block text-sm font-medium text-gray-700 mb-2">Payroll Period *</label>
                        <select name="period_id" id="period_id" required class="form-select">
                            <option value="">Select Period</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo $period['id']; ?>">
                                    <?php echo htmlspecialchars($period['period_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="bank_format" class="block text-sm font-medium text-gray-700 mb-2">Bank Format</label>
                        <select name="bank_format" id="bank_format" class="form-select">
                            <option value="generic">Generic Format</option>
                            <option value="sbi">State Bank of India</option>
                            <option value="hdfc">HDFC Bank</option>
                            <option value="icici">ICICI Bank</option>
                            <option value="axis">Axis Bank</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Generate File
                        </button>
                    </div>
                </div>
                
                <div class="border-t pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">File Options</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_header" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Include Header Row</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_summary" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Include Summary Row</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bank Format Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Generic Format -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Generic Format</h3>
            </div>
            <div class="p-6">
                <div class="text-sm text-gray-600 space-y-2">
                    <p><strong>Fields:</strong></p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Employee Code</li>
                        <li>Employee Name</li>
                        <li>Bank Account Number</li>
                        <li>IFSC Code</li>
                        <li>Net Amount</li>
                    </ul>
                    <p class="mt-4"><strong>Format:</strong> CSV with comma separation</p>
                </div>
            </div>
        </div>

        <!-- Bank Specific Formats -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Bank Specific Formats</h3>
            </div>
            <div class="p-6">
                <div class="text-sm text-gray-600 space-y-3">
                    <div>
                        <p><strong>SBI Format:</strong> Fixed-width format as per SBI specifications</p>
                    </div>
                    <div>
                        <p><strong>HDFC Format:</strong> Excel format with specific column headers</p>
                    </div>
                    <div>
                        <p><strong>ICICI Format:</strong> CSV format with ICICI field requirements</p>
                    </div>
                    <div>
                        <p><strong>Axis Format:</strong> Tab-separated format for Axis Bank</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Data Preview -->
    <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Sample Transfer Data</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Emp Code</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account Number</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">IFSC</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="text-sm">
                            <td class="px-3 py-2 text-gray-500">EMP001</td>
                            <td class="px-3 py-2 text-gray-500">John Doe</td>
                            <td class="px-3 py-2 text-gray-500 font-mono">1234567890123456</td>
                            <td class="px-3 py-2 text-gray-500 font-mono">SBIN0001234</td>
                            <td class="px-3 py-2 text-gray-500">State Bank of India</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900">₹38,550</td>
                        </tr>
                        <tr class="text-sm">
                            <td class="px-3 py-2 text-gray-500">EMP002</td>
                            <td class="px-3 py-2 text-gray-500">Jane Smith</td>
                            <td class="px-3 py-2 text-gray-500 font-mono">1234567890123457</td>
                            <td class="px-3 py-2 text-gray-500 font-mono">HDFC0001234</td>
                            <td class="px-3 py-2 text-gray-500">HDFC Bank</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900">₹55,050</td>
                        </tr>
                        <tr class="bg-gray-50 font-semibold text-sm">
                            <td colspan="5" class="px-3 py-2 text-right">Total Transfer Amount:</td>
                            <td class="px-3 py-2 text-right">₹93,600</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Form submission for bank transfer file generation
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
    
    fetch('/reports/bank-transfer', {
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
                        showMessage('Bank transfer file generated successfully', 'success');
                    } else {
                        showMessage(data.message || 'Failed to generate file', 'error');
                    }
                });
            } else {
                // File download
                const bankFormat = document.getElementById('bank_format').value;
                const filename = `bank_transfer_${bankFormat}_${new Date().toISOString().split('T')[0]}.csv`;
                
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
                    
                    showMessage('Bank transfer file downloaded successfully', 'success');
                });
            }
        } else {
            showMessage('Failed to generate bank transfer file', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while generating the file', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>