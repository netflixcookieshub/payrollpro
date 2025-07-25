<?php
/**
 * API V1 Controller
 * Handles REST API endpoints for external integrations
 */

require_once __DIR__ . '/../core/Controller.php';

class ApiV1Controller extends Controller {
    
    private $apiKey;
    
    public function __construct() {
        parent::__construct();
        $this->authenticateAPI();
    }
    
    // Employee endpoints
    public function employees() {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getEmployees();
                break;
            case 'POST':
                $this->createEmployee();
                break;
            default:
                $this->apiResponse(['error' => 'Method not allowed'], 405);
        }
    }
    
    public function employee($id) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getEmployee($id);
                break;
            case 'PUT':
                $this->updateEmployee($id);
                break;
            case 'DELETE':
                $this->deleteEmployee($id);
                break;
            default:
                $this->apiResponse(['error' => 'Method not allowed'], 405);
        }
    }
    
    // Attendance endpoints
    public function attendance() {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getAttendance();
                break;
            case 'POST':
                $this->markAttendance();
                break;
            default:
                $this->apiResponse(['error' => 'Method not allowed'], 405);
        }
    }
    
    // Payroll endpoints
    public function payroll() {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getPayrollData();
                break;
            case 'POST':
                $this->processPayroll();
                break;
            default:
                $this->apiResponse(['error' => 'Method not allowed'], 405);
        }
    }
    
    // Salary structure endpoints
    public function salaryStructure($employeeId) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getSalaryStructure($employeeId);
                break;
            case 'POST':
                $this->updateSalaryStructure($employeeId);
                break;
            default:
                $this->apiResponse(['error' => 'Method not allowed'], 405);
        }
    }
    
    private function authenticateAPI() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->apiResponse(['error' => 'Missing or invalid authorization header'], 401);
            return;
        }
        
        $apiKey = $matches[1];
        
        // Verify API key
        $keyRecord = $this->db->fetch(
            "SELECT * FROM api_keys WHERE status = 'active'",
            []
        );
        
        $validKey = false;
        if ($keyRecord) {
            foreach ($this->db->fetchAll("SELECT * FROM api_keys WHERE status = 'active'") as $key) {
                if (password_verify($apiKey, $key['key_hash'])) {
                    $this->apiKey = $key;
                    $validKey = true;
                    break;
                }
            }
        }
        
        if (!$validKey) {
            $this->apiResponse(['error' => 'Invalid API key'], 401);
            return;
        }
        
        // Log API access
        $this->logApiAccess();
    }
    
    private function getEmployees() {
        $page = intval($_GET['page'] ?? 1);
        $limit = min(100, intval($_GET['limit'] ?? 25));
        $department = $_GET['department'] ?? '';
        $status = $_GET['status'] ?? 'active';
        
        $conditions = ['e.status = :status'];
        $params = ['status' => $status];
        
        if (!empty($department)) {
            $conditions[] = 'd.code = :department';
            $params['department'] = $department;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT e.*, d.name as department_name, des.name as designation_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                {$whereClause}
                ORDER BY e.emp_code
                LIMIT {$limit} OFFSET {$offset}";
        
        $employees = $this->db->fetchAll($sql, $params);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM employees e LEFT JOIN departments d ON e.department_id = d.id {$whereClause}";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        $this->apiResponse([
            'data' => $employees,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    private function getEmployee($id) {
        $employee = $this->db->fetch(
            "SELECT e.*, d.name as department_name, des.name as designation_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN designations des ON e.designation_id = des.id
             WHERE e.id = :id",
            ['id' => $id]
        );
        
        if (!$employee) {
            $this->apiResponse(['error' => 'Employee not found'], 404);
            return;
        }
        
        $this->apiResponse(['data' => $employee]);
    }
    
    private function createEmployee() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->hasPermission('write')) {
            $this->apiResponse(['error' => 'Insufficient permissions'], 403);
            return;
        }
        
        $employeeModel = $this->loadModel('Employee');
        $result = $employeeModel->createEmployee($input);
        
        if ($result['success']) {
            $this->apiResponse(['data' => ['id' => $result['id']], 'message' => 'Employee created'], 201);
        } else {
            $this->apiResponse(['error' => $result['message'], 'errors' => $result['errors'] ?? []], 400);
        }
    }
    
    private function updateEmployee($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->hasPermission('write')) {
            $this->apiResponse(['error' => 'Insufficient permissions'], 403);
            return;
        }
        
        $employeeModel = $this->loadModel('Employee');
        $result = $employeeModel->updateEmployee($id, $input);
        
        if ($result['success']) {
            $this->apiResponse(['message' => 'Employee updated']);
        } else {
            $this->apiResponse(['error' => $result['message']], 400);
        }
    }
    
    private function deleteEmployee($id) {
        if (!$this->hasPermission('delete')) {
            $this->apiResponse(['error' => 'Insufficient permissions'], 403);
            return;
        }
        
        $employeeModel = $this->loadModel('Employee');
        $result = $employeeModel->update($id, ['status' => 'deleted']);
        
        if ($result) {
            $this->apiResponse(['message' => 'Employee deleted']);
        } else {
            $this->apiResponse(['error' => 'Failed to delete employee'], 400);
        }
    }
    
    private function getAttendance() {
        $date = $_GET['date'] ?? date('Y-m-d');
        $employeeId = $_GET['employee_id'] ?? '';
        
        $conditions = ['a.attendance_date = :date'];
        $params = ['date' => $date];
        
        if (!empty($employeeId)) {
            $conditions[] = 'a.employee_id = :emp_id';
            $params['emp_id'] = $employeeId;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        
        $sql = "SELECT a.*, e.emp_code, e.first_name, e.last_name
                FROM attendance a
                JOIN employees e ON a.employee_id = e.id
                {$whereClause}
                ORDER BY e.emp_code";
        
        $attendance = $this->db->fetchAll($sql, $params);
        
        $this->apiResponse(['data' => $attendance]);
    }
    
    private function markAttendance() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->hasPermission('write')) {
            $this->apiResponse(['error' => 'Insufficient permissions'], 403);
            return;
        }
        
        $attendanceModel = $this->loadModel('Attendance');
        $result = $attendanceModel->markAttendance($input);
        
        if ($result['success']) {
            $this->apiResponse(['data' => ['id' => $result['id']], 'message' => 'Attendance marked'], 201);
        } else {
            $this->apiResponse(['error' => $result['message']], 400);
        }
    }
    
    private function getPayrollData() {
        $periodId = $_GET['period_id'] ?? '';
        $employeeId = $_GET['employee_id'] ?? '';
        
        if (empty($periodId)) {
            $this->apiResponse(['error' => 'Period ID required'], 400);
            return;
        }
        
        $conditions = ['pt.period_id = :period_id'];
        $params = ['period_id' => $periodId];
        
        if (!empty($employeeId)) {
            $conditions[] = 'pt.employee_id = :emp_id';
            $params['emp_id'] = $employeeId;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        
        $sql = "SELECT pt.*, e.emp_code, e.first_name, e.last_name, 
                       sc.name as component_name, sc.code as component_code, sc.type
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                {$whereClause}
                ORDER BY e.emp_code, sc.display_order";
        
        $payrollData = $this->db->fetchAll($sql, $params);
        
        $this->apiResponse(['data' => $payrollData]);
    }
    
    private function processPayroll() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->hasPermission('write')) {
            $this->apiResponse(['error' => 'Insufficient permissions'], 403);
            return;
        }
        
        $payrollModel = $this->loadModel('Payroll');
        $result = $payrollModel->processPayroll($input['period_id'], $input['employee_ids'] ?? []);
        
        if ($result['success']) {
            $this->apiResponse(['message' => 'Payroll processed', 'processed' => $result['processed']]);
        } else {
            $this->apiResponse(['error' => $result['message']], 400);
        }
    }
    
    private function getSalaryStructure($employeeId) {
        $sql = "SELECT ss.*, sc.name as component_name, sc.code as component_code, sc.type
                FROM salary_structures ss
                JOIN salary_components sc ON ss.component_id = sc.id
                WHERE ss.employee_id = :emp_id AND (ss.end_date IS NULL OR ss.end_date >= CURDATE())
                ORDER BY sc.display_order";
        
        $structure = $this->db->fetchAll($sql, ['emp_id' => $employeeId]);
        
        $this->apiResponse(['data' => $structure]);
    }
    
    private function updateSalaryStructure($employeeId) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->hasPermission('write')) {
            $this->apiResponse(['error' => 'Insufficient permissions'], 403);
            return;
        }
        
        $employeeModel = $this->loadModel('Employee');
        $result = $employeeModel->assignSalaryStructure($employeeId, $input['components']);
        
        if ($result['success']) {
            $this->apiResponse(['message' => 'Salary structure updated']);
        } else {
            $this->apiResponse(['error' => $result['message']], 400);
        }
    }
    
    private function hasPermission($permission) {
        $permissions = explode(',', $this->apiKey['permissions']);
        return in_array($permission, $permissions) || in_array('all', $permissions);
    }
    
    private function apiResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    private function logApiAccess() {
        $this->db->insert('api_access_logs', [
            'api_key_id' => $this->apiKey['id'],
            'endpoint' => $_SERVER['REQUEST_URI'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}