<?php
/**
 * Loan Model
 */

require_once __DIR__ . '/../core/Model.php';

class Loan extends Model {
    protected $table = 'employee_loans';
    
    public function getLoansWithDetails($status = 'active', $page = 1) {
        $conditions = "el.status = :status";
        $params = ['status' => $status];
        
        $sql = "SELECT el.*, 
                       e.emp_code, e.first_name, e.last_name,
                       lt.name as loan_type_name,
                       d.name as department_name
                FROM {$this->table} el
                JOIN employees e ON el.employee_id = e.id
                JOIN loan_types lt ON el.loan_type_id = lt.id
                JOIN departments d ON e.department_id = d.id
                WHERE {$conditions}
                ORDER BY el.disbursed_date DESC";
        
        return $this->paginate($page, RECORDS_PER_PAGE, $conditions, $params, 'el.disbursed_date DESC');
    }
    
    public function getLoanWithDetails($id) {
        $sql = "SELECT el.*, 
                       e.emp_code, e.first_name, e.last_name, e.email,
                       lt.name as loan_type_name, lt.interest_rate as type_interest_rate,
                       d.name as department_name
                FROM {$this->table} el
                JOIN employees e ON el.employee_id = e.id
                JOIN loan_types lt ON el.loan_type_id = lt.id
                JOIN departments d ON e.department_id = d.id
                WHERE el.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    public function createLoan($data) {
        $rules = [
            'employee_id' => ['required' => true, 'type' => 'numeric'],
            'loan_type_id' => ['required' => true, 'type' => 'numeric'],
            'loan_amount' => ['required' => true, 'type' => 'numeric'],
            'tenure_months' => ['required' => true, 'type' => 'numeric'],
            'disbursed_date' => ['required' => true, 'type' => 'date']
        ];
        
        $errors = $this->validateData($data, $rules);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Calculate EMI
        $loanAmount = floatval($data['loan_amount']);
        $interestRate = floatval($data['interest_rate'] ?? 0);
        $tenureMonths = intval($data['tenure_months']);
        
        $emiAmount = $this->calculateEMI($loanAmount, $interestRate, $tenureMonths);
        
        try {
            $loanData = [
                'employee_id' => $data['employee_id'],
                'loan_type_id' => $data['loan_type_id'],
                'loan_amount' => $loanAmount,
                'interest_rate' => $interestRate,
                'tenure_months' => $tenureMonths,
                'emi_amount' => $emiAmount,
                'disbursed_date' => $data['disbursed_date'],
                'first_emi_date' => $data['first_emi_date'] ?? date('Y-m-d', strtotime($data['disbursed_date'] . ' +1 month')),
                'outstanding_amount' => $loanAmount,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->create($loanData);
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create loan'];
        }
    }
    
    public function recordPayment($loanId, $data) {
        $rules = [
            'payment_amount' => ['required' => true, 'type' => 'numeric'],
            'payment_date' => ['required' => true, 'type' => 'date']
        ];
        
        $errors = $this->validateData($data, $rules);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $loan = $this->findById($loanId);
        if (!$loan) {
            return ['success' => false, 'message' => 'Loan not found'];
        }
        
        $paymentAmount = floatval($data['payment_amount']);
        $newOutstanding = $loan['outstanding_amount'] - $paymentAmount;
        
        if ($newOutstanding < 0) {
            return ['success' => false, 'message' => 'Payment amount exceeds outstanding balance'];
        }
        
        try {
            $this->beginTransaction();
            
            // Update loan outstanding amount
            $this->update($loanId, [
                'outstanding_amount' => $newOutstanding,
                'status' => $newOutstanding <= 0 ? 'closed' : 'active'
            ]);
            
            // Record payment in loan payments table (if exists)
            // This would be implemented if you have a loan_payments table
            
            $this->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to record payment'];
        }
    }
    
    private function calculateEMI($principal, $annualRate, $tenureMonths) {
        if ($annualRate == 0) {
            return $principal / $tenureMonths;
        }
        
        $monthlyRate = $annualRate / (12 * 100);
        $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / 
               (pow(1 + $monthlyRate, $tenureMonths) - 1);
        
        return round($emi, 2);
    }
    
    public function getEmployeeLoans($employeeId) {
        return $this->findAll(
            'employee_id = :emp_id AND status = :status',
            ['emp_id' => $employeeId, 'status' => 'active'],
            'disbursed_date DESC'
        );
    }
    
    public function getLoanStats() {
        $sql = "SELECT 
                    COUNT(*) as total_loans,
                    SUM(loan_amount) as total_disbursed,
                    SUM(outstanding_amount) as total_outstanding,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_loans,
                    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_loans
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
}