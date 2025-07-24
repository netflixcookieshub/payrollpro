<?php 
$title = 'Mark Attendance - Payroll System';
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
                <h1 class="text-3xl font-bold text-gray-900">Mark Attendance</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Mark individual employee attendance
                </p>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Attendance Details</h3>
        </div>
        
        <div class="p-6">
            <form id="attendance-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
                        <select name="employee_id" id="employee_id" required class="form-select">
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo htmlspecialchars($employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="attendance_date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" name="attendance_date" id="attendance_date" required
                               value="<?php echo date('Y-m-d'); ?>" class="form-input">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" id="status" required class="form-select">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="half_day">Half Day</option>
                            <option value="late">Late</option>
                            <option value="early_out">Early Out</option>
                        </select>
                    </div>
                    
                    <div id="time-fields">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="check_in" class="block text-sm font-medium text-gray-700 mb-2">Check In</label>
                                <input type="time" name="check_in" id="check_in" class="form-input">
                            </div>
                            <div>
                                <label for="check_out" class="block text-sm font-medium text-gray-700 mb-2">Check Out</label>
                                <input type="time" name="check_out" id="check_out" class="form-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                        <textarea name="remarks" id="remarks" rows="3" class="form-textarea" 
                                  placeholder="Optional remarks or notes"></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end space-x-4">
                    <a href="/attendance" class="btn btn-outline">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Mark Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show/hide time fields based on status
document.getElementById('status').addEventListener('change', function() {
    const timeFields = document.getElementById('time-fields');
    const checkInField = document.getElementById('check_in');
    const checkOutField = document.getElementById('check_out');
    
    if (this.value === 'absent') {
        timeFields.style.display = 'none';
        checkInField.value = '';
        checkOutField.value = '';
    } else {
        timeFields.style.display = 'block';
        if (this.value === 'present' && !checkInField.value) {
            checkInField.value = '09:00';
            checkOutField.value = '18:00';
        }
    }
});

// Form submission
document.getElementById('attendance-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    showLoading();
    
    fetch('/attendance/mark', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => {
                window.location.href = '/attendance';
            }, 2000);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while marking attendance', 'error');
    });
});

// Set default time values
document.addEventListener('DOMContentLoaded', function() {
    const status = document.getElementById('status');
    status.dispatchEvent(new Event('change'));
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>