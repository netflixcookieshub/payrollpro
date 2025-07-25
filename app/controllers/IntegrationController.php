<?php
/**
 * Integration Controller
 * Handles external system integrations and API connections
 */

require_once __DIR__ . '/../core/Controller.php';

class IntegrationController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $integrations = $this->getAvailableIntegrations();
        $activeIntegrations = $this->getActiveIntegrations();
        
        $this->loadView('integrations/index', [
            'integrations' => $integrations,
            'active_integrations' => $activeIntegrations,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function configure($integration) {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleConfiguration($integration);
        } else {
            $this->showConfigurationForm($integration);
        }
    }
    
    public function test($integration) {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $integrationModel = $this->loadModel('Integration');
        $result = $integrationModel->testConnection($integration);
        
        $this->jsonResponse($result);
    }
    
    public function sync($integration) {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $integrationModel = $this->loadModel('Integration');
        $result = $integrationModel->syncData($integration);
        
        $this->jsonResponse($result);
    }
    
    public function webhook($integration) {
        // Handle incoming webhooks from external systems
        $payload = file_get_contents('php://input');
        $headers = getallheaders();
        
        $integrationModel = $this->loadModel('Integration');
        $result = $integrationModel->processWebhook($integration, $payload, $headers);
        
        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
        exit;
    }
    
    public function exportData() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $format = $_GET['format'] ?? 'json';
        $tables = $_GET['tables'] ?? [];
        
        $exportModel = $this->loadModel('DataExport');
        $result = $exportModel->exportData($tables, $format);
        
        if ($result['success']) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="payroll_export_' . date('Y-m-d') . '.' . $format . '"');
            echo $result['data'];
            exit;
        } else {
            $this->jsonResponse($result, 500);
        }
    }
    
    public function importData() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDataImport();
        } else {
            $this->showImportForm();
        }
    }
    
    public function apiKeys() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleApiKeyAction();
        } else {
            $this->showApiKeys();
        }
    }
    
    private function getAvailableIntegrations() {
        return [
            'hrms' => [
                'name' => 'HRMS Integration',
                'description' => 'Sync employee data with external HRMS',
                'type' => 'employee_sync',
                'status' => 'available'
            ],
            'banking' => [
                'name' => 'Banking Integration',
                'description' => 'Direct salary transfers to banks',
                'type' => 'payment',
                'status' => 'available'
            ],
            'attendance' => [
                'name' => 'Biometric Attendance',
                'description' => 'Sync with biometric devices',
                'type' => 'attendance',
                'status' => 'available'
            ],
            'accounting' => [
                'name' => 'Accounting Software',
                'description' => 'Sync with Tally, QuickBooks, etc.',
                'type' => 'accounting',
                'status' => 'available'
            ],
            'email' => [
                'name' => 'Email Service',
                'description' => 'Send payslips via email',
                'type' => 'notification',
                'status' => 'available'
            ],
            'sms' => [
                'name' => 'SMS Gateway',
                'description' => 'Send notifications via SMS',
                'type' => 'notification',
                'status' => 'available'
            ]
        ];
    }
    
    private function getActiveIntegrations() {
        return $this->db->fetchAll(
            "SELECT * FROM integrations WHERE status = 'active' ORDER BY name ASC"
        );
    }
    
    private function handleConfiguration($integration) {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $integrationModel = $this->loadModel('Integration');
        $result = $integrationModel->saveConfiguration($integration, $data);
        
        if ($result['success']) {
            $this->logActivity('configure_integration', 'integrations', null);
            $this->redirect('/integrations?success=configured');
        } else {
            $this->showConfigurationForm($integration, $result['errors'] ?? []);
        }
    }
    
    private function showConfigurationForm($integration, $errors = []) {
        $config = $this->db->fetch(
            "SELECT * FROM integrations WHERE type = :type",
            ['type' => $integration]
        );
        
        $this->loadView('integrations/configure', [
            'integration' => $integration,
            'config' => $config,
            'errors' => $errors,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function handleDataImport() {
        $importType = $_POST['import_type'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        if (empty($_FILES['import_file']['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'No file selected'], 400);
            return;
        }
        
        try {
            $filePath = $this->uploadFile($_FILES['import_file'], 'imports');
            
            $importModel = $this->loadModel('DataImport');
            $result = $importModel->processImport($importType, $filePath);
            
            $this->logActivity('data_import', 'imports', null);
            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    private function showImportForm() {
        $importTypes = [
            'employees' => 'Employee Data',
            'attendance' => 'Attendance Records',
            'salary_structures' => 'Salary Structures',
            'loans' => 'Loan Data'
        ];
        
        $this->loadView('integrations/import', [
            'import_types' => $importTypes,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function handleApiKeyAction() {
        $action = $_POST['action'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'generate':
                $this->generateApiKey();
                break;
            case 'revoke':
                $this->revokeApiKey();
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function generateApiKey() {
        $apiKey = bin2hex(random_bytes(32));
        $hashedKey = password_hash($apiKey, PASSWORD_DEFAULT);
        
        $this->db->insert('api_keys', [
            'key_hash' => $hashedKey,
            'name' => $_POST['key_name'] ?? 'API Key',
            'permissions' => $_POST['permissions'] ?? 'read',
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->jsonResponse(['success' => true, 'api_key' => $apiKey]);
    }
    
    private function revokeApiKey() {
        $keyId = $_POST['key_id'] ?? '';
        
        $this->db->update('api_keys', 
            ['status' => 'revoked'], 
            'id = :id', 
            ['id' => $keyId]
        );
        
        $this->jsonResponse(['success' => true, 'message' => 'API key revoked']);
    }
    
    private function showApiKeys() {
        $apiKeys = $this->db->fetchAll(
            "SELECT id, name, permissions, status, created_at FROM api_keys WHERE created_by = :user_id ORDER BY created_at DESC",
            ['user_id' => $_SESSION['user_id']]
        );
        
        $this->loadView('integrations/api-keys', [
            'api_keys' => $apiKeys,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
}