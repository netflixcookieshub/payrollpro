<?php 
$title = 'Tax Report - Reports';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/reports" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php 
                    switch($type) {
                        case 'tds': echo 'TDS Report'; break;
                        case 'pf': echo 'PF Report'; break;
                        case 'esi': echo 'ESI Report'; break;
                        default: echo 'Tax Report';
                    }
                    ?>
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate statutory compliance reports
                </p>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Report Parameters</h3>
        </div>
        <div class="p-6">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="period_id" class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                        <select name="period_id" id="period_id" class="form-select">
                            <option value="">Select Period</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo $period['id']; ?>">
                                    <?php echo htmlspecialchars($period['period_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="financial_year" class="block text-sm font-medium text-gray-700 mb-2">Financial Year</label>
                        <select name="financial_year" id="financial_year" class="form-select">
                            <option value="">Select Financial Year</option>
                            <?php foreach ($financial_years as $fy): ?>
                                <option value="<?php echo $fy['financial_year']; ?>">
                                    <?php echo htmlspecialchars($fy['financial_year']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Description -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Report Information</h3>
        </div>
        <div class="p-6">
            <?php switch($type): case 'tds': ?>
                <div class="text-sm text-gray-600">
                    <h4 class="font-semibold mb-2">TDS (Tax Deducted at Source) Report</h4>
                    <p class="mb-4">This report provides details of income tax deducted from employee salaries.</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Employee-wise TDS deduction summary</li>
                        <li>PAN number and tax details</li>
                        <li>Monthly and cumulative TDS amounts</li>
                        <li>Form 16 preparation data</li>
                    </ul>
                </div>
            <?php break; case 'pf': ?>
                <div class="text-sm text-gray-600">
                    <h4 class="font-semibold mb-2">PF (Provident Fund) Report</h4>
                    <p class="mb-4">This report contains employee and employer PF contribution details.</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Employee PF contributions (12%)</li>
                        <li>Employer PF contributions (12%)</li>
                        <li>UAN and PF account numbers</li>
                        <li>ECR file generation data</li>
                    </ul>
                </div>
            <?php break; case 'esi': ?>
                <div class="text-sm text-gray-600">
                    <h4 class="font-semibold mb-2">ESI (Employee State Insurance) Report</h4>
                    <p class="mb-4">This report shows ESI contributions for eligible employees.</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Employee ESI contributions (0.75%)</li>
                        <li>Employer ESI contributions (3.25%)</li>
                        <li>ESI numbers and eligibility</li>
                        <li>Monthly contribution summary</li>
                    </ul>
                </div>
            <?php endswitch; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>