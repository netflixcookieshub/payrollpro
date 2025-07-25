<?php 
$title = 'Reports - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="mt-1 text-sm text-gray-500">
            Generate comprehensive payroll reports and analytics
        </p>
    </div>

    <!-- Report Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Payroll Reports -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-blue-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Payroll Reports</h3>
            </div>
            <div class="space-y-3">
                <a href="/reports/salary-register" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Salary Register</h4>
                            <p class="text-xs text-gray-500">Complete salary details by period</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/reports/component-report" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Component Report</h4>
                            <p class="text-xs text-gray-500">Component-wise breakdown</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/reports/bank-transfer" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Bank Transfer</h4>
                            <p class="text-xs text-gray-500">Bank-ready transfer files</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tax & Compliance Reports -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-receipt text-green-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Tax & Compliance</h3>
            </div>
            <div class="space-y-3">
                <a href="/reports/tax-report?type=tds" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">TDS Report</h4>
                            <p class="text-xs text-gray-500">Income tax deduction summary</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/reports/tax-report?type=pf" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">PF Report</h4>
                            <p class="text-xs text-gray-500">Provident fund contributions</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/reports/tax-report?type=esi" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">ESI Report</h4>
                            <p class="text-xs text-gray-500">Employee state insurance</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Employee Reports -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Employee Reports</h3>
            </div>
            <div class="space-y-3">
                <a href="/reports/loan-report" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Loan Report</h4>
                            <p class="text-xs text-gray-500">Outstanding loans and EMIs</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/reports/attendance-report" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Attendance Report</h4>
                            <p class="text-xs text-gray-500">Employee attendance summary</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/loans" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Loan Management</h4>
                            <p class="text-xs text-gray-500">Employee loans and EMIs</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/employees/export" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Employee Master</h4>
                            <p class="text-xs text-gray-500">Complete employee database</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Custom Reports -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tools text-orange-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Custom Reports</h3>
            </div>
            <div class="space-y-3">
                <a href="/reports/custom-builder" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Report Builder</h4>
                            <p class="text-xs text-gray-500">Create custom reports</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/reports/saved-reports" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Saved Reports</h4>
                            <p class="text-xs text-gray-500">Previously saved report templates</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Analytics -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-indigo-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Analytics</h3>
            </div>
            <div class="space-y-3">
                <a href="/reports/salary-trends" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Salary Trends</h4>
                            <p class="text-xs text-gray-500">Monthly salary trend analysis</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
                
                <a href="/reports/cost-analysis" class="block p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Cost Analysis</h4>
                            <p class="text-xs text-gray-500">Department-wise cost breakdown</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bolt text-red-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Quick Actions</h3>
            </div>
            <div class="space-y-3">
                <button onclick="generateCurrentMonthReport()" class="w-full text-left p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Current Month Report</h4>
                            <p class="text-xs text-gray-500">Generate current period salary register</p>
                        </div>
                        <i class="fas fa-play text-gray-400"></i>
                    </div>
                </button>
                
                <button onclick="exportAllPayslips()" class="w-full text-left p-3 rounded-md hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Export All Payslips</h4>
                            <p class="text-xs text-gray-500">Download all payslips as ZIP</p>
                        </div>
                        <i class="fas fa-download text-gray-400"></i>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-history text-gray-500 mr-2"></i>
                Recent Report Activity
            </h3>
        </div>
        <div class="p-6">
            <div class="text-center text-gray-500">
                <i class="fas fa-file-alt text-4xl mb-4"></i>
                <p class="text-lg font-medium">No recent reports</p>
                <p class="text-sm">Generate your first report to see activity here</p>
            </div>
        </div>
    </div>
</div>

<script>
function generateCurrentMonthReport() {
    showLoading();
    
    // Get current period and redirect to salary register
    fetch('/api/current-period')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.period_id) {
                window.location.href = `/reports/salary-register?period=${data.period_id}&auto=1`;
            } else {
                showMessage('No active payroll period found', 'warning');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Failed to get current period', 'error');
        });
}

function exportAllPayslips() {
    showLoading();
    
    // This would trigger a bulk payslip export
    fetch('/payroll/export-payslips?format=zip')
        .then(response => {
            hideLoading();
            if (response.ok) {
                showMessage('Payslip export initiated. Download will start shortly.', 'success');
                // In a real implementation, this would trigger a file download
            } else {
                showMessage('Failed to export payslips', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Failed to export payslips', 'error');
        });
}

// Add hover effects and animations
document.addEventListener('DOMContentLoaded', function() {
    const reportCards = document.querySelectorAll('.bg-white.rounded-lg');
    
    reportCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>