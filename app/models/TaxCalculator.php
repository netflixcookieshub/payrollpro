<?php
/**
 * Advanced Tax Calculator
 * Handles complex TDS calculations with investment declarations
 */

require_once __DIR__ . '/../core/Model.php';

class TaxCalculator extends Model {
    
    private $taxSlabs = [];
    private $standardDeduction = 50000; // Standard deduction for FY 2024-25
    
    /**
     * Calculate TDS for an employee
     */
    public function calculateEmployeeTDS($employee, $monthlyGross, $period) {
        // Get annual projections
        $annualGross = $this->projectAnnualIncome($employee['id'], $monthlyGross, $period);
        
        // Get investment declarations
        $investments = $this->getInvestmentDeclarations($employee['id'], $period['financial_year']);
        
        // Calculate taxable income
        $taxableIncome = $this->calculateTaxableIncome($annualGross, $investments);
        
        // Calculate annual tax liability
        $annualTax = $this->calculateAnnualTax($taxableIncome, $period['financial_year']);
        
        // Get previous TDS paid
        $previousTDS = $this->getPreviousTDS($employee['id'], $period['financial_year']);
        
        // Calculate remaining months
        $remainingMonths = $this->getRemainingMonths($period);
        
        // Calculate monthly TDS
        $monthlyTDS = 0;
        if ($remainingMonths > 0) {
            $remainingTax = $annualTax - $previousTDS;
            $monthlyTDS = max(0, $remainingTax / $remainingMonths);
        }
        
        return [
            'monthly_tds' => round($monthlyTDS, 2),
            'annual_gross' => $annualGross,
            'taxable_income' => $taxableIncome,
            'annual_tax' => $annualTax,
            'previous_tds' => $previousTDS,
            'remaining_tax' => $annualTax - $previousTDS
        ];
    }
    
    /**
     * Project annual income based on current salary
     */
    private function projectAnnualIncome($employeeId, $monthlyGross, $period) {
        // Get salary history for the financial year
        $sql = "SELECT SUM(CASE WHEN sc.type = 'earning' AND sc.is_taxable = 1 THEN pt.amount ELSE 0 END) as taxable_earnings
                FROM payroll_transactions pt
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pt.employee_id = :emp_id 
                AND pp.financial_year = :fy 
                AND pp.end_date < :current_period_start";
        
        $previousEarnings = $this->db->fetch($sql, [
            'emp_id' => $employeeId,
            'fy' => $period['financial_year'],
            'current_period_start' => $period['start_date']
        ]);
        
        $earnedSoFar = $previousEarnings['taxable_earnings'] ?? 0;
        $remainingMonths = $this->getRemainingMonths($period);
        
        // Project annual income
        $projectedAnnual = $earnedSoFar + ($monthlyGross * ($remainingMonths + 1));
        
        return $projectedAnnual;
    }
    
    /**
     * Get investment declarations for tax calculation
     */
    private function getInvestmentDeclarations($employeeId, $financialYear) {
        $sql = "SELECT 
                    SUM(CASE WHEN section = '80C' THEN amount ELSE 0 END) as section_80c,
                    SUM(CASE WHEN section = '80D' THEN amount ELSE 0 END) as section_80d,
                    SUM(CASE WHEN section = 'HRA' THEN amount ELSE 0 END) as hra_exemption,
                    SUM(amount) as total_deductions
                FROM investment_declarations 
                WHERE employee_id = :emp_id 
                AND financial_year = :fy 
                AND status = 'approved'";
        
        $result = $this->db->fetch($sql, ['emp_id' => $employeeId, 'fy' => $financialYear]);
        
        return [
            'section_80c' => min($result['section_80c'] ?? 0, 150000), // 80C limit
            'section_80d' => min($result['section_80d'] ?? 0, 25000),  // 80D limit
            'hra_exemption' => $result['hra_exemption'] ?? 0,
            'standard_deduction' => $this->standardDeduction
        ];
    }
    
    /**
     * Calculate taxable income after deductions
     */
    private function calculateTaxableIncome($grossIncome, $investments) {
        $taxableIncome = $grossIncome;
        
        // Apply standard deduction
        $taxableIncome -= $investments['standard_deduction'];
        
        // Apply investment deductions
        $taxableIncome -= $investments['section_80c'];
        $taxableIncome -= $investments['section_80d'];
        $taxableIncome -= $investments['hra_exemption'];
        
        return max(0, $taxableIncome);
    }
    
    /**
     * Calculate annual tax based on tax slabs
     */
    private function calculateAnnualTax($taxableIncome, $financialYear) {
        $taxSlabs = $this->getTaxSlabs($financialYear);
        $totalTax = 0;
        
        foreach ($taxSlabs as $slab) {
            if ($taxableIncome > $slab['min_amount']) {
                $maxAmount = $slab['max_amount'] ?? $taxableIncome;
                $slabIncome = min($taxableIncome, $maxAmount) - $slab['min_amount'];
                
                if ($slabIncome > 0) {
                    $slabTax = ($slabIncome * $slab['tax_rate']) / 100;
                    
                    // Apply surcharge if applicable
                    if ($slab['surcharge_rate'] > 0) {
                        $slabTax += ($slabTax * $slab['surcharge_rate']) / 100;
                    }
                    
                    $totalTax += $slabTax;
                }
            }
        }
        
        // Apply health and education cess (4%)
        $totalTax += ($totalTax * 4) / 100;
        
        return $totalTax;
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
     * Get previous TDS paid in financial year
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
     * Get remaining months in financial year
     */
    private function getRemainingMonths($period) {
        $periodEnd = new DateTime($period['end_date']);
        $fyEnd = new DateTime(explode('-', $period['financial_year'])[1] . '-03-31');
        
        if ($periodEnd >= $fyEnd) {
            return 0;
        }
        
        $diff = $periodEnd->diff($fyEnd);
        return $diff->m + ($diff->y * 12);
    }
    
    /**
     * Calculate HRA exemption
     */
    public function calculateHRAExemption($employee, $basicSalary, $hraReceived, $rentPaid) {
        // HRA exemption is minimum of:
        // 1. Actual HRA received
        // 2. 50% of basic salary (40% for non-metro cities)
        // 3. Rent paid minus 10% of basic salary
        
        $cityType = $this->getEmployeeCityType($employee['id']);
        $hraPercentage = ($cityType === 'metro') ? 50 : 40;
        
        $exemption1 = $hraReceived;
        $exemption2 = ($basicSalary * $hraPercentage) / 100;
        $exemption3 = max(0, $rentPaid - (($basicSalary * 10) / 100));
        
        return min($exemption1, $exemption2, $exemption3);
    }
    
    /**
     * Get employee city type for HRA calculation
     */
    private function getEmployeeCityType($employeeId) {
        // This would be based on employee's work location
        // For now, returning 'metro' as default
        return 'metro';
    }
    
    /**
     * Generate TDS certificate data
     */
    public function generateTDSCertificate($employeeId, $financialYear) {
        $sql = "SELECT 
                    e.emp_code, e.first_name, e.last_name, e.pan_number,
                    SUM(CASE WHEN sc.type = 'earning' AND sc.is_taxable = 1 THEN pt.amount ELSE 0 END) as gross_salary,
                    SUM(CASE WHEN sc.code = 'TDS' THEN ABS(pt.amount) ELSE 0 END) as tds_deducted,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as pf_deducted
                FROM employees e
                JOIN payroll_transactions pt ON e.id = pt.employee_id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE e.id = :emp_id 
                AND pp.financial_year = :fy
                GROUP BY e.id";
        
        return $this->db->fetch($sql, ['emp_id' => $employeeId, 'fy' => $financialYear]);
    }
}