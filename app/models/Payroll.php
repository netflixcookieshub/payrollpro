<?php
/**
 * Payroll Model
 */

require_once __DIR__ . '/../core/Model.php';

class Payroll extends Model {
    protected $table = 'payroll_transactions';
    
    public function processPayroll($periodId, $employeeIds = []) {
        try {
            $this->beginTransaction();
            
            $period = $this->getPayrollPeriod($periodId);
            if (!$period) {
                throw new Exception('Invalid payroll period');
            }
            
            // Get employees to process
            $employees = $this->getEmployeesForPayroll($employeeIds, $period);
            
            $processedCount = 0;
            foreach ($employees as $employee) {
                if ($this->processEmployeePayroll($employee, $period)) {
                    $processedCount++;
                }
            }
            
            $this->commit();
            return ['success' => true, 'processed' => $processedCount];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function processEmployeePayroll($employee, $period) {
        // Check if payroll already processed for this employee and period
        $existing = $this->db->fetch(
            "SELECT COUNT(*) as count FROM payroll_transactions 
             WHERE employee_id = :emp_id AND period_id = :period_id",
            ['emp_id' => $employee['id'], 'period_id' => $period['id']]
        );
        
        if ($existing['count'] > 0) {
            return false; // Already processed
        }
        
        // Get employee salary structure
        $salaryStructure = $this->getEmployeeSalaryStructure($employee['id']);
        
        // Calculate attendance and LOP
        $attendance = $this->calculateAttendance($employee['id'], $period);
        
        // Process each salary component
        foreach ($salaryStructure as $component) {
            $amount = $this->calculateComponentAmount($component, $employee, $period, $attendance);
            
            // Insert payroll transaction
            $this->db->insert('payroll_transactions', [
                'employee_id' => $employee['id'],
                'period_id' => $period['id'],
                'component_id' => $component['component_id'],
                'amount' => $amount,
                'calculated_amount' => $amount,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Process loans and advances
        $this->processLoanEMI($employee['id'], $period);
        
        // Calculate and process TDS
        $this->processTDS($employee['id'], $period);
        
        return true;
    }
    
    private function calculateComponentAmount($component, $employee, $period, $attendance) {
        $amount = $component['amount'];
        
        // If formula exists, calculate based on formula
        if (!empty($component['formula'])) {
            $amount = $this->evaluateFormula($component['formula'], $employee, $period);
        }
        
        // Apply pro-rata for joining/leaving employees
        if ($this->isProRataApplicable($employee, $period)) {
            $amount = $this->applyProRata($amount, $employee, $period);
        }
        
        // Apply LOP if applicable
        if ($attendance['lop_days'] > 0 && in_array($component['component_type'], ['earning'])) {
            $lopAmount = ($amount / $attendance['total_days']) * $attendance['lop_days'];
            $amount = $amount - $lopAmount;
        }
        
        return round($amount, 2);
    }
    
    private function evaluateFormula($formula, $employee, $period) {
        // Get all salary components for the employee
        $components = $this->getEmployeeSalaryStructure($employee['id']);
        $componentValues = [];
        
        foreach ($components as $comp) {
            $componentValues[$comp['component_code']] = $comp['amount'];
        }
        
        // Replace component codes with values in formula
        $evaluatedFormula = $formula;
        foreach ($componentValues as $code => $value) {
            $evaluatedFormula = str_replace($code, $value, $evaluatedFormula);
        }
        
        // Evaluate mathematical expression safely
        return $this->evaluateMathExpression($evaluatedFormula);
    }
    
    private function evaluateMathExpression($expression) {
        // Simple and safe evaluation for basic arithmetic
        $expression = preg_replace('/[^0-9+\-*\/.() ]/', '', $expression);
        
        try {
            return eval("return $expression;");
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function calculateAttendance($employeeId, $period) {
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'half_day' THEN 0.5 ELSE 0 END) as half_days
                FROM attendance 
                WHERE employee_id = :emp_id 
                AND attendance_date BETWEEN :start_date AND :end_date";
        
        $attendance = $this->db->fetch($sql, [
            'emp_id' => $employeeId,
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date']
        ]);
        
        // Calculate working days in the period
        $workingDays = $this->getWorkingDays($period['start_date'], $period['end_date']);
        
        $lopDays = $workingDays - ($attendance['present_days'] + ($attendance['half_days'] * 0.5));
        
        return [
            'working_days' => $workingDays,
            'present_days' => $attendance['present_days'],
            'absent_days' => $attendance['absent_days'],
            'half_days' => $attendance['half_days'],
            'lop_days' => max(0, $lopDays),
            'total_days' => $workingDays
        ];
    }
    
    private function processLoanEMI($employeeId, $period) {
        $loans = $this->getActiveLoans($employeeId);
        
        foreach ($loans as $loan) {
            if ($this->isEMIDue($loan, $period)) {
                // Add loan EMI as deduction
                $this->db->insert('payroll_transactions', [
                    'employee_id' => $employeeId,
                    'period_id' => $period['id'],
                    'component_id' => $this->getLoanDeductionComponentId(),
                    'amount' => -$loan['emi_amount'], // Negative for deduction
                    'remarks' => 'Loan EMI - ' . $loan['loan_type_name'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // Update loan outstanding amount
                $newOutstanding = $loan['outstanding_amount'] - $loan['emi_amount'];
                $this->db->update('employee_loans', 
                    ['outstanding_amount' => $newOutstanding],
                    'id = :id',
                    ['id' => $loan['id']]
                );
            }
        }
    }
    
    private function processTDS($employeeId, $period) {
        $grossSalary = $this->calculateGrossSalary($employeeId, $period);
        $tdsAmount = $this->calculateTDS($grossSalary, $employeeId, $period);
        
        if ($tdsAmount > 0) {
            $this->db->insert('payroll_transactions', [
                'employee_id' => $employeeId,
                'period_id' => $period['id'],
                'component_id' => $this->getTDSComponentId(),
                'amount' => -$tdsAmount, // Negative for deduction
                'calculated_amount' => $tdsAmount,
                'remarks' => 'Income Tax (TDS)',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    public function getEmployeePayslip($employeeId, $periodId) {
        $sql = "SELECT pt.*, sc.name as component_name, sc.code as component_code, 
                       sc.type as component_type, sc.display_order,
                       e.first_name, e.last_name, e.emp_code, e.pan_number, e.pf_number,
                       e.bank_account_number, e.bank_name, e.bank_ifsc,
                       d.name as department_name, des.name as designation_name,
                       pp.period_name, pp.start_date, pp.end_date
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN employees e ON pt.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                JOIN designations des ON e.designation_id = des.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pt.employee_id = :emp_id AND pt.period_id = :period_id
                ORDER BY sc.display_order ASC";
        
        return $this->db->fetchAll($sql, [
            'emp_id' => $employeeId,
            'period_id' => $periodId
        ]);
    }
    
    public function getPayrollSummary($periodId, $departmentId = null) {
        $conditions = "pt.period_id = :period_id";
        $params = ['period_id' => $periodId];
        
        if ($departmentId) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    COUNT(DISTINCT pt.employee_id) as total_employees,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as total_earnings,
                    SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as total_deductions,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE -pt.amount END) as net_payable
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN employees e ON pt.employee_id = e.id
                WHERE {$conditions}";
        
        return $this->db->fetch($sql, $params);
    }
    
    // Helper methods
    private function getPayrollPeriod($periodId) {
        return $this->db->fetch("SELECT * FROM payroll_periods WHERE id = :id", ['id' => $periodId]);
    }
    
    private function getEmployeesForPayroll($employeeIds, $period) {
        if (empty($employeeIds)) {
            // Get all active employees
            return $this->db->fetchAll(
                "SELECT * FROM employees WHERE status = 'active' AND join_date <= :end_date",
                ['end_date' => $period['end_date']]
            );
        } else {
            $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
            return $this->db->fetchAll(
                "SELECT * FROM employees WHERE id IN ({$placeholders}) AND status = 'active'",
                $employeeIds
            );
        }
    }
    
    private function getEmployeeSalaryStructure($employeeId) {
        $sql = "SELECT ss.*, sc.code as component_code, sc.type as component_type, sc.formula
                FROM salary_structures ss
                JOIN salary_components sc ON ss.component_id = sc.id
                WHERE ss.employee_id = :emp_id 
                AND (ss.end_date IS NULL OR ss.end_date >= CURDATE())
                ORDER BY sc.display_order ASC";
        
        return $this->db->fetchAll($sql, ['emp_id' => $employeeId]);
    }
    
    private function getWorkingDays($startDate, $endDate) {
        // Simple calculation - exclude weekends
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $workingDays = 0;
        
        while ($start <= $end) {
            if ($start->format('N') < 6) { // Monday = 1, Sunday = 7
                $workingDays++;
            }
            $start->modify('+1 day');
        }
        
        return $workingDays;
    }
    
    private function isProRataApplicable($employee, $period) {
        $joinDate = new DateTime($employee['join_date']);
        $periodStart = new DateTime($period['start_date']);
        $periodEnd = new DateTime($period['end_date']);
        
        return ($joinDate > $periodStart) || 
               (!empty($employee['leave_date']) && new DateTime($employee['leave_date']) < $periodEnd);
    }
    
    private function applyProRata($amount, $employee, $period) {
        $periodStart = new DateTime($period['start_date']);
        $periodEnd = new DateTime($period['end_date']);
        $joinDate = new DateTime($employee['join_date']);
        
        $actualStart = max($joinDate, $periodStart);
        $actualEnd = !empty($employee['leave_date']) ? 
                     min(new DateTime($employee['leave_date']), $periodEnd) : $periodEnd;
        
        $totalDays = $periodStart->diff($periodEnd)->days + 1;
        $actualDays = $actualStart->diff($actualEnd)->days + 1;
        
        return ($amount / $totalDays) * $actualDays;
    }
    
    private function getActiveLoans($employeeId) {
        $sql = "SELECT el.*, lt.name as loan_type_name 
                FROM employee_loans el
                JOIN loan_types lt ON el.loan_type_id = lt.id
                WHERE el.employee_id = :emp_id AND el.status = 'active' AND el.outstanding_amount > 0";
        
        return $this->db->fetchAll($sql, ['emp_id' => $employeeId]);
    }
    
    private function isEMIDue($loan, $period) {
        $emiDate = new DateTime($loan['first_emi_date']);
        $periodEnd = new DateTime($period['end_date']);
        
        return $emiDate <= $periodEnd;
    }
    
    private function getLoanDeductionComponentId() {
        // Get or create loan deduction component
        $component = $this->db->fetch(
            "SELECT id FROM salary_components WHERE code = 'LOAN_EMI'"
        );
        
        if (!$component) {
            return $this->db->insert('salary_components', [
                'name' => 'Loan EMI',
                'code' => 'LOAN_EMI',
                'type' => 'deduction',
                'is_mandatory' => 0,
                'is_taxable' => 0
            ]);
        }
        
        return $component['id'];
    }
    
    private function getTDSComponentId() {
        $component = $this->db->fetch(
            "SELECT id FROM salary_components WHERE code = 'TDS'"
        );
        
        return $component ? $component['id'] : null;
    }
    
    private function calculateGrossSalary($employeeId, $period) {
        $sql = "SELECT SUM(amount) as gross_salary
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pt.employee_id = :emp_id AND pt.period_id = :period_id 
                AND sc.type = 'earning' AND sc.is_taxable = 1";
        
        $result = $this->db->fetch($sql, [
            'emp_id' => $employeeId,
            'period_id' => $period['id']
        ]);
        
        return $result['gross_salary'] ?? 0;
    }
    
    private function calculateTDS($grossSalary, $employeeId, $period) {
        // Simplified TDS calculation
        $annualSalary = $grossSalary * 12;
        $taxSlabs = $this->getTaxSlabs($period['financial_year']);
        
        $tds = 0;
        foreach ($taxSlabs as $slab) {
            if ($annualSalary > $slab['min_amount']) {
                $taxableAmount = min($annualSalary, $slab['max_amount'] ?? $annualSalary) - $slab['min_amount'];
                $tds += ($taxableAmount * $slab['tax_rate']) / 100;
            }
        }
        
        return $tds / 12; // Monthly TDS
    }
    
    private function getTaxSlabs($financialYear) {
        return $this->db->fetchAll(
            "SELECT * FROM tax_slabs WHERE financial_year = :fy ORDER BY min_amount ASC",
            ['fy' => $financialYear]
        );
    }
}