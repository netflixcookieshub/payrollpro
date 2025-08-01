<?php 
$title = 'Attendance Report - Reports';
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
                <h1 class="text-3xl font-bold text-gray-900">Attendance Report</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate comprehensive attendance reports and analytics
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
                        <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">Report Type *</label>
                        <select name="report_type" id="report_type" required class="form-select">
                            <option value="monthly">Monthly Summary</option>
                            <option value="daily">Daily Attendance</option>
                            <option value="employee_wise">Employee-wise Summary</option>
                            <option value="department_wise">Department-wise Summary</option>
                        </select>
                    </div>
                    
                    <div id="date-range-fields">
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" required
                               value="<?php echo date('Y-m-01'); ?>" class="form-input">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                        <input type="date" name="end_date" id="end_date" required
                               value="<?php echo date('Y-m-t'); ?>" class="form-input">
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
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                        <select name="format" id="format" class="form-select">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="include_weekends" class="block text-sm font-medium text-gray-700 mb-2">Include Weekends</label>
                        <select name="include_weekends" id="include_weekends" class="form-select">
                            <option value="0">Exclude Weekends</option>
                            <option value="1">Include Weekends</option>
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
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Advanced Options</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_overtime" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Include Overtime Hours</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_late_marks" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Include Late Marks</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_early_outs" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Include Early Outs</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sample Report Preview -->
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
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Working Days</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Present</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Absent</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Late</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Half Day</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Attendance %</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="text-sm">
                            <td class="px-3 py-2 text-gray-500">EMP001</td>
                            <td class="px-3 py-2 text-gray-500">John Doe</td>
                            <td class="px-3 py-2 text-gray-500">IT</td>
                            <td class="px-3 py-2 text-right text-gray-500">22</td>
                            <td class="px-3 py-2 text-right text-gray-500">20</td>
                            <td class="px-3 py-2 text-right text-gray-500">1</td>
                            <td class="px-3 py-2 text-right text-gray-500">2</td>
                            <td class="px-3 py-2 text-right text-gray-500">1</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900">90.9%</td>
                        </tr>
                        <tr class="text-sm">
                            <td class="px-3 py-2 text-gray-500">EMP002</td>
                            <td class="px-3 py-2 text-gray-500">Jane Smith</td>
                            <td class="px-3 py-2 text-gray-500">IT</td>
                            <td class="px-3 py-2 text-right text-gray-500">22</td>
                            <td class="px-3 py-2 text-right text-gray-500">22</td>
                            <td class="px-3 py-2 text-right text-gray-500">0</td>
                            <td class="px-3 py-2 text-right text-gray-500">0</td>
                            <td class="px-3 py-2 text-right text-gray-500">0</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900">100%</td>
                        </tr>
                        <tr class="bg-gray-50 font-semibold text-sm">
                            <td colspan="3" class="px-3 py-2 text-right">Total:</td>
                            <td class="px-3 py-2 text-right">44</td>
                            <td class="px-3 py-2 text-right">42</td>
                            <td class="px-3 py-2 text-right">1</td>
                            <td class="px-3 py-2 text-right">2</td>
                            <td class="px-3 py-2 text-right">1</td>
                            <td class="px-3 py-2 text-right">95.5%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Update date fields based on report type
document.getElementById('report_type').addEventListener('change', function() {
    const reportType = this.value;
    const startDateField = document.getElementById('start_date');
    const endDateField = document.getElementById('end_date');
    
    const today = new Date();
    
    switch (reportType) {
        case 'monthly':
            // Set to current month
            startDateField.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            endDateField.value = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'daily':
            // Set to current date
            startDateField.value = today.toISOString().split('T')[0];
            endDateField.value = today.toISOString().split('T')[0];
            break;
        default:
            // Keep current values
            break;
    }
});

// Form submission
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    showLoading();
    
    fetch('/reports/attendance-report', {
        method: 'POST',
        body: formData
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
                const format = formData.get('format');
                const filename = `attendance_report_${new Date().toISOString().split('T')[0]}.${format}`;
                
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