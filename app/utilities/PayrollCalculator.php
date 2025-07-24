<?php
/**
 * Payroll Calculation Utilities
 */

class PayrollCalculator {
    
    /**
     * Calculate PF contribution
     */
    public static function calculatePF($basicSalary, $employeeRate = 12, $employerRate = 12) {
        $pfCeiling = 15000; // PF ceiling as per current rules
        $pfBasic = min($basicSalary, $pfCeiling);
        
        return [
            'employee_contribution' => round(($pfBasic * $employeeRate) / 100, 2),
            'employer_contribution' => round(($pfBasic * $employerRate) / 100, 2),
            'pf_basic' => $pfBasic
        ];
    }
    
    /**
     * Calculate ESI contribution
     */
    public static function calculateESI($grossSalary, $employeeRate = 0.75, $employerRate = 3.25, $threshold = 21000) {
        if ($grossSalary > $threshold) {
            return [
                'employee_contribution' => 0,
                'employer_contribution' => 0,
                'applicable' => false
            ];
        }
        
        return [
            'employee_contribution' => round(($grossSalary * $employeeRate) / 100, 2),
            'employer_contribution' => round(($grossSalary * $employerRate) / 100, 2),
            'applicable' => true
        ];
    }
    
    /**
     * Calculate Professional Tax
     */
    public static function calculateProfessionalTax($grossSalary, $state = 'Maharashtra') {
        // Professional tax varies by state - this is a simplified calculation
        $ptSlabs = [
            'Maharashtra' => [
                ['min' => 0, 'max' => 5000, 'tax' => 0],
                ['min' => 5001, 'max' => 10000, 'tax' => 150],
                ['min' => 10001, 'max' => 15000, 'tax' => 300],
                ['min' => 15001, 'max' => 25000, 'tax' => 500],
                ['min' => 25001, 'max' => null, 'tax' => 200]
            ]
        ];
        
        $slabs = $ptSlabs[$state] ?? $ptSlabs['Maharashtra'];
        
        foreach ($slabs as $slab) {
            if ($grossSalary >= $slab['min'] && ($slab['max'] === null || $grossSalary <= $slab['max'])) {
                return $slab['tax'];
            }
        }
        
        return 0;
    }
    
    /**
     * Calculate HRA exemption
     */
    public static function calculateHRAExemption($basicSalary, $hraReceived, $rentPaid, $isMetroCity = false) {
        $exemptions = [];
        
        // 50% of basic salary (40% for non-metro)
        $exemptions[] = $basicSalary * ($isMetroCity ? 0.50 : 0.40);
        
        // Actual HRA received
        $exemptions[] = $hraReceived;
        
        // Rent paid minus 10% of basic salary
        $exemptions[] = max(0, $rentPaid - ($basicSalary * 0.10));
        
        return min($exemptions);
    }
    
    /**
     * Calculate income tax using tax slabs
     */
    public static function calculateIncomeTax($annualIncome, $deductions, $taxSlabs) {
        $taxableIncome = max(0, $annualIncome - $deductions);
        $totalTax = 0;
        
        foreach ($taxSlabs as $slab) {
            if ($taxableIncome > $slab['min_amount']) {
                $slabMax = $slab['max_amount'] ?? $taxableIncome;
                $taxableInSlab = min($taxableIncome, $slabMax) - $slab['min_amount'];
                
                if ($taxableInSlab > 0) {
                    $totalTax += ($taxableInSlab * $slab['tax_rate']) / 100;
                }
            }
        }
        
        // Add surcharge if applicable
        $surcharge = 0;
        if ($taxableIncome > 5000000) {
            $surcharge = $totalTax * 0.10; // 10% surcharge
        } elseif ($taxableIncome > 1000000) {
            $surcharge = $totalTax * 0.15; // 15% surcharge
        }
        
        // Add health and education cess
        $cess = ($totalTax + $surcharge) * 0.04; // 4% cess
        
        return [
            'taxable_income' => $taxableIncome,
            'basic_tax' => $totalTax,
            'surcharge' => $surcharge,
            'cess' => $cess,
            'total_tax' => $totalTax + $surcharge + $cess
        ];
    }
    
    /**
     * Calculate loan EMI
     */
    public static function calculateLoanEMI($principal, $rate, $tenure) {
        if ($rate == 0) {
            return $principal / $tenure;
        }
        
        $monthlyRate = $rate / (12 * 100);
        $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $tenure)) / 
               (pow(1 + $monthlyRate, $tenure) - 1);
        
        return round($emi, 2);
    }
    
    /**
     * Calculate pro-rata salary
     */
    public static function calculateProRata($amount, $totalDays, $actualDays) {
        if ($totalDays <= 0) {
            return 0;
        }
        
        return round(($amount / $totalDays) * $actualDays, 2);
    }
    
    /**
     * Calculate working days between dates
     */
    public static function calculateWorkingDays($startDate, $endDate, $holidays = []) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $workingDays = 0;
        
        while ($start <= $end) {
            $dayOfWeek = $start->format('N'); // 1 = Monday, 7 = Sunday
            $dateString = $start->format('Y-m-d');
            
            // Skip weekends (Saturday = 6, Sunday = 7)
            if ($dayOfWeek < 6) {
                // Skip holidays
                if (!in_array($dateString, $holidays)) {
                    $workingDays++;
                }
            }
            
            $start->modify('+1 day');
        }
        
        return $workingDays;
    }
    
    /**
     * Evaluate mathematical formula safely
     */
    public static function evaluateFormula($formula, $variables = []) {
        // Replace variables with values
        foreach ($variables as $var => $value) {
            $formula = str_replace($var, $value, $formula);
        }
        
        // Remove any non-mathematical characters for security
        $formula = preg_replace('/[^0-9+\-*\/.() ]/', '', $formula);
        
        try {
            // Use eval carefully - in production, consider using a math expression parser
            $result = eval("return $formula;");
            return is_numeric($result) ? round($result, 2) : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Generate payslip number
     */
    public static function generatePayslipNumber($employeeCode, $periodId) {
        return 'PS-' . $employeeCode . '-' . str_pad($periodId, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Convert number to words (for payslips)
     */
    public static function numberToWords($number) {
        $ones = [
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen'
        ];
        
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];
        
        if ($number < 20) {
            return $ones[$number];
        } elseif ($number < 100) {
            return $tens[intval($number / 10)] . ($number % 10 != 0 ? ' ' . $ones[$number % 10] : '');
        } elseif ($number < 1000) {
            return $ones[intval($number / 100)] . ' Hundred' . ($number % 100 != 0 ? ' ' . self::numberToWords($number % 100) : '');
        } elseif ($number < 100000) {
            return self::numberToWords(intval($number / 1000)) . ' Thousand' . ($number % 1000 != 0 ? ' ' . self::numberToWords($number % 1000) : '');
        } elseif ($number < 10000000) {
            return self::numberToWords(intval($number / 100000)) . ' Lakh' . ($number % 100000 != 0 ? ' ' . self::numberToWords($number % 100000) : '');
        } else {
            return self::numberToWords(intval($number / 10000000)) . ' Crore' . ($number % 10000000 != 0 ? ' ' . self::numberToWords($number % 10000000) : '');
        }
    }
}