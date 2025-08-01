<?php
/**
 * Salary Component Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class SalaryComponentController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleComponentAction();
        } else {
            $this->showComponents();
        }
    }
    
    private function handleComponentAction() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createComponent($input);
                break;
            case 'update':
                $this->updateComponent($input);
                break;
            case 'delete':
                $this->deleteComponent($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createComponent($data) {
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'code' => ['required' => true, 'max_length' => 20],
            'type' => ['required' => true]
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        // Check if code already exists
        $existing = $this->db->fetch("SELECT id FROM salary_components WHERE code = :code", ['code' => $data['code']]);
        if ($existing) {
            $this->jsonResponse(['success' => false, 'message' => 'Component code already exists'], 400);
            return;
        }
        
        try {
            // Set default display order if not provided
            if (empty($data['display_order'])) {
                $maxOrder = $this->db->fetch("SELECT MAX(display_order) as max_order FROM salary_components");
                $data['display_order'] = ($maxOrder['max_order'] ?? 0) + 1;
            }
            
            $insertData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'type' => $data['type'],
                'formula' => $data['formula'] ?? null,
                'is_mandatory' => $data['is_mandatory'] ?? 0,
                'is_taxable' => $data['is_taxable'] ?? 1,
                'is_pf_applicable' => $data['is_pf_applicable'] ?? 0,
                'is_esi_applicable' => $data['is_esi_applicable'] ?? 0,
                'display_order' => $data['display_order'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->db->insert('salary_components', $insertData);
            
            $this->logActivity('create_salary_component', 'salary_components', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Salary component created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create salary component'], 500);
        }
    }
    
    private function updateComponent($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Component ID is required'], 400);
            return;
        }
        
        try {
            $updateData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'type' => $data['type'],
                'formula' => $data['formula'] ?? null,
                'is_mandatory' => $data['is_mandatory'] ?? 0,
                'is_taxable' => $data['is_taxable'] ?? 1,
                'is_pf_applicable' => $data['is_pf_applicable'] ?? 0,
                'is_esi_applicable' => $data['is_esi_applicable'] ?? 0,
                'display_order' => $data['display_order'] ?? 0
            ];
            
            $this->db->update('salary_components', $updateData, 'id = :id', ['id' => $id]);
            
            $this->logActivity('update_salary_component', 'salary_components', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Salary component updated successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update salary component'], 500);
        }
    }
    
    private function deleteComponent($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Component ID is required'], 400);
            return;
        }
        
        // Check if component is used in salary structures
        $usageCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM salary_structures WHERE component_id = :id",
            ['id' => $id]
        );
        
        if ($usageCount['count'] > 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot delete component that is in use'], 400);
            return;
        }
        
        try {
            $this->db->update('salary_components', ['status' => 'inactive'], 'id = :id', ['id' => $id]);
            
            $this->logActivity('delete_salary_component', 'salary_components', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Salary component deleted successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete salary component'], 500);
        }
    }
    
    private function showComponents() {
        $components = $this->db->fetchAll(
            "SELECT * FROM salary_components WHERE status = 'active' ORDER BY display_order ASC"
        );
        
        $this->loadView('masters/salary-components', [
            'components' => $components,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
}