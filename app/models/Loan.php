<?php
/**
 * Loan Model
 */

require_once __DIR__ . '/../core/Model.php';

class Loan extends Model {
    protected $table = 'employee_loans';
    
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
        
        // Get loan type details
        $loanType = $this->db->fetch("SELECT * FROM loan_types WHERE id = :id", ['id' => $data['loan_type_id']]);
        
        if (!$loanType) {
            return ['success' => false, 'message' => 'Invalid loan type'];
        }
        
        // Validate loan amount against maximum
        if ($loanType['max_amount'] && $data['loan_amount'] > $loanType['max_amount']) {
            return ['success' => false, 'message' => 'Loan amount exceeds maximum limit'];
        }
        
        // Calculate EMI
        $interestRate = $data['interest_rate'] ?? $loanType['interest_rate'];
        $emiAmount = $this->calculateEMI($data['loan_amount'], $interestRate, $data['tenure_months']);
        
        // Set first EMI date (next month from disbursement)
        $disbursedDate = new DateTime($data['disbursed_date']);
        $firstEMIDate = clone $disbursedDate;
        $firstEMIDate->modify('first day of next month');
        
        try {
            $loanData = [
                'employee_id' => $data['employee_id'],
                'loan_type_id' => $data['loan_type_id'],
                'loan_amount' => $data['loan_amount'],
                'interest_rate' => $interestRate,
                'tenure_months' => $data['tenure_months'],
                'emi_amount' => $emiAmount,
                'disbursed_date' => $data['disbursed_date'],
                'first_emi_date' => $firstEMIDate->format('Y-m-d'),
                'outstanding_amount' => $data['loan_amount'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->create($loanData);
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create loan'];
        }
    }
    
    public function updateLoan($id, $data) {
        try {
            // Recalculate EMI if amount or tenure changed
            if (isset($data['loan_amount']) || isset($data['tenure_months']) || isset($data['interest_rate'])) {
                $currentLoan = $this->findById($id);
                
                $loanAmount = $data['loan_amount'] ?? $currentLoan['loan_amount'];
                $interestRate = $data['interest_rate'] ?? $currentLoan['interest_rate'];
                $tenureMonths = $data['tenure_months'] ?? $currentLoan['tenure_months'];
                
                $data['emi_amount'] = $this->calculateEMI($loanAmount, $interestRate, $tenureMonths);
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->update($id, $data);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update loan'];
        }
    }
    
    public function getLoansWithDetails($conditions = '', $params = []) {
        $sql = "SELECT el.*, 
                       e.emp_code, e.first_name, e.last_name,
                       lt.name as loan_type_name,
                       d.name as department_name
                FROM {$this->table} el
                JOIN employees e ON el.employee_id = e.id
                JOIN loan_types lt ON el.loan_type_id = lt.id
                JOIN departments d ON e.department_id = d.id";
        
        if (!empty($conditions)) {
            $sql .= " WHERE {$conditions}";
        }
        
        $sql .= " ORDER BY el.disbursed_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getLoanWithDetails($id) {
        $sql = "SELECT el.*, 
                       e.emp_code, e.first_name, e.last_name, e.email,
                       lt.name as loan_type_name, lt.max_amount, lt.max_tenure_months,
                       d.name as department_name, des.name as designation_name
                FROM {$this->table} el
                JOIN employees e ON el.employee_id = e.id
                JOIN loan_types lt ON el.loan_type_id = lt.id
                JOIN departments d ON e.department_id = d.id
                JOIN designations des ON e.designation_id = des.id
                WHERE el.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    public function getPaymentHistory($loanId) {
        $sql = "SELECT pt.*, pp.period_name, pp.start_date, pp.end_date
                FROM payroll_transactions pt
                JOIN payroll_periods pp ON pt.period_id = pp.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE sc.code = 'LOAN_EMI' 
                AND pt.remarks LIKE :loan_ref
                ORDER BY pp.start_date DESC";
        
        return $this->db->fetchAll($sql, ['loan_ref' => "%Loan ID: {$loanId}%"]);
    }
    
    public function calculateEMI($loanAmount, $interestRate, $tenureMonths) {
        if ($interestRate == 0) {
            return $loanAmount / $tenureMonths;
        }
        
        $monthlyRate = $interestRate / (12 * 100);
        $emi = ($loanAmount * $monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / 
               (pow(1 + $monthlyRate, $tenureMonths) - 1);
        
        return round($emi, 2);
    }
    
    public function calculateRemainingEMIs($loan) {
        $disbursedDate = new DateTime($loan['disbursed_date']);
        $currentDate = new DateTime();
        
        $monthsPassed = $disbursedDate->diff($currentDate)->m + 
                       ($disbursedDate->diff($currentDate)->y * 12);
        
        $remainingMonths = max(0, $loan['tenure_months'] - $monthsPassed);
        
        return [
            'remaining_months' => $remainingMonths,
            'paid_emis' => $monthsPassed,
            'total_emis' => $loan['tenure_months'],
            'remaining_amount' => $loan['outstanding_amount']
        ];
    }
    
    public function closeLoan($loanId, $closureAmount, $closureDate, $remarks = '') {
        try {
            $this->beginTransaction();
            
            $loan = $this->findById($loanId);
            if (!$loan) {
                throw new Exception('Loan not found');
            }
            
            if ($loan['status'] === 'closed') {
                throw new Exception('Loan is already closed');
            }
            
            // Update loan status
            $this->update($loanId, [
                'status' => 'closed',
                'outstanding_amount' => 0,
                'closure_date' => $closureDate,
                'closure_amount' => $closureAmount,
                'closure_remarks' => $remarks
            ]);
            
            $this->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getActiveLoansForEmployee($employeeId) {
        return $this->findAll(
            'employee_id = :emp_id AND status = :status',
            ['emp_id' => $employeeId, 'status' => 'active'],
            'disbursed_date DESC'
        );
    }
    
    public function getLoanStats() {
        $stats = [];
        
        // Total active loans
        $stats['total_active'] = $this->count('status = :status', ['status' => 'active']);
        
        // Total outstanding amount
        $result = $this->db->fetch("SELECT SUM(outstanding_amount) as total FROM {$this->table} WHERE status = 'active'");
        $stats['total_outstanding'] = $result['total'] ?? 0;
        
        // Loans by type
        $sql = "SELECT lt.name, COUNT(el.id) as count, SUM(el.outstanding_amount) as amount
                FROM loan_types lt
                LEFT JOIN {$this->table} el ON lt.id = el.loan_type_id AND el.status = 'active'
                GROUP BY lt.id
                ORDER BY count DESC";
        $stats['by_type'] = $this->db->fetchAll($sql);
        
        return $stats;
    }
}