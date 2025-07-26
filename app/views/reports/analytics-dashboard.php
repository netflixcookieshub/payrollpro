<?php 
$title = 'Analytics Dashboard - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">
            Advanced payroll analytics and business intelligence
        </p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Payroll Cost</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($analytics['total_cost'] ?? 0, 0); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Employees</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($analytics['active_employees'] ?? 0); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calculator text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Avg Cost/Employee</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($analytics['avg_cost_per_employee'] ?? 0, 0); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Attendance Rate</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($analytics['attendance_analytics']['avg_attendance'] ?? 0, 1); ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Salary Trends Chart -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Salary Trends (Last 12 Months)</h3>
            </div>
            <div class="p-6">
                <canvas id="salaryTrendsChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Department Cost Distribution -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Department Cost Distribution</h3>
            </div>
            <div class="p-6">
                <canvas id="departmentCostChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cost Per Employee Trends -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Cost Per Employee</h3>
            </div>
            <div class="p-6">
                <canvas id="costPerEmployeeChart" width="300" height="200"></canvas>
            </div>
        </div>

        <!-- Top Departments by Cost -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Departments by Cost</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach (array_slice($analytics['department_costs'] ?? [], 0, 5) as $dept): ?>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dept['department']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo $dept['employee_count']; ?> employees</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">₹<?php echo number_format($dept['total_cost'], 0); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <button onclick="exportAnalytics('summary')" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-download mr-2"></i>
                        Export Summary Report
                    </button>
                    <button onclick="exportAnalytics('detailed')" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-file-excel mr-2"></i>
                        Export Detailed Analytics
                    </button>
                    <button onclick="generatePredictiveReport()" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-crystal-ball mr-2"></i>
                        Predictive Analytics
                    </button>
                    <button onclick="scheduledReports()" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-clock mr-2"></i>
                        Schedule Reports
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Predictive Analytics Section -->
    <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Predictive Analytics</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700">Predicted Next Month Cost</h4>
                    <p class="text-2xl font-bold text-blue-600">₹<?php echo number_format($analytics['predictions']['next_month_cost'] ?? 0, 0); ?></p>
                    <p class="text-xs text-gray-500">Based on 6-month average</p>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700">Attrition Rate</h4>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($analytics['predictions']['attrition_rate'] ?? 0, 1); ?>%</p>
                    <p class="text-xs text-gray-500">Annual projection</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700">Overtime Projection</h4>
                    <p class="text-2xl font-bold text-green-600">₹<?php echo number_format($analytics['predictions']['next_month_overtime'] ?? 0, 0); ?></p>
                    <p class="text-xs text-gray-500">Next month estimate</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Salary Trends Chart
const salaryTrendsCtx = document.getElementById('salaryTrendsChart').getContext('2d');
const salaryTrendsChart = new Chart(salaryTrendsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($analytics['salary_trends'] ?? [], 'period_name')); ?>,
        datasets: [{
            label: 'Total Earnings',
            data: <?php echo json_encode(array_column($analytics['salary_trends'] ?? [], 'total_earnings')); ?>,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.1
        }, {
            label: 'Total Deductions',
            data: <?php echo json_encode(array_column($analytics['salary_trends'] ?? [], 'total_deductions')); ?>,
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString('en-IN');
                    }
                }
            }
        }
    }
});

// Department Cost Chart
const departmentCostCtx = document.getElementById('departmentCostChart').getContext('2d');
const departmentCostChart = new Chart(departmentCostCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($analytics['department_costs'] ?? [], 'department')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($analytics['department_costs'] ?? [], 'total_cost')); ?>,
            backgroundColor: [
                'rgb(59, 130, 246)',
                'rgb(16, 185, 129)',
                'rgb(245, 158, 11)',
                'rgb(239, 68, 68)',
                'rgb(139, 92, 246)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Cost Per Employee Chart
const costPerEmployeeCtx = document.getElementById('costPerEmployeeChart').getContext('2d');
const costPerEmployeeChart = new Chart(costPerEmployeeCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($analytics['cost_per_employee'] ?? [], 'period_name')); ?>,
        datasets: [{
            label: 'Cost Per Employee',
            data: <?php echo json_encode(array_column($analytics['cost_per_employee'] ?? [], 'avg_cost_per_employee')); ?>,
            backgroundColor: 'rgba(139, 92, 246, 0.8)',
            borderColor: 'rgb(139, 92, 246)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString('en-IN');
                    }
                }
            }
        }
    }
});

function exportAnalytics(type) {
    window.location.href = `/reports/export-analytics?type=${type}&format=excel`;
}

function generatePredictiveReport() {
    window.location.href = '/reports/predictive-analytics';
}

function scheduledReports() {
    showMessage('Scheduled reports feature coming soon', 'info');
}

// Auto-refresh charts every 5 minutes
setInterval(function() {
    // Refresh chart data
    fetch('/reports/analytics-data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update charts with new data
                updateCharts(data.analytics);
            }
        })
        .catch(error => console.error('Error refreshing analytics:', error));
}, 300000);

function updateCharts(analytics) {
    // Update salary trends chart
    salaryTrendsChart.data.labels = analytics.salary_trends.map(item => item.period_name);
    salaryTrendsChart.data.datasets[0].data = analytics.salary_trends.map(item => item.total_earnings);
    salaryTrendsChart.data.datasets[1].data = analytics.salary_trends.map(item => item.total_deductions);
    salaryTrendsChart.update();
    
    // Update department cost chart
    departmentCostChart.data.labels = analytics.department_costs.map(item => item.department);
    departmentCostChart.data.datasets[0].data = analytics.department_costs.map(item => item.total_cost);
    departmentCostChart.update();
    
    // Update cost per employee chart
    costPerEmployeeChart.data.labels = analytics.cost_per_employee.map(item => item.period_name);
    costPerEmployeeChart.data.datasets[0].data = analytics.cost_per_employee.map(item => item.avg_cost_per_employee);
    costPerEmployeeChart.update();
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>