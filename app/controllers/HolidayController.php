<?php
/**
 * Holiday Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class HolidayController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleHolidayAction();
        } else {
            $this->showHolidays();
        }
    }
    
    private function handleHolidayAction() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createHoliday($input);
                break;
            case 'update':
                $this->updateHoliday($input);
                break;
            case 'delete':
                $this->deleteHoliday($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createHoliday($data) {
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'holiday_date' => ['required' => true, 'type' => 'date'],
            'type' => ['required' => true]
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        try {
            $insertData = [
                'name' => $data['name'],
                'holiday_date' => $data['holiday_date'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->db->insert('holidays', $insertData);
            
            $this->logActivity('create_holiday', 'holidays', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Holiday created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create holiday'], 500);
        }
    }
    
    private function updateHoliday($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Holiday ID is required'], 400);
            return;
        }
        
        try {
            $updateData = [
                'name' => $data['name'],
                'holiday_date' => $data['holiday_date'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null
            ];
            
            $this->db->update('holidays', $updateData, 'id = :id', ['id' => $id]);
            
            $this->logActivity('update_holiday', 'holidays', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Holiday updated successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update holiday'], 500);
        }
    }
    
    private function deleteHoliday($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Holiday ID is required'], 400);
            return;
        }
        
        try {
            $this->db->delete('holidays', 'id = :id', ['id' => $id]);
            
            $this->logActivity('delete_holiday', 'holidays', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Holiday deleted successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete holiday'], 500);
        }
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
}