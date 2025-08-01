<?php 
$title = 'Attendance Management - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Attendance Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Track and manage employee attendance records
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <a href="/attendance/mark" class="btn btn-outline">
                <i class="fas fa-user-check mr-2"></i>
                Mark Attendance
            </a>
            <a href="/attendance/report" class="btn btn-primary">
                <i class="fas fa-chart-bar mr-2"></i>
                Generate Report
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                    <input type="date" name="date" id="date" value="<?php echo $selected_date; ?>" 
                           class="form-input">
                </div>
                
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select name="department" id="department" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo $selected_department == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full btn btn-primary">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    Attendance for <?php echo date('M j, Y', strtotime($selected_date)); ?>
                </h3>
                <button onclick="bulkMarkAttendance()" class="btn btn-outline btn-sm">
                    <i class="fas fa-save mr-2"></i>
                    Save All Changes
                </button>
            </div>
        </div>
        
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Check In
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Check Out
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($attendance)): ?>
                        <?php foreach ($attendance as $record): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500 ml-2">
                                            (<?php echo htmlspecialchars($record['emp_code']); ?>)
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($record['department_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $record['check_in'] ? date('H:i', strtotime($record['check_in'])) : '-'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $record['check_out'] ? date('H:i', strtotime($record['check_out'])) : '-'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="attendance[<?php echo $record['employee_id']; ?>]" 
                                            class="attendance-status form-select text-sm">
                                        <option value="present" <?php echo ($record['status'] ?? '') === 'present' ? 'selected' : ''; ?>>Present</option>
                                        <option value="absent" <?php echo ($record['status'] ?? '') === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                        <option value="half_day" <?php echo ($record['status'] ?? '') === 'half_day' ? 'selected' : ''; ?>>Half Day</option>
                                        <option value="late" <?php echo ($record['status'] ?? '') === 'late' ? 'selected' : ''; ?>>Late</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="markIndividualAttendance(<?php echo $record['employee_id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900" title="Save">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-calendar-times text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No employees found</p>
                                    <p class="text-sm">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function bulkMarkAttendance() {
    const attendanceData = {};
    const selects = document.querySelectorAll('.attendance-status');
    
    selects.forEach(select => {
        const employeeId = select.name.match(/\[(\d+)\]/)[1];
        attendanceData[employeeId] = select.value;
    });
    
    showLoading();
    
    fetch('/attendance/bulk-mark', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            attendance: attendanceData,
            date: '<?php echo $selected_date; ?>',
            csrf_token: '<?php echo $this->generateCSRFToken(); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage(data.message, 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving attendance', 'error');
    });
}

function markIndividualAttendance(employeeId) {
    const select = document.querySelector(`select[name="attendance[${employeeId}]"]`);
    const status = select.value;
    
    fetch('/attendance/mark', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            employee_id: employeeId,
            attendance_date: '<?php echo $selected_date; ?>',
            status: status,
            csrf_token: '<?php echo $this->generateCSRFToken(); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Attendance updated successfully', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while updating attendance', 'error');
    });
}

// Auto-submit form when date changes
document.getElementById('date').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('department').addEventListener('change', function() {
    this.form.submit();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>