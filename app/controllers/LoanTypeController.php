<?php
/**
 * Loan Type Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class LoanTypeController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLoanTypeAction();
        } else {
            $this->showLoanTypes();
        }
    }
    
    private function handleLoanTypeAction() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createLoanType($input);
                break;
            case 'update':
                $this->updateLoanType($input);
                break;
            case 'delete':
                $this->deleteLoanType($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createLoanType($data) {
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'code' => ['required' => true, 'max_length' => 20],
            'interest_rate' => ['type' => 'numeric']
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        // Check if code already exists
        $existing = $this->db->fetch("SELECT id FROM loan_types WHERE code = :code", ['code' => $data['code']]);
        if ($existing) {
            $this->jsonResponse(['success' => false, 'message' => 'Loan type code already exists'], 400);
            return;
        }
        
        try {
            $insertData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'max_amount' => !empty($data['max_amount']) ? $data['max_amount'] : null,
                'interest_rate' => $data['interest_rate'] ?? 0,
                'max_tenure_months' => !empty($data['max_tenure_months']) ? $data['max_tenure_months'] : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->db->insert('loan_types', $insertData);
            
            $this->logActivity('create_loan_type', 'loan_types', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Loan type created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create loan type'], 500);
        }
    }
    
    private function updateLoanType($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Loan type ID is required'], 400);
            return;
        }
        
        try {
            $updateData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'max_amount' => !empty($data['max_amount']) ? $data['max_amount'] : null,
                'interest_rate' => $data['interest_rate'] ?? 0,
                'max_tenure_months' => !empty($data['max_tenure_months']) ? $data['max_tenure_months'] : null
            ];
            
            $this->db->update('loan_types', $updateData, 'id = :id', ['id' => $id]);
            
            $this->logActivity('update_loan_type', 'loan_types', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Loan type updated successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update loan type'], 500);
        }
    }
    
    private function deleteLoanType($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Loan type ID is required'], 400);
            return;
        }
        
        // Check if loan type is used in employee loans
        $usageCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM employee_loans WHERE loan_type_id = :id AND status = 'active'",
            ['id' => $id]
        );
        
        if ($usageCount['count'] > 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot delete loan type with active loans'], 400);
            return;
        }
        
        try {
            $this->db->update('loan_types', ['status' => 'inactive'], 'id = :id', ['id' => $id]);
            
            $this->logActivity('delete_loan_type', 'loan_types', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Loan type deleted successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete loan type'], 500);
        }
    }
    
    private function showLoanTypes() {
        $loanTypes = $this->db->fetchAll(
            "SELECT lt.*,
                    (SELECT COUNT(*) FROM employee_loans WHERE loan_type_id = lt.id AND status = 'active') as active_loans
             FROM loan_types lt
             WHERE lt.status = 'active'
             ORDER BY lt.name ASC"
        );
        
        $this->loadView('masters/loan-types', [
            'loan_types' => $loanTypes,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
}