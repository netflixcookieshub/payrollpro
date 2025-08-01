<?php 
$title = 'Process Payroll - Payroll Management System';
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
                <h1 class="text-3xl font-bold text-gray-900">Process Payroll</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Calculate and process employee salaries for the selected period
                </p>
            </div>
        </div>
    </div>

    <!-- Processing Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Payroll Processing Options</h3>
        </div>
        <div class="p-6">
            <form id="payroll-process-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="period_id" class="block text-sm font-medium text-gray-700 mb-2">Payroll Period *</label>
                        <select name="period_id" id="period_id" required class="form-select">
                            <option value="">Select Period</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo $period['id']; ?>" 
                                        <?php echo (isset($_GET['period']) && $_GET['period'] == $period['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($period['period_name']); ?> 
                                    (<?php echo date('M j', strtotime($period['start_date'])); ?> - <?php echo date('M j, Y', strtotime($period['end_date'])); ?>)
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
                        <label for="processing_mode" class="block text-sm font-medium text-gray-700 mb-2">Processing Mode</label>
                        <select name="processing_mode" id="processing_mode" class="form-select">
                            <option value="all">Process All Employees</option>
                            <option value="selected">Process Selected Employees</option>
                            <option value="new">Process New Employees Only</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="include_arrears" class="form-checkbox">
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
                            <input type="checkbox" name="include_variable_pay" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Include Variable Pay</span>
                        </label>
                    </div>
                    
                    <button type="button" onclick="startProcessing()" class="btn btn-primary">
                        <i class="fas fa-play-circle mr-2"></i>
                        Start Processing
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Advanced Processing Options -->
    <div id="advanced-options" class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6 hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Advanced Processing Options</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Calculation Options</h4>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="apply_pro_rata" class="form-checkbox" checked>
                            <span class="ml-2 text-sm text-gray-700">Apply Pro-rata for New/Leaving Employees</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="calculate_lop" class="form-checkbox" checked>
                            <span class="ml-2 text-sm text-gray-700">Calculate Loss of Pay (LOP)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="use_formulas" class="form-checkbox" checked>
                            <span class="ml-2 text-sm text-gray-700">Use Component Formulas</span>
                        </label>
                    </div>
                </div>
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Processing Mode</h4>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="processing_type" value="fresh" class="form-radio" checked>
                            <span class="ml-2 text-sm text-gray-700">Fresh Processing</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="processing_type" value="reprocess" class="form-radio">
                            <span class="ml-2 text-sm text-gray-700">Reprocess (Delete & Recalculate)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="processing_type" value="supplement" class="form-radio">
                            <span class="ml-2 text-sm text-gray-700">Supplementary Processing</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Selection (shown when "selected" mode is chosen) -->
    <div id="employee-selection" class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6 hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Select Employees</h3>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="selectAllEmployees()" class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                    <span class="text-gray-300">|</span>
                    <button type="button" onclick="deselectAllEmployees()" class="text-sm text-blue-600 hover:text-blue-800">Deselect All</button>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                <?php foreach ($employees as $employee): ?>
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                        <input type="checkbox" name="employee_ids[]" value="<?php echo $employee['id']; ?>" 
                               class="form-checkbox employee-checkbox">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($employee['name']); ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?php echo htmlspecialchars($employee['emp_code']); ?>
                            </p>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Include Arrears:</span>
                            <span id="summary-arrears" class="font-medium">No</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Calculate TDS:</span>
                            <span id="summary-tds" class="font-medium">Yes</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Process Loans:</span>
                            <span id="summary-loans" class="font-medium">Yes</span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Processing Status -->
    <div id="processing-status" class="bg-white shadow-sm rounded-lg border border-gray-200 hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Processing Status</h3>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progress</span>
                    <span id="progress-text" class="text-sm text-gray-500">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <div id="processing-log" class="bg-gray-50 rounded-lg p-4 h-64 overflow-y-auto">
                <p class="text-sm text-gray-500">Processing log will appear here...</p>
            </div>
            
            <div class="mt-4 flex items-center justify-between">
                <div id="processing-summary" class="text-sm text-gray-600">
                    Ready to process payroll
                </div>
                <button id="cancel-processing" onclick="cancelProcessing()" class="btn btn-outline btn-sm hidden">
                    <i class="fas fa-stop mr-2"></i>
                    Cancel Processing
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let processingInterval;
let isProcessing = false;

// Show/hide employee selection based on processing mode
document.getElementById('processing_mode').addEventListener('change', function() {
    const employeeSelection = document.getElementById('employee-selection');
    if (this.value === 'selected') {
        employeeSelection.classList.remove('hidden');
    } else {
        employeeSelection.classList.add('hidden');
    }
});

// Filter employees by department
document.getElementById('department_filter').addEventListener('change', function() {
    const departmentId = this.value;
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    
    checkboxes.forEach(checkbox => {
        const label = checkbox.closest('label');
        const employeeId = checkbox.value;
        
        // In a real implementation, you would filter based on employee department
        // For now, we'll show all employees
        label.style.display = 'flex';
    });
});

function selectAllEmployees() {
    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllEmployees() {
    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function startProcessing() {
    const form = document.getElementById('payroll-process-form');
    const formData = new FormData(form);
    
    // Get all form data including checkboxes
    const data = {
        period_id: formData.get('period_id'),
        department_filter: formData.get('department_filter'),
        processing_mode: formData.get('processing_mode'),
        include_arrears: formData.has('include_arrears'),
        calculate_tds: formData.has('calculate_tds'),
        process_loans: formData.has('process_loans'),
        include_variable_pay: formData.has('include_variable_pay'),
        csrf_token: formData.get('csrf_token')
    };
    
    // Validate form
    if (!data.period_id) {
        showMessage('Please select a payroll period', 'error');
        return;
    }
    
    if (data.processing_mode === 'selected') {
        const selectedEmployees = document.querySelectorAll('.employee-checkbox:checked');
        if (selectedEmployees.length === 0) {
            showMessage('Please select at least one employee', 'error');
            return;
        }
        
        // Add selected employee IDs to form data
        data.employee_ids = [];
        selectedEmployees.forEach(checkbox => {
            data.employee_ids.push(checkbox.value);
        });
    }
    
    const confirmMessage = data.processing_mode === 'reprocess' ? 
        'Are you sure you want to reprocess payroll? This will delete existing data and recalculate.' :
        'Are you sure you want to start payroll processing?';
        
    if (confirm(confirmMessage)) {
        isProcessing = true;
        showProcessingStatus();
        
        // Start processing
        fetch('/payroll/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateProcessingLog(`✓ Payroll processing completed successfully`);
                updateProcessingLog(`✓ Processed ${data.processed || 0} out of ${data.total || 0} employees`);
                
                if (data.errors && data.errors.length > 0) {
                    updateProcessingLog(`⚠ ${data.errors.length} errors occurred:`);
                    data.errors.forEach(error => {
                        updateProcessingLog(`  • ${error}`);
                    });
                }
                
                updateProgress(100);
                updateProcessingSummary(`Processing completed: ${data.processed || 0}/${data.total || 0} employees processed`);
                showMessage('Payroll processing completed successfully', 'success');
            } else {
                updateProcessingLog('✗ Payroll processing failed: ' + data.message);
                showMessage('Payroll processing failed: ' + data.message, 'error');
            }
            isProcessing = false;
            document.getElementById('cancel-processing').classList.add('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            updateProcessingLog('✗ An error occurred during processing');
            showMessage('An error occurred during payroll processing', 'error');
            isProcessing = false;
            document.getElementById('cancel-processing').classList.add('hidden');
        });
        
        // Simulate processing progress
        simulateProgress();
    }
}

function showProcessingStatus() {
    document.getElementById('processing-status').classList.remove('hidden');
    document.getElementById('cancel-processing').classList.remove('hidden');
    
    // Clear previous log
    const log = document.getElementById('processing-log');
    log.innerHTML = '<p class="text-sm text-gray-500">Starting payroll processing...</p>';
    
    updateProgress(0);
    updateProcessingSummary('Processing started...');
}

function updateProgress(percentage) {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    progressBar.style.width = percentage + '%';
    progressText.textContent = Math.round(percentage) + '%';
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

function updateProcessingSummary(message) {
    document.getElementById('processing-summary').textContent = message;
}

function simulateProgress() {
    let progress = 0;
    const steps = [
        { progress: 5, message: 'Initializing payroll processing...' },
        { progress: 15, message: 'Validating employee data and salary structures...' },
        { progress: 25, message: 'Calculating attendance and LOP...' },
        { progress: 35, message: 'Processing basic salary components...' },
        { progress: 45, message: 'Evaluating component formulas...' },
        { progress: 55, message: 'Applying pro-rata calculations...' },
        { progress: 65, message: 'Calculating statutory deductions (PF, ESI, PT)...' },
        { progress: 75, message: 'Computing TDS with tax slabs...' },
        { progress: 85, message: 'Processing loan EMI deductions...' },
        { progress: 90, message: 'Processing arrears and variable pay...' },
        { progress: 95, message: 'Generating payroll transactions...' },
        { progress: 100, message: 'Finalizing payroll calculations...' }
    ];
    
    let stepIndex = 0;
    
    processingInterval = setInterval(() => {
        if (!isProcessing || stepIndex >= steps.length) {
            clearInterval(processingInterval);
            return;
        }
        
        const step = steps[stepIndex];
        updateProgress(step.progress);
        updateProcessingLog('• ' + step.message);
        updateProcessingSummary(step.message);
        
        stepIndex++;
    }, 800);
}

// Update processing summary when options change
document.addEventListener('change', function(e) {
    if (e.target.matches('input[name="processing_mode"]')) {
        document.getElementById('summary-mode').textContent = 
            e.target.value === 'all' ? 'All Employees' :
            e.target.value === 'selected' ? 'Selected Employees' : 'New Employees Only';
    }
    
    if (e.target.matches('input[name="include_arrears"]')) {
        document.getElementById('summary-arrears').textContent = e.target.checked ? 'Yes' : 'No';
    }
    
    if (e.target.matches('input[name="calculate_tds"]')) {
        document.getElementById('summary-tds').textContent = e.target.checked ? 'Yes' : 'No';
    }
    
    if (e.target.matches('input[name="process_loans"]')) {
        document.getElementById('summary-loans').textContent = e.target.checked ? 'Yes' : 'No';
    }
});

// Show advanced options toggle
function toggleAdvancedOptions() {
    const advancedOptions = document.getElementById('advanced-options');
    advancedOptions.classList.toggle('hidden');
}

function cancelProcessing() {
    if (confirm('Are you sure you want to cancel payroll processing?')) {
        isProcessing = false;
        clearInterval(processingInterval);
        
        updateProcessingLog('✗ Processing cancelled by user');
        updateProcessingSummary('Processing cancelled');
        document.getElementById('cancel-processing').classList.add('hidden');
        
        showMessage('Payroll processing cancelled', 'warning');
    }
}

// Auto-select period if provided in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const periodParam = urlParams.get('period');
    
    if (periodParam) {
        document.getElementById('period_id').value = periodParam;
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>