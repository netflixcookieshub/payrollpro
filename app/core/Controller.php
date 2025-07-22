<?php
/**
 * Base Controller Class
 */

class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->checkSession();
    }
    
    protected function loadModel($model) {
        require_once __DIR__ . "/../models/{$model}.php";
        return new $model($this->db);
    }
    
    protected function loadView($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/{$view}.php";
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    protected function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }
    
    protected function checkPermission($permission) {
        if (!$this->hasPermission($permission)) {
            $this->loadView('errors/403');
            exit;
        }
    }
    
    protected function hasPermission($permission) {
        $userRole = $_SESSION['role'] ?? '';
        $permissions = $_SESSION['permissions'] ?? '';
        
        if ($permissions === 'all') {
            return true;
        }
        
        $permissionArray = explode(',', $permissions);
        return in_array($permission, $permissionArray);
    }
    
    protected function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    protected function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token) &&
               (time() - $_SESSION['csrf_token_time']) <= CSRF_TOKEN_EXPIRY;
    }
    
    protected function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    protected function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (!empty($value)) {
                if (isset($rule['min']) && strlen($value) < $rule['min']) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min'] . ' characters';
                }
                
                if (isset($rule['max']) && strlen($value) > $rule['max']) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . $rule['max'] . ' characters';
                }
                
                if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email address';
                }
                
                if (isset($rule['numeric']) && $rule['numeric'] && !is_numeric($value)) {
                    $errors[$field] = ucfirst($field) . ' must be a number';
                }
                
                if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                    $errors[$field] = ucfirst($field) . ' format is invalid';
                }
            }
        }
        
        return $errors;
    }
    
    protected function logActivity($action, $table = null, $recordId = null, $oldValues = null, $newValues = null) {
        $auditLog = $this->loadModel('AuditLog');
        $auditLog->log(
            $_SESSION['user_id'],
            $action,
            $table,
            $recordId,
            $oldValues,
            $newValues,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );
    }
    
    protected function uploadFile($file, $directory = 'documents') {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds limit');
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            throw new Exception('File type not allowed');
        }
        
        $uploadDir = UPLOAD_PATH . $directory . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        return $directory . '/' . $filename;
    }
    
    private function checkSession() {
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
            session_destroy();
            $this->redirect('/login?timeout=1');
        }
        $_SESSION['last_activity'] = time();
    }
    
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}