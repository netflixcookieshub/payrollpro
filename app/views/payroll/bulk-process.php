<?php 
$title = 'Bulk Payroll Processing - Payroll System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/payroll" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Bulk Payroll Processing</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Process payroll for multiple employees simultaneously
                </p>
            </div>
        </div>
    </div>

    <!-- Processing Configuration -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Processing Configuration</h3>
        </div>
        <div class="p-6">
            <form id="bulk-process-form">
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
                        <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-2">Department Filter</label>
                        <select name="department_filter" id="department_filter" class="form-select">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="batch_size" class="block text-sm font-medium text-gray-700 mb-2">Batch Size</label>
                        <select name="batch_size" id="batch_size" class="form-select">
                            <option value="10">10 employees</option>
                            <option value="25" selected>25 employees</option>
                            <option value="50">50 employees</option>
                            <option value="100">100 employees</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Processing Options</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="include_arrears" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Include Arrears</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="calculate_tds" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Calculate TDS</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="process_loans" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Process Loan EMIs</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="apply_lop" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Apply Loss of Pay</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Validation Rules</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="validate_salary_structure" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Validate Salary Structure</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="validate_attendance" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Validate Attendance Data</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="skip_processed" class="form-checkbox" checked>
                                <span class="ml-2 text-sm text-gray-700">Skip Already Processed</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <span id="employee-count">0</span> employees will be processed
                    </div>
                    <button type="button" onclick="startBulkProcessing()" class="btn btn-primary">
                        <i class="fas fa-play-circle mr-2"></i>
                        Start Bulk Processing
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Processing Status -->
    <div id="processing-status" class="bg-white shadow-sm rounded-lg border border-gray-200 hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Processing Status</h3>
                <button id="cancel-processing" onclick="cancelProcessing()" class="btn btn-outline btn-sm hidden">
                    <i class="fas fa-stop mr-2"></i>
                    Cancel
                </button>
            </div>
        </div>
        <div class="p-6">
            <!-- Overall Progress -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                    <span id="overall-progress-text" class="text-sm text-gray-500">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="overall-progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Batch Progress -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Current Batch</span>
                    <span id="batch-progress-text" class="text-sm text-gray-500">0/0</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="batch-progress-bar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600" id="processed-count">0</div>
                    <div class="text-xs text-gray-500">Processed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600" id="success-count">0</div>
                    <div class="text-xs text-gray-500">Success</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600" id="error-count">0</div>
                    <div class="text-xs text-gray-500">Errors</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600" id="skipped-count">0</div>
                    <div class="text-xs text-gray-500">Skipped</div>
                </div>
            </div>
            
            <!-- Processing Log -->
            <div>
                <h4 class="text-md font-semibold text-gray-900 mb-3">Processing Log</h4>
                <div id="processing-log" class="bg-gray-50 rounded-lg p-4 h-64 overflow-y-auto">
                    <p class="text-sm text-gray-500">Processing log will appear here...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let processingInterval;
let isProcessing = false;
let processedCount = 0;
let successCount = 0;
let errorCount = 0;
let skippedCount = 0;

// Update employee count when filters change
document.getElementById('period_id').addEventListener('change', updateEmployeeCount);
document.getElementById('department_filter').addEventListener('change', updateEmployeeCount);

function updateEmployeeCount() {
    const periodId = document.getElementById('period_id').value;
    const departmentId = document.getElementById('department_filter').value;
    
    if (periodId) {
        // In a real implementation, you would fetch the actual count
        // For now, we'll use a placeholder
        document.getElementById('employee-count').textContent = '25';
    } else {
        document.getElementById('employee-count').textContent = '0';
    }
}

function startBulkProcessing() {
    const form = document.getElementById('bulk-process-form');
    const formData = new FormData(form);
    
    const periodId = formData.get('period_id');
    if (!periodId) {
        showMessage('Please select a payroll period', 'error');
        return;
    }
    
    if (confirm('Are you sure you want to start bulk payroll processing? This action cannot be undone.')) {
        isProcessing = true;
        showProcessingStatus();
        
        // Reset counters
        processedCount = 0;
        successCount = 0;
        errorCount = 0;
        skippedCount = 0;
        
        // Start processing
        processBatch(formData);
    }
}

function processBatch(formData) {
    fetch('/payroll/bulk-process', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateProcessingStats(data);
            updateProcessingLog('✓ Batch completed successfully');
            
            if (data.has_more) {
                // Process next batch
                setTimeout(() => processBatch(formData), 1000);
            } else {
                // Processing complete
                completeProcessing();
            }
        } else {
            updateProcessingLog('✗ Batch processing failed: ' + data.message);
            showMessage('Bulk processing failed: ' + data.message, 'error');
            isProcessing = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        updateProcessingLog('✗ An error occurred during processing');
        showMessage('An error occurred during bulk processing', 'error');
        isProcessing = false;
    });
}

function showProcessingStatus() {
    document.getElementById('processing-status').classList.remove('hidden');
    document.getElementById('cancel-processing').classList.remove('hidden');
    
    // Clear previous log
    const log = document.getElementById('processing-log');
    log.innerHTML = '<p class="text-sm text-gray-500">Starting bulk payroll processing...</p>';
    
    updateOverallProgress(0);
    updateBatchProgress(0, 0);
}

function updateProcessingStats(data) {
    processedCount += data.processed || 0;
    successCount += data.success_count || 0;
    errorCount += data.error_count || 0;
    skippedCount += data.skipped_count || 0;
    
    document.getElementById('processed-count').textContent = processedCount;
    document.getElementById('success-count').textContent = successCount;
    document.getElementById('error-count').textContent = errorCount;
    document.getElementById('skipped-count').textContent = skippedCount;
    
    // Update progress
    const totalEmployees = data.total_employees || 100;
    const overallProgress = (processedCount / totalEmployees) * 100;
    updateOverallProgress(overallProgress);
    
    const batchProgress = data.batch_progress || 0;
    updateBatchProgress(data.batch_current || 0, data.batch_total || 0);
}

function updateOverallProgress(percentage) {
    const progressBar = document.getElementById('overall-progress-bar');
    const progressText = document.getElementById('overall-progress-text');
    
    progressBar.style.width = percentage + '%';
    progressText.textContent = Math.round(percentage) + '%';
}

function updateBatchProgress(current, total) {
    const progressBar = document.getElementById('batch-progress-bar');
    const progressText = document.getElementById('batch-progress-text');
    
    const percentage = total > 0 ? (current / total) * 100 : 0;
    progressBar.style.width = percentage + '%';
    progressText.textContent = `${current}/${total}`;
}

function updateProcessingLog(message) {
    const log = document.getElementById('processing-log');
    const timestamp = new Date().toLocaleTimeString();
    
    const logEntry = document.createElement('div');
    logEntry.className = 'text-sm text-gray-700 mb-1';
    logEntry.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${message}`;
    
    log.appendChild(logEntry);
    log.scrollTop = log.scrollHeight;
}

function completeProcessing() {
    isProcessing = false;
    document.getElementById('cancel-processing').classList.add('hidden');
    
    updateProcessingLog('✓ Bulk payroll processing completed successfully');
    updateOverallProgress(100);
    
    showMessage(`Bulk processing completed: ${successCount} successful, ${errorCount} errors, ${skippedCount} skipped`, 'success');
}

function cancelProcessing() {
    if (confirm('Are you sure you want to cancel bulk processing?')) {
        isProcessing = false;
        
        updateProcessingLog('✗ Processing cancelled by user');
        document.getElementById('cancel-processing').classList.add('hidden');
        
        showMessage('Bulk processing cancelled', 'warning');
    }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>