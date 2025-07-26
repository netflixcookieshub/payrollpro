<?php
/**
 * Advanced Report Controller
 * Handles complex reporting, analytics, and business intelligence
 */

require_once __DIR__ . '/../core/Controller.php';

class AdvancedReportController extends Controller {
    
    public function dashboard() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $analytics = $this->getAnalyticsDashboard();
        
        $this->loadView('reports/analytics-dashboard', [
            'analytics' => $analytics,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function salaryTrends() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $months = intval($_GET['months'] ?? 12);
        $departmentId = $_GET['department_id'] ?? '';
        
        $trends = $this->getSalaryTrends($months, $departmentId);
        
        $this->jsonResponse(['success' => true, 'data' => $trends]);
    }
    
    public function costAnalysis() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateCostAnalysisReport();
        } else {
            $this->showCostAnalysisForm();
        }
    }
    
    public function complianceReport() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $financialYear = $_GET['fy'] ?? $this->getCurrentFinancialYear();
        $reportType = $_GET['type'] ?? 'pf';
        
        $compliance = $this->getComplianceData($financialYear, $reportType);
        
        $this->loadView('reports/compliance-report', [
            'compliance' => $compliance,
            'financial_year' => $financialYear,
            'report_type' => $reportType,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function predictiveAnalytics() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $predictions = $this->generatePredictiveAnalytics();
        
        $this->loadView('reports/predictive-analytics', [
            'predictions' => $predictions,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function exportAnalytics() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $reportType = $_GET['type'] ?? 'summary';
        $format = $_GET['format'] ?? 'excel';
        
        $data = $this->getAnalyticsData($reportType);
        
        if ($format === 'excel') {
            $this->exportToExcel($data, "analytics_report_" . date('Y-m-d') . ".xlsx");
        } else {
            $this->exportToCSV($data, "analytics_report_" . date('Y-m-d') . ".csv");
        }
    }
    
    private function getAnalyticsDashboard() {
        $currentMonth = date('Y-m-01');
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        
        // Salary trends
        $salaryTrends = $this->db->fetchAll(
            "SELECT pp.period_name, 
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as total_earnings,
                    SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as total_deductions,
                    COUNT(DISTINCT pt.employee_id) as employee_count
             FROM payroll_periods pp
             LEFT JOIN payroll_transactions pt ON pp.id = pt.period_id
             LEFT JOIN salary_components sc ON pt.component_id = sc.id
             WHERE pp.start_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY pp.id, pp.period_name
             ORDER BY pp.start_date ASC"
        );
        
        // Department costs
        $departmentCosts = $this->db->fetchAll(
            "SELECT d.name as department,
                    COUNT(DISTINCT e.id) as employee_count,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as total_cost
             FROM departments d
             LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
             LEFT JOIN payroll_transactions pt ON e.id = pt.employee_id
             LEFT JOIN payroll_periods pp ON pt.period_id = pp.id
             LEFT JOIN salary_components sc ON pt.component_id = sc.id
             WHERE pp.start_date >= :current_month
             GROUP BY d.id, d.name
             ORDER BY total_cost DESC",
            ['current_month' => $currentMonth]
        );
        
        // Attendance analytics
        $attendanceAnalytics = $this->db->fetch(
            "SELECT 
                COUNT(DISTINCT a.employee_id) as total_employees,
                AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100 as avg_attendance,
                SUM(a.overtime_hours) as total_overtime
             FROM attendance a
             WHERE a.attendance_date >= :current_month",
            ['current_month' => $currentMonth]
        );
        
        // Cost per employee trends
        $costPerEmployee = $this->db->fetchAll(
            "SELECT pp.period_name,
                    ROUND(SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) / COUNT(DISTINCT pt.employee_id), 2) as avg_cost_per_employee
             FROM payroll_periods pp
             JOIN payroll_transactions pt ON pp.id = pt.period_id
             JOIN salary_components sc ON pt.component_id = sc.id
             WHERE pp.start_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY pp.id, pp.period_name
             ORDER BY pp.start_date ASC"
        );
        
        return [
            'salary_trends' => $salaryTrends,
            'department_costs' => $departmentCosts,
            'attendance_analytics' => $attendanceAnalytics,
            'cost_per_employee' => $costPerEmployee
        ];
    }
    
    private function getSalaryTrends($months, $departmentId) {
        $conditions = "pp.start_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)";
        $params = ['months' => $months];
        
        if (!empty($departmentId)) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        return $this->db->fetchAll(
            "SELECT pp.period_name,
                    pp.start_date,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as earnings,
                    SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as deductions,
                    COUNT(DISTINCT pt.employee_id) as employees,
                    ROUND(AVG(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END), 2) as avg_salary
             FROM payroll_periods pp
             JOIN payroll_transactions pt ON pp.id = pt.period_id
             JOIN salary_components sc ON pt.component_id = sc.id
             JOIN employees e ON pt.employee_id = e.id
             WHERE {$conditions}
             GROUP BY pp.id, pp.period_name, pp.start_date
             ORDER BY pp.start_date ASC",
            $params
        );
    }
    
    private function generateCostAnalysisReport() {
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $groupBy = $_POST['group_by'] ?? 'department';
        $format = $_POST['format'] ?? 'excel';
        
        $data = $this->getCostAnalysisData($startDate, $endDate, $groupBy);
        
        if ($format === 'excel') {
            $this->exportToExcel($data, "cost_analysis_" . date('Y-m-d') . ".xlsx");
        } else {
            $this->exportToCSV($data, "cost_analysis_" . date('Y-m-d') . ".csv");
        }
    }
    
    private function getCostAnalysisData($startDate, $endDate, $groupBy) {
        $groupField = $groupBy === 'department' ? 'd.name' : 'des.name';
        $groupTable = $groupBy === 'department' ? 'departments d' : 'designations des';
        $joinCondition = $groupBy === 'department' ? 'e.department_id = d.id' : 'e.designation_id = des.id';
        
        return $this->db->fetchAll(
            "SELECT {$groupField} as category,
                    COUNT(DISTINCT e.id) as employee_count,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as total_earnings,
                    SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as total_deductions,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE pt.amount END) as net_cost,
                    ROUND(AVG(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE pt.amount END), 2) as avg_cost_per_employee
             FROM {$groupTable}
             JOIN employees e ON {$joinCondition}
             JOIN payroll_transactions pt ON e.id = pt.employee_id
             JOIN payroll_periods pp ON pt.period_id = pp.id
             JOIN salary_components sc ON pt.component_id = sc.id
             WHERE pp.start_date >= :start_date AND pp.end_date <= :end_date
             GROUP BY {$groupField}
             ORDER BY total_earnings DESC",
            ['start_date' => $startDate, 'end_date' => $endDate]
        );
    }
    
    private function getComplianceData($financialYear, $reportType) {
        switch ($reportType) {
            case 'pf':
                return $this->getPFComplianceData($financialYear);
            case 'esi':
                return $this->getESIComplianceData($financialYear);
            case 'tds':
                return $this->getTDSComplianceData($financialYear);
            default:
                return [];
        }
    }
    
    private function getPFComplianceData($financialYear) {
        return $this->db->fetchAll(
            "SELECT e.emp_code, e.first_name, e.last_name, e.pf_number, e.uan_number,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as employee_pf,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as employer_pf,
                    SUM(CASE WHEN sc.code = 'BASIC' THEN pt.amount ELSE 0 END) as pf_wages
             FROM employees e
             JOIN payroll_transactions pt ON e.id = pt.employee_id
             JOIN payroll_periods pp ON pt.period_id = pp.id
             JOIN salary_components sc ON pt.component_id = sc.id
             WHERE pp.financial_year = :fy AND sc.code IN ('PF', 'BASIC')
             GROUP BY e.id
             HAVING employee_pf > 0
             ORDER BY e.emp_code",
            ['fy' => $financialYear]
        );
    }
    
    private function generatePredictiveAnalytics() {
        // Simple predictive analytics based on historical data
        $predictions = [];
        
        // Predict next month's payroll cost
        $avgMonthlyCost = $this->db->fetch(
            "SELECT AVG(monthly_cost) as avg_cost
             FROM (
                 SELECT SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as monthly_cost
                 FROM payroll_transactions pt
                 JOIN payroll_periods pp ON pt.period_id = pp.id
                 JOIN salary_components sc ON pt.component_id = sc.id
                 WHERE pp.start_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY pp.id
             ) as monthly_costs"
        );
        
        $predictions['next_month_cost'] = $avgMonthlyCost['avg_cost'] ?? 0;
        
        // Predict attrition based on recent trends
        $attritionRate = $this->db->fetch(
            "SELECT COUNT(*) as terminated_employees
             FROM employees
             WHERE status = 'terminated' 
             AND leave_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"
        );
        
        $totalEmployees = $this->db->fetch("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")['count'];
        $predictions['attrition_rate'] = $totalEmployees > 0 ? ($attritionRate['terminated_employees'] / $totalEmployees) * 100 : 0;
        
        // Predict overtime costs
        $avgOvertimeCost = $this->db->fetch(
            "SELECT AVG(overtime_hours * 500) as avg_overtime_cost
             FROM attendance
             WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
             AND overtime_hours > 0"
        );
        
        $predictions['next_month_overtime'] = $avgOvertimeCost['avg_overtime_cost'] ?? 0;
        
        return $predictions;
    }
    
    private function getCurrentFinancialYear() {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
    
    private function exportToExcel($data, $filename) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo "<table border='1'>";
        if (!empty($data)) {
            echo "<tr>";
            foreach (array_keys($data[0]) as $header) {
                echo "<th>" . ucwords(str_replace('_', ' ', $header)) . "</th>";
            }
            echo "</tr>";
            
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
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}