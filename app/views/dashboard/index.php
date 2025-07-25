<?php 
$title = 'Dashboard - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">
            Welcome back, <?php echo htmlspecialchars($user['name']); ?>! Here's an overview of your payroll system.
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Employees -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Employees</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['employees']['total']); ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i>
                        <?php echo number_format($stats['employees']['active']); ?> Active
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Period -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Current Period</p>
                        <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($stats['payroll']['current_period']); ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-user-check text-blue-500 mr-1"></i>
                        <?php echo number_format($stats['payroll']['processed_employees']); ?> Processed
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Earnings -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-emerald-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Earnings</p>
                        <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($stats['payroll']['total_earnings'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Payable -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hand-holding-usd text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Net Payable</p>
                        <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($stats['payroll']['net_payable'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Employee Distribution Chart -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                        Department-wise Employee Distribution
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach ($stats['employees']['departments'] as $dept): ?>
                            <?php 
                            $percentage = $stats['employees']['total'] > 0 ? ($dept['count'] / $stats['employees']['total']) * 100 : 0;
                            ?>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($dept['name']); ?></span>
                                    <span class="text-sm text-gray-500"><?php echo $dept['count']; ?> employees</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-6">
            <!-- Quick Actions Card -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="/employees/create" class="w-full flex items-center px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Add New Employee</span>
                        </a>
                        
                        <a href="/payroll/process" class="w-full flex items-center px-4 py-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-play-circle text-green-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Process Payroll</span>
                        </a>
                        
                        <a href="/attendance/mark" class="w-full flex items-center px-4 py-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar-check text-yellow-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Mark Attendance</span>
                        </a>
                        
                        <a href="/reports/salary-register" class="w-full flex items-center px-4 py-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-file-alt text-purple-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Generate Reports</span>
                        </a>
                        
                        <a href="/employees?status=active" class="w-full flex items-center px-4 py-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-search text-indigo-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">View Employees</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history text-gray-500 mr-2"></i>
                        Recent Activity
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php if (!empty($stats['recent_activities'])): ?>
                            <?php foreach (array_slice($stats['recent_activities'], 0, 5) as $activity): ?>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                    <div class="ml-3">
                                        <p class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($activity['full_name']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($activity['action']); ?>
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            <?php echo date('M j, H:i', strtotime($activity['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 italic">No recent activity</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-server text-green-500 mr-2"></i>
                System Status
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Database</p>
                        <p class="text-xs text-gray-500">Connected</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">File System</p>
                        <p class="text-xs text-gray-500">Accessible</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Application</p>
                        <p class="text-xs text-gray-500">Running v<?php echo APP_VERSION; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard auto-refresh every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);

// Animate progress bars on load
document.addEventListener('DOMContentLoaded', function() {
    const progressBars = document.querySelectorAll('.bg-blue-500');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>