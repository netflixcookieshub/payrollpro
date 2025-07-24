<?php
/**
 * Loan Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class LoanController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $loanModel = $this->loadModel('Loan');
        $page = max(1, intval($_GET['page'] ?? 1));
        $search = $this->sanitizeInput($_GET['search'] ?? '');
        $status = $this->sanitizeInput($_GET['status'] ?? '');
        $loanType = $this->sanitizeInput($_GET['loan_type'] ?? '');
        
        $conditions = [];
        $params = [];
        
        if (!empty($search)) {
            $conditions[] = "(e.emp_code LIKE :search OR e.first_name LIKE :search OR e.last_name LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($status)) {
            $conditions[] = "el.status = :status";
            $params['status'] = $status;
        }
        
        if (!empty($loanType)) {
            $conditions[] = "el.loan_type_id = :loan_type";
            $params['loan_type'] = $loanType;
        }
        
        $whereClause = !empty($conditions) ? implode(' AND ', $conditions) : '';
        
        $loans = $loanModel->getLoansWithDetails($whereClause, $params);
        $loanTypes = $this->db->fetchAll("SELECT * FROM loan_types WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('loans/index', [
            'loans' => $loans,
            'loan_types' => $loanTypes,
            'filters' => compact('search', 'status', 'loanType')
        ]);
    }
    
    public function create() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $this->showCreateForm();
        }
    }
    
    public function view($id) {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $loanModel = $this->loadModel('Loan');
        $loan = $loanModel->getLoanWithDetails($id);
        
        if (!$loan) {
            $this->loadView('errors/404');
            return;
        }
        
        // Get payment history
        $paymentHistory = $loanModel->getPaymentHistory($id);
        
        // Calculate remaining EMIs
        $remainingEMIs = $loanModel->calculateRemainingEMIs($loan);
        
        $this->loadView('loans/view', [
            'loan' => $loan,
            'payment_history' => $paymentHistory,
            'remaining_emis' => $remainingEMIs
        ]);
    }
    
    public function edit($id) {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate($id);
        } else {
            $this->showEditForm($id);
        }
    }
    
    public function close($id) {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            $closureAmount = $_POST['closure_amount'] ?? '';
            $closureDate = $_POST['closure_date'] ?? date('Y-m-d');
            $remarks = $_POST['remarks'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            $loanModel = $this->loadModel('Loan');
            $result = $loanModel->closeLoan($id, $closureAmount, $closureDate, $remarks);
            
            if ($result['success']) {
                $this->logActivity('close_loan', 'employee_loans', $id);
                $this->jsonResponse(['success' => true, 'message' => 'Loan closed successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
            }
        }
    }
    
    public function calculateEMI() {
        $this->checkAuth();
        
        $loanAmount = floatval($_POST['loan_amount'] ?? 0);
        $interestRate = floatval($_POST['interest_rate'] ?? 0);
        $tenureMonths = intval($_POST['tenure_months'] ?? 0);
        
        if ($loanAmount <= 0 || $tenureMonths <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid loan parameters'], 400);
            return;
        }
        
        $loanModel = $this->loadModel('Loan');
        $emiAmount = $loanModel->calculateEMI($loanAmount, $interestRate, $tenureMonths);
        $totalAmount = $emiAmount * $tenureMonths;
        $totalInterest = $totalAmount - $loanAmount;
        
        $this->jsonResponse([
            'success' => true,
            'emi_amount' => round($emiAmount, 2),
            'total_amount' => round($totalAmount, 2),
            'total_interest' => round($totalInterest, 2)
        ]);
    }
    
    private function handleCreate() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $loanModel = $this->loadModel('Loan');
        $result = $loanModel->createLoan($data);
        
        if ($result['success']) {
            $this->logActivity('create_loan', 'employee_loans', $result['id']);
            $this->redirect('/loans?success=created');
        } else {
            $this->showCreateForm($result['errors'] ?? ['message' => $result['message']]);
        }
    }
    
    private function handleUpdate($id) {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $loanModel = $this->loadModel('Loan');
        $result = $loanModel->updateLoan($id, $data);
        
        if ($result['success']) {
            $this->logActivity('update_loan', 'employee_loans', $id);
            $this->redirect('/loans/' . $id . '?success=updated');
        } else {
            $this->showEditForm($id, $result['errors'] ?? ['message' => $result['message']]);
        }
    }
    
    private function showCreateForm($errors = []) {
        $employeeModel = $this->loadModel('Employee');
        $employees = $employeeModel->getActiveEmployees();
        $loanTypes = $this->db->fetchAll("SELECT * FROM loan_types WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('loans/create', [
            'employees' => $employees,
            'loan_types' => $loanTypes,
            'errors' => $errors,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showEditForm($id, $errors = []) {
        $loanModel = $this->loadModel('Loan');
        $loan = $loanModel->getLoanWithDetails($id);
        
        if (!$loan) {
            $this->loadView('errors/404');
            return;
        }
        
        $employeeModel = $this->loadModel('Employee');
        $employees = $employeeModel->getActiveEmployees();
        $loanTypes = $this->db->fetchAll("SELECT * FROM loan_types WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('loans/edit', [
            'loan' => $loan,
            'employees' => $employees,
            'loan_types' => $loanTypes,
            'errors' => $errors,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
}