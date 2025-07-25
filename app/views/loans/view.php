<?php 
$title = 'Loan Details - ' . $loan['first_name'] . ' ' . $loan['last_name'];
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/loans" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Loan Details</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Loan information for <?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Loan Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Details -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Loan Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Employee</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?>
                                <span class="text-gray-500">(<?php echo htmlspecialchars($loan['emp_code']); ?>)</span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Department</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($loan['department_name']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Loan Type</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($loan['loan_type_name']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Loan Amount</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900">₹<?php echo number_format($loan['loan_amount'], 2); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Interest Rate</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $loan['interest_rate']; ?>% per annum</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tenure</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $loan['tenure_months']; ?> months</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Monthly EMI</label>
                            <p class="mt-1 text-lg font-semibold text-blue-600">₹<?php echo number_format($loan['emi_amount'], 2); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Outstanding Amount</label>
                            <p class="mt-1 text-lg font-semibold text-red-600">₹<?php echo number_format($loan['outstanding_amount'], 2); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Disbursement Date</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo date('M j, Y', strtotime($loan['disbursed_date'])); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">First EMI Date</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo date('M j, Y', strtotime($loan['first_emi_date'])); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                switch($loan['status']) {
                                    case 'active': echo 'bg-green-100 text-green-800'; break;
                                    case 'closed': echo 'bg-gray-100 text-gray-800'; break;
                                    case 'defaulted': echo 'bg-red-100 text-red-800'; break;
                                }
                                ?>">
                                <?php echo ucfirst($loan['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Summary -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Loan Summary</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">
                                ₹<?php echo number_format($loan['loan_amount'], 2); ?>
                            </div>
                            <div class="text-sm text-gray-500">Principal Amount</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-600">
                                ₹<?php echo number_format(($loan['emi_amount'] * $loan['tenure_months']) - $loan['loan_amount'], 2); ?>
                            </div>
                            <div class="text-sm text-gray-500">Total Interest</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">
                                ₹<?php echo number_format($loan['emi_amount'] * $loan['tenure_months'], 2); ?>
                            </div>
                            <div class="text-sm text-gray-500">Total Payable</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <?php if ($loan['status'] === 'active'): ?>
                        <a href="/loans/<?php echo $loan['id']; ?>/payment" class="w-full btn btn-primary btn-sm">
                            <i class="fas fa-money-bill mr-2"></i>
                            Record Payment
                        </a>
                    <?php endif; ?>
                    
                    <button onclick="generateLoanStatement()" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-file-alt mr-2"></i>
                        Generate Statement
                    </button>
                    
                    <button onclick="printLoanDetails()" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-print mr-2"></i>
                        Print Details
                    </button>
                </div>
            </div>

            <!-- Progress -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Repayment Progress</h3>
                </div>
                <div class="p-6">
                    <?php 
                    $paidAmount = $loan['loan_amount'] - $loan['outstanding_amount'];
                    $progressPercentage = ($paidAmount / $loan['loan_amount']) * 100;
                    ?>
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Paid</span>
                            <span><?php echo number_format($progressPercentage, 1); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $progressPercentage; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount Paid:</span>
                            <span class="font-medium">₹<?php echo number_format($paidAmount, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Outstanding:</span>
                            <span class="font-medium text-red-600">₹<?php echo number_format($loan['outstanding_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMI Schedule -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">EMI Schedule</h3>
                </div>
                <div class="p-6">
                    <div class="text-center mb-4">
                        <div class="text-2xl font-bold text-blue-600">
                            ₹<?php echo number_format($loan['emi_amount'], 2); ?>
                        </div>
                        <div class="text-sm text-gray-500">Monthly EMI</div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Next EMI Date:</span>
                            <span class="font-medium"><?php echo date('M j, Y', strtotime($loan['first_emi_date'] . ' +1 month')); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Remaining EMIs:</span>
                            <span class="font-medium"><?php echo ceil($loan['outstanding_amount'] / $loan['emi_amount']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateLoanStatement() {
    showMessage('Feature coming soon', 'info');
}

function printLoanDetails() {
    window.print();
}
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>