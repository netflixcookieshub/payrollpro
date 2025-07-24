<?php
/**
 * API Controller for AJAX endpoints
 */

require_once __DIR__ . '/../core/Controller.php';

class ApiController extends Controller {
    
    public function getCurrentPeriod() {
        $this->checkAuth();
        
        $currentDate = date('Y-m-d');
        $period = $this->db->fetch(
            "SELECT * FROM payroll_periods 
             WHERE start_date <= :date AND end_date >= :date
             ORDER BY start_date DESC LIMIT 1",
            ['date' => $currentDate]
        );
        
        if ($period) {
            $this->jsonResponse(['period_id' => $period['id'], 'period_name' => $period['period_name']]);
        } else {
            $this->jsonResponse(['period_id' => null, 'message' => 'No active period found']);
        }
    }
    
    public function getEmployeesByDepartment() {
        $this->checkAuth();
        
        $departmentId = $_GET['department_id'] ?? '';
        
        if (empty($departmentId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Department ID required'], 400);
            return;
        }
        
        $employees = $this->db->fetchAll(
            "SELECT id, emp_code, CONCAT(first_name, ' ', last_name) as name 
             FROM employees 
             WHERE department_id = :dept_id AND status = 'active' 
             ORDER BY emp_code ASC",
            ['dept_id' => $departmentId]
        );
        
        $this->jsonResponse(['success' => true, 'employees' => $employees]);
    }
    
    public function getEmployeeSalaryStructure() {
        $this->checkAuth();
        
        $employeeId = $_GET['employee_id'] ?? '';
        
        if (empty($employeeId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Employee ID required'], 400);
            return;
        }
        
        $employeeModel = $this->loadModel('Employee');
        $salaryStructure = $employeeModel->getEmployeeSalaryStructure($employeeId);
        
        $this->jsonResponse(['success' => true, 'salary_structure' => $salaryStructure]);
    }
    
    public function validatePayrollPeriod() {
        $this->checkAuth();
        
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $excludeId = $_GET['exclude_id'] ?? '';
        
        if (empty($startDate) || empty($endDate)) {
            $this->jsonResponse(['success' => false, 'message' => 'Start and end dates required'], 400);
            return;
        }
        
        $conditions = "(start_date <= :end_date AND end_date >= :start_date)";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if (!empty($excludeId)) {
            $conditions .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $existing = $this->db->fetch(
            "SELECT id, period_name FROM payroll_periods WHERE {$conditions}",
            $params
        );
        
        if ($existing) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Period overlaps with existing period: ' . $existing['period_name']
            ]);
        } else {
            $this->jsonResponse(['success' => true, 'message' => 'Period dates are valid']);
        }
    }
    
    public function getPayrollSummary() {
        $this->checkAuth();
        
        $periodId = $_GET['period_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        
        if (empty($periodId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Period ID required'], 400);
            return;
        }
        
        $payrollModel = $this->loadModel('Payroll');
        $summary = $payrollModel->getPayrollSummary($periodId, $departmentId);
        
        $this->jsonResponse(['success' => true, 'summary' => $summary]);
    }
    
    public function searchEmployees() {
        $this->checkAuth();
        
        $query = $_GET['q'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $limit = min(50, intval($_GET['limit'] ?? 10));
        
        if (strlen($query) < 2) {
            $this->jsonResponse(['success' => true, 'employees' => []]);
            return;
        }
        
        $conditions = "(emp_code LIKE :query OR first_name LIKE :query OR last_name LIKE :query) AND status = 'active'";
        $params = ['query' => "%{$query}%"];
        
        if (!empty($departmentId)) {
            $conditions .= " AND department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT id, emp_code, CONCAT(first_name, ' ', last_name) as name, 
                       d.name as department_name
                FROM employees e
                JOIN departments d ON e.department_id = d.id
                WHERE {$conditions}
                ORDER BY emp_code ASC
                LIMIT {$limit}";
        
        $employees = $this->db->fetchAll($sql, $params);
        
        $this->jsonResponse(['success' => true, 'employees' => $employees]);
    }
    
    public function getAttendanceCalendar() {
        $this->checkAuth();
        
        $employeeId = $_GET['employee_id'] ?? '';
        $month = $_GET['month'] ?? date('Y-m');
        
        if (empty($employeeId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Employee ID required'], 400);
            return;
        }
        
        $attendanceModel = $this->loadModel('Attendance');
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $attendance = $attendanceModel->getEmployeeAttendance($employeeId, $startDate, $endDate);
        $summary = $attendanceModel->getAttendanceSummary($employeeId, $month);
        
        $this->jsonResponse([
            'success' => true, 
            'attendance' => $attendance,
            'summary' => $summary
        ]);
    }
    
    public function getLoanCalculation() {
        $this->checkAuth();
        
        $loanAmount = floatval($_GET['loan_amount'] ?? 0);
        $interestRate = floatval($_GET['interest_rate'] ?? 0);
        $tenureMonths = intval($_GET['tenure_months'] ?? 0);
        
        if ($loanAmount <= 0 || $tenureMonths <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters'], 400);
            return;
        }
        
        $loanModel = $this->loadModel('Loan');
        $emiAmount = $loanModel->calculateEMI($loanAmount, $interestRate, $tenureMonths);
        $totalAmount = $emiAmount * $tenureMonths;
        $totalInterest = $totalAmount - $loanAmount;
        
        $this->jsonResponse([
            'success' => true,
            'emi_amount' => round($emiAmount, 2),
            'total_amount' => round($totalAmount, 2),
            'total_interest' => round($totalInterest, 2)
        ]);
    }
    
    public function getSystemStats() {
        $this->checkAuth();
        
        $stats = [
            'employees' => [
                'total' => $this->db->fetch("SELECT COUNT(*) as count FROM employees WHERE status != 'deleted'")['count'],
                'active' => $this->db->fetch("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")['count'],
                'new_this_month' => $this->db->fetch("SELECT COUNT(*) as count FROM employees WHERE join_date >= :date", ['date' => date('Y-m-01')])['count']
            ],
            'payroll' => [
                'current_period' => $this->getCurrentPeriodName(),
                'processed_this_month' => $this->getProcessedEmployeesCount(),
                'total_payable' => $this->getTotalPayableAmount()
            ],
            'attendance' => [
                'present_today' => $this->getTodayAttendanceCount('present'),
                'absent_today' => $this->getTodayAttendanceCount('absent'),
                'late_today' => $this->getTodayAttendanceCount('late')
            ]
        ];
        
        $this->jsonResponse(['success' => true, 'stats' => $stats]);
    }
    
    private function getCurrentPeriodName() {
        $period = $this->db->fetch(
            "SELECT period_name FROM payroll_periods 
             WHERE start_date <= CURDATE() AND end_date >= CURDATE()
             LIMIT 1"
        );
        
        return $period ? $period['period_name'] : 'No active period';
    }
    
    private function getProcessedEmployeesCount() {
        $currentPeriod = $this->db->fetch(
            "SELECT id FROM payroll_periods 
             WHERE start_date <= CURDATE() AND end_date >= CURDATE()
             LIMIT 1"
        );
        
        if (!$currentPeriod) {
            return 0;
        }
        
        $result = $this->db->fetch(
            "SELECT COUNT(DISTINCT employee_id) as count 
             FROM payroll_transactions 
             WHERE period_id = :period_id",
            ['period_id' => $currentPeriod['id']]
        );
        
        return $result['count'] ?? 0;
    }
    
    private function getTotalPayableAmount() {
        $currentPeriod = $this->db->fetch(
            "SELECT id FROM payroll_periods 
             WHERE start_date <= CURDATE() AND end_date >= CURDATE()
             LIMIT 1"
        );
        
        if (!$currentPeriod) {
            return 0;
        }
        
        $result = $this->db->fetch(
            "SELECT SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE -pt.amount END) as total
             FROM payroll_transactions pt
             JOIN salary_components sc ON pt.component_id = sc.id
             WHERE pt.period_id = :period_id",
            ['period_id' => $currentPeriod['id']]
        );
        
        return $result['total'] ?? 0;
    }
    
    private function getTodayAttendanceCount($status) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM attendance 
             WHERE attendance_date = CURDATE() AND status = :status",
            ['status' => $status]
        );
        
        return $result['count'] ?? 0;
    }
}