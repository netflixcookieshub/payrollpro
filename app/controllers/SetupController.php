<?php
/**
 * Setup Controller
 * Handles system installation and configuration
 */

require_once __DIR__ . '/../core/Controller.php';

class SetupController extends Controller {
    
    public function index() {
        // Check if system is already installed
        if ($this->isSystemInstalled()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->loadView('setup/index');
    }
    
    public function install() {
        if ($this->isSystemInstalled()) {
            $this->jsonResponse(['success' => false, 'message' => 'System already installed']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleInstallation();
        } else {
            $this->showInstallationForm();
        }
    }
    
    public function database() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->testDatabaseConnection();
        } else {
            $this->showDatabaseForm();
        }
    }
    
    public function migrate() {
        $this->runMigrations();
    }
    
    public function seed() {
        $this->seedDatabase();
    }
    
    public function complete() {
        $this->completeInstallation();
    }
    
    private function handleInstallation() {
        $data = $this->sanitizeInput($_POST);
        
        try {
            // Step 1: Test database connection
            $dbResult = $this->testDatabase($data['database']);
            if (!$dbResult['success']) {
                throw new Exception('Database connection failed: ' . $dbResult['message']);
            }
            
            // Step 2: Run migrations
            $migrationResult = $this->runDatabaseMigrations();
            if (!$migrationResult['success']) {
                throw new Exception('Migration failed: ' . $migrationResult['message']);
            }
            
            // Step 3: Create admin user
            $adminResult = $this->createAdminUser($data['admin']);
            if (!$adminResult['success']) {
                throw new Exception('Admin user creation failed: ' . $adminResult['message']);
            }
            
            // Step 4: Configure system settings
            $configResult = $this->configureSystem($data['settings']);
            if (!$configResult['success']) {
                throw new Exception('System configuration failed: ' . $configResult['message']);
            }
            
            // Step 5: Mark installation as complete
            $this->markInstallationComplete();
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Installation completed successfully',
                'redirect' => '/dashboard'
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function testDatabaseConnection() {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->testDatabase($data);
        $this->jsonResponse($result);
    }
    
    private function testDatabase($config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            return ['success' => true, 'message' => 'Database connection successful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function runDatabaseMigrations() {
        try {
            $migrationFiles = glob(__DIR__ . '/../../database/migrations/*.sql');
            
            foreach ($migrationFiles as $file) {
                $sql = file_get_contents($file);
                $this->db->query($sql);
            }
            
            return ['success' => true, 'message' => 'Migrations completed'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function createAdminUser($adminData) {
        try {
            $hashedPassword = password_hash($adminData['password'], PASSWORD_DEFAULT);
            
            $this->db->insert('users', [
                'username' => $adminData['username'],
                'email' => $adminData['email'],
                'password' => $hashedPassword,
                'full_name' => $adminData['full_name'],
                'role_id' => 1, // Super Admin
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return ['success' => true, 'message' => 'Admin user created'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function configureSystem($settings) {
        try {
            // Update configuration file
            $configContent = "<?php\n";
            $configContent .= "define('APP_NAME', '{$settings['app_name']}');\n";
            $configContent .= "define('BASE_URL', '{$settings['base_url']}');\n";
            $configContent .= "define('TIMEZONE', '{$settings['timezone']}');\n";
            $configContent .= "define('CURRENCY', '{$settings['currency']}');\n";
            
            file_put_contents(__DIR__ . '/../../config/app_config.php', $configContent);
            
            return ['success' => true, 'message' => 'System configured'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function markInstallationComplete() {
        file_put_contents(__DIR__ . '/../../.installed', date('Y-m-d H:i:s'));
    }
    
    private function isSystemInstalled() {
        return file_exists(__DIR__ . '/../../.installed');
    }
    
    private function showInstallationForm() {
        $this->loadView('setup/install');
    }
    
    private function showDatabaseForm() {
        $this->loadView('setup/database');
    }
    
    private function runMigrations() {
        $result = $this->runDatabaseMigrations();
        $this->jsonResponse($result);
    }
    
    private function seedDatabase() {
        try {
            // Insert default data
            $this->insertDefaultRoles();
            $this->insertDefaultDepartments();
            $this->insertDefaultSalaryComponents();
            $this->insertDefaultTaxSlabs();
            
            $this->jsonResponse(['success' => true, 'message' => 'Database seeded successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private function insertDefaultRoles() {
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Full system access', 'permissions' => 'all'],
            ['name' => 'HR Admin', 'description' => 'HR operations access', 'permissions' => 'employees,payroll,reports'],
            ['name' => 'Payroll Manager', 'description' => 'Payroll processing access', 'permissions' => 'payroll,reports'],
            ['name' => 'Unit HR', 'description' => 'Unit level HR access', 'permissions' => 'employees,reports'],
            ['name' => 'Viewer', 'description' => 'Read-only access', 'permissions' => 'view']
        ];
        
        foreach ($roles as $role) {
            $this->db->insert('roles', $role);
        }
    }
    
    private function insertDefaultDepartments() {
        $departments = [
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Finance & Accounts', 'code' => 'FIN'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Operations', 'code' => 'OPS']
        ];
        
        foreach ($departments as $dept) {
            $this->db->insert('departments', $dept);
        }
    }
    
    private function insertDefaultSalaryComponents() {
        $components = [
            ['name' => 'Basic Salary', 'code' => 'BASIC', 'type' => 'earning', 'is_mandatory' => 1, 'is_taxable' => 1, 'is_pf_applicable' => 1, 'display_order' => 1],
            ['name' => 'House Rent Allowance', 'code' => 'HRA', 'type' => 'earning', 'formula' => 'BASIC * 0.4', 'is_taxable' => 1, 'display_order' => 2],
            ['name' => 'Transport Allowance', 'code' => 'TA', 'type' => 'earning', 'formula' => '1600', 'is_taxable' => 1, 'display_order' => 3],
            ['name' => 'Medical Allowance', 'code' => 'MA', 'type' => 'earning', 'formula' => '1250', 'is_taxable' => 1, 'display_order' => 4],
            ['name' => 'Provident Fund', 'code' => 'PF', 'type' => 'deduction', 'formula' => 'BASIC * 0.12', 'is_mandatory' => 1, 'display_order' => 5],
            ['name' => 'ESI Contribution', 'code' => 'ESI', 'type' => 'deduction', 'formula' => '(BASIC + HRA + TA + MA) * 0.0075', 'display_order' => 6],
            ['name' => 'Professional Tax', 'code' => 'PT', 'type' => 'deduction', 'formula' => '200', 'display_order' => 7],
            ['name' => 'Income Tax (TDS)', 'code' => 'TDS', 'type' => 'deduction', 'display_order' => 8]
        ];
        
        foreach ($components as $component) {
            $this->db->insert('salary_components', $component);
        }
    }
    
    private function insertDefaultTaxSlabs() {
        $currentFY = $this->getCurrentFinancialYear();
        
        $taxSlabs = [
            ['financial_year' => $currentFY, 'min_amount' => 0, 'max_amount' => 300000, 'tax_rate' => 0],
            ['financial_year' => $currentFY, 'min_amount' => 300001, 'max_amount' => 700000, 'tax_rate' => 5],
            ['financial_year' => $currentFY, 'min_amount' => 700001, 'max_amount' => 1000000, 'tax_rate' => 10],
            ['financial_year' => $currentFY, 'min_amount' => 1000001, 'max_amount' => 1200000, 'tax_rate' => 15],
            ['financial_year' => $currentFY, 'min_amount' => 1200001, 'max_amount' => 1500000, 'tax_rate' => 20],
            ['financial_year' => $currentFY, 'min_amount' => 1500001, 'max_amount' => null, 'tax_rate' => 30]
        ];
        
        foreach ($taxSlabs as $slab) {
            $this->db->insert('tax_slabs', $slab);
        }
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
    
    private function completeInstallation() {
        $this->markInstallationComplete();
        $this->jsonResponse(['success' => true, 'message' => 'Installation completed']);
    }
}