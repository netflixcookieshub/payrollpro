<?php
/**
 * Automated Installation Script
 * Run this script to automatically install the payroll system
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

require_once __DIR__ . '/app/utilities/SystemInstaller.php';

echo "PayrollPro Automated Installation\n";
echo "=================================\n\n";

// Check system requirements
echo "Checking system requirements...\n";
$requirements = SystemInstaller::checkRequirements();

foreach ($requirements as $requirement => $met) {
    echo sprintf("%-30s %s\n", $requirement, $met ? '✓ PASS' : '✗ FAIL');
}

$failed = array_filter($requirements, function($met) { return !$met; });

if (!empty($failed)) {
    echo "\nInstallation cannot proceed. Please fix the failed requirements.\n";
    exit(1);
}

echo "\nAll requirements met!\n\n";

// Get configuration from command line arguments or prompt
$config = [
    'database' => [
        'host' => $argv[1] ?? readline('Database Host [localhost]: ') ?: 'localhost',
        'port' => $argv[2] ?? readline('Database Port [3306]: ') ?: '3306',
        'database' => $argv[3] ?? readline('Database Name [payroll_system]: ') ?: 'payroll_system',
        'username' => $argv[4] ?? readline('Database Username [root]: ') ?: 'root',
        'password' => $argv[5] ?? readline('Database Password: ')
    ],
    'admin' => [
        'username' => $argv[6] ?? readline('Admin Username [admin]: ') ?: 'admin',
        'email' => $argv[7] ?? readline('Admin Email: '),
        'full_name' => $argv[8] ?? readline('Admin Full Name: '),
        'password' => $argv[9] ?? readline('Admin Password: ')
    ],
    'settings' => [
        'app_name' => $argv[10] ?? readline('Application Name [PayrollPro]: ') ?: 'PayrollPro',
        'base_url' => $argv[11] ?? readline('Base URL [http://localhost]: ') ?: 'http://localhost',
        'timezone' => $argv[12] ?? readline('Timezone [Asia/Kolkata]: ') ?: 'Asia/Kolkata',
        'currency' => $argv[13] ?? readline('Currency [INR]: ') ?: 'INR'
    ]
];

// Validate required fields
if (empty($config['admin']['email']) || empty($config['admin']['full_name']) || empty($config['admin']['password'])) {
    echo "Error: Admin email, full name, and password are required.\n";
    exit(1);
}

echo "\nStarting installation...\n";

$installer = new SystemInstaller($config);
$result = $installer->install();

if ($result['success']) {
    echo "\n✓ Installation completed successfully!\n";
    echo "\nYou can now access your payroll system at: {$config['settings']['base_url']}\n";
    echo "Login with:\n";
    echo "Username: {$config['admin']['username']}\n";
    echo "Password: {$config['admin']['password']}\n\n";
    echo "Next steps:\n";
    echo "1. Configure your web server to point to the 'public' directory\n";
    echo "2. Set up SSL certificate for production use\n";
    echo "3. Configure email settings for notifications\n";
    echo "4. Add your company's departments and employees\n\n";
} else {
    echo "\n✗ Installation failed: {$result['message']}\n";
    exit(1);
}