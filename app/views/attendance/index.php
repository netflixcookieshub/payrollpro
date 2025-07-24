<?php 
$title = 'Attendance Management - Payroll System';
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
            <a href="/attendance/bulk-mark" class="btn btn-outline">
                <i class="fas fa-users mr-2"></i>
                Bulk Mark
            </a>
            <a href="/attendance/import" class="btn btn-primary">
                <i class="fas fa-upload mr-2"></i>
                Import Attendance
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                    <input type="month" name="month" id="month" value="<?php echo $selected_month; ?>" 
                           class="form-input">
                </div>
                
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select name="department" id="department" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" 
                                    <?php echo $selected_department == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 btn btn-primary">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    <a href="/attendance/export?month=<?php echo $selected_month; ?>&department=<?php echo $selected_department; ?>&format=excel" 
                       class="btn btn-outline">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Calendar View -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                Attendance for <?php echo date('F Y', strtotime($selected_month . '-01')); ?>
            </h3>
        </div>
        
        <div class="p-6">
            <?php if (!empty($employees)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">
                                    Employee
                                </th>
                                <?php
                                $daysInMonth = date('t', strtotime($selected_month . '-01'));
                                for ($day = 1; $day <= $daysInMonth; $day++):
                                    $date = $selected_month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                    $dayOfWeek = date('w', strtotime($date));
                                    $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
                                ?>
                                    <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider <?php echo $isWeekend ? 'bg-red-50' : ''; ?>">
                                        <?php echo $day; ?>
                                        <br>
                                        <span class="text-xs"><?php echo substr(date('D', strtotime($date)), 0, 1); ?></span>
                                    </th>
                                <?php endfor; ?>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Summary
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($employees as $employee): ?>
                                <?php
                                // Get attendance data for this employee
                                $employeeAttendance = [];
                                foreach ($attendance_data as $record) {
                                    if ($record['employee_id'] == $employee['id']) {
                                        $employeeAttendance[$record['attendance_date']] = $record;
                                    }
                                }
                                
                                // Calculate summary
                                $presentDays = 0;
                                $absentDays = 0;
                                $lateDays = 0;
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap sticky left-0 bg-white">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($employee['emp_code']); ?>
                                        </div>
                                    </td>
                                    
                                    <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                                        <?php
                                        $date = $selected_month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                        $dayOfWeek = date('w', strtotime($date));
                                        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
                                        $attendance = $employeeAttendance[$date] ?? null;
                                        
                                        if ($attendance) {
                                            switch ($attendance['status']) {
                                                case 'present':
                                                    $presentDays++;
                                                    $bgColor = 'bg-green-100';
                                                    $textColor = 'text-green-800';
                                                    $symbol = 'P';
                                                    break;
                                                case 'absent':
                                                    $absentDays++;
                                                    $bgColor = 'bg-red-100';
                                                    $textColor = 'text-red-800';
                                                    $symbol = 'A';
                                                    break;
                                                case 'half_day':
                                                    $bgColor = 'bg-yellow-100';
                                                    $textColor = 'text-yellow-800';
                                                    $symbol = 'H';
                                                    break;
                                                case 'late':
                                                    $lateDays++;
                                                    $bgColor = 'bg-orange-100';
                                                    $textColor = 'text-orange-800';
                                                    $symbol = 'L';
                                                    break;
                                                default:
                                                    $bgColor = 'bg-gray-100';
                                                    $textColor = 'text-gray-800';
                                                    $symbol = '-';
                                            }
                                        } else {
                                            $bgColor = $isWeekend ? 'bg-gray-200' : 'bg-white';
                                            $textColor = 'text-gray-400';
                                            $symbol = $isWeekend ? 'W' : '-';
                                        }
                                        ?>
                                        <td class="px-2 py-4 text-center <?php echo $isWeekend ? 'bg-red-50' : ''; ?>">
                                            <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-medium rounded-full <?php echo $bgColor . ' ' . $textColor; ?>">
                                                <?php echo $symbol; ?>
                                            </span>
                                        </td>
                                    <?php endfor; ?>
                                    
                                    <td class="px-4 py-4 text-center">
                                        <div class="text-xs space-y-1">
                                            <div class="text-green-600">P: <?php echo $presentDays; ?></div>
                                            <div class="text-red-600">A: <?php echo $absentDays; ?></div>
                                            <div class="text-orange-600">L: <?php echo $lateDays; ?></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Legend -->
                <div class="mt-6 flex items-center justify-center space-x-6 text-sm">
                    <div class="flex items-center">
                        <span class="w-4 h-4 bg-green-100 rounded mr-2"></span>
                        <span>Present (P)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 bg-red-100 rounded mr-2"></span>
                        <span>Absent (A)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 bg-yellow-100 rounded mr-2"></span>
                        <span>Half Day (H)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 bg-orange-100 rounded mr-2"></span>
                        <span>Late (L)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 bg-gray-200 rounded mr-2"></span>
                        <span>Weekend (W)</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                    <p class="text-lg font-medium text-gray-900">No employees found</p>
                    <p class="text-sm text-gray-500">Add employees to start tracking attendance</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-submit form when month or department changes
document.getElementById('month').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('department').addEventListener('change', function() {
    this.form.submit();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>