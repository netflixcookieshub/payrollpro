<?php
/**
 * Report Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class ReportController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $this->loadView('reports/index');
    }
    
    public function salaryRegister() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateSalaryRegister();
        } else {
            $this->showSalaryRegisterForm();
        }
    }
    
    public function componentReport() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateComponentReport();
        } else {
            $this->showComponentReportForm();
        }
    }
    
    public function bankTransfer() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateBankTransferFile();
        } else {
            $this->showBankTransferForm();
        }
    }
    
    public function taxReport() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $reportType = $_GET['type'] ?? 'tds';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateTaxReport($reportType);
        } else {
            $this->showTaxReportForm($reportType);
        }
    }
    
    public function loanReport() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateLoanReport();
        } else {
            $this->showLoanReportForm();
        }
    }
    
    public function customBuilder() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCustomReport();
        } else {
            $this->showCustomBuilder();
        }
    }
    
    public function attendanceReport() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateAttendanceReport();
        } else {
            $this->showAttendanceReportForm();
        }
    }
    
    private function generateSalaryRegister() {
        $periodId = $_POST['period_id'] ?? '';
        $departmentId = $_POST['department_id'] ?? '';
        $format = $_POST['format'] ?? 'excel';
        
        if (empty($periodId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Period is required'], 400);
            return;
        }
        
        // Build query
        $conditions = "pt.period_id = :period_id";
        $params = ['period_id' => $periodId];
        
        if (!empty($departmentId)) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    e.emp_code, e.first_name, e.last_name,
                    d.name as department_name,
                    des.name as designation_name,
                    pp.period_name,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as total_earnings,
                    SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as total_deductions,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE -pt.amount END) as net_pay
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN departments d ON e.department_id = d.id
                JOIN designations des ON e.designation_id = des.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE {$conditions}
                GROUP BY pt.employee_id
                ORDER BY e.emp_code";
        
        $data = $this->db->fetchAll($sql, $params);
        
        if ($format === 'excel') {
            $this->exportToExcel($data, 'salary_register_' . date('Y-m-d') . '.xlsx', 'Salary Register');
        } else {
            $this->exportToCSV($data, 'salary_register_' . date('Y-m-d') . '.csv');
        }
    }
    
    private function generateComponentReport() {
        $periodId = $_POST['period_id'] ?? '';
        $componentId = $_POST['component_id'] ?? '';
        $format = $_POST['format'] ?? 'excel';
        
        $conditions = "pt.period_id = :period_id";
        $params = ['period_id' => $periodId];
        
        if (!empty($componentId)) {
            $conditions .= " AND pt.component_id = :comp_id";
            $params['comp_id'] = $componentId;
        }
        
        $sql = "SELECT 
                    e.emp_code, e.first_name, e.last_name,
                    sc.name as component_name, sc.type as component_type,
                    pt.amount, pt.calculated_amount,
                    pp.period_name
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE {$conditions}
                ORDER BY e.emp_code, sc.display_order";
        
        $data = $this->db->fetchAll($sql, $params);
        
        if ($format === 'excel') {
            $this->exportToExcel($data, 'component_report_' . date('Y-m-d') . '.xlsx', 'Component Report');
        } else {
            $this->exportToCSV($data, 'component_report_' . date('Y-m-d') . '.csv');
        }
    }
    
    private function generateBankTransferFile() {
        $periodId = $_POST['period_id'] ?? '';
        $bankFormat = $_POST['bank_format'] ?? 'generic';
        
        if (empty($periodId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Period is required'], 400);
            return;
        }
        
        $sql = "SELECT 
                    e.emp_code, e.first_name, e.last_name,
                    e.bank_account_number, e.bank_name, e.bank_ifsc,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE -pt.amount END) as net_pay
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pt.period_id = :period_id
                GROUP BY pt.employee_id
                HAVING net_pay > 0
                ORDER BY e.emp_code";
        
        $data = $this->db->fetchAll($sql, ['period_id' => $periodId]);
        
        switch ($bankFormat) {
            case 'sbi':
                $this->generateSBIFormat($data, $periodId);
                break;
            case 'hdfc':
                $this->generateHDFCFormat($data, $periodId);
                break;
            case 'icici':
                $this->generateICICIFormat($data, $periodId);
                break;
            default:
                $this->generateGenericBankFormat($data, $periodId);
        }
    }
    
    private function generateTaxReport($type) {
        $periodId = $_POST['period_id'] ?? '';
        $financialYear = $_POST['financial_year'] ?? '';
        
        switch ($type) {
            case 'tds':
                $this->generateTDSReport($periodId, $financialYear);
                break;
            case 'pf':
                $this->generatePFReport($periodId, $financialYear);
                break;
            case 'esi':
                $this->generateESIReport($periodId, $financialYear);
                break;
        }
    }
    
    private function generateLoanReport() {
        $asOfDate = $_POST['as_of_date'] ?? date('Y-m-d');
        $loanTypeId = $_POST['loan_type_id'] ?? '';
        $format = $_POST['format'] ?? 'excel';
        
        $conditions = "el.disbursed_date <= :as_of_date";
        $params = ['as_of_date' => $asOfDate];
        
        if (!empty($loanTypeId)) {
            $conditions .= " AND el.loan_type_id = :loan_type_id";
            $params['loan_type_id'] = $loanTypeId;
        }
        
        $sql = "SELECT 
                    e.emp_code, e.first_name, e.last_name,
                    lt.name as loan_type,
                    el.loan_amount, el.emi_amount,
                    el.outstanding_amount, el.disbursed_date,
                    el.status
                FROM employee_loans el
                JOIN employees e ON el.employee_id = e.id
                JOIN loan_types lt ON el.loan_type_id = lt.id
                WHERE {$conditions}
                ORDER BY e.emp_code, el.disbursed_date";
        
        $data = $this->db->fetchAll($sql, $params);
        
        if ($format === 'excel') {
            $this->exportToExcel($data, 'loan_report_' . date('Y-m-d') . '.xlsx', 'Loan Report');
        } else {
            $this->exportToCSV($data, 'loan_report_' . date('Y-m-d') . '.csv');
        }
    }
    
    private function handleCustomReport() {
        $tables = $_POST['tables'] ?? [];
        $fields = $_POST['fields'] ?? [];
        $conditions = $_POST['conditions'] ?? '';
        $orderBy = $_POST['order_by'] ?? '';
        $format = $_POST['format'] ?? 'excel';
        
        if (empty($tables) || empty($fields)) {
            $this->jsonResponse(['success' => false, 'message' => 'Tables and fields are required'], 400);
            return;
        }
        
        // Build dynamic query (with security checks)
        $allowedTables = ['employees', 'payroll_transactions', 'salary_components', 'departments', 'designations'];
        $safeTables = array_intersect($tables, $allowedTables);
        
        if (empty($safeTables)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid tables selected'], 400);
            return;
        }
        
        // This is a simplified version - in production, you'd want more sophisticated query building
        $sql = "SELECT " . implode(', ', $fields) . " FROM " . implode(', ', $safeTables);
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . $conditions;
        }
        
        if (!empty($orderBy)) {
            $sql .= " ORDER BY " . $orderBy;
        }
        
        try {
            $data = $this->db->fetchAll($sql);
            
            if ($format === 'excel') {
                $this->exportToExcel($data, 'custom_report_' . date('Y-m-d') . '.xlsx', 'Custom Report');
            } else {
                $this->exportToCSV($data, 'custom_report_' . date('Y-m-d') . '.csv');
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Query execution failed'], 500);
        }
    }
    
    private function showSalaryRegisterForm() {
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC");
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('reports/salary-register', [
            'periods' => $periods,
            'departments' => $departments,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showComponentReportForm() {
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC");
        $components = $this->db->fetchAll("SELECT * FROM salary_components WHERE status = 'active' ORDER BY display_order ASC");
        
        $this->loadView('reports/component-report', [
            'periods' => $periods,
            'components' => $components,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showBankTransferForm() {
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC");
        
        $this->loadView('reports/bank-transfer', [
            'periods' => $periods,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showTaxReportForm($type) {
        $periods = $this->db->fetchAll("SELECT * FROM payroll_periods ORDER BY start_date DESC");
        $financialYears = $this->db->fetchAll("SELECT DISTINCT financial_year FROM payroll_periods ORDER BY financial_year DESC");
        
        $this->loadView('reports/tax-report', [
            'type' => $type,
            'periods' => $periods,
            'financial_years' => $financialYears,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showLoanReportForm() {
        $loanTypes = $this->db->fetchAll("SELECT * FROM loan_types WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('reports/loan-report', [
            'loan_types' => $loanTypes,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showCustomBuilder() {
        $this->loadView('reports/custom-builder', [
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function exportToExcel($data, $filename, $title = 'Report') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo "<table border='1'>";
        echo "<tr><th colspan='" . count($data[0] ?? []) . "'><h2>$title</h2></th></tr>";
        
        if (!empty($data)) {
            // Headers
            echo "<tr>";
            foreach (array_keys($data[0]) as $header) {
                echo "<th>" . ucwords(str_replace('_', ' ', $header)) . "</th>";
            }
            echo "</tr>";
            
            // Data
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        exit;
    }
    
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Headers
            fputcsv($output, array_keys($data[0]));
            
            // Data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    private function generateSBIFormat($data, $periodId) {
        // SBI specific format implementation
        $this->exportToCSV($data, "sbi_transfer_period_{$periodId}.csv");
    }
    
    private function generateHDFCFormat($data, $periodId) {
        // HDFC specific format implementation
        $this->exportToCSV($data, "hdfc_transfer_period_{$periodId}.csv");
    }
    
    private function generateICICIFormat($data, $periodId) {
        // ICICI specific format implementation
        $this->exportToCSV($data, "icici_transfer_period_{$periodId}.csv");
    }
    
    private function generateGenericBankFormat($data, $periodId) {
        // Generic bank transfer format
        $this->exportToCSV($data, "bank_transfer_period_{$periodId}.csv");
    }
    
    private function generateTDSReport($periodId, $financialYear) {
        // TDS report implementation
        $sql = "SELECT 
                    e.emp_code, e.first_name, e.last_name, e.pan_number,
                    SUM(CASE WHEN sc.code = 'TDS' THEN ABS(pt.amount) ELSE 0 END) as tds_deducted
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pp.financial_year = :fy
                GROUP BY pt.employee_id
                HAVING tds_deducted > 0
                ORDER BY e.emp_code";
        
        $data = $this->db->fetchAll($sql, ['fy' => $financialYear]);
        $this->exportToExcel($data, "tds_report_{$financialYear}.xlsx", 'TDS Report');
    }
    
    private function generatePFReport($periodId, $financialYear) {
        // PF report implementation
        $sql = "SELECT 
                    e.emp_code, e.first_name, e.last_name, e.pf_number, e.uan_number,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as pf_deducted
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pp.financial_year = :fy
                GROUP BY pt.employee_id
                HAVING pf_deducted > 0
                ORDER BY e.emp_code";
        
        $data = $this->db->fetchAll($sql, ['fy' => $financialYear]);
        $this->exportToExcel($data, "pf_report_{$financialYear}.xlsx", 'PF Report');
    }
    
    private function generateESIReport($periodId, $financialYear) {
        // ESI report implementation
        $sql = "SELECT 
                    e.emp_code, e.first_name, e.last_name, e.esi_number,
                    SUM(CASE WHEN sc.code = 'ESI' THEN ABS(pt.amount) ELSE 0 END) as esi_deducted
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pp.financial_year = :fy
                GROUP BY pt.employee_id
                HAVING esi_deducted > 0
                ORDER BY e.emp_code";
        
        $data = $this->db->fetchAll($sql, ['fy' => $financialYear]);
        $this->exportToExcel($data, "esi_report_{$financialYear}.xlsx", 'ESI Report');
    }
}