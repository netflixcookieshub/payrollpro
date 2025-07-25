<?php
/**
 * Advanced Payroll Calculator
 * Handles complex payroll calculations with all business rules
 */

require_once __DIR__ . '/../core/Model.php';

class PayrollCalculator extends Model {
    
    private $formulaEngine;
    private $taxCalculator;
    
    public function __construct($database) {
        parent::__construct($database);
        $this->formulaEngine = new FormulaEngine();
        $this->taxCalculator = new TaxCalculator($database);
    }
    
    /**
     * Calculate complete salary for an employee
     */
    public function calculateEmployeeSalary($employee, $period, $options = []) {
        try {
            // Get salary structure
            $salaryStructure = $this->getSalaryStructure($employee['id'], $period['start_date']);
            
            if (empty($salaryStructure)) {
                throw new Exception('No salary structure found for employee');
            }
            
            // Calculate attendance and LOP
            $attendance = $this->calculateAttendanceDetails($employee['id'], $period);
            
            // Calculate pro-rata factor
            $proRataFactor = $this->calculateProRataFactor($employee, $period);
            
            // Process earnings with formulas
            $earnings = $this->calculateEarnings($employee, $period, $salaryStructure, $attendance, $proRataFactor);
            
            // Process deductions
            $deductions = $this->calculateDeductions($employee, $period, $earnings, $attendance, $options);
            
            // Process arrears if enabled
            $arrears = [];
            if ($options['include_arrears'] ?? false) {
                $arrears = $this->calculateArrears($employee['id'], $period['id']);
            }
            
            // Process variable pay if enabled
            $variablePay = [];
            if ($options['include_variable_pay'] ?? false) {
                $variablePay = $this->calculateVariablePay($employee['id'], $period['id']);
            }
            
            // Calculate totals
            $totalEarnings = array_sum(array_column($earnings, 'amount')) + 
                           array_sum(array_column($arrears, 'amount')) + 
                           array_sum(array_column($variablePay, 'amount'));
            
            $totalDeductions = array_sum(array_column($deductions, 'amount'));
            $netSalary = $totalEarnings - $totalDeductions;
            
            return [
                'success' => true,
                'earnings' => $earnings,
                'deductions' => $deductions,
                'arrears' => $arrears,
                'variable_pay' => $variablePay,
                'totals' => [
                    'earnings' => $totalEarnings,
                    'deductions' => $totalDeductions,
                    'net_salary' => $netSalary
                ],
                'attendance' => $attendance,
                'pro_rata_factor' => $proRataFactor,
                'calculation_details' => [
                    'period' => $period,
                    'employee' => $employee,
                    'options' => $options
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'employee_code' => $employee['emp_code'] ?? 'Unknown'
            ];
        }
    }
    
    /**
     * Calculate earnings with formula support
     */
    private function calculateEarnings($employee, $period, $salaryStructure, $attendance, $proRataFactor) {
        $earnings = [];
        $componentValues = [];
        
        // First pass: Calculate non-formula components
        foreach ($salaryStructure as $component) {
            if ($component['component_type'] === 'earning' && empty($component['formula'])) {
                $amount = $component['amount'] * $proRataFactor;
                
                // Apply LOP for earnings
                if ($attendance['lop_days'] > 0 && $component['is_taxable']) {
                    $lopDeduction = ($amount / $attendance['working_days']) * $attendance['lop_days'];
                    $amount = max(0, $amount - $lopDeduction);
                }
                
                $componentValues[$component['component_code']] = $amount;
                
                $earnings[] = [
                    'component_id' => $component['component_id'],
                    'name' => $component['component_name'],
                    'code' => $component['component_code'],
                    'amount' => round($amount, 2),
                    'original_amount' => $component['amount'],
                    'calculation_note' => $this->generateCalculationNote($proRataFactor, $attendance, $component)
                ];
            }
        }
        
        // Second pass: Calculate formula-based components
        foreach ($salaryStructure as $component) {
            if ($component['component_type'] === 'earning' && !empty($component['formula'])) {
                $variables = array_merge($componentValues, [
                    'GROSS' => array_sum($componentValues),
                    'DAYS_IN_MONTH' => date('t', strtotime($period['start_date'])),
                    'WORKING_DAYS' => $attendance['working_days'],
                    'PRESENT_DAYS' => $attendance['present_days'],
                    'LOP_DAYS' => $attendance['lop_days']
                ]);
                
                $amount = $this->formulaEngine->evaluate($component['formula'], $variables);
                $amount = $amount * $proRataFactor;
                
                // Apply LOP for formula-based earnings
                if ($attendance['lop_days'] > 0 && $component['is_taxable']) {
                    $lopDeduction = ($amount / $attendance['working_days']) * $attendance['lop_days'];
                    $amount = max(0, $amount - $lopDeduction);
                }
                
                $componentValues[$component['component_code']] = $amount;
                
                $earnings[] = [
                    'component_id' => $component['component_id'],
                    'name' => $component['component_name'],
                    'code' => $component['component_code'],
                    'amount' => round($amount, 2),
                    'formula' => $component['formula'],
                    'calculation_note' => "Formula: {$component['formula']}"
                ];
            }
        }
        
        return $earnings;
    }
    
    /**
     * Calculate deductions including statutory and loans
     */
    private function calculateDeductions($employee, $period, $earnings, $attendance, $options) {
        $deductions = [];
        $grossSalary = array_sum(array_column($earnings, 'amount'));
        
        // Get deduction components from salary structure
        $salaryStructure = $this->getSalaryStructure($employee['id'], $period['start_date']);
        
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
                            $tdsResult = $this->taxCalculator->calculateEmployeeTDS($employee, $grossSalary, $period);
                            $amount = $tdsResult['monthly_tds'];
                        }
                        break;
                    default:
                        // Formula-based or fixed deductions
                        if (!empty($component['formula'])) {
                            $variables = $this->getEarningsVariables($earnings);
                            $amount = $this->formulaEngine->evaluate($component['formula'], $variables);
                        } else {
                            $amount = $component['amount'];
                        }
                }
                
                if ($amount > 0) {
                    $deductions[] = [
                        'component_id' => $component['component_id'],
                        'name' => $component['component_name'],
                        'code' => $component['component_code'],
                        'amount' => round($amount, 2),
                        'calculation_note' => $this->getDeductionNote($component['component_code'], $amount, $grossSalary)
                    ];
                }
            }
        }
        
        // Process loan EMIs
        if ($options['process_loans'] ?? true) {
            $loanDeductions = $this->calculateLoanEMIs($employee['id'], $period);
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
                "SELECT is_pf_applicable FROM salary_components WHERE id = :id",
                ['id' => $earning['component_id']]
            );
            
            if ($component && $component['is_pf_applicable']) {
                $pfBasic += $earning['amount'];
            }
        }
        
        // PF ceiling (₹15,000 as of 2024)
        $pfCeiling = 15000;
        $pfBasic = min($pfBasic, $pfCeiling);
        
        return $pfBasic * 0.12; // 12% employee contribution
    }
    
    /**
     * Calculate ESI contribution
     */
    private function calculateESI($employee, $earnings) {
        $grossSalary = array_sum(array_column($earnings, 'amount'));
        
        // ESI threshold (₹21,000 as of 2024)
        if ($grossSalary <= 21000) {
            return $grossSalary * 0.0075; // 0.75% employee contribution
        }
        
        return 0;
    }
    
    /**
     * Calculate Professional Tax
     */
    private function calculatePT($grossSalary) {
        // Simplified PT calculation (can be made state-specific)
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
     * Calculate loan EMI deductions
     */
    private function calculateLoanEMIs($employeeId, $period) {
        $loans = $this->getActiveLoanEMIs($employeeId, $period);
        $loanDeductions = [];
        
        foreach ($loans as $loan) {
            if ($this->isEMIDueInPeriod($loan, $period)) {
                $loanDeductions[] = [
                    'component_id' => $this->getLoanEMIComponentId(),
                    'name' => 'Loan EMI - ' . $loan['loan_type_name'],
                    'code' => 'LOAN_EMI',
                    'amount' => $loan['emi_amount'],
                    'calculation_note' => "EMI for {$loan['loan_type_name']} (Outstanding: ₹" . number_format($loan['outstanding_amount'], 2) . ")"
                ];
                
                // Update loan outstanding
                $this->updateLoanOutstanding($loan['id'], $loan['emi_amount']);
            }
        }
        
        return $loanDeductions;
    }
    
    /**
     * Calculate arrears
     */
    private function calculateArrears($employeeId, $periodId) {
        $arrears = $this->db->fetchAll(
            "SELECT ea.*, sc.name as component_name, sc.code as component_code
             FROM employee_arrears ea
             JOIN salary_components sc ON ea.component_id = sc.id
             WHERE ea.employee_id = :emp_id AND ea.status = 'pending' 
             AND ea.effective_period_id = :period_id",
            ['emp_id' => $employeeId, 'period_id' => $periodId]
        );
        
        $arrearsData = [];
        foreach ($arrears as $arrear) {
            $arrearsData[] = [
                'component_id' => $arrear['component_id'],
                'name' => 'Arrears - ' . $arrear['component_name'],
                'code' => $arrear['component_code'],
                'amount' => $arrear['amount'],
                'calculation_note' => $arrear['description']
            ];
            
            // Mark as processed
            $this->db->update('employee_arrears', 
                ['status' => 'processed'], 
                'id = :id', 
                ['id' => $arrear['id']]
            );
        }
        
        return $arrearsData;
    }
    
    /**
     * Calculate variable pay
     */
    private function calculateVariablePay($employeeId, $periodId) {
        $variablePay = $this->db->fetchAll(
            "SELECT evp.*, sc.name as component_name, sc.code as component_code
             FROM employee_variable_pay evp
             JOIN salary_components sc ON evp.component_id = sc.id
             WHERE evp.employee_id = :emp_id AND evp.period_id = :period_id 
             AND evp.status = 'approved'",
            ['emp_id' => $employeeId, 'period_id' => $periodId]
        );
        
        $variableData = [];
        foreach ($variablePay as $variable) {
            $variableData[] = [
                'component_id' => $variable['component_id'],
                'name' => 'Variable - ' . $variable['component_name'],
                'code' => $variable['component_code'],
                'amount' => $variable['amount'],
                'calculation_note' => $variable['description']
            ];
        }
        
        return $variableData;
    }
    
    /**
     * Calculate attendance details with LOP
     */
    private function calculateAttendanceDetails($employeeId, $period) {
        $sql = "SELECT 
                    COUNT(*) as marked_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'half_day' THEN 0.5 ELSE 0 END) as half_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    SUM(overtime_hours) as total_overtime_hours
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
            'effective_days' => $effectivePresentDays,
            'overtime_hours' => $attendance['total_overtime_hours'] ?? 0
        ];
    }
    
    /**
     * Calculate pro-rata factor for joining/leaving employees
     */
    private function calculateProRataFactor($employee, $period) {
        $periodStart = new DateTime($period['start_date']);
        $periodEnd = new DateTime($period['end_date']);
        $joinDate = new DateTime($employee['join_date']);
        
        // Determine actual start date
        $actualStart = max($joinDate, $periodStart);
        
        // Determine actual end date
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
     * Get salary structure for employee
     */
    private function getSalaryStructure($employeeId, $effectiveDate) {
        $sql = "SELECT ss.*, sc.name as component_name, sc.code as component_code, 
                       sc.type as component_type, sc.formula, sc.display_order,
                       sc.is_mandatory, sc.is_taxable, sc.is_pf_applicable, sc.is_esi_applicable
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
     * Get active loan EMIs
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
        
        return $emiDate <= $periodEnd;
    }
    
    /**
     * Update loan outstanding amount
     */
    private function updateLoanOutstanding($loanId, $emiAmount) {
        $this->db->query(
            "UPDATE employee_loans 
             SET outstanding_amount = GREATEST(0, outstanding_amount - :emi_amount),
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
     * Generate calculation note
     */
    private function generateCalculationNote($proRataFactor, $attendance, $component) {
        $notes = [];
        
        if ($proRataFactor < 1) {
            $notes[] = "Pro-rata: " . round($proRataFactor * 100, 2) . "%";
        }
        
        if ($attendance['lop_days'] > 0 && $component['is_taxable']) {
            $notes[] = "LOP: {$attendance['lop_days']} days";
        }
        
        return implode(', ', $notes);
    }
    
    /**
     * Get deduction calculation note
     */
    private function getDeductionNote($componentCode, $amount, $grossSalary) {
        switch ($componentCode) {
            case 'PF':
                return "12% of PF applicable earnings (max ₹15,000)";
            case 'ESI':
                return "0.75% of gross salary (applicable if ≤ ₹21,000)";
            case 'PT':
                return "Professional Tax as per state rules";
            case 'TDS':
                return "Income Tax as per IT slabs";
            default:
                return "Calculated as per component rules";
        }
    }
    
    /**
     * Get earnings variables for formula calculation
     */
    private function getEarningsVariables($earnings) {
        $variables = [];
        
        foreach ($earnings as $earning) {
            $variables[$earning['code']] = $earning['amount'];
        }
        
        $variables['GROSS'] = array_sum(array_column($earnings, 'amount'));
        
        return $variables;
    }
}