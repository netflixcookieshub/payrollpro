<?php
/**
 * Payroll Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class PayrollController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $payrollModel = $this->loadModel('Payroll');
        
        // Get current and recent periods
        $periods = $this->db->fetchAll(
            "SELECT * FROM payroll_periods ORDER BY start_date DESC LIMIT 12"
        );
        
        // Get processing status for current period
        $currentPeriod = $this->getCurrentPeriod();
        $processingStats = [];
        
        if ($currentPeriod) {
            $processingStats = $payrollModel->getPayrollSummary($currentPeriod['id']);
        }
        
        $this->loadView('payroll/index', [
            'periods' => $periods,
            'current_period' => $currentPeriod,
            'processing_stats' => $processingStats
        ]);
    }
    
    public function periods() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreatePeriod();
        } else {
            $this->showPeriodsPage();
        }
    }
    
    public function process() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProcessPayroll();
        } else {
            $this->showProcessPage();
        }
    }
    
    public function payslip($employeeId, $periodId) {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $payrollModel = $this->loadModel('Payroll');
        $employeeModel = $this->loadModel('Employee');
        
        $employee = $employeeModel->getEmployeeWithDetails($employeeId);
        $payslip = $payrollModel->getEmployeePayslip($employeeId, $periodId);
        
        if (!$employee || empty($payslip)) {
            $this->loadView('errors/404');
            return;
        }
        
        // Group payslip data
        $earnings = [];
        $deductions = [];
        $totalEarnings = 0;
        $totalDeductions = 0;
        
        foreach ($payslip as $item) {
            if ($item['component_type'] === 'earning') {
                $earnings[] = $item;
                $totalEarnings += $item['amount'];
            } else {
                $deductions[] = $item;
                $totalDeductions += abs($item['amount']);
            }
        }
        
        $netPay = $totalEarnings - $totalDeductions;
        
        $this->loadView('payroll/payslip', [
            'employee' => $employee,
            'payslip' => $payslip[0], // For period info
            'earnings' => $earnings,
            'deductions' => $deductions,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay
        ]);
    }
    
    public function bulkProcess() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $periodId = $_POST['period_id'] ?? '';
            $departmentId = $_POST['department_id'] ?? '';
            $employeeIds = $_POST['employee_ids'] ?? [];
            
            if (empty($periodId)) {
                $this->jsonResponse(['success' => false, 'message' => 'Period is required'], 400);
                return;
            }
            
            $payrollModel = $this->loadModel('Payroll');
            
            // Get employees to process
            $conditions = [];
            $params = [];
            
            if (!empty($departmentId)) {
                $conditions[] = 'department_id = :dept_id';
                $params['dept_id'] = $departmentId;
            }
            
            if (!empty($employeeIds)) {
                $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
                $conditions[] = "id IN ($placeholders)";
                $params = array_merge($params, $employeeIds);
            }
            
            $whereClause = !empty($conditions) ? implode(' AND ', $conditions) : '';
            
            $result = $payrollModel->processPayroll($periodId, $employeeIds);
            
            if ($result['success']) {
                $this->logActivity('bulk_payroll_process', 'payroll_transactions', $periodId);
                $this->jsonResponse([
                    'success' => true, 
                    'message' => "Payroll processed for {$result['processed']} employees"
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']], 500);
            }
        }
    }
    
    public function lockPeriod() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $periodId = $_POST['period_id'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            if (empty($periodId)) {
                $this->jsonResponse(['success' => false, 'message' => 'Period ID is required'], 400);
                return;
            }
            
            try {
                $this->db->update('payroll_periods', 
                    ['status' => 'locked'], 
                    'id = :id', 
                    ['id' => $periodId]
                );
                
                $this->logActivity('lock_payroll_period', 'payroll_periods', $periodId);
                $this->jsonResponse(['success' => true, 'message' => 'Payroll period locked successfully']);
            } catch (Exception $e) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to lock period'], 500);
            }
        }
    }
    
    public function exportPayslips() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $periodId = $_GET['period_id'] ?? '';
        $departmentId = $_GET['department_id'] ?? '';
        $format = $_GET['format'] ?? 'pdf';
        
        if (empty($periodId)) {
            $this->redirect('/payroll?error=period_required');
            return;
        }
        
        $payrollModel = $this->loadModel('Payroll');
        
        // Get employees for the period
        $conditions = "pt.period_id = :period_id";
        $params = ['period_id' => $periodId];
        
        if (!empty($departmentId)) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT DISTINCT e.id, e.first_name, e.last_name, e.emp_code
                FROM employees e
                JOIN payroll_transactions pt ON e.id = pt.employee_id
                WHERE {$conditions}
                ORDER BY e.emp_code";
        
        $employees = $this->db->fetchAll($sql, $params);
        
        if ($format === 'zip') {
            $this->exportPayslipsAsZip($employees, $periodId);
        } else {
            $this->exportPayslipsAsPDF($employees, $periodId);
        }
    }
    
    private function handleCreatePeriod() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $rules = [
            'period_name' => ['required' => true, 'max_length' => 50],
            'start_date' => ['required' => true, 'type' => 'date'],
            'end_date' => ['required' => true, 'type' => 'date'],
            'financial_year' => ['required' => true, 'max_length' => 9]
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        // Check for overlapping periods
        $existing = $this->db->fetch(
            "SELECT id FROM payroll_periods 
             WHERE (start_date <= :end_date AND end_date >= :start_date)",
            ['start_date' => $data['start_date'], 'end_date' => $data['end_date']]
        );
        
        if ($existing) {
            $this->jsonResponse(['success' => false, 'message' => 'Period overlaps with existing period'], 400);
            return;
        }
        
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->db->insert('payroll_periods', $data);
            
            $this->logActivity('create_payroll_period', 'payroll_periods', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Payroll period created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create period'], 500);
        }
    }
    
    private function handleProcessPayroll() {
        $periodId = $_POST['period_id'] ?? '';
        $employeeIds = $_POST['employee_ids'] ?? [];
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        if (empty($periodId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Period is required'], 400);
            return;
        }
        
        $payrollModel = $this->loadModel('Payroll');
        $result = $payrollModel->processPayroll($periodId, $employeeIds);
        
        if ($result['success']) {
            $this->logActivity('process_payroll', 'payroll_transactions', $periodId);
            $this->jsonResponse([
                'success' => true, 
                'message' => "Payroll processed for {$result['processed']} employees"
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 500);
        }
    }
    
    private function showPeriodsPage() {
        $periods = $this->db->fetchAll(
            "SELECT * FROM payroll_periods ORDER BY start_date DESC"
        );
        
        $this->loadView('payroll/periods', [
            'periods' => $periods,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showProcessPage() {
        $periods = $this->db->fetchAll(
            "SELECT * FROM payroll_periods WHERE status IN ('open', 'processing') ORDER BY start_date DESC"
        );
        
        $departments = $this->db->fetchAll(
            "SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC"
        );
        
        $employees = $this->db->fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name, emp_code, department_id 
             FROM employees WHERE status = 'active' ORDER BY emp_code ASC"
        );
        
        $this->loadView('payroll/process', [
            'periods' => $periods,
            'departments' => $departments,
            'employees' => $employees,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function getCurrentPeriod() {
        $currentDate = date('Y-m-d');
        return $this->db->fetch(
            "SELECT * FROM payroll_periods 
             WHERE start_date <= :date AND end_date >= :date
             ORDER BY start_date DESC LIMIT 1",
            ['date' => $currentDate]
        );
    }
    
    private function exportPayslipsAsZip($employees, $periodId) {
        // Implementation for ZIP export of multiple payslips
        // This would generate individual PDF payslips and zip them
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="payslips_period_' . $periodId . '.zip"');
        
        // For now, redirect back with success message
        $this->redirect('/payroll?success=export_initiated');
    }
    
    private function exportPayslipsAsPDF($employees, $periodId) {
        // Implementation for bulk PDF export
        // This would generate a single PDF with all payslips
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="payslips_period_' . $periodId . '.pdf"');
        
        // For now, redirect back with success message
        $this->redirect('/payroll?success=export_initiated');
    }
}