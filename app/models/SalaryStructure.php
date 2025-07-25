<?php
/**
 * Salary Structure Model
 */

require_once __DIR__ . '/../core/Model.php';

class SalaryStructure extends Model {
    protected $table = 'salary_structures';
    
    public function getEmployeeSalaryStructure($employeeId, $effectiveDate = null) {
        $effectiveDate = $effectiveDate ?: date('Y-m-d');
        
        $sql = "SELECT ss.*, sc.name as component_name, sc.code as component_code, 
                       sc.type as component_type, sc.formula, sc.display_order,
                       sc.is_mandatory, sc.is_taxable, sc.is_pf_applicable, sc.is_esi_applicable
                FROM {$this->table} ss
                JOIN salary_components sc ON ss.component_id = sc.id
                WHERE ss.employee_id = :emp_id 
                AND ss.effective_date <= :effective_date
                AND (ss.end_date IS NULL OR ss.end_date >= :effective_date)
                AND sc.status = 'active'
                ORDER BY sc.display_order ASC";
        
        return $this->db->fetchAll($sql, [
            'emp_id' => $employeeId,
            'effective_date' => $effectiveDate
        ]);
    }
    
    public function createSalaryStructure($employeeId, $components, $effectiveDate) {
        try {
            $this->beginTransaction();
            
            // End current salary structure
            $this->db->query(
                "UPDATE {$this->table} SET end_date = DATE_SUB(:effective_date, INTERVAL 1 DAY) 
                 WHERE employee_id = :emp_id AND end_date IS NULL",
                ['emp_id' => $employeeId, 'effective_date' => $effectiveDate]
            );
            
            // Insert new salary structure
            foreach ($components as $component) {
                if (!empty($component['amount']) && $component['amount'] > 0) {
                    $this->create([
                        'employee_id' => $employeeId,
                        'component_id' => $component['component_id'],
                        'amount' => $component['amount'],
                        'effective_date' => $effectiveDate,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            $this->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to create salary structure'];
        }
    }
    
    public function getSalaryHistory($employeeId) {
        $sql = "SELECT ss.effective_date, ss.end_date,
                       SUM(CASE WHEN sc.type = 'earning' THEN ss.amount ELSE 0 END) as total_earnings,
                       SUM(CASE WHEN sc.type = 'deduction' THEN ss.amount ELSE 0 END) as total_deductions,
                       COUNT(ss.id) as component_count
                FROM {$this->table} ss
                JOIN salary_components sc ON ss.component_id = sc.id
                WHERE ss.employee_id = :emp_id
                GROUP BY ss.effective_date, ss.end_date
                ORDER BY ss.effective_date DESC";
        
        return $this->db->fetchAll($sql, ['emp_id' => $employeeId]);
    }
    
    public function validateSalaryStructure($components) {
        $errors = [];
        $hasBasic = false;
        $totalEarnings = 0;
        
        foreach ($components as $component) {
            if ($component['component_code'] === 'BASIC') {
                $hasBasic = true;
                if ($component['amount'] <= 0) {
                    $errors[] = 'Basic salary must be greater than zero';
                }
            }
            
            if ($component['component_type'] === 'earning') {
                $totalEarnings += $component['amount'];
            }
        }
        
        if (!$hasBasic) {
            $errors[] = 'Basic salary component is mandatory';
        }
        
        if ($totalEarnings <= 0) {
            $errors[] = 'Total earnings must be greater than zero';
        }
        
        return $errors;
    }
    
    public function cloneSalaryStructure($fromEmployeeId, $toEmployeeId, $effectiveDate) {
        $sourceStructure = $this->getEmployeeSalaryStructure($fromEmployeeId);
        
        if (empty($sourceStructure)) {
            return ['success' => false, 'message' => 'Source employee has no salary structure'];
        }
        
        $components = [];
        foreach ($sourceStructure as $component) {
            $components[] = [
                'component_id' => $component['component_id'],
                'amount' => $component['amount']
            ];
        }
        
        return $this->createSalaryStructure($toEmployeeId, $components, $effectiveDate);
    }
    
    public function calculateGrossSalary($employeeId, $effectiveDate = null) {
        $structure = $this->getEmployeeSalaryStructure($employeeId, $effectiveDate);
        $grossSalary = 0;
        
        foreach ($structure as $component) {
            if ($component['component_type'] === 'earning') {
                $grossSalary += $component['amount'];
            }
        }
        
        return $grossSalary;
    }
    
    public function getComponentBreakdown($employeeId, $effectiveDate = null) {
        $structure = $this->getEmployeeSalaryStructure($employeeId, $effectiveDate);
        
        $breakdown = [
            'earnings' => [],
            'deductions' => [],
            'reimbursements' => [],
            'totals' => [
                'earnings' => 0,
                'deductions' => 0,
                'reimbursements' => 0,
                'net_salary' => 0
            ]
        ];
        
        foreach ($structure as $component) {
            $componentData = [
                'id' => $component['component_id'],
                'name' => $component['component_name'],
                'code' => $component['component_code'],
                'amount' => $component['amount'],
                'formula' => $component['formula'],
                'is_mandatory' => $component['is_mandatory'],
                'is_taxable' => $component['is_taxable']
            ];
            
            switch ($component['component_type']) {
                case 'earning':
                    $breakdown['earnings'][] = $componentData;
                    $breakdown['totals']['earnings'] += $component['amount'];
                    break;
                case 'deduction':
                    $breakdown['deductions'][] = $componentData;
                    $breakdown['totals']['deductions'] += $component['amount'];
                    break;
                case 'reimbursement':
                    $breakdown['reimbursements'][] = $componentData;
                    $breakdown['totals']['reimbursements'] += $component['amount'];
                    break;
            }
        }
        
        $breakdown['totals']['net_salary'] = $breakdown['totals']['earnings'] - $breakdown['totals']['deductions'];
        
        return $breakdown;
    }
}