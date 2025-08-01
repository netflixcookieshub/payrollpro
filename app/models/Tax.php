<?php
/**
 * Tax Model
 */

require_once __DIR__ . '/../core/Model.php';

class Tax extends Model {
    protected $table = 'tax_slabs';
    
    public function calculateTax($annualIncome, $deductions, $financialYear) {
        $taxableIncome = max(0, $annualIncome - $deductions);
        
        $taxSlabs = $this->findAll(
            'financial_year = :fy',
            ['fy' => $financialYear],
            'min_amount ASC'
        );
        
        $totalTax = 0;
        $taxBreakdown = [];
        
        foreach ($taxSlabs as $slab) {
            if ($taxableIncome > $slab['min_amount']) {
                $slabMax = $slab['max_amount'] ?? $taxableIncome;
                $taxableInSlab = min($taxableIncome, $slabMax) - $slab['min_amount'];
                
                if ($taxableInSlab > 0) {
                    $taxInSlab = ($taxableInSlab * $slab['tax_rate']) / 100;
                    $totalTax += $taxInSlab;
                    
                    $taxBreakdown[] = [
                        'slab' => $slab['min_amount'] . ' - ' . ($slab['max_amount'] ?? 'Above'),
                        'rate' => $slab['tax_rate'] . '%',
                        'taxable_amount' => $taxableInSlab,
                        'tax_amount' => $taxInSlab
                    ];
                }
            }
        }
        
        // Add surcharge and cess if applicable
        $surcharge = 0;
        $cess = 0;
        
        if ($taxableIncome > 5000000) {
            $surcharge = $totalTax * 0.10; // 10% surcharge for income > 50 lakhs
        } elseif ($taxableIncome > 1000000) {
            $surcharge = $totalTax * 0.15; // 15% surcharge for income > 10 lakhs
        }
        
        $cess = ($totalTax + $surcharge) * 0.04; // 4% health and education cess
        
        $finalTax = $totalTax + $surcharge + $cess;
        
        return [
            'annual_income' => $annualIncome,
            'deductions' => $deductions,
            'taxable_income' => $taxableIncome,
            'tax_breakdown' => $taxBreakdown,
            'basic_tax' => $totalTax,
            'surcharge' => $surcharge,
            'cess' => $cess,
            'total_tax' => $finalTax,
            'monthly_tds' => $finalTax / 12
        ];
    }
    
    public function saveDeclarations($employeeId, $financialYear, $declarations) {
        try {
            $this->beginTransaction();
            
            // Delete existing declarations for the employee and FY
            $this->db->query(
                "DELETE FROM tax_declarations WHERE employee_id = :emp_id AND financial_year = :fy",
                ['emp_id' => $employeeId, 'fy' => $financialYear]
            );
            
            // Insert new declarations
            foreach ($declarations as $sectionId => $amount) {
                if (!empty($amount) && is_numeric($amount)) {
                    $this->db->insert('tax_declarations', [
                        'employee_id' => $employeeId,
                        'financial_year' => $financialYear,
                        'section_id' => $sectionId,
                        'declared_amount' => $amount,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            $this->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to save declarations'];
        }
    }
    
    public function getEmployeeDeclarations($employeeId, $financialYear) {
        $sql = "SELECT td.*, ds.name as section_name, ds.max_limit
                FROM tax_declarations td
                JOIN declaration_sections ds ON td.section_id = ds.id
                WHERE td.employee_id = :emp_id AND td.financial_year = :fy
                ORDER BY ds.display_order";
        
        return $this->db->fetchAll($sql, [
            'emp_id' => $employeeId,
            'fy' => $financialYear
        ]);
    }
    
    public function getDeclarationSections() {
        return $this->db->fetchAll(
            "SELECT * FROM declaration_sections WHERE status = 'active' ORDER BY display_order ASC"
        );
    }
    
    public function generateForm16Data($employeeId, $financialYear) {
        // Get employee salary data for the financial year
        $salaryData = $this->getEmployeeSalaryData($employeeId, $financialYear);
        
        // Get tax declarations
        $declarations = $this->getEmployeeDeclarations($employeeId, $financialYear);
        
        // Get TDS deducted
        $tdsDeducted = $this->getTDSDeducted($employeeId, $financialYear);
        
        // Calculate tax liability
        $grossSalary = $salaryData['gross_salary'] ?? 0;
        $totalDeductions = array_sum(array_column($declarations, 'declared_amount'));
        $taxCalculation = $this->calculateTax($grossSalary, $totalDeductions, $financialYear);
        
        return [
            'salary_data' => $salaryData,
            'declarations' => $declarations,
            'tds_deducted' => $tdsDeducted,
            'tax_calculation' => $taxCalculation,
            'total_deductions' => $totalDeductions
        ];
    }
    
    private function getEmployeeSalaryData($employeeId, $financialYear) {
        $sql = "SELECT 
                    SUM(CASE WHEN sc.type = 'earning' AND sc.is_taxable = 1 THEN pt.amount ELSE 0 END) as gross_salary,
                    SUM(CASE WHEN sc.code = 'BASIC' THEN pt.amount ELSE 0 END) as basic_salary,
                    SUM(CASE WHEN sc.code = 'HRA' THEN pt.amount ELSE 0 END) as hra,
                    SUM(CASE WHEN sc.code = 'PF' THEN ABS(pt.amount) ELSE 0 END) as pf_deducted,
                    SUM(CASE WHEN sc.code = 'TDS' THEN ABS(pt.amount) ELSE 0 END) as tds_deducted
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pt.employee_id = :emp_id AND pp.financial_year = :fy";
        
        return $this->db->fetch($sql, [
            'emp_id' => $employeeId,
            'fy' => $financialYear
        ]);
    }
    
    private function getTDSDeducted($employeeId, $financialYear) {
        $sql = "SELECT 
                    pp.period_name,
                    ABS(pt.amount) as tds_amount
                FROM payroll_transactions pt
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pt.employee_id = :emp_id 
                AND pp.financial_year = :fy 
                AND sc.code = 'TDS'
                ORDER BY pp.start_date";
        
        return $this->db->fetchAll($sql, [
            'emp_id' => $employeeId,
            'fy' => $financialYear
        ]);
    }
    
    public function calculateHRAExemption($basicSalary, $hraReceived, $rentPaid, $isMetroCity = false) {
        $exemptions = [];
        
        // 50% of basic salary (40% for non-metro)
        $exemptions[] = $basicSalary * ($isMetroCity ? 0.50 : 0.40);
        
        // Actual HRA received
        $exemptions[] = $hraReceived;
        
        // Rent paid minus 10% of basic salary
        $exemptions[] = max(0, $rentPaid - ($basicSalary * 0.10));
        
        return min($exemptions);
    }
    
    public function getEmployeeTaxSummary($employeeId, $financialYear) {
        $salaryData = $this->getEmployeeSalaryData($employeeId, $financialYear);
        $declarations = $this->getEmployeeDeclarations($employeeId, $financialYear);
        
        $grossSalary = $salaryData['gross_salary'] ?? 0;
        $totalDeductions = array_sum(array_column($declarations, 'declared_amount'));
        
        $taxCalculation = $this->calculateTax($grossSalary, $totalDeductions, $financialYear);
        
        return [
            'gross_salary' => $grossSalary,
            'total_deductions' => $totalDeductions,
            'taxable_income' => $taxCalculation['taxable_income'],
            'tax_liability' => $taxCalculation['total_tax'],
            'tds_deducted' => $salaryData['tds_deducted'] ?? 0,
            'tax_payable' => max(0, $taxCalculation['total_tax'] - ($salaryData['tds_deducted'] ?? 0))
        ];
    }
}