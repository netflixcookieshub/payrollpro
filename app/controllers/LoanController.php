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
        $status = $_GET['status'] ?? 'active';
        
        $result = $loanModel->getLoansWithDetails($status, $page);
        
        $this->loadView('loans/index', [
            'loans' => $result['data'],
            'pagination' => $result['pagination'],
            'status_filter' => $status
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
        
        $this->loadView('loans/view', [
            'loan' => $loan
        ]);
    }
    
    public function payment($id) {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePayment($id);
        } else {
            $this->showPaymentForm($id);
        }
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
    
    private function handlePayment($id) {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $loanModel = $this->loadModel('Loan');
        $result = $loanModel->recordPayment($id, $data);
        
        if ($result['success']) {
            $this->logActivity('loan_payment', 'employee_loans', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Payment recorded successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
        }
    }
    
    private function showCreateForm($errors = []) {
        $employees = $this->db->fetchAll(
            "SELECT id, CONCAT(emp_code, ' - ', first_name, ' ', last_name) as name 
             FROM employees WHERE status = 'active' ORDER BY emp_code ASC"
        );
        
        $loanTypes = $this->db->fetchAll(
            "SELECT * FROM loan_types WHERE status = 'active' ORDER BY name ASC"
        );
        
        $this->loadView('loans/create', [
            'employees' => $employees,
            'loan_types' => $loanTypes,
            'errors' => $errors,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showPaymentForm($id) {
        $loanModel = $this->loadModel('Loan');
        $loan = $loanModel->getLoanWithDetails($id);
        
        if (!$loan) {
            $this->loadView('errors/404');
            return;
        }
        
        $this->loadView('loans/payment', [
            'loan' => $loan,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
}