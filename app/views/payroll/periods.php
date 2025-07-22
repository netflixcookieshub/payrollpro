<?php 
$title = 'Payroll Periods - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Payroll Periods</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage payroll processing periods and financial years
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Create Period
            </button>
        </div>
    </div>

    <!-- Periods List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Period Name
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
                                               class="text-blue-600 hover:text-blue-900" title="Process Payroll">
                                                <i class="fas fa-play-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="/reports/salary-register?period=<?php echo $period['id']; ?>" 
                                           class="text-green-600 hover:text-green-900" title="View Reports">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        
                                        <?php if ($period['status'] !== 'closed'): ?>
                                            <button onclick="editPeriod(<?php echo $period['id']; ?>)" 
                                                    class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                        
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
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create Period
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Period Modal -->
<div id="period-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Create Payroll Period</h3>
            <form id="period-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="period_id" id="period_id">
                
                <div class="mb-4">
                    <label for="period_name" class="block text-sm font-medium text-gray-700 mb-2">Period Name *</label>
                    <input type="text" name="period_name" id="period_name" required
                           class="form-input" placeholder="e.g., January 2024">
                </div>
                
                <div class="mb-4">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                    <input type="date" name="start_date" id="start_date" required class="form-input">
                </div>
                
                <div class="mb-4">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                    <input type="date" name="end_date" id="end_date" required class="form-input">
                </div>
                
                <div class="mb-6">
                    <label for="financial_year" class="block text-sm font-medium text-gray-700 mb-2">Financial Year *</label>
                    <input type="text" name="financial_year" id="financial_year" required
                           class="form-input" placeholder="e.g., 2024-2025">
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closePeriodModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submit-text">Create Period</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Create Payroll Period';
    document.getElementById('submit-text').textContent = 'Create Period';
    document.getElementById('period-form').reset();
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('period_id').value = '';
    
    // Set default financial year
    const currentDate = new Date();
    const currentMonth = currentDate.getMonth() + 1;
    const currentYear = currentDate.getFullYear();
    
    let financialYear;
    if (currentMonth >= 4) {
        financialYear = currentYear + '-' + (currentYear + 1);
    } else {
        financialYear = (currentYear - 1) + '-' + currentYear;
    }
    
    document.getElementById('financial_year').value = financialYear;
    document.getElementById('period-modal').classList.remove('hidden');
}

function editPeriod(periodId) {
    // This would fetch period data and populate the form
    showMessage('Edit functionality coming soon', 'info');
}

function closePeriodModal() {
    document.getElementById('period-modal').classList.add('hidden');
}

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
                csrf_token: '<?php echo $csrf_token; ?>'
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

// Form submission
document.getElementById('period-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/payroll/periods', {
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
            closePeriodModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage(data.message || 'Failed to create period', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while creating the period', 'error');
    });
});

// Auto-generate period name based on dates
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    if (startDate) {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        const periodName = monthNames[startDate.getMonth()] + ' ' + startDate.getFullYear();
        document.getElementById('period_name').value = periodName;
        
        // Auto-set end date to last day of month
        const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0);
        document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>