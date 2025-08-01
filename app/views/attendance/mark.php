<?php 
$title = 'Mark Attendance - Payroll Management System';
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
                    Mark attendance for employees individually
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Mark Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Quick Mark</h3>
        </div>
        <div class="p-6">
            <form id="quick-mark-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-check mr-2"></i>
                            Mark Attendance
                        </button>
                    </div>
                </div>
                
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="check_in" class="block text-sm font-medium text-gray-700 mb-2">Check In Time</label>
                        <input type="time" name="check_in" id="check_in" class="form-input">
                    </div>
                    
                    <div>
                        <label for="check_out" class="block text-sm font-medium text-gray-700 mb-2">Check Out Time</label>
                        <input type="time" name="check_out" id="check_out" class="form-input">
                    </div>
                </div>
                
                <div class="mt-4">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="2" class="form-textarea" 
                              placeholder="Optional remarks about attendance"></textarea>
                </div>
            </form>
        </div>
    </div>

    <!-- Today's Attendance Summary -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Today's Attendance Summary</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600" id="present-count">0</div>
                    <div class="text-sm text-gray-500">Present</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600" id="absent-count">0</div>
                    <div class="text-sm text-gray-500">Absent</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600" id="late-count">0</div>
                    <div class="text-sm text-gray-500">Late</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600" id="half-day-count">0</div>
                    <div class="text-sm text-gray-500">Half Day</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('quick-mark-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/attendance/mark', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage(data.message, 'success');
            // Reset form
            document.getElementById('employee_id').value = '';
            document.getElementById('check_in').value = '';
            document.getElementById('check_out').value = '';
            document.getElementById('remarks').value = '';
            
            // Update summary
            loadTodaysSummary();
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

// Auto-calculate total hours when check in/out times change
document.getElementById('check_in').addEventListener('change', calculateTotalHours);
document.getElementById('check_out').addEventListener('change', calculateTotalHours);

function calculateTotalHours() {
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    
    if (checkIn && checkOut) {
        const inTime = new Date('2000-01-01 ' + checkIn);
        const outTime = new Date('2000-01-01 ' + checkOut);
        
        if (outTime > inTime) {
            const diffMs = outTime - inTime;
            const diffHours = diffMs / (1000 * 60 * 60);
            
            // You could display this somewhere or use it for validation
            console.log('Total hours:', diffHours.toFixed(2));
        }
    }
}

function loadTodaysSummary() {
    const today = new Date().toISOString().split('T')[0];
    
    fetch(`/api/attendance-summary?date=${today}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('present-count').textContent = data.summary.present || 0;
                document.getElementById('absent-count').textContent = data.summary.absent || 0;
                document.getElementById('late-count').textContent = data.summary.late || 0;
                document.getElementById('half-day-count').textContent = data.summary.half_day || 0;
            }
        })
        .catch(error => {
            console.error('Error loading summary:', error);
        });
}

// Load summary on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTodaysSummary();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>