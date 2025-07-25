<?php
/**
 * Formula Editor Controller
 * Handles advanced formula creation, validation, and custom queries
 */

require_once __DIR__ . '/../core/Controller.php';

class FormulaEditorController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $this->loadView('formula-editor/index', [
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function builder() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        // Get available components and functions
        $components = $this->getAvailableComponents();
        $functions = $this->getAvailableFunctions();
        $variables = $this->getSystemVariables();
        
        $this->loadView('formula-editor/builder', [
            'components' => $components,
            'functions' => $functions,
            'variables' => $variables,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function validateFormula() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $formula = $input['formula'] ?? '';
        $context = $input['context'] ?? [];
        
        if (empty($formula)) {
            $this->jsonResponse(['success' => false, 'message' => 'Formula is required']);
            return;
        }
        
        $formulaEngine = $this->loadModel('FormulaEngine');
        $result = $formulaEngine->validateFormula($formula, $context);
        
        $this->jsonResponse($result);
    }
    
    public function testFormula() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $formula = $input['formula'] ?? '';
        $testData = $input['test_data'] ?? [];
        
        if (empty($formula)) {
            $this->jsonResponse(['success' => false, 'message' => 'Formula is required']);
            return;
        }
        
        $formulaEngine = $this->loadModel('FormulaEngine');
        
        try {
            $result = $formulaEngine->evaluate($formula, $testData);
            $this->jsonResponse([
                'success' => true,
                'result' => $result,
                'formatted_result' => number_format($result, 2)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function saveFormula() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $data = [
            'name' => $input['name'] ?? '',
            'formula' => $input['formula'] ?? '',
            'description' => $input['description'] ?? '',
            'category' => $input['category'] ?? 'custom',
            'variables' => json_encode($input['variables'] ?? []),
            'created_by' => $_SESSION['user_id']
        ];
        
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'formula' => ['required' => true]
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->db->insert('formula_templates', $data);
            
            $this->logActivity('create_formula_template', 'formula_templates', $id);
            $this->jsonResponse(['success' => true, 'id' => $id, 'message' => 'Formula saved successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to save formula'], 500);
        }
    }
    
    public function customQuery() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->executeCustomQuery();
        } else {
            $this->showQueryBuilder();
        }
    }
    
    private function executeCustomQuery() {
        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? '';
        $parameters = $input['parameters'] ?? [];
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        if (empty($query)) {
            $this->jsonResponse(['success' => false, 'message' => 'Query is required'], 400);
            return;
        }
        
        // Validate query for security
        if (!$this->isQuerySafe($query)) {
            $this->jsonResponse(['success' => false, 'message' => 'Query contains unsafe operations'], 400);
            return;
        }
        
        try {
            $result = $this->db->fetchAll($query, $parameters);
            
            $this->logActivity('execute_custom_query', 'custom_queries', null);
            $this->jsonResponse([
                'success' => true,
                'data' => $result,
                'count' => count($result)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Query execution failed: ' . $e->getMessage()
            ], 400);
        }
    }
    
    public function getFormulaTemplates() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $category = $_GET['category'] ?? '';
        $conditions = '';
        $params = [];
        
        if (!empty($category)) {
            $conditions = 'category = :category';
            $params['category'] = $category;
        }
        
        $templates = $this->db->fetchAll(
            "SELECT ft.*, u.full_name as created_by_name 
             FROM formula_templates ft
             LEFT JOIN users u ON ft.created_by = u.id
             " . (!empty($conditions) ? "WHERE {$conditions}" : "") . "
             ORDER BY ft.created_at DESC",
            $params
        );
        
        $this->jsonResponse(['success' => true, 'templates' => $templates]);
    }
    
    public function exportFormula() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Formula ID is required'], 400);
            return;
        }
        
        $formula = $this->db->fetch("SELECT * FROM formula_templates WHERE id = :id", ['id' => $id]);
        
        if (!$formula) {
            $this->jsonResponse(['success' => false, 'message' => 'Formula not found'], 404);
            return;
        }
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="formula_' . $formula['name'] . '.json"');
        
        echo json_encode($formula, JSON_PRETTY_PRINT);
        exit;
    }
    
    public function saveQuery() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $data = [
            'name' => $input['name'] ?? '',
            'query_sql' => $input['query'] ?? '',
            'description' => $input['description'] ?? '',
            'category' => $input['category'] ?? 'custom',
            'parameters' => json_encode($input['parameters'] ?? []),
            'created_by' => $_SESSION['user_id']
        ];
        
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'query_sql' => ['required' => true]
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        // Validate query safety
        if (!$this->isQuerySafe($data['query_sql'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Query contains unsafe operations'], 400);
            return;
        }
        
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->db->insert('saved_queries', $data);
            
            $this->logActivity('create_saved_query', 'saved_queries', $id);
            $this->jsonResponse(['success' => true, 'id' => $id, 'message' => 'Query saved successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to save query'], 500);
        }
    }
    
    public function getQueryHistory() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        $history = $this->db->fetchAll(
            "SELECT qe.*, sq.name as query_name, u.full_name as executed_by_name
             FROM query_executions qe
             LEFT JOIN saved_queries sq ON qe.query_id = sq.id
             LEFT JOIN users u ON qe.executed_by = u.id
             WHERE qe.executed_by = :user_id
             ORDER BY qe.executed_at DESC
             LIMIT 50",
            ['user_id' => $_SESSION['user_id']]
        );
        
        $this->jsonResponse(['success' => true, 'history' => $history]);
    }
    
    private function getAvailableComponents() {
        return $this->db->fetchAll(
            "SELECT id, name, code, type FROM salary_components WHERE status = 'active' ORDER BY display_order ASC"
        );
    }
    
    private function getAvailableFunctions() {
        return [
            'ROUND' => ['description' => 'Round to specified decimal places', 'syntax' => 'ROUND(value, decimals)'],
            'CEIL' => ['description' => 'Round up to nearest integer', 'syntax' => 'CEIL(value)'],
            'FLOOR' => ['description' => 'Round down to nearest integer', 'syntax' => 'FLOOR(value)'],
            'ABS' => ['description' => 'Absolute value', 'syntax' => 'ABS(value)'],
            'MIN' => ['description' => 'Minimum of two values', 'syntax' => 'MIN(value1, value2)'],
            'MAX' => ['description' => 'Maximum of two values', 'syntax' => 'MAX(value1, value2)'],
            'IF' => ['description' => 'Conditional expression', 'syntax' => 'IF(condition, true_value, false_value)'],
            'SUM' => ['description' => 'Sum of multiple values', 'syntax' => 'SUM(value1, value2, ...)'],
            'AVG' => ['description' => 'Average of multiple values', 'syntax' => 'AVG(value1, value2, ...)']
        ];
    }
    
    private function getSystemVariables() {
        return [
            'BASIC' => 'Basic Salary',
            'GROSS' => 'Gross Salary (Sum of all earnings)',
            'DAYS_IN_MONTH' => 'Total days in month',
            'WORKING_DAYS' => 'Working days in month',
            'PRESENT_DAYS' => 'Employee present days',
            'LOP_DAYS' => 'Loss of Pay days',
            'OVERTIME_HOURS' => 'Overtime hours worked',
            'EXPERIENCE_YEARS' => 'Years of experience',
            'GRADE_MULTIPLIER' => 'Grade-based multiplier'
        ];
    }
    
    private function showQueryBuilder() {
        $tables = $this->getAvailableTables();
        $savedQueries = $this->getSavedQueries();
        
        $this->loadView('formula-editor/query-builder', [
            'tables' => $tables,
            'saved_queries' => $savedQueries,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function getAvailableTables() {
        return [
            'employees' => [
                'name' => 'Employees',
                'fields' => ['id', 'emp_code', 'first_name', 'last_name', 'email', 'department_id', 'designation_id', 'join_date', 'status']
            ],
            'payroll_transactions' => [
                'name' => 'Payroll Transactions',
                'fields' => ['id', 'employee_id', 'period_id', 'component_id', 'amount', 'created_at']
            ],
            'salary_components' => [
                'name' => 'Salary Components',
                'fields' => ['id', 'name', 'code', 'type', 'formula', 'is_taxable']
            ],
            'departments' => [
                'name' => 'Departments',
                'fields' => ['id', 'name', 'code', 'head_id']
            ],
            'attendance' => [
                'name' => 'Attendance',
                'fields' => ['id', 'employee_id', 'attendance_date', 'status', 'total_hours']
            ]
        ];
    }
    
    private function getSavedQueries() {
        return $this->db->fetchAll(
            "SELECT * FROM saved_queries WHERE created_by = :user_id ORDER BY created_at DESC",
            ['user_id' => $_SESSION['user_id']]
        );
    }
    
    private function isQuerySafe($query) {
        $query = strtoupper($query);
        
        // Disallow dangerous operations
        $dangerousKeywords = [
            'DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE',
            'TRUNCATE', 'REPLACE', 'GRANT', 'REVOKE', 'EXEC', 'EXECUTE'
        ];
        
        foreach ($dangerousKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                return false;
            }
        }
        
        // Only allow SELECT statements
        if (!preg_match('/^\s*SELECT\s+/i', $query)) {
            return false;
        }
        
        return true;
    }
}