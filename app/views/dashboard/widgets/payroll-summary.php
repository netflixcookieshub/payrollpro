<?php
// Payroll Summary Widget
$payrollModel = $this->loadModel('Payroll');
$currentPeriod = $this->getCurrentPeriod();

if ($currentPeriod) {
    $summary = $payrollModel->getPayrollSummary($currentPeriod['id']);
} else {
    $summary = [
        'total_employees' => 0,
        'total_earnings' => 0,
        'total_deductions' => 0,
        'net_payable' => 0
    ];
}

echo json_encode([
    'current_period' => $currentPeriod ? $currentPeriod['period_name'] : 'No active period',
    'summary' => $summary
]);
?>