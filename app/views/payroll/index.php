<?php 
$title = 'Payroll Management - Payroll System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Payroll Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Process payroll, manage periods, and generate payslips
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <a href="/payroll/periods" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-calendar-alt mr-2"></i>
                Manage Periods
            </a>
            <a href="/payroll/process" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-play-circle mr-2"></i>
                Process Payroll
            </a>
        </div>
    </div>

    <!-- Current Period Status -->
    <?php if ($current_period): ?>
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-calendar-check text-green-500 mr-2"></i>
                        Current Period: <?php echo htmlspecialchars($current_period['period_name']); ?>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <?php echo date('M j, Y', strtotime($current_period['start_date'])); ?> - 
                        <?php echo date('M j, Y', strtotime($current_period['end_date'])); ?>
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">
                            <?php echo number_format($processing_stats['total_employees'] ?? 0); ?>
                        </div>
                        <div class="text-xs text-gray-500">Employees</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">
                            ₹<?php echo number_format($processing_stats['net_payable'] ?? 0, 2); ?>
                        </div>
                        <div class="text-xs text-gray-500">Net Payable</div>
                    </div>
                    <div class="text-center">
                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                            <?php 
                            switch($current_period['status']) {
                                case 'open': echo 'bg-blue-100 text-blue-800'; break;
                                case 'processing': echo 'bg-yellow-100 text-yellow-800'; break;
                                case 'locked': echo 'bg-green-100 text-green-800'; break;
                                case 'closed': echo 'bg-gray-100 text-gray-800'; break;
                            }
                            ?>">
                            <?php echo ucfirst($current_period['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-play-circle text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Process Payroll</h3>
                    <p class="text-sm text-gray-500">Calculate and process employee salaries</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="/payroll/process" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Start Processing →
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Generate Payslips</h3>
                    <p class="text-sm text-gray-500">Create and download payslips</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="/payroll/payslips" class="text-green-600 hover:text-green-800 text-sm font-medium">
                    Generate →
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-bar text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Reports</h3>
                    <p class="text-sm text-gray-500">Salary registers and tax reports</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="/reports" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                    View Reports →
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-university text-orange-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Bank Transfer</h3>
                    <p class="text-sm text-gray-500">Generate bank transfer files</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="/reports/bank-transfer" class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                    Generate →
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Periods -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-history text-gray-500 mr-2"></i>
                Recent Payroll Periods
            </h3>
        </div>
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Period
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Duration
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Financial Year
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
                    <?php if (!empty($periods)): ?>
                        <?php foreach ($periods as $period): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($period['period_name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M j', strtotime($period['start_date'])); ?> - 
                                    <?php echo date('M j, Y', strtotime($period['end_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($period['financial_year']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($period['status']) {
                                            case 'open': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'processing': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'locked': echo 'bg-green-100 text-green-800'; break;
                                            case 'closed': echo 'bg-gray-100 text-gray-800'; break;
                                        }
                                        ?>">
                                        <?php echo ucfirst($period['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($period['status'] === 'open'): ?>
                                            <a href="/payroll/process?period=<?php echo $period['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900" title="Process">
                                                <i class="fas fa-play-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="/reports/salary-register?period=<?php echo $period['id']; ?>" 
                                           class="text-green-600 hover:text-green-900" title="Reports">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        
                                        <a href="/payroll/payslips?period=<?php echo $period['id']; ?>" 
                                           class="text-purple-600 hover:text-purple-900" title="Payslips">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        
                                        <?php if ($period['status'] === 'processing'): ?>
                                            <button onclick="lockPeriod(<?php echo $period['id']; ?>)" 
                                                    class="text-orange-600 hover:text-orange-900" title="Lock Period">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-calendar-times text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No payroll periods found</p>
                                    <p class="text-sm">Create your first payroll period to get started</p>
                                    <a href="/payroll/periods" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create Period
                                    </a>
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
function lockPeriod(periodId) {
    if (confirm('Are you sure you want to lock this payroll period? This action cannot be undone.')) {
        showLoading();
        
        fetch('/payroll/lock-period', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                period_id: periodId,
                csrf_token: '<?php echo $this->generateCSRFToken(); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('An error occurred while locking the period', 'error');
        });
    }
}

// Success/error messages from URL
<?php if (isset($_GET['success'])): ?>
    showMessage('<?php echo $_GET['success'] === 'export_initiated' ? 'Export has been initiated' : 'Operation completed successfully'; ?>', 'success');
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    showMessage('<?php echo $_GET['error'] === 'period_required' ? 'Please select a period' : 'An error occurred'; ?>', 'error');
<?php endif; ?>
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>