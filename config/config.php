<?php
/**
 * Application Configuration
 */

// Basic Configuration
define('APP_NAME', 'Payroll Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost');

// Security
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_DURATION', 900); // 15 minutes

// File Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xlsx']);

// Pagination
define('RECORDS_PER_PAGE', 25);

// Financial Year
define('FINANCIAL_YEAR_START_MONTH', 4); // April

// Payroll Settings
define('PF_RATE_EMPLOYEE', 12);
define('PF_RATE_EMPLOYER', 12);
define('ESI_RATE_EMPLOYEE', 0.75);
define('ESI_RATE_EMPLOYER', 3.25);
define('ESI_THRESHOLD', 21000);

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@company.com');
define('FROM_NAME', 'Payroll System');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting
if (getenv('ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);