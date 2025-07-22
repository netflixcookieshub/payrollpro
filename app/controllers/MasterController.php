<?php
/**
 * Master Data Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class MasterController extends Controller {
    
    public function departments() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDepartmentAction();
        } else {
            $this->showDepartments();
        }
    }
    
    public function designations() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDesignationAction();
        } else {
            $this->showDesignations();
        }
    }
    
    public function salaryComponents() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSalaryComponentAction();
        } else {
            $this->showSalaryComponents();
        }
    }
    
    public function loanTypes() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLoanTypeAction();
        } else {
            $this->showLoanTypes();
        }
    }
    
    public function leaveTypes() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLeaveTypeAction();
        } else {
            $this->showLeaveTypes();
        }
    }
    
    public function holidays() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleHolidayAction();
        } else {
            $this->showHolidays();
        }
    }
    
    public function taxSlabs() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleTaxSlabAction();
        } else {
            $this->showTaxSlabs();
        }
    }
    
    private function handleDepartmentAction() {
        $action = $_POST['action'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createDepartment();
                break;
            case 'update':
                $this->updateDepartment();
                break;
            case 'delete':
                $this->deleteDepartment();
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createDepartment() {
        $data = $this->sanitizeInput($_POST);
        
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'code' => ['required' => true, 'max_length' => 10, 'unique' => true],
            'head_id' => ['type' => 'numeric']
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        // Check if code already exists
        $existing = $this->db->fetch("SELECT id FROM departments WHERE code = :code", ['code' => $data['code']]);
        if ($existing) {
            $this->jsonResponse(['success' => false, 'message' => 'Department code already exists'], 400);
            return;
        }
        
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->db->insert('departments', $data);
            
            $this->logActivity('create_department', 'departments', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Department created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create department'], 500);
        }
    }
    
    private function updateDepartment() {
        $id = $_POST['id'] ?? '';
        $data = $this->sanitizeInput($_POST);
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Department ID is required'], 400);
            return;
        }
        
        try {
            unset($data['action'], $data['csrf_token'], $data['id']);
            $this->db->update('departments', $data, 'id = :id', ['id' => $id]);
            
            $this->logActivity('update_department', 'departments', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Department updated successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update department'], 500);
        }
    }
    
    private function deleteDepartment() {
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Department ID is required'], 400);
            return;
        }
        
        // Check if department has employees
        $employeeCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM employees WHERE department_id = :id AND status != 'deleted'",
            ['id' => $id]
        );
        
        if ($employeeCount['count'] > 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot delete department with active employees'], 400);
            return;
        }
        
        try {
            $this->db->update('departments', ['status' => 'inactive'], 'id = :id', ['id' => $id]);
            
            $this->logActivity('delete_department', 'departments', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Department deleted successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete department'], 500);
        }
    }
    
    private function showDepartments() {
        $departments = $this->db->fetchAll(
            "SELECT d.*, e.first_name, e.last_name,
                    (SELECT COUNT(*) FROM employees WHERE department_id = d.id AND status = 'active') as employee_count
             FROM departments d
             LEFT JOIN employees e ON d.head_id = e.id
             WHERE d.status = 'active'
             ORDER BY d.name ASC"
        );
        
        $employees = $this->db->fetchAll(
            "SELECT id, CONCAT(first_name, ' ', last_name) as name 
             FROM employees WHERE status = 'active' ORDER BY first_name ASC"
        );
        
        $this->loadView('masters/departments', [
            'departments' => $departments,
            'employees' => $employees,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showDesignations() {
        $designations = $this->db->fetchAll(
            "SELECT des.*, d.name as department_name,
                    (SELECT COUNT(*) FROM employees WHERE designation_id = des.id AND status = 'active') as employee_count
             FROM designations des
             LEFT JOIN departments d ON des.department_id = d.id
             WHERE des.status = 'active'
             ORDER BY des.name ASC"
        );
        
        $departments = $this->db->fetchAll(
            "SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC"
        );
        
        $this->loadView('masters/designations', [
            'designations' => $designations,
            'departments' => $departments,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showSalaryComponents() {
        $components = $this->db->fetchAll(
            "SELECT * FROM salary_components WHERE status = 'active' ORDER BY display_order ASC"
        );
        
        $this->loadView('masters/salary-components', [
            'components' => $components,
            'csrf_token' => $this->generateCSRFToken()
        ]);
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
    
    private function showLeaveTypes() {
        $leaveTypes = $this->db->fetchAll(
            "SELECT * FROM leave_types WHERE status = 'active' ORDER BY name ASC"
        );
        
        $this->loadView('masters/leave-types', [
            'leave_types' => $leaveTypes,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showHolidays() {
        $currentYear = date('Y');
        $holidays = $this->db->fetchAll(
            "SELECT * FROM holidays 
             WHERE YEAR(holiday_date) = :year 
             ORDER BY holiday_date ASC",
            ['year' => $currentYear]
        );
        
        $this->loadView('masters/holidays', [
            'holidays' => $holidays,
            'current_year' => $currentYear,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showTaxSlabs() {
        $currentFY = $this->getCurrentFinancialYear();
        $taxSlabs = $this->db->fetchAll(
            "SELECT * FROM tax_slabs 
             WHERE financial_year = :fy 
             ORDER BY min_amount ASC",
            ['fy' => $currentFY]
        );
        
        $this->loadView('masters/tax-slabs', [
            'tax_slabs' => $taxSlabs,
            'current_fy' => $currentFY,
            'csrf_token' => $this->generateCSRFToken()
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
    
    // Similar methods for other master data operations...
    private function handleDesignationAction() {
        // Implementation similar to handleDepartmentAction
    }
    
    private function handleSalaryComponentAction() {
        // Implementation for salary component CRUD
    }
    
    private function handleLoanTypeAction() {
        // Implementation for loan type CRUD
    }
    
    private function handleLeaveTypeAction() {
        // Implementation for leave type CRUD
    }
    
    private function handleHolidayAction() {
        // Implementation for holiday CRUD
    }
    
    private function handleTaxSlabAction() {
        // Implementation for tax slab CRUD
    }
}