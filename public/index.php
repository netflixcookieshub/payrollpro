<?php
/**
 * Front Controller - Entry point for the application
 */

// Check if system is installed
if (!file_exists(__DIR__ . '/../.installed') && !str_contains($_SERVER['REQUEST_URI'], '/setup')) {
    header('Location: /setup');
    exit;
}

session_start();

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Model.php';

class Router {
    private $routes = [];
    
    public function addRoute($method, $pattern, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch($requestUri, $requestMethod) {
        // Remove query string from URI
        $uri = parse_url($requestUri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod && $route['method'] !== 'ANY') {
                continue;
            }
            
            // Convert route pattern to regex
            $pattern = preg_replace('/\{(\w+)\}/', '(\w+)', $route['pattern']);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Load and instantiate controller
                $controllerName = $route['controller'] . 'Controller';
                $controllerFile = __DIR__ . "/../app/controllers/{$controllerName}.php";
                
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    $controller = new $controllerName();
                    
                    $action = $route['action'];
                    if (method_exists($controller, $action)) {
                        call_user_func_array([$controller, $action], $matches);
                        return;
                    }
                }
            }
        }
        
        // 404 Not Found
        $this->show404();
    }
    
    private function show404() {
        http_response_code(404);
        include __DIR__ . '/../app/views/errors/404.php';
    }
}

// Initialize router
$router = new Router();

// Define routes
$router->addRoute('GET', '/', 'Dashboard', 'index');
$router->addRoute('GET', '/dashboard', 'Dashboard', 'index');

// Setup routes
$router->addRoute('GET', '/setup', 'Setup', 'index');
$router->addRoute('GET', '/setup/install', 'Setup', 'install');
$router->addRoute('POST', '/setup/install', 'Setup', 'install');
$router->addRoute('POST', '/setup/database', 'Setup', 'database');
$router->addRoute('POST', '/setup/migrate', 'Setup', 'migrate');
$router->addRoute('POST', '/setup/seed', 'Setup', 'seed');
$router->addRoute('POST', '/setup/complete', 'Setup', 'complete');

// Authentication routes
$router->addRoute('GET', '/login', 'Auth', 'login');
$router->addRoute('POST', '/login', 'Auth', 'login');
$router->addRoute('GET', '/logout', 'Auth', 'logout');
$router->addRoute('GET', '/profile', 'Auth', 'profile');
$router->addRoute('POST', '/profile', 'Auth', 'profile');
$router->addRoute('GET', '/change-password', 'Auth', 'changePassword');
$router->addRoute('POST', '/change-password', 'Auth', 'changePassword');

// Employee routes
$router->addRoute('GET', '/employees', 'Employee', 'index');
$router->addRoute('GET', '/employees/create', 'Employee', 'create');
$router->addRoute('POST', '/employees/create', 'Employee', 'create');
$router->addRoute('GET', '/employees/{id}', 'Employee', 'view');
$router->addRoute('GET', '/employees/{id}/edit', 'Employee', 'edit');
$router->addRoute('POST', '/employees/{id}/edit', 'Employee', 'edit');
$router->addRoute('POST', '/employees/{id}/delete', 'Employee', 'delete');
$router->addRoute('GET', '/employees/{id}/salary-structure', 'Employee', 'salaryStructure');
$router->addRoute('POST', '/employees/{id}/salary-structure', 'Employee', 'salaryStructure');
$router->addRoute('POST', '/employees/{id}/upload-document', 'Employee', 'uploadDocument');
$router->addRoute('GET', '/employees/export', 'Employee', 'export');

// Payroll routes
$router->addRoute('GET', '/payroll', 'Payroll', 'index');
$router->addRoute('GET', '/payroll/periods', 'Payroll', 'periods');
$router->addRoute('POST', '/payroll/periods', 'Payroll', 'periods');
$router->addRoute('GET', '/payroll/process', 'Payroll', 'process');
$router->addRoute('POST', '/payroll/process', 'Payroll', 'process');
$router->addRoute('POST', '/payroll/reprocess', 'Payroll', 'reprocess');
$router->addRoute('GET', '/payroll/calculate-salary', 'Payroll', 'calculateSalary');
$router->addRoute('GET', '/payroll/advanced-calculator', 'Payroll', 'advancedCalculator');
$router->addRoute('GET', '/payroll/salary-calculator', 'Payroll', 'salaryCalculator');
$router->addRoute('POST', '/payroll/lock-period', 'Payroll', 'lockPeriod');
$router->addRoute('GET', '/payroll/payslip/{employeeId}/{periodId}', 'Payroll', 'payslip');
$router->addRoute('GET', '/payroll/arrears', 'Payroll', 'arrears');
$router->addRoute('POST', '/payroll/create-arrears', 'Payroll', 'createArrears');
$router->addRoute('POST', '/payroll/approve-arrears', 'Payroll', 'approveArrears');
$router->addRoute('POST', '/payroll/reject-arrears', 'Payroll', 'rejectArrears');
$router->addRoute('POST', '/payroll/bulk-approve-arrears', 'Payroll', 'bulkApproveArrears');
$router->addRoute('GET', '/payroll/variable-pay', 'Payroll', 'variablePay');
$router->addRoute('POST', '/payroll/create-variable-pay', 'Payroll', 'createVariablePay');
$router->addRoute('POST', '/payroll/approve-variable-pay', 'Payroll', 'approveVariablePay');
$router->addRoute('POST', '/payroll/reject-variable-pay', 'Payroll', 'rejectVariablePay');
$router->addRoute('POST', '/payroll/bulk-approve-variable-pay', 'Payroll', 'bulkApproveVariablePay');
$router->addRoute('GET', '/payroll/bulk-process', 'Payroll', 'bulkProcess');
$router->addRoute('POST', '/payroll/bulk-process', 'Payroll', 'bulkProcess');
$router->addRoute('GET', '/payroll/export-payslips', 'Payroll', 'exportPayslips');
$router->addRoute('GET', '/payroll/performance-bonus', 'Payroll', 'performanceBonus');
$router->addRoute('POST', '/payroll/calculate-performance-bonus', 'Payroll', 'calculatePerformanceBonus');

// Master data routes
$router->addRoute('GET', '/departments', 'Department', 'index');
$router->addRoute('POST', '/departments', 'Department', 'index');
$router->addRoute('GET', '/designations', 'Designation', 'index');
$router->addRoute('POST', '/designations', 'Designation', 'index');
$router->addRoute('GET', '/salary-components', 'SalaryComponent', 'index');
$router->addRoute('POST', '/salary-components', 'SalaryComponent', 'index');
$router->addRoute('GET', '/loan-types', 'LoanType', 'index');
$router->addRoute('POST', '/loan-types', 'LoanType', 'index');
$router->addRoute('GET', '/leave-types', 'LeaveType', 'index');
$router->addRoute('POST', '/leave-types', 'LeaveType', 'index');
$router->addRoute('GET', '/holidays', 'Holiday', 'index');
$router->addRoute('POST', '/holidays', 'Holiday', 'index');
$router->addRoute('GET', '/tax-slabs', 'TaxSlab', 'index');
$router->addRoute('POST', '/tax-slabs', 'TaxSlab', 'index');

// Report routes
$router->addRoute('GET', '/reports', 'Report', 'index');
$router->addRoute('GET', '/reports/salary-register', 'Report', 'salaryRegister');
$router->addRoute('POST', '/reports/salary-register', 'Report', 'salaryRegister');
$router->addRoute('GET', '/reports/component-report', 'Report', 'componentReport');
$router->addRoute('POST', '/reports/component-report', 'Report', 'componentReport');
$router->addRoute('GET', '/reports/bank-transfer', 'Report', 'bankTransfer');
$router->addRoute('POST', '/reports/bank-transfer', 'Report', 'bankTransfer');
$router->addRoute('GET', '/reports/tax-report', 'Report', 'taxReport');
$router->addRoute('POST', '/reports/tax-report', 'Report', 'taxReport');
$router->addRoute('GET', '/reports/loan-report', 'Report', 'loanReport');
$router->addRoute('POST', '/reports/loan-report', 'Report', 'loanReport');

// Attendance routes
$router->addRoute('GET', '/attendance', 'Attendance', 'index');
$router->addRoute('GET', '/attendance/mark', 'Attendance', 'mark');
$router->addRoute('POST', '/attendance/mark', 'Attendance', 'mark');
$router->addRoute('POST', '/attendance/bulk-mark', 'Attendance', 'bulkMark');
$router->addRoute('GET', '/attendance/report', 'Attendance', 'report');
$router->addRoute('POST', '/attendance/report', 'Attendance', 'report');

// Loan routes
$router->addRoute('GET', '/loans', 'Loan', 'index');
$router->addRoute('GET', '/loans/create', 'Loan', 'create');
$router->addRoute('POST', '/loans/create', 'Loan', 'create');
$router->addRoute('GET', '/loans/{id}', 'Loan', 'view');
$router->addRoute('GET', '/loans/{id}/payment', 'Loan', 'payment');
$router->addRoute('POST', '/loans/{id}/payment', 'Loan', 'payment');

// User management routes
$router->addRoute('GET', '/users', 'User', 'index');
$router->addRoute('GET', '/users/create', 'User', 'create');
$router->addRoute('POST', '/users/create', 'User', 'create');
$router->addRoute('GET', '/users/{id}/edit', 'User', 'edit');
$router->addRoute('POST', '/users/{id}/edit', 'User', 'edit');

// Formula Editor routes
$router->addRoute('GET', '/formula-editor', 'FormulaEditor', 'index');
$router->addRoute('GET', '/formula-editor/builder', 'FormulaEditor', 'builder');
$router->addRoute('POST', '/formula-editor/validate', 'FormulaEditor', 'validateFormula');
$router->addRoute('POST', '/formula-editor/test', 'FormulaEditor', 'testFormula');
$router->addRoute('POST', '/formula-editor/save', 'FormulaEditor', 'saveFormula');
$router->addRoute('GET', '/formula-editor/templates', 'FormulaEditor', 'getFormulaTemplates');
$router->addRoute('GET', '/formula-editor/export', 'FormulaEditor', 'exportFormula');
$router->addRoute('GET', '/formula-editor/custom-query', 'FormulaEditor', 'customQuery');
$router->addRoute('POST', '/formula-editor/custom-query', 'FormulaEditor', 'customQuery');
$router->addRoute('POST', '/formula-editor/save-query', 'FormulaEditor', 'saveQuery');
$router->addRoute('GET', '/formula-editor/query-history', 'FormulaEditor', 'getQueryHistory');

// API routes for AJAX calls
$router->addRoute('GET', '/integrations', 'Integration', 'index');
$router->addRoute('GET', '/integrations/configure/{integration}', 'Integration', 'configure');
$router->addRoute('POST', '/integrations/configure/{integration}', 'Integration', 'configure');
$router->addRoute('POST', '/integrations/test/{integration}', 'Integration', 'test');
$router->addRoute('POST', '/integrations/sync/{integration}', 'Integration', 'sync');
$router->addRoute('POST', '/integrations/webhook/{integration}', 'Integration', 'webhook');
$router->addRoute('GET', '/integrations/export-data', 'Integration', 'exportData');
$router->addRoute('GET', '/integrations/import', 'Integration', 'importData');
$router->addRoute('POST', '/integrations/import', 'Integration', 'importData');
$router->addRoute('GET', '/integrations/api-keys', 'Integration', 'apiKeys');
$router->addRoute('POST', '/integrations/api-keys', 'Integration', 'apiKeys');

// API v1 routes
$router->addRoute('GET', '/api/v1/employees', 'ApiV1', 'employees');
$router->addRoute('POST', '/api/v1/employees', 'ApiV1', 'employees');
$router->addRoute('GET', '/api/v1/employees/{id}', 'ApiV1', 'employee');
$router->addRoute('PUT', '/api/v1/employees/{id}', 'ApiV1', 'employee');
$router->addRoute('DELETE', '/api/v1/employees/{id}', 'ApiV1', 'employee');
$router->addRoute('GET', '/api/v1/attendance', 'ApiV1', 'attendance');
$router->addRoute('POST', '/api/v1/attendance', 'ApiV1', 'attendance');
$router->addRoute('GET', '/api/v1/payroll', 'ApiV1', 'payroll');
$router->addRoute('POST', '/api/v1/payroll', 'ApiV1', 'payroll');
$router->addRoute('GET', '/api/v1/salary-structure/{employeeId}', 'ApiV1', 'salaryStructure');
$router->addRoute('POST', '/api/v1/salary-structure/{employeeId}', 'ApiV1', 'salaryStructure');

$router->addRoute('GET', '/api/attendance-summary', 'Api', 'attendanceSummary');
$router->addRoute('GET', '/api/current-period', 'Api', 'currentPeriod');
$router->addRoute('GET', '/api/employee-search', 'Api', 'employeeSearch');
$router->addRoute('GET', '/api/salary-calculator', 'Api', 'salaryCalculator');

$router->addRoute('GET', '/workflow', 'Workflow', 'index');
$router->addRoute('POST', '/workflow/approve', 'Workflow', 'approve');
$router->addRoute('POST', '/workflow/reject', 'Workflow', 'reject');

// System administration routes
$router->addRoute('GET', '/system', 'System', 'dashboard');
$router->addRoute('GET', '/system/backup-manager', 'System', 'backupManager');
$router->addRoute('GET', '/system/security', 'System', 'securityDashboard');
$router->addRoute('POST', '/system/create-backup', 'System', 'createBackup');
$router->addRoute('POST', '/system/restore-backup', 'System', 'restoreBackup');
$router->addRoute('POST', '/system/cleanup-backups', 'System', 'cleanupBackups');
$router->addRoute('POST', '/system/block-ip', 'System', 'blockIP');
$router->addRoute('POST', '/system/unblock-ip', 'System', 'unblockIP');
$router->addRoute('GET', '/system/health', 'System', 'systemHealth');
$router->addRoute('POST', '/system/maintenance', 'System', 'systemMaintenance');
$router->addRoute('GET', '/system/export-security-logs', 'System', 'exportSecurityLogs');

// Advanced reporting routes
$router->addRoute('GET', '/reports/analytics', 'AdvancedReport', 'dashboard');
$router->addRoute('GET', '/reports/salary-trends', 'AdvancedReport', 'salaryTrends');
$router->addRoute('GET', '/reports/cost-analysis', 'AdvancedReport', 'costAnalysis');
$router->addRoute('POST', '/reports/cost-analysis', 'AdvancedReport', 'costAnalysis');
$router->addRoute('GET', '/reports/compliance', 'AdvancedReport', 'complianceReport');
$router->addRoute('GET', '/reports/predictive', 'AdvancedReport', 'predictiveAnalytics');
$router->addRoute('GET', '/reports/export-analytics', 'AdvancedReport', 'exportAnalytics');

// Static file serving (for development)
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|svg)$/', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath)) {
        $mimeType = mime_content_type($filePath);
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
        exit;
    }
}

// Redirect root to dashboard if logged in, otherwise to login
if ($requestUri === '/') {
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
}

// Dispatch the request
try {
    $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
} catch (Exception $e) {
    // Log error and show 500 page
    error_log("Application error: " . $e->getMessage());
    http_response_code(500);
    echo "Internal Server Error";
}