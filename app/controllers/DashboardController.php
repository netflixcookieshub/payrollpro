<?php
/**
 * Dashboard Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class DashboardController extends Controller {
    
    public function index() {
        $this->checkAuth();
        
        $employeeModel = $this->loadModel('Employee');
        $payrollModel = $this->loadModel('Payroll');
        
        // Get dashboard statistics
        $stats = [
            'employees' => $employeeModel->getEmployeeStats(),
            'payroll' => $this->getPayrollStats($payrollModel),
            'recent_activities' => $this->getRecentActivities()
        ];
        
        $this->loadView('dashboard/index', [
            'stats' => $stats,
            'user' => [
                'name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ]
        ]);
    }
    
    private function getPayrollStats($payrollModel) {
        $currentMonth = date('Y-m-01');
        $currentPeriod = $this->db->fetch(
            "SELECT * FROM payroll_periods WHERE start_date <= :date AND end_date >= :date",
            ['date' => $currentMonth]
        );
        
        $stats = [
            'current_period' => $currentPeriod ? $currentPeriod['period_name'] : 'No active period',
            'processed_employees' => 0,
            'total_earnings' => 0,
            'total_deductions' => 0,
            'net_payable' => 0
        ];
        
        if ($currentPeriod) {
            $summary = $payrollModel->getPayrollSummary($currentPeriod['id']);
            if ($summary) {
                $stats['processed_employees'] = $summary['total_employees'];
                $stats['total_earnings'] = $summary['total_earnings'];
                $stats['total_deductions'] = $summary['total_deductions'];
                $stats['net_payable'] = $summary['net_payable'];
            }
        }
        
        return $stats;
    }
    
    private function getRecentActivities() {
        $sql = "SELECT al.*, u.full_name 
                FROM audit_logs al
                JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT 10";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getWidgetData() {
        $this->checkAuth();
        
        $widget = $_GET['widget'] ?? '';
        
        switch ($widget) {
            case 'employee_distribution':
                echo json_encode($this->getEmployeeDistribution());
                break;
            case 'salary_trends':
                echo json_encode($this->getSalaryTrends());
                break;
            case 'attendance_overview':
                echo json_encode($this->getAttendanceOverview());
                break;
            default:
                $this->jsonResponse(['error' => 'Invalid widget'], 400);
        }
    }
    
    private function getEmployeeDistribution() {
        $sql = "SELECT d.name, COUNT(e.id) as count 
                FROM departments d 
                LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
                GROUP BY d.id, d.name
                ORDER BY count DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getSalaryTrends() {
        $sql = "SELECT pp.period_name, 
                       SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as earnings,
                       SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as deductions
                FROM payroll_periods pp
                LEFT JOIN payroll_transactions pt ON pp.id = pt.period_id
                LEFT JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pp.start_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY pp.id, pp.period_name
                ORDER BY pp.start_date ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getAttendanceOverview() {
        $currentMonth = date('Y-m-01');
        $nextMonth = date('Y-m-01', strtotime('+1 month'));
        
        $sql = "SELECT 
                    COUNT(DISTINCT employee_id) as total_employees,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day
                FROM attendance 
                WHERE attendance_date >= :start_date AND attendance_date < :end_date";
        
        return $this->db->fetch($sql, [
            'start_date' => $currentMonth,
            'end_date' => $nextMonth
        ]);
    }
}