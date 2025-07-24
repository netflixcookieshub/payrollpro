<?php
/**
 * Tax Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class TaxController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $this->loadView('tax/index');
    }
    
    public function slabs() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSlabAction();
        } else {
            $this->showSlabs();
        }
    }
    
    public function calculate() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleTaxCalculation();
        } else {
            $this->showCalculator();
        }
    }
    
    public function declarations() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $employeeId = $_GET['employee'] ?? '';
        $financialYear = $_GET['fy'] ?? $this->getCurrentFinancialYear();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDeclarationSubmission();
        } else {
            $this->showDeclarations($employeeId, $financialYear);
        }
    }
    
    public function form16() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $employeeId = $_GET['employee'] ?? '';
        $financialYear = $_GET['fy'] ?? $this->getCurrentFinancialYear();
        
        if (empty($employeeId)) {
            $this->redirect('/tax/declarations?error=employee_required');
            return;
        }
        
        $this->generateForm16($employeeId, $financialYear);
    }
    
    private function handleSlabAction() {
        $action = $_POST['action'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createTaxSlab();
                break;
            case 'update':
                $this->updateTaxSlab();
                break;
            case 'delete':
                $this->deleteTaxSlab();
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createTaxSlab() {
        $data = $this->sanitizeInput($_POST);
        
        $rules = [
            'financial_year' => ['required' => true],
            'min_amount' => ['required' => true, 'type' => 'numeric'],
            'tax_rate' => ['required' => true, 'type' => 'numeric']
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->db->insert('tax_slabs', $data);
            
            $this->logActivity('create_tax_slab', 'tax_slabs', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Tax slab created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create tax slab'], 500);
        }
    }
    
    private function handleTaxCalculation() {
        $annualIncome = floatval($_POST['annual_income'] ?? 0);
        $deductions = floatval($_POST['deductions'] ?? 0);
        $financialYear = $_POST['financial_year'] ?? $this->getCurrentFinancialYear();
        
        if ($annualIncome <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid income amount'], 400);
            return;
        }
        
        $taxModel = $this->loadModel('Tax');
        $calculation = $taxModel->calculateTax($annualIncome, $deductions, $financialYear);
        
        $this->jsonResponse(['success' => true, 'calculation' => $calculation]);
    }
    
    private function handleDeclarationSubmission() {
        $employeeId = $_POST['employee_id'] ?? '';
        $financialYear = $_POST['financial_year'] ?? '';
        $declarations = $_POST['declarations'] ?? [];
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        if (empty($employeeId) || empty($financialYear)) {
            $this->jsonResponse(['success' => false, 'message' => 'Employee and financial year are required'], 400);
            return;
        }
        
        $taxModel = $this->loadModel('Tax');
        $result = $taxModel->saveDeclarations($employeeId, $financialYear, $declarations);
        
        if ($result['success']) {
            $this->logActivity('save_tax_declarations', 'tax_declarations', $employeeId);
            $this->jsonResponse(['success' => true, 'message' => 'Declarations saved successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
        }
    }
    
    private function showSlabs() {
        $currentFY = $this->getCurrentFinancialYear();
        $taxSlabs = $this->db->fetchAll(
            "SELECT * FROM tax_slabs WHERE financial_year = :fy ORDER BY min_amount ASC",
            ['fy' => $currentFY]
        );
        
        $this->loadView('tax/slabs', [
            'tax_slabs' => $taxSlabs,
            'current_fy' => $currentFY,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showCalculator() {
        $this->loadView('tax/calculator', [
            'current_fy' => $this->getCurrentFinancialYear(),
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showDeclarations($employeeId, $financialYear) {
        $employeeModel = $this->loadModel('Employee');
        $taxModel = $this->loadModel('Tax');
        
        if (!empty($employeeId)) {
            $employee = $employeeModel->getEmployeeWithDetails($employeeId);
            $declarations = $taxModel->getEmployeeDeclarations($employeeId, $financialYear);
        } else {
            $employee = null;
            $declarations = [];
        }
        
        $employees = $employeeModel->getActiveEmployees();
        $declarationSections = $taxModel->getDeclarationSections();
        
        $this->loadView('tax/declarations', [
            'employee' => $employee,
            'employees' => $employees,
            'declarations' => $declarations,
            'declaration_sections' => $declarationSections,
            'financial_year' => $financialYear,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function generateForm16($employeeId, $financialYear) {
        $employeeModel = $this->loadModel('Employee');
        $taxModel = $this->loadModel('Tax');
        
        $employee = $employeeModel->getEmployeeWithDetails($employeeId);
        if (!$employee) {
            $this->loadView('errors/404');
            return;
        }
        
        $form16Data = $taxModel->generateForm16Data($employeeId, $financialYear);
        
        $this->loadView('tax/form16', [
            'employee' => $employee,
            'form16_data' => $form16Data,
            'financial_year' => $financialYear
        ]);
    }
    
    private function getCurrentFinancialYear() {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
}