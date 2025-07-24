<?php 
// Dashboard widgets for AJAX loading
$widgetType = $_GET['widget'] ?? '';

switch ($widgetType) {
    case 'payroll_summary':
        include __DIR__ . '/widgets/payroll-summary.php';
        break;
    case 'attendance_overview':
        include __DIR__ . '/widgets/attendance-overview.php';
        break;
    case 'employee_stats':
        include __DIR__ . '/widgets/employee-stats.php';
        break;
    case 'recent_activities':
        include __DIR__ . '/widgets/recent-activities.php';
        break;
    default:
        echo json_encode(['error' => 'Invalid widget type']);
}
?>