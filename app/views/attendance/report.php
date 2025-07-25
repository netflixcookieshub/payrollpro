<?php 
$title = 'Attendance Report - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/attendance" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Attendance Report</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate comprehensive attendance reports
                </p>
            </div>
        </div>
    </div>

    <!-- Report Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Report Parameters</h3>
        </div>
        <div class="p-6">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" required 
                               value="<?php echo date('Y-m-01'); ?>" class="form-input">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                        <input type="date" name="end_date" id="end_date" required 
                               value="<?php echo date('Y-m-d'); ?>" class="form-input">
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
                        </select>
                    </div>
                </div>
                
                <div class="border-t pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Report Options</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_summary" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Include Summary Statistics</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_details" class="form-checkbox">
                                <span class="ml-2 text-gray-700">Include Daily Details</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download mr-2"></i>
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>