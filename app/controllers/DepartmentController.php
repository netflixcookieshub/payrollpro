<?php
/**
 * Department Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class DepartmentController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDepartmentAction();
        } else {
            $this->showDepartments();
        }
    }
    
    private function handleDepartmentAction() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createDepartment($input);
                break;
            case 'update':
                $this->updateDepartment($input);
                break;
            case 'delete':
                $this->deleteDepartment($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createDepartment($data) {
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'code' => ['required' => true, 'max_length' => 10],
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
            $insertData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'head_id' => !empty($data['head_id']) ? $data['head_id'] : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->db->insert('departments', $insertData);
            
            $this->logActivity('create_department', 'departments', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Department created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create department'], 500);
        }
    }
    
    private function updateDepartment($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Department ID is required'], 400);
            return;
        }
        
        try {
            $updateData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'head_id' => !empty($data['head_id']) ? $data['head_id'] : null
            ];
            
            $this->db->update('departments', $updateData, 'id = :id', ['id' => $id]);
            
            $this->logActivity('update_department', 'departments', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Department updated successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update department'], 500);
        }
    }
    
    private function deleteDepartment($data) {
        $id = $data['id'] ?? '';
        
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
}