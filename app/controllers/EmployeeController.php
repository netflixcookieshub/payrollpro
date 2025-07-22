<?php
/**
 * Employee Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class EmployeeController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        $employeeModel = $this->loadModel('Employee');
        $page = max(1, intval($_GET['page'] ?? 1));
        $search = $this->sanitizeInput($_GET['search'] ?? '');
        $department = $this->sanitizeInput($_GET['department'] ?? '');
        $status = $this->sanitizeInput($_GET['status'] ?? '');
        
        if (!empty($search) || !empty($department) || !empty($status)) {
            $employees = $employeeModel->searchEmployees($search, $department, $status);
            $pagination = null;
        } else {
            $result = $employeeModel->paginate($page, RECORDS_PER_PAGE, 'status != :status', ['status' => 'deleted'], 'first_name ASC');
            $employees = $result['data'];
            $pagination = $result['pagination'];
        }
        
        // Get departments for filter
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('employees/index', [
            'employees' => $employees,
            'departments' => $departments,
            'pagination' => $pagination,
            'filters' => compact('search', 'department', 'status')
        ]);
    }
    
    public function create() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $this->showCreateForm();
        }
    }
    
    public function edit($id) {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate($id);
        } else {
            $this->showEditForm($id);
        }
    }
    
    public function view($id) {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        $employeeModel = $this->loadModel('Employee');
        $employee = $employeeModel->getEmployeeWithDetails($id);
        
        if (!$employee) {
            $this->loadView('errors/404');
            return;
        }
        
        // Get salary structure
        $salaryStructure = $employeeModel->getEmployeeSalaryStructure($id);
        
        // Get recent payslips
        $payrollModel = $this->loadModel('Payroll');
        $recentPayslips = $this->getRecentPayslips($id, 3);
        
        $this->loadView('employees/view', [
            'employee' => $employee,
            'salary_structure' => $salaryStructure,
            'recent_payslips' => $recentPayslips
        ]);
    }
    
    public function delete($id) {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            $employeeModel = $this->loadModel('Employee');
            $employee = $employeeModel->findById($id);
            
            if (!$employee) {
                $this->jsonResponse(['success' => false, 'message' => 'Employee not found'], 404);
                return;
            }
            
            // Soft delete - update status instead of actual deletion
            $result = $employeeModel->update($id, [
                'status' => 'deleted',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                $this->logActivity('delete_employee', 'employees', $id);
                $this->jsonResponse(['success' => true, 'message' => 'Employee deleted successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to delete employee'], 500);
            }
        }
    }
    
    public function salaryStructure($id) {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSalaryStructureUpdate($id);
        } else {
            $this->showSalaryStructureForm($id);
        }
    }
    
    public function uploadDocument($id) {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $documentType = $this->sanitizeInput($_POST['document_type'] ?? '');
                
                if (empty($_FILES['document']['name'])) {
                    $this->jsonResponse(['success' => false, 'message' => 'No file selected'], 400);
                    return;
                }
                
                $filePath = $this->uploadFile($_FILES['document'], 'employee_documents');
                
                // Update employee record with document path
                $employeeModel = $this->loadModel('Employee');
                $updateData = [];
                
                switch ($documentType) {
                    case 'photo':
                        $updateData['photo'] = $filePath;
                        break;
                    case 'signature':
                        $updateData['signature'] = $filePath;
                        break;
                    default:
                        // For other documents, you might want to create a separate documents table
                        break;
                }
                
                if (!empty($updateData)) {
                    $employeeModel->updateEmployee($id, $updateData);
                }
                
                $this->logActivity('upload_document', 'employees', $id);
                $this->jsonResponse(['success' => true, 'message' => 'Document uploaded successfully']);
                
            } catch (Exception $e) {
                $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
            }
        }
    }
    
    public function export() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        $format = $_GET['format'] ?? 'excel';
        $department = $_GET['department'] ?? '';
        $status = $_GET['status'] ?? 'active';
        
        $employeeModel = $this->loadModel('Employee');
        $conditions = '';
        $params = [];
        
        if (!empty($department)) {
            $conditions = 'e.department_id = :department';
            $params['department'] = $department;
        }
        
        if (!empty($status)) {
            $conditions .= (!empty($conditions) ? ' AND ' : '') . 'e.status = :status';
            $params['status'] = $status;
        }
        
        $employees = $employeeModel->getEmployeesWithDetails($conditions, $params);
        
        if ($format === 'csv') {
            $this->exportToCSV($employees, 'employees_' . date('Y-m-d') . '.csv');
        } else {
            $this->exportToExcel($employees, 'employees_' . date('Y-m-d') . '.xlsx');
        }
    }
    
    private function handleCreate() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $employeeModel = $this->loadModel('Employee');
        $result = $employeeModel->createEmployee($data);
        
        if ($result['success']) {
            $this->logActivity('create_employee', 'employees', $result['id']);
            $this->redirect('/employees?success=created');
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
        
        $employeeModel = $this->loadModel('Employee');
        $result = $employeeModel->updateEmployee($id, $data);
        
        if ($result['success']) {
            $this->logActivity('update_employee', 'employees', $id);
            $this->redirect('/employees/' . $id . '?success=updated');
        } else {
            $this->showEditForm($id, $result['errors'] ?? ['message' => $result['message']]);
        }
    }
    
    private function showCreateForm($errors = []) {
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        $designations = $this->db->fetchAll("SELECT * FROM designations WHERE status = 'active' ORDER BY name ASC");
        $costCenters = $this->db->fetchAll("SELECT * FROM cost_centers WHERE status = 'active' ORDER BY name ASC");
        $managers = $this->db->fetchAll("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM employees WHERE status = 'active' ORDER BY first_name ASC");
        
        $this->loadView('employees/create', [
            'departments' => $departments,
            'designations' => $designations,
            'cost_centers' => $costCenters,
            'managers' => $managers,
            'errors' => $errors,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showEditForm($id, $errors = []) {
        $employeeModel = $this->loadModel('Employee');
        $employee = $employeeModel->getEmployeeWithDetails($id);
        
        if (!$employee) {
            $this->loadView('errors/404');
            return;
        }
        
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        $designations = $this->db->fetchAll("SELECT * FROM designations WHERE status = 'active' ORDER BY name ASC");
        $costCenters = $this->db->fetchAll("SELECT * FROM cost_centers WHERE status = 'active' ORDER BY name ASC");
        $managers = $this->db->fetchAll("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM employees WHERE status = 'active' AND id != ? ORDER BY first_name ASC", [$id]);
        
        $this->loadView('employees/edit', [
            'employee' => $employee,
            'departments' => $departments,
            'designations' => $designations,
            'cost_centers' => $costCenters,
            'managers' => $managers,
            'errors' => $errors,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function handleSalaryStructureUpdate($id) {
        $components = $_POST['components'] ?? [];
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $employeeModel = $this->loadModel('Employee');
        $componentData = [];
        
        foreach ($components as $componentId => $amount) {
            if (!empty($amount) && is_numeric($amount)) {
                $componentData[] = [
                    'component_id' => $componentId,
                    'amount' => floatval($amount),
                    'effective_date' => $effectiveDate
                ];
            }
        }
        
        if (empty($componentData)) {
            $this->jsonResponse(['success' => false, 'message' => 'No valid components provided'], 400);
            return;
        }
        
        $result = $employeeModel->assignSalaryStructure($id, $componentData);
        
        if ($result['success']) {
            $this->logActivity('update_salary_structure', 'salary_structures', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Salary structure updated successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 500);
        }
    }
    
    private function showSalaryStructureForm($id) {
        $employeeModel = $this->loadModel('Employee');
        $employee = $employeeModel->getEmployeeWithDetails($id);
        
        if (!$employee) {
            $this->loadView('errors/404');
            return;
        }
        
        $salaryComponents = $this->db->fetchAll("SELECT * FROM salary_components WHERE status = 'active' ORDER BY display_order ASC");
        $currentStructure = $employeeModel->getEmployeeSalaryStructure($id);
        
        // Create array for easy lookup
        $currentAmounts = [];
        foreach ($currentStructure as $component) {
            $currentAmounts[$component['component_id']] = $component['amount'];
        }
        
        $this->loadView('employees/salary-structure', [
            'employee' => $employee,
            'salary_components' => $salaryComponents,
            'current_amounts' => $currentAmounts,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function getRecentPayslips($employeeId, $limit = 5) {
        $sql = "SELECT pp.period_name, pp.start_date, pp.end_date, pp.id as period_id,
                       SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as earnings,
                       SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as deductions
                FROM payroll_periods pp
                LEFT JOIN payroll_transactions pt ON pp.id = pt.period_id AND pt.employee_id = :emp_id
                LEFT JOIN salary_components sc ON pt.component_id = sc.id
                GROUP BY pp.id
                ORDER BY pp.start_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':emp_id', $employeeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'Employee Code', 'First Name', 'Last Name', 'Email', 'Phone',
            'Department', 'Designation', 'Join Date', 'Status', 'PAN Number'
        ]);
        
        // Data
        foreach ($data as $employee) {
            fputcsv($output, [
                $employee['emp_code'],
                $employee['first_name'],
                $employee['last_name'],
                $employee['email'],
                $employee['phone'],
                $employee['department_name'],
                $employee['designation_name'],
                $employee['join_date'],
                $employee['status'],
                $employee['pan_number']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function exportToExcel($data, $filename) {
        // For simplicity, using CSV format with Excel MIME type
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $this->exportToCSV($data, $filename);
    }
}