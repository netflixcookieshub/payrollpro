<?php 
$title = 'Payslip - ' . $employee['first_name'] . ' ' . $employee['last_name'];
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Print Controls -->
    <div class="mb-6 no-print flex justify-between items-center">
        <div>
            <a href="/payroll" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Payroll
            </a>
        </div>
        <div class="space-x-2">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-print mr-2"></i>
                Print
            </button>
            <button onclick="downloadPDF()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-download mr-2"></i>
                Download PDF
            </button>
        </div>
    </div>

    <!-- Payslip -->
    <div class="payslip bg-white shadow-lg rounded-lg border">
        <!-- Header -->
        <div class="payslip-header bg-gray-50 px-8 py-6 border-b text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">PayrollPro</h1>
            <h2 class="text-xl font-semibold text-gray-700">Salary Slip</h2>
            <p class="text-sm text-gray-600 mt-2">
                For the period: <?php echo date('F Y', strtotime($payslip['start_date'])); ?>
            </p>
        </div>

        <!-- Employee Information -->
        <div class="payslip-section">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Employee Details</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Employee Code:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($employee['emp_code']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Department:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($employee['department_name']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Designation:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($employee['designation_name']); ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statutory Details</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">PAN Number:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($employee['pan_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">PF Number:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($employee['pf_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bank A/C:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($employee['bank_account_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bank Name:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($employee['bank_name'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Details -->
        <div class="payslip-section">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Earnings -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 text-green-700">Earnings</h3>
                    <div class="space-y-2">
                        <?php foreach ($earnings as $earning): ?>
                            <div class="payslip-row flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-700"><?php echo htmlspecialchars($earning['component_name']); ?></span>
                                <span class="font-medium text-green-600">₹<?php echo number_format($earning['amount'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="payslip-row payslip-total bg-green-50 px-3 py-2 rounded">
                            <div class="flex justify-between font-bold text-green-700">
                                <span>Total Earnings</span>
                                <span>₹<?php echo number_format($total_earnings, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deductions -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 text-red-700">Deductions</h3>
                    <div class="space-y-2">
                        <?php foreach ($deductions as $deduction): ?>
                            <div class="payslip-row flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-700"><?php echo htmlspecialchars($deduction['component_name']); ?></span>
                                <span class="font-medium text-red-600">₹<?php echo number_format(abs($deduction['amount']), 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="payslip-row payslip-total bg-red-50 px-3 py-2 rounded">
                            <div class="flex justify-between font-bold text-red-700">
                                <span>Total Deductions</span>
                                <span>₹<?php echo number_format($total_deductions, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Pay -->
        <div class="payslip-section border-t-2 border-gray-200">
            <div class="bg-blue-50 p-6 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-xl font-bold text-gray-900">Net Pay</span>
                    <span class="text-2xl font-bold text-blue-600">₹<?php echo number_format($net_pay, 2); ?></span>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    Net Pay in words: <?php echo $this->numberToWords($net_pay); ?> Rupees Only
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="payslip-section border-t bg-gray-50 text-center">
            <p class="text-xs text-gray-500">
                This is a computer-generated payslip and does not require a signature.
            </p>
            <p class="text-xs text-gray-500 mt-1">
                Generated on: <?php echo date('d-m-Y H:i:s'); ?>
            </p>
        </div>
    </div>
</div>

<script>
function downloadPDF() {
    // In a real implementation, you would call a PDF generation endpoint
    window.print();
}

// Add number to words conversion (simplified version)
<?php
function numberToWords($number) {
    $ones = array(
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
        14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
        18 => 'Eighteen', 19 => 'Nineteen'
    );
    
    $tens = array(
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    );
    
    if ($number < 20) {
        return $ones[$number];
    } elseif ($number < 100) {
        return $tens[intval($number / 10)] . ($number % 10 != 0 ? ' ' . $ones[$number % 10] : '');
    } elseif ($number < 1000) {
        return $ones[intval($number / 100)] . ' Hundred' . ($number % 100 != 0 ? ' ' . numberToWords($number % 100) : '');
    } elseif ($number < 100000) {
        return numberToWords(intval($number / 1000)) . ' Thousand' . ($number % 1000 != 0 ? ' ' . numberToWords($number % 1000) : '');
    } elseif ($number < 10000000) {
        return numberToWords(intval($number / 100000)) . ' Lakh' . ($number % 100000 != 0 ? ' ' . numberToWords($number % 100000) : '');
    } else {
        return numberToWords(intval($number / 10000000)) . ' Crore' . ($number % 10000000 != 0 ? ' ' . numberToWords($number % 10000000) : '');
    }
}
?>
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
        -webkit-print-color-adjust: exact;
    }
    
    .payslip {
        box-shadow: none !important;
        border: 1px solid #000 !important;
        margin: 0 !important;
    }
    
    .payslip-section {
        page-break-inside: avoid;
    }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>