<?php
/**
 * Formula Engine for Salary Calculations
 * Handles complex formula evaluation with validation
 */

class FormulaEngine {
    
    private $allowedFunctions = ['round', 'ceil', 'floor', 'abs', 'min', 'max'];
    private $allowedOperators = ['+', '-', '*', '/', '(', ')', '%'];
    
    /**
     * Evaluate a salary formula safely
     */
    public function evaluate($formula, $variables = [], $context = []) {
        if (empty($formula)) {
            return 0;
        }
        
        try {
            // Sanitize and prepare formula
            $sanitizedFormula = $this->sanitizeFormula($formula);
            
            // Replace variables with values
            $evaluatedFormula = $this->replaceVariables($sanitizedFormula, $variables);
            
            // Add context variables
            $evaluatedFormula = $this->addContextVariables($evaluatedFormula, $context);
            
            // Validate formula syntax
            if (!$this->validateFormula($evaluatedFormula)) {
                throw new Exception("Invalid formula syntax");
            }
            
            // Evaluate the formula
            $result = $this->safeEvaluate($evaluatedFormula);
            
            return round($result, 2);
        } catch (Exception $e) {
            error_log("Formula evaluation error: {$formula} - " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Sanitize formula to prevent code injection
     */
    private function sanitizeFormula($formula) {
        // Remove any potentially dangerous characters
        $formula = preg_replace('/[^A-Za-z0-9+\-*\/.()%_ ]/', '', $formula);
        
        // Convert to uppercase for consistency
        return strtoupper(trim($formula));
    }
    
    /**
     * Replace variable names with actual values
     */
    private function replaceVariables($formula, $variables) {
        foreach ($variables as $variable => $value) {
            $variable = strtoupper($variable);
            $formula = str_replace($variable, $value, $formula);
        }
        
        return $formula;
    }
    
    /**
     * Add context variables like DAYS_IN_MONTH, WORKING_DAYS, etc.
     */
    private function addContextVariables($formula, $context) {
        $contextVars = [
            'DAYS_IN_MONTH' => $context['days_in_month'] ?? date('t'),
            'WORKING_DAYS' => $context['working_days'] ?? 22,
            'PRESENT_DAYS' => $context['present_days'] ?? 22,
            'LOP_DAYS' => $context['lop_days'] ?? 0,
            'OVERTIME_HOURS' => $context['overtime_hours'] ?? 0
        ];
        
        foreach ($contextVars as $var => $value) {
            $formula = str_replace($var, $value, $formula);
        }
        
        return $formula;
    }
    
    /**
     * Validate formula syntax
     */
    private function validateFormula($formula) {
        // Check for balanced parentheses
        $openCount = substr_count($formula, '(');
        $closeCount = substr_count($formula, ')');
        
        if ($openCount !== $closeCount) {
            return false;
        }
        
        // Check for division by zero
        if (preg_match('/\/\s*0(?!\d)/', $formula)) {
            return false;
        }
        
        // Check for valid characters only
        if (!preg_match('/^[0-9+\-*\/.() ]+$/', $formula)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Safely evaluate mathematical expression
     */
    private function safeEvaluate($expression) {
        // Final sanitization
        $expression = preg_replace('/[^0-9+\-*\/.() ]/', '', $expression);
        
        // Check for empty expression
        if (empty(trim($expression))) {
            return 0;
        }
        
        // Use eval with extreme caution - only for mathematical expressions
        try {
            $result = eval("return $expression;");
            
            if (!is_numeric($result)) {
                return 0;
            }
            
            return $result;
        } catch (ParseError $e) {
            throw new Exception("Formula parse error: " . $e->getMessage());
        } catch (Error $e) {
            throw new Exception("Formula execution error: " . $e->getMessage());
        }
    }
    
    /**
     * Get available variables for formula building
     */
    public function getAvailableVariables() {
        return [
            'BASIC' => 'Basic Salary',
            'HRA' => 'House Rent Allowance',
            'TA' => 'Transport Allowance',
            'MA' => 'Medical Allowance',
            'SA' => 'Special Allowance',
            'GROSS' => 'Gross Salary (Sum of all earnings)',
            'DAYS_IN_MONTH' => 'Total days in month',
            'WORKING_DAYS' => 'Working days in month',
            'PRESENT_DAYS' => 'Employee present days',
            'LOP_DAYS' => 'Loss of Pay days',
            'OVERTIME_HOURS' => 'Overtime hours worked'
        ];
    }
    
    /**
     * Get available functions for formula building
     */
    public function getAvailableFunctions() {
        return [
            'ROUND(value, decimals)' => 'Round to specified decimal places',
            'CEIL(value)' => 'Round up to nearest integer',
            'FLOOR(value)' => 'Round down to nearest integer',
            'ABS(value)' => 'Absolute value',
            'MIN(value1, value2)' => 'Minimum of two values',
            'MAX(value1, value2)' => 'Maximum of two values'
        ];
    }
    
    /**
     * Validate formula before saving
     */
    public function validateFormulaForSaving($formula, $availableComponents = []) {
        if (empty($formula)) {
            return ['valid' => true];
        }
        
        try {
            // Check basic syntax
            if (!$this->validateFormula($this->sanitizeFormula($formula))) {
                return ['valid' => false, 'message' => 'Invalid formula syntax'];
            }
            
            // Check if all referenced components exist
            $referencedComponents = $this->extractComponentReferences($formula);
            $missingComponents = array_diff($referencedComponents, $availableComponents);
            
            if (!empty($missingComponents)) {
                return [
                    'valid' => false, 
                    'message' => 'Referenced components not found: ' . implode(', ', $missingComponents)
                ];
            }
            
            // Test evaluation with dummy values
            $testVariables = [];
            foreach ($availableComponents as $component) {
                $testVariables[$component] = 1000; // Dummy value
            }
            
            $testResult = $this->evaluate($formula, $testVariables);
            
            if (!is_numeric($testResult)) {
                return ['valid' => false, 'message' => 'Formula does not evaluate to a number'];
            }
            
            return ['valid' => true];
        } catch (Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Extract component references from formula
     */
    private function extractComponentReferences($formula) {
        preg_match_all('/[A-Z_]+/', strtoupper($formula), $matches);
        
        $components = [];
        $systemVariables = ['GROSS', 'DAYS_IN_MONTH', 'WORKING_DAYS', 'PRESENT_DAYS', 'LOP_DAYS', 'OVERTIME_HOURS'];
        
        foreach ($matches[0] as $match) {
            if (!in_array($match, $systemVariables) && !in_array($match, $this->allowedFunctions)) {
                $components[] = $match;
            }
        }
        
        return array_unique($components);
    }
}