<?php
/**
 * Leave Type Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class LeaveTypeController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLeaveTypeAction();
        } else {
            $this->showLeaveTypes();
        }
    }
    
    private function handleLeaveTypeAction() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createLeaveType($input);
                break;
            case 'update':
                $this->updateLeaveType($input);
                break;
            case 'delete':
                $this->deleteLeaveType($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createLeaveType($data) {
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'code' => ['required' => true, 'max_length' => 20]
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        // Check if code already exists
        $existing = $this->db->fetch("SELECT id FROM leave_types WHERE code = :code", ['code' => $data['code']]);
        if ($existing) {
            $this->jsonResponse(['success' => false, 'message' => 'Leave type code already exists'], 400);
            return;
        }
        
        try {
            $insertData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'is_paid' => $data['is_paid'] ?? 1,
                'max_days_per_year' => !empty($data['max_days_per_year']) ? $data['max_days_per_year'] : null,
                'carry_forward' => $data['carry_forward'] ?? 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->db->insert('leave_types', $insertData);
            
            $this->logActivity('create_leave_type', 'leave_types', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Leave type created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create leave type'], 500);
        }
    }
    
    private function updateLeaveType($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Leave type ID is required'], 400);
            return;
        }
        
        try {
            $updateData = [
                'name' => $data['name'],
                'code' => $data['code'],
                'is_paid' => $data['is_paid'] ?? 1,
                'max_days_per_year' => !empty($data['max_days_per_year']) ? $data['max_days_per_year'] : null,
                'carry_forward' => $data['carry_forward'] ?? 0
            ];
            
            $this->db->update('leave_types', $updateData, 'id = :id', ['id' => $id]);
            
            $this->logActivity('update_leave_type', 'leave_types', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Leave type updated successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update leave type'], 500);
        }
    }
    
    private function deleteLeaveType($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Leave type ID is required'], 400);
            return;
        }
        
        try {
            $this->db->update('leave_types', ['status' => 'inactive'], 'id = :id', ['id' => $id]);
            
            $this->logActivity('delete_leave_type', 'leave_types', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Leave type deleted successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete leave type'], 500);
        }
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
}