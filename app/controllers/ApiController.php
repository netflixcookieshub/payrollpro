<?php
/**
 * API Controller for AJAX endpoints
 */

require_once __DIR__ . '/../core/Controller.php';

class ApiController extends Controller {
    
    public function attendanceSummary() {
        $this->checkAuth();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM attendance 
                WHERE attendance_date = :date
                GROUP BY status";
        
        $results = $this->db->fetchAll($sql, ['date' => $date]);
        
        $summary = [
            'present' => 0,
            'absent' => 0,
            'half_day' => 0,
            'late' => 0
        ];
        
        foreach ($results as $result) {
            $summary[$result['status']] = $result['count'];
        }
        
        $this->jsonResponse(['success' => true, 'summary' => $summary]);
    }
    
    public function currentPeriod() {
        $this->checkAuth();
        
        $currentDate = date('Y-m-d');
        $period = $this->db->fetch(
            "SELECT id, period_name FROM payroll_periods 
             WHERE start_date <= :date AND end_date >= :date
             ORDER BY start_date DESC LIMIT 1",
            ['date' => $currentDate]
        );
        
        if ($period) {
            $this->jsonResponse(['success' => true, 'period_id' => $period['id'], 'period_name' => $period['period_name']]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'No active period found']);
        }
    }
    
    public function employeeSearch() {
        $this->checkAuth();
        
        $query = $_GET['q'] ?? '';
        $limit = min(20, intval($_GET['limit'] ?? 10));
        
        if (strlen($query) < 2) {
            $this->jsonResponse(['success' => false, 'message' => 'Query too short']);
            return;
        }
        
        $sql = "SELECT id, emp_code, first_name, last_name, email
                FROM employees 
                WHERE status = 'active' 
                AND (emp_code LIKE :query OR first_name LIKE :query OR last_name LIKE :query OR email LIKE :query)
                ORDER BY emp_code ASC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $employees = $stmt->fetchAll();
        
        $this->jsonResponse(['success' => true, 'employees' => $employees]);
    }
    
    public function salaryCalculator() {
        $this->checkAuth();
        
        $basic = floatval($_GET['basic'] ?? 0);
        $components = $_GET['components'] ?? [];
        
        if ($basic <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid basic salary']);
            return;
        }
        
        // Get salary components with formulas
        $salaryComponents = $this->db->fetchAll(
            "SELECT * FROM salary_components WHERE status = 'active' ORDER BY display_order ASC"
        );
        
        $calculations = [];
        $totalEarnings = 0;
        $totalDeductions = 0;
        
        foreach ($salaryComponents as $component) {
            $amount = 0;
            
            if (!empty($component['formula'])) {
                // Simple formula evaluation (replace BASIC with actual value)
                $formula = str_replace('BASIC', $basic, $component['formula']);
                $amount = $this->evaluateFormula($formula);
            } elseif (isset($components[$component['code']])) {
                $amount = floatval($components[$component['code']]);
            }
            
            $calculations[$component['code']] = [
                'name' => $component['name'],
                'type' => $component['type'],
                'amount' => $amount
            ];
            
            if ($component['type'] === 'earning') {
                $totalEarnings += $amount;
            } else {
                $totalDeductions += $amount;
            }
        }
        
        $netSalary = $totalEarnings - $totalDeductions;
        
        $this->jsonResponse([
            'success' => true,
            'calculations' => $calculations,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary
        ]);
    }
    
    private function evaluateFormula($formula) {
        // Simple and safe evaluation for basic arithmetic
        $formula = preg_replace('/[^0-9+\-*\/.() ]/', '', $formula);
        
        try {
            return eval("return $formula;");
        } catch (Exception $e) {
            return 0;
        }
    }
}