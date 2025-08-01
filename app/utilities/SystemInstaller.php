<?php
/**
 * System Installer Utility
 * Handles automated system installation and configuration
 */

class SystemInstaller {
    
    private $db;
    private $config;
    
    public function __construct($config = []) {
        $this->config = $config;
    }
    
    public function install() {
        try {
            $this->validateRequirements();
            $this->setupDatabase();
            $this->runMigrations();
            $this->seedDefaultData();
            $this->createAdminUser();
            $this->configureSystem();
            $this->setPermissions();
            $this->markInstalled();
            
            return ['success' => true, 'message' => 'Installation completed successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function validateRequirements() {
        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'gd' => extension_loaded('gd'),
            'openssl' => extension_loaded('openssl'),
            'fileinfo' => extension_loaded('fileinfo'),
            'mbstring' => extension_loaded('mbstring'),
            'json' => extension_loaded('json')
        ];
        
        $failed = [];
        foreach ($requirements as $requirement => $met) {
            if (!$met) {
                $failed[] = $requirement;
            }
        }
        
        if (!empty($failed)) {
            throw new Exception('Missing requirements: ' . implode(', ', $failed));
        }
        
        // Check directory permissions
        $directories = [
            __DIR__ . '/../../uploads/',
            __DIR__ . '/../../config/',
            __DIR__ . '/../../logs/'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            if (!is_writable($dir)) {
                throw new Exception("Directory not writable: {$dir}");
            }
        }
    }
    
    private function setupDatabase() {
        $dbConfig = $this->config['database'];
        
        try {
            $dsn = "mysql:host={$dbConfig['host']};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Connect to the database
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
            $this->db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
        } catch (PDOException $e) {
            throw new Exception('Database setup failed: ' . $e->getMessage());
        }
    }
    
    private function runMigrations() {
        $migrationFiles = glob(__DIR__ . '/../../database/migrations/*.sql');
        sort($migrationFiles);
        
        foreach ($migrationFiles as $file) {
            $sql = file_get_contents($file);
            
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->db->exec($statement);
                }
            }
        }
    }
    
    private function seedDefaultData() {
        // Insert roles
        $this->insertRoles();
        
        // Insert departments
        $this->insertDepartments();
        
        // Insert designations
        $this->insertDesignations();
        
        // Insert salary components
        $this->insertSalaryComponents();
        
        // Insert tax slabs
        $this->insertTaxSlabs();
        
        // Insert leave types
        $this->insertLeaveTypes();
        
        // Insert loan types
        $this->insertLoanTypes();
        
        // Insert holidays
        $this->insertHolidays();
    }
    
    private function insertRoles() {
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Full system access', 'permissions' => 'all'],
            ['name' => 'HR Admin', 'description' => 'HR operations access', 'permissions' => 'employees,payroll,reports'],
            ['name' => 'Payroll Manager', 'description' => 'Payroll processing access', 'permissions' => 'payroll,reports'],
            ['name' => 'Unit HR', 'description' => 'Unit level HR access', 'permissions' => 'employees,reports'],
            ['name' => 'Viewer', 'description' => 'Read-only access', 'permissions' => 'view']
        ];
        
        $stmt = $this->db->prepare("INSERT INTO roles (name, description, permissions) VALUES (?, ?, ?)");
        
        foreach ($roles as $role) {
            $stmt->execute([$role['name'], $role['description'], $role['permissions']]);
        }
    }
    
    private function insertDepartments() {
        $departments = [
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Finance & Accounts', 'code' => 'FIN'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Operations', 'code' => 'OPS']
        ];
        
        $stmt = $this->db->prepare("INSERT INTO departments (name, code) VALUES (?, ?)");
        
        foreach ($departments as $dept) {
            $stmt->execute([$dept['name'], $dept['code']]);
        }
    }
    
    private function insertDesignations() {
        $designations = [
            ['name' => 'Manager', 'code' => 'MGR', 'department_id' => 1],
            ['name' => 'Senior Executive', 'code' => 'SE', 'department_id' => 1],
            ['name' => 'Executive', 'code' => 'EXE', 'department_id' => 1],
            ['name' => 'Software Engineer', 'code' => 'SWE', 'department_id' => 2],
            ['name' => 'Senior Software Engineer', 'code' => 'SSE', 'department_id' => 2],
            ['name' => 'Team Lead', 'code' => 'TL', 'department_id' => 2],
            ['name' => 'Accountant', 'code' => 'ACC', 'department_id' => 3],
            ['name' => 'Finance Manager', 'code' => 'FM', 'department_id' => 3]
        ];
        
        $stmt = $this->db->prepare("INSERT INTO designations (name, code, department_id) VALUES (?, ?, ?)");
        
        foreach ($designations as $designation) {
            $stmt->execute([$designation['name'], $designation['code'], $designation['department_id']]);
        }
    }
    
    private function insertSalaryComponents() {
        $components = [
            ['name' => 'Basic Salary', 'code' => 'BASIC', 'type' => 'earning', 'is_mandatory' => 1, 'is_taxable' => 1, 'is_pf_applicable' => 1, 'display_order' => 1],
            ['name' => 'House Rent Allowance', 'code' => 'HRA', 'type' => 'earning', 'formula' => 'BASIC * 0.4', 'is_taxable' => 1, 'display_order' => 2],
            ['name' => 'Transport Allowance', 'code' => 'TA', 'type' => 'earning', 'formula' => '1600', 'is_taxable' => 1, 'display_order' => 3],
            ['name' => 'Medical Allowance', 'code' => 'MA', 'type' => 'earning', 'formula' => '1250', 'is_taxable' => 1, 'display_order' => 4],
            ['name' => 'Special Allowance', 'code' => 'SA', 'type' => 'earning', 'is_taxable' => 1, 'display_order' => 5],
            ['name' => 'Provident Fund', 'code' => 'PF', 'type' => 'deduction', 'formula' => 'BASIC * 0.12', 'is_mandatory' => 1, 'display_order' => 6],
            ['name' => 'ESI Contribution', 'code' => 'ESI', 'type' => 'deduction', 'formula' => '(BASIC + HRA + TA + MA) * 0.0075', 'display_order' => 7],
            ['name' => 'Professional Tax', 'code' => 'PT', 'type' => 'deduction', 'formula' => '200', 'display_order' => 8],
            ['name' => 'Income Tax (TDS)', 'code' => 'TDS', 'type' => 'deduction', 'display_order' => 9]
        ];
        
        $stmt = $this->db->prepare("INSERT INTO salary_components (name, code, type, formula, is_mandatory, is_taxable, is_pf_applicable, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($components as $component) {
            $stmt->execute([
                $component['name'],
                $component['code'],
                $component['type'],
                $component['formula'] ?? null,
                $component['is_mandatory'] ?? 0,
                $component['is_taxable'] ?? 1,
                $component['is_pf_applicable'] ?? 0,
                $component['display_order']
            ]);
        }
    }
    
    private function insertTaxSlabs() {
        $currentFY = $this->getCurrentFinancialYear();
        
        $taxSlabs = [
            [$currentFY, 0, 300000, 0],
            [$currentFY, 300001, 700000, 5],
            [$currentFY, 700001, 1000000, 10],
            [$currentFY, 1000001, 1200000, 15],
            [$currentFY, 1200001, 1500000, 20],
            [$currentFY, 1500001, null, 30]
        ];
        
        $stmt = $this->db->prepare("INSERT INTO tax_slabs (financial_year, min_amount, max_amount, tax_rate) VALUES (?, ?, ?, ?)");
        
        foreach ($taxSlabs as $slab) {
            $stmt->execute($slab);
        }
    }
    
    private function insertLeaveTypes() {
        $leaveTypes = [
            ['Casual Leave', 'CL', 1, 12],
            ['Sick Leave', 'SL', 1, 12],
            ['Earned Leave', 'EL', 1, 21],
            ['Maternity Leave', 'ML', 1, 183],
            ['Paternity Leave', 'PL', 1, 15]
        ];
        
        $stmt = $this->db->prepare("INSERT INTO leave_types (name, code, is_paid, max_days_per_year) VALUES (?, ?, ?, ?)");
        
        foreach ($leaveTypes as $type) {
            $stmt->execute($type);
        }
    }
    
    private function insertLoanTypes() {
        $loanTypes = [
            ['Personal Loan', 'PL', 500000, 12.00, 60],
            ['Home Loan', 'HL', 5000000, 8.50, 240],
            ['Vehicle Loan', 'VL', 1000000, 10.00, 84],
            ['Emergency Loan', 'EL', 100000, 0.00, 12]
        ];
        
        $stmt = $this->db->prepare("INSERT INTO loan_types (name, code, max_amount, interest_rate, max_tenure_months) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($loanTypes as $type) {
            $stmt->execute($type);
        }
    }
    
    private function insertHolidays() {
        $currentYear = date('Y');
        
        $holidays = [
            ['New Year', "{$currentYear}-01-01", 'national'],
            ['Republic Day', "{$currentYear}-01-26", 'national'],
            ['Independence Day', "{$currentYear}-08-15", 'national'],
            ['Gandhi Jayanti', "{$currentYear}-10-02", 'national'],
            ['Christmas', "{$currentYear}-12-25", 'national']
        ];
        
        $stmt = $this->db->prepare("INSERT INTO holidays (name, holiday_date, type) VALUES (?, ?, ?)");
        
        foreach ($holidays as $holiday) {
            $stmt->execute($holiday);
        }
    }
    
    private function createAdminUser() {
        $adminConfig = $this->config['admin'];
        
        $hashedPassword = password_hash($adminConfig['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, full_name, role_id, status) VALUES (?, ?, ?, ?, 1, 'active')");
        $stmt->execute([
            $adminConfig['username'],
            $adminConfig['email'],
            $hashedPassword,
            $adminConfig['full_name']
        ]);
    }
    
    private function configureSystem() {
        $settings = $this->config['settings'];
        
        $stmt = $this->db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
    }
    
    private function setPermissions() {
        $directories = [
            __DIR__ . '/../../uploads/' => 0777,
            __DIR__ . '/../../config/' => 0755,
            __DIR__ . '/../../logs/' => 0755
        ];
        
        foreach ($directories as $dir => $permission) {
            if (is_dir($dir)) {
                chmod($dir, $permission);
            }
        }
    }
    
    private function markInstalled() {
        file_put_contents(__DIR__ . '/../../.installed', json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'installer' => 'auto'
        ]));
    }
    
    private function getCurrentFinancialYear() {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
    
    public static function checkRequirements() {
        $requirements = [
            'PHP Version (>= 8.0)' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
            'GD Extension' => extension_loaded('gd'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'FileInfo Extension' => extension_loaded('fileinfo'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'JSON Extension' => extension_loaded('json')
        ];
        
        return $requirements;
    }
}