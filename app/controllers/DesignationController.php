<?php
/**
 * Designation Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class DesignationController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDesignationAction();
        } else {
            $this->showDesignations();
        }
    }
    
    private function handleDesignationAction() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createDesignation($input);
                break;
            case 'update':
                $this->updateDesignation($input);
                break;
            case 'delete':
                $this->deleteDesignation($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createDesignation($data) {
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'code' => ['required' => true, 'max_length' => 10],
            'department_id' => ['required' => true, 'type' => 'numeric']
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        // Check if code already exists
        $existing = $this->db->fetch("SELECT id FROM designations WHERE code = :code", ['code' => $data['code']]);
        if ($existing) {
            $this->jsonResponse(['success' => false, 'message' => 'Designation code already exists'], 400);
            return;
        }
        
        try {
            $insertData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'department_id' => $data['department_id'],
                'grade' => $data['grade'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->db->insert('designations', $insertData);
            
            $this->logActivity('create_designation', 'designations', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Designation created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create designation'], 500);
        }
    }
    
    private function updateDesignation($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Designation ID is required'], 400);
            return;
        }
        
        try {
            $updateData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'department_id' => $data['department_id'],
                'grade' => $data['grade'] ?? null
            ];
            
            $this->db->update('designations', $updateData, 'id = :id', ['id' => $id]);
            
            $this->logActivity('update_designation', 'designations', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Designation updated successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update designation'], 500);
        }
    }
    
    private function deleteDesignation($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Designation ID is required'], 400);
            return;
        }
        
        // Check if designation has employees
        $employeeCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM employees WHERE designation_id = :id AND status != 'deleted'",
            ['id' => $id]
        );
        
        if ($employeeCount['count'] > 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot delete designation with active employees'], 400);
            return;
        }
        
        try {
            $this->db->update('designations', ['status' => 'inactive'], 'id = :id', ['id' => $id]);
            
            $this->logActivity('delete_designation', 'designations', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Designation deleted successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete designation'], 500);
        }
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
}