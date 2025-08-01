<?php
/**
 * Advanced Payroll Processor
 * Handles complex payroll calculations with formulas, pro-rata, LOP, TDS, etc.
 */

require_once __DIR__ . '/../core/Model.php';

class PayrollProcessor extends Model {
    protected $table = 'payroll_transactions';
    
    private $taxSlabs = [];
    private $pfSettings = [];
    private $esiSettings = [];
    
    public function __construct($database) {
        parent::__construct($database);
        $this->loadTaxConfiguration();
    }
    
    /**
     * Process payroll for employees with advanced calculations
     */
    public function processAdvancedPayroll($periodId, $employeeIds = [], $options = []) {
        try {
            $this->beginTransaction();
            
            $period = $this->getPayrollPeriod($periodId);
            if (!$period) {
                throw new Exception('Invalid payroll period');
            }
            
            // Get employees to process
            $employees = $this->getEmployeesForProcessing($employeeIds, $period);
            
            $processedCount = 0;
            $errors = [];
            
            foreach ($employees as $employee) {
                try {
                    $result = $this->processEmployeeAdvancedPayroll($employee, $period, $options);
                    if ($result['success']) {
                        $processedCount++;
                    } else {
                        $errors[] = "Employee {$employee['emp_code']}: {$result['message']}";
                    }
                } catch (Exception $e) {
                    $errors[] = "Employee {$employee['emp_code']}: {$e->getMessage()}";
                }
            }
            
            $this->commit();
            return [
                'success' => true, 
                'processed' => $processedCount,
                'errors' => $errors,
                'total_employees' => count($employees)
            ];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Process individual employee payroll with advanced features
     */
    private function processEmployeeAdvancedPayroll($employee, $period, $options) {
        // Check if already processed
        if ($this->isPayrollProcessed($employee['id'], $period['id'])) {
            return ['success' => false, 'message' => 'Payroll already processed'];
        }
        
        // Get employee salary structure
        $salaryStructure = $this->getEmployeeSalaryStructure($employee['id'], $period['start_date']);
        
        if (empty($salaryStructure)) {
            return ['success' => false, 'message' => 'No salary structure found'];
        }
        
        // Calculate attendance and LOP
        $attendance = $this->calculateAdvancedAttendance($employee['id'], $period);
        
        // Calculate pro-rata if applicable
        $proRataFactor = $this->calculateProRataFactor($employee, $period);
        
        // Process earnings first
        $earnings = $this->processEarnings($employee, $period, $salaryStructure, $attendance, $proRataFactor);
        
        // Process deductions
        $deductions = $this->processDeductions($employee, $period, $earnings, $attendance, $options);
        
        // Process arrears if any
        if ($options['include_arrears'] ?? false) {
            $arrears = $this->processArrears($employee['id'], $period['id']);
            $earnings = array_merge($earnings, $arrears);
        }
        
        // Process variable pay
        if ($options['include_variable_pay'] ?? false) {
            $variablePay = $this->processVariablePay($employee['id'], $period['id']);
            $earnings = array_merge($earnings, $variablePay);
        }
        
        // Save all transactions
        foreach (array_merge($earnings, $deductions) as $transaction) {
            $this->create($transaction);
        }
        
        return ['success' => true];
    }
    
    /**
     * Process earnings with formula calculations
     */
    private function processEarnings($employee, $period, $salaryStructure, $attendance, $proRataFactor) {
        $earnings = [];
        $componentValues = [];
        
        // First pass: Calculate basic components without formulas
        foreach ($salaryStructure as $component) {
            if ($component['component_type'] === 'earning' && empty($component['formula'])) {
                $amount = $component['amount'] * $proRataFactor;
                
                // Apply LOP for earnings
                if ($attendance['lop_days'] > 0) {
                    $lopDeduction = ($amount / $attendance['working_days']) * $attendance['lop_days'];
                    $amount = $amount - $lopDeduction;
                }
                
                $componentValues[$component['component_code']] = $amount;
                
                $earnings[] = [
                    'employee_id' => $employee['id'],
                    'period_id' => $period['id'],
                    'component_id' => $component['component_id'],
                    'amount' => $amount,
                    'calculated_amount' => $component['amount'],
                    'remarks' => $this->generateRemarks($proRataFactor, $attendance),
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        // Second pass: Calculate formula-based components
        foreach ($salaryStructure as $component) {
            if ($component['component_type'] === 'earning' && !empty($component['formula'])) {
                $amount = $this->evaluateFormula($component['formula'], $componentValues, $employee, $period);
                $amount = $amount * $proRataFactor;
                
                // Apply LOP for earnings
                if ($attendance['lop_days'] > 0) {
                    $lopDeduction = ($amount / $attendance['working_days']) * $attendance['lop_days'];
                    $amount = $amount - $lopDeduction;
                }
                
                $componentValues[$component['component_code']] = $amount;
                
                $earnings[] = [
                    'employee_id' => $employee['id'],
                    'period_id' => $period['id'],
                    'component_id' => $component['component_id'],
                    'amount' => $amount,
                    'calculated_amount' => $amount,
                    'remarks' => "Formula: {$component['formula']}",
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        return $earnings;
    }
    
    /**
     * Process deductions including PF, ESI, TDS, Loans
     */
    private function processDeductions($employee, $period, $earnings, $attendance, $options) {
        $deductions = [];
        $grossSalary = array_sum(array_column($earnings, 'amount'));
        
        // Get deduction components from salary structure
        $salaryStructure = $this->getEmployeeSalaryStructure($employee['id'], $period['start_date']);
        
        foreach ($salaryStructure as $component) {
            if ($component['component_type'] === 'deduction') {
                $amount = 0;
                
                switch ($component['component_code']) {
                    case 'PF':
                        $amount = $this->calculatePF($employee, $earnings);
                        break;
                    case 'ESI':
                        $amount = $this->calculateESI($employee, $earnings);
                        break;
                    case 'PT':
                        $amount = $this->calculatePT($grossSalary);
                        break;
                    case 'TDS':
                        if ($options['calculate_tds'] ?? true) {
                            $amount = $this->calculateTDS($employee, $grossSalary, $period);
                        }
                        break;
                    default:
                        // Formula-based or fixed deductions
                        if (!empty($component['formula'])) {
                            $componentValues = $this->getComponentValues($earnings);
                            $amount = $this->evaluateFormula($component['formula'], $componentValues, $employee, $period);
                        } else {
                            $amount = $component['amount'];
                        }
                }
                
                if ($amount > 0) {
                    $deductions[] = [
                        'employee_id' => $employee['id'],
                        'period_id' => $period['id'],
                        'component_id' => $component['component_id'],
                        'amount' => -$amount, // Negative for deductions
                        'calculated_amount' => $amount,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }
        
        // Process loan EMIs
        if ($options['process_loans'] ?? true) {
            $loanDeductions = $this->processLoanEMIs($employee['id'], $period);
            $deductions = array_merge($deductions, $loanDeductions);
        }
        
        return $deductions;
    }
    
    /**
     * Calculate PF contribution
     */
    private function calculatePF($employee, $earnings) {
        $pfBasic = 0;
        
        foreach ($earnings as $earning) {
            // Get component details
            $component = $this->db->fetch(
                "SELECT code, is_pf_applicable FROM salary_components WHERE id = :id",
                ['id' => $earning['component_id']]
            );
            
            if ($component && $component['is_pf_applicable']) {
                $pfBasic += $earning['amount'];
            }
        }
        
        // PF ceiling check (₹15,000 as of 2024)
        $pfCeiling = 15000;
        $pfBasic = min($pfBasic, $pfCeiling);
        
        return $pfBasic * (PF_RATE_EMPLOYEE / 100);
    }
    
    /**
     * Calculate ESI contribution
     */
    private function calculateESI($employee, $earnings) {
        $grossSalary = array_sum(array_column($earnings, 'amount'));
        
        // ESI threshold check
        if ($grossSalary <= ESI_THRESHOLD) {
            return $grossSalary * (ESI_RATE_EMPLOYEE / 100);
        }
        
        return 0;
    }
    
    /**
     * Calculate Professional Tax
     */
    private function calculatePT($grossSalary) {
        // Simplified PT calculation - can be made state-specific
        if ($grossSalary > 21000) {
            return 200;
        } elseif ($grossSalary > 15000) {
            return 150;
        } elseif ($grossSalary > 10000) {
            return 100;
        }
        
        return 0;
    }
    
    /**
     * Calculate TDS with tax slabs
     */
    private function calculateTDS($employee, $monthlyGross, $period) {
        // Get annual gross salary
        $annualGross = $monthlyGross * 12;
        
        // Get employee's previous TDS for the financial year
        $previousTDS = $this->getPreviousTDS($employee['id'], $period['financial_year']);
        
        // Calculate annual tax liability
        $annualTax = $this->calculateAnnualTax($annualGross, $period['financial_year']);
        
        // Calculate monthly TDS
        $monthlyTDS = ($annualTax / 12) - ($previousTDS / $this->getMonthsElapsed($period));
        
        return max(0, $monthlyTDS);
    }
    
    /**
     * Calculate annual tax based on tax slabs
     */
    private function calculateAnnualTax($annualIncome, $financialYear) {
        $taxSlabs = $this->getTaxSlabs($financialYear);
        $totalTax = 0;
        
        foreach ($taxSlabs as $slab) {
            if ($annualIncome > $slab['min_amount']) {
                $maxAmount = $slab['max_amount'] ?? $annualIncome;
                $taxableAmount = min($annualIncome, $maxAmount) - $slab['min_amount'];
                
                if ($taxableAmount > 0) {
                    $slabTax = ($taxableAmount * $slab['tax_rate']) / 100;
                    $totalTax += $slabTax;
                }
            }
        }
        
        return $totalTax;
    }
    
    /**
     * Process loan EMI deductions
     */
    private function processLoanEMIs($employeeId, $period) {
        $loans = $this->getActiveLoanEMIs($employeeId, $period);
        $loanDeductions = [];
        
        foreach ($loans as $loan) {
            // Check if EMI is due this period
            if ($this->isEMIDueInPeriod($loan, $period)) {
                $loanDeductions[] = [
                    'employee_id' => $employeeId,
                    'period_id' => $period['id'],
                    'component_id' => $this->getLoanEMIComponentId(),
                    'amount' => -$loan['emi_amount'],
                    'calculated_amount' => $loan['emi_amount'],
                    'remarks' => "Loan EMI - {$loan['loan_type_name']}",
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Update loan outstanding amount
                $this->updateLoanOutstanding($loan['id'], $loan['emi_amount']);
            }
        }
        
        return $loanDeductions;
    }
    
    /**
     * Process arrears payments
     */
    private function processArrears($employeeId, $periodId) {
        $arrears = $this->db->fetchAll(
            "SELECT * FROM employee_arrears 
             WHERE employee_id = :emp_id AND status = 'pending' AND effective_period_id = :period_id",
            ['emp_id' => $employeeId, 'period_id' => $periodId]
        );
        
        $arrearsTransactions = [];
        
        foreach ($arrears as $arrear) {
            $arrearsTransactions[] = [
                'employee_id' => $employeeId,
                'period_id' => $periodId,
                'component_id' => $arrear['component_id'],
                'amount' => $arrear['amount'],
                'calculated_amount' => $arrear['amount'],
                'remarks' => "Arrears: {$arrear['description']}",
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Mark arrear as processed
            $this->db->update('employee_arrears', 
                ['status' => 'processed'], 
                'id = :id', 
                ['id' => $arrear['id']]
            );
        }
        
        return $arrearsTransactions;
    }
    
    /**
     * Process variable pay components
     */
    private function processVariablePay($employeeId, $periodId) {
        $variablePay = $this->db->fetchAll(
            "SELECT * FROM employee_variable_pay 
             WHERE employee_id = :emp_id AND period_id = :period_id AND status = 'approved'",
            ['emp_id' => $employeeId, 'period_id' => $periodId]
        );
        
        $variableTransactions = [];
        
        foreach ($variablePay as $variable) {
            $variableTransactions[] = [
                'employee_id' => $employeeId,
                'period_id' => $periodId,
                'component_id' => $variable['component_id'],
                'amount' => $variable['amount'],
                'calculated_amount' => $variable['amount'],
                'remarks' => "Variable Pay: {$variable['description']}",
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return $variableTransactions;
    }
    
    /**
     * Calculate advanced attendance with LOP
     */
    private function calculateAdvancedAttendance($employeeId, $period) {
        $sql = "SELECT 
                    COUNT(*) as marked_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'half_day' THEN 0.5 ELSE 0 END) as half_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
                FROM attendance 
                WHERE employee_id = :emp_id 
                AND attendance_date BETWEEN :start_date AND :end_date";
        
        $attendance = $this->db->fetch($sql, [
            'emp_id' => $employeeId,
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date']
        ]);
        
        // Calculate working days (excluding weekends and holidays)
        $workingDays = $this->calculateWorkingDays($period['start_date'], $period['end_date']);
        
        // Calculate effective present days
        $effectivePresentDays = $attendance['present_days'] + $attendance['half_days'] + $attendance['late_days'];
        
        // Calculate LOP days
        $lopDays = max(0, $workingDays - $effectivePresentDays);
        
        return [
            'working_days' => $workingDays,
            'present_days' => $attendance['present_days'],
            'absent_days' => $attendance['absent_days'],
            'half_days' => $attendance['half_days'],
            'late_days' => $attendance['late_days'],
            'lop_days' => $lopDays,
            'effective_days' => $effectivePresentDays
        ];
    }
    
    /**
     * Calculate pro-rata factor for joining/leaving employees
     */
    private function calculateProRataFactor($employee, $period) {
        $periodStart = new DateTime($period['start_date']);
        $periodEnd = new DateTime($period['end_date']);
        $joinDate = new DateTime($employee['join_date']);
        
        // Check if employee joined during this period
        $actualStart = max($joinDate, $periodStart);
        
        // Check if employee left during this period
        $actualEnd = $periodEnd;
        if (!empty($employee['leave_date'])) {
            $leaveDate = new DateTime($employee['leave_date']);
            $actualEnd = min($leaveDate, $periodEnd);
        }
        
        // Calculate pro-rata factor
        $totalDays = $periodStart->diff($periodEnd)->days + 1;
        $actualDays = $actualStart->diff($actualEnd)->days + 1;
        
        return $actualDays / $totalDays;
    }
    
    /**
     * Evaluate formula with component values
     */
    private function evaluateFormula($formula, $componentValues, $employee, $period) {
        // Get all salary components for the employee
        $components = $this->getEmployeeSalaryStructure($employee['id'], $period['start_date']);
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
    
    /**
     * Safely evaluate mathematical expression
     */
    private function evaluateMathExpression($expression) {
        // Simple and safe evaluation for basic arithmetic
        $expression = preg_replace('/[^0-9+\-*\/.() ]/', '', $expression);
        
        try {
            return eval("return $expression;");
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Calculate working days excluding weekends and holidays
     */
    private function calculateWorkingDays($startDate, $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $workingDays = 0;
        
        // Get holidays in the period
        $holidays = $this->getHolidaysInPeriod($startDate, $endDate);
        $holidayDates = array_column($holidays, 'holiday_date');
        
        while ($start <= $end) {
            $dayOfWeek = $start->format('N'); // 1 = Monday, 7 = Sunday
            $currentDate = $start->format('Y-m-d');
            
            // Count if it's a weekday and not a holiday
            if ($dayOfWeek < 6 && !in_array($currentDate, $holidayDates)) {
                $workingDays++;
            }
            
            $start->modify('+1 day');
        }
        
        return $workingDays;
    }
    
    /**
     * Get holidays in a specific period
     */
    private function getHolidaysInPeriod($startDate, $endDate) {
        return $this->db->fetchAll(
            "SELECT holiday_date FROM holidays 
             WHERE holiday_date BETWEEN :start_date AND :end_date",
            ['start_date' => $startDate, 'end_date' => $endDate]
        );
    }
    
    /**
     * Get tax slabs for financial year
     */
    private function getTaxSlabs($financialYear) {
        if (!isset($this->taxSlabs[$financialYear])) {
            $this->taxSlabs[$financialYear] = $this->db->fetchAll(
                "SELECT * FROM tax_slabs WHERE financial_year = :fy ORDER BY min_amount ASC",
                ['fy' => $financialYear]
            );
        }
        
        return $this->taxSlabs[$financialYear];
    }
    
    /**
     * Get previous TDS for financial year
     */
    private function getPreviousTDS($employeeId, $financialYear) {
        $sql = "SELECT SUM(ABS(pt.amount)) as total_tds
                FROM payroll_transactions pt
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pt.employee_id = :emp_id 
                AND pp.financial_year = :fy 
                AND sc.code = 'TDS'";
        
        $result = $this->db->fetch($sql, ['emp_id' => $employeeId, 'fy' => $financialYear]);
        return $result['total_tds'] ?? 0;
    }
    
    /**
     * Get months elapsed in financial year
     */
    private function getMonthsElapsed($period) {
        $fyStart = $this->getFinancialYearStart($period['financial_year']);
        $periodEnd = new DateTime($period['end_date']);
        
        $diff = $fyStart->diff($periodEnd);
        return $diff->m + ($diff->y * 12) + 1;
    }
    
    /**
     * Get financial year start date
     */
    private function getFinancialYearStart($financialYear) {
        $years = explode('-', $financialYear);
        return new DateTime($years[0] . '-04-01');
    }
    
    /**
     * Get active loan EMIs for employee
     */
    private function getActiveLoanEMIs($employeeId, $period) {
        $sql = "SELECT el.*, lt.name as loan_type_name
                FROM employee_loans el
                JOIN loan_types lt ON el.loan_type_id = lt.id
                WHERE el.employee_id = :emp_id 
                AND el.status = 'active' 
                AND el.outstanding_amount > 0
                AND el.first_emi_date <= :period_end";
        
        return $this->db->fetchAll($sql, [
            'emp_id' => $employeeId,
            'period_end' => $period['end_date']
        ]);
    }
    
    /**
     * Check if EMI is due in current period
     */
    private function isEMIDueInPeriod($loan, $period) {
        $emiDate = new DateTime($loan['first_emi_date']);
        $periodEnd = new DateTime($period['end_date']);
        
        // Calculate next EMI date based on disbursement
        $monthsElapsed = $emiDate->diff($periodEnd)->m + ($emiDate->diff($periodEnd)->y * 12);
        
        return $emiDate <= $periodEnd && $monthsElapsed >= 0;
    }
    
    /**
     * Update loan outstanding amount
     */
    private function updateLoanOutstanding($loanId, $emiAmount) {
        $this->db->query(
            "UPDATE employee_loans 
             SET outstanding_amount = outstanding_amount - :emi_amount,
                 status = CASE WHEN outstanding_amount - :emi_amount <= 0 THEN 'closed' ELSE 'active' END
             WHERE id = :loan_id",
            ['loan_id' => $loanId, 'emi_amount' => $emiAmount]
        );
    }
    
    /**
     * Get or create loan EMI component
     */
    private function getLoanEMIComponentId() {
        $component = $this->db->fetch(
            "SELECT id FROM salary_components WHERE code = 'LOAN_EMI'"
        );
        
        if (!$component) {
            return $this->db->insert('salary_components', [
                'name' => 'Loan EMI',
                'code' => 'LOAN_EMI',
                'type' => 'deduction',
                'is_mandatory' => 0,
                'is_taxable' => 0,
                'display_order' => 99
            ]);
        }
        
        return $component['id'];
    }
    
    /**
     * Generate remarks for payroll transaction
     */
    private function generateRemarks($proRataFactor, $attendance) {
        $remarks = [];
        
        if ($proRataFactor < 1) {
            $remarks[] = "Pro-rata: " . round($proRataFactor * 100, 2) . "%";
        }
        
        if ($attendance['lop_days'] > 0) {
            $remarks[] = "LOP: {$attendance['lop_days']} days";
        }
        
        return implode(', ', $remarks);
    }
    
    /**
     * Get component values from earnings array
     */
    private function getComponentValues($earnings) {
        $values = [];
        
        foreach ($earnings as $earning) {
            $component = $this->db->fetch(
                "SELECT code FROM salary_components WHERE id = :id",
                ['id' => $earning['component_id']]
            );
            
            if ($component) {
                $values[$component['code']] = $earning['amount'];
            }
        }
        
        return $values;
    }
    
    /**
     * Check if payroll is already processed
     */
    private function isPayrollProcessed($employeeId, $periodId) {
        $count = $this->db->fetch(
            "SELECT COUNT(*) as count FROM payroll_transactions 
             WHERE employee_id = :emp_id AND period_id = :period_id",
            ['emp_id' => $employeeId, 'period_id' => $periodId]
        );
        
        return $count['count'] > 0;
    }
    
    /**
     * Get payroll period details
     */
    private function getPayrollPeriod($periodId) {
        return $this->db->fetch("SELECT * FROM payroll_periods WHERE id = :id", ['id' => $periodId]);
    }
    
    /**
     * Get employees for processing
     */
    private function getEmployeesForProcessing($employeeIds, $period) {
        if (empty($employeeIds)) {
            return $this->db->fetchAll(
                "SELECT * FROM employees 
                 WHERE status = 'active' 
                 AND join_date <= :end_date 
                 AND (leave_date IS NULL OR leave_date >= :start_date)
                 ORDER BY emp_code ASC",
                ['start_date' => $period['start_date'], 'end_date' => $period['end_date']]
            );
        } else {
            $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
            return $this->db->fetchAll(
                "SELECT * FROM employees 
                 WHERE id IN ($placeholders) 
                 AND status = 'active'
                 ORDER BY emp_code ASC",
                $employeeIds
            );
        }
    }
    
    /**
     * Get employee salary structure for a specific date
     */
    private function getEmployeeSalaryStructure($employeeId, $effectiveDate) {
        $sql = "SELECT ss.*, sc.code as component_code, sc.type as component_type, 
                       sc.formula, sc.name as component_name
                FROM salary_structures ss
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
    
    /**
     * Load tax configuration
     */
    private function loadTaxConfiguration() {
        $this->pfSettings = [
            'employee_rate' => PF_RATE_EMPLOYEE,
            'employer_rate' => PF_RATE_EMPLOYER,
            'ceiling' => 15000
        ];
        
        $this->esiSettings = [
            'employee_rate' => ESI_RATE_EMPLOYEE,
            'employer_rate' => ESI_RATE_EMPLOYER,
            'threshold' => ESI_THRESHOLD
        ];
    }
    
    /**
     * Get payroll summary for a period
     */
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
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE pt.amount END) as net_payable
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN employees e ON pt.employee_id = e.id
                WHERE {$conditions}";
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Reprocess payroll for specific employees
     */
    public function reprocessPayroll($periodId, $employeeIds, $options = []) {
        try {
            $this->beginTransaction();
            
            // Delete existing transactions
            foreach ($employeeIds as $employeeId) {
                $this->db->delete('payroll_transactions', 
                    'employee_id = :emp_id AND period_id = :period_id',
                    ['emp_id' => $employeeId, 'period_id' => $periodId]
                );
            }
            
            // Reprocess
            $result = $this->processAdvancedPayroll($periodId, $employeeIds, $options);
            
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}