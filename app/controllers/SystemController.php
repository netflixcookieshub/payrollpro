<?php
/**
 * System Controller
 * Handles system administration, backups, security, and maintenance
 */

require_once __DIR__ . '/../core/Controller.php';

class SystemController extends Controller {
    
    public function dashboard() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $systemStats = $this->getSystemStats();
        
        $this->loadView('system/dashboard', [
            'stats' => $systemStats,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function backupManager() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $backupManager = $this->loadModel('BackupManager');
        $backupHistory = $backupManager->getBackupHistory();
        
        $this->loadView('system/backup-manager', [
            'backup_history' => $backupHistory,
            'last_backup' => $this->getLastBackupTime(),
            'backup_count' => count($backupHistory),
            'storage_used' => $this->getBackupStorageUsed(),
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function securityDashboard() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $securityManager = $this->loadModel('SecurityManager');
        $securityReport = $securityManager->generateSecurityReport(30);
        $suspiciousActivities = $securityManager->detectSuspiciousActivity();
        
        $this->loadView('system/security-dashboard', [
            'security_report' => $securityReport,
            'suspicious_activities' => $suspiciousActivities,
            'recent_events' => $securityReport['recent_events'] ?? [],
            'blocked_ips' => $securityReport['blocked_ips'] ?? [],
            'security_score' => $this->calculateSecurityScore($securityReport),
            'login_success_rate' => $this->calculateLoginSuccessRate($securityReport),
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function createBackup() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $type = $input['type'] ?? 'full';
            $includeFiles = $input['include_files'] ?? true;
            
            $backupManager = $this->loadModel('BackupManager');
            $result = $backupManager->createFullBackup($includeFiles);
            
            if ($result['success']) {
                $this->logActivity('create_backup', 'backups', null);
            }
            
            $this->jsonResponse($result);
        }
    }
    
    public function restoreBackup() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $backupName = $input['backup_name'] ?? '';
            
            if (empty($backupName)) {
                $this->jsonResponse(['success' => false, 'message' => 'Backup name is required'], 400);
                return;
            }
            
            $backupManager = $this->loadModel('BackupManager');
            $backupFile = __DIR__ . '/../../backups/' . $backupName . '.zip';
            
            $result = $backupManager->restoreFromBackup($backupFile);
            
            if ($result['success']) {
                $this->logActivity('restore_backup', 'backups', null);
            }
            
            $this->jsonResponse($result);
        }
    }
    
    public function cleanupBackups() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $backupManager = $this->loadModel('BackupManager');
            $result = $backupManager->cleanOldBackups(30);
            
            $this->logActivity('cleanup_backups', 'backups', null);
            $this->jsonResponse($result);
        }
    }
    
    public function blockIP() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $ipAddress = $input['ip_address'] ?? '';
            $reason = $input['reason'] ?? '';
            $duration = intval($input['duration'] ?? 3600);
            
            if (empty($ipAddress) || empty($reason)) {
                $this->jsonResponse(['success' => false, 'message' => 'IP address and reason are required'], 400);
                return;
            }
            
            $securityManager = $this->loadModel('SecurityManager');
            $result = $securityManager->blockIP($ipAddress, $reason, $duration);
            
            $this->logActivity('block_ip', 'security', null);
            $this->jsonResponse($result);
        }
    }
    
    public function unblockIP() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $ipAddress = $input['ip_address'] ?? '';
            
            if (empty($ipAddress)) {
                $this->jsonResponse(['success' => false, 'message' => 'IP address is required'], 400);
                return;
            }
            
            $securityManager = $this->loadModel('SecurityManager');
            $result = $securityManager->unblockIP($ipAddress);
            
            $this->logActivity('unblock_ip', 'security', null);
            $this->jsonResponse($result);
        }
    }
    
    public function systemMaintenance() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->performMaintenance();
        } else {
            $this->showMaintenancePage();
        }
    }
    
    public function exportSecurityLogs() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $format = $_GET['format'] ?? 'csv';
        $days = intval($_GET['days'] ?? 30);
        
        $securityLogs = $this->db->fetchAll(
            "SELECT * FROM security_logs 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             ORDER BY created_at DESC",
            ['days' => $days]
        );
        
        if ($format === 'csv') {
            $this->exportToCSV($securityLogs, "security_logs_" . date('Y-m-d') . ".csv");
        } else {
            $this->exportToExcel($securityLogs, "security_logs_" . date('Y-m-d') . ".xlsx");
        }
    }
    
    public function systemHealth() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'memory' => $this->checkMemoryUsage(),
            'performance' => $this->checkPerformanceMetrics()
        ];
        
        $this->jsonResponse(['success' => true, 'health' => $health]);
    }
    
    private function getSystemStats() {
        return [
            'total_users' => $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'],
            'total_employees' => $this->db->fetch("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")['count'],
            'database_size' => $this->getDatabaseSize(),
            'storage_used' => $this->getStorageUsed(),
            'uptime' => $this->getSystemUptime(),
            'last_backup' => $this->getLastBackupTime(),
            'security_events_today' => $this->getSecurityEventsToday()
        ];
    }
    
    private function performMaintenance() {
        $tasks = $_POST['tasks'] ?? [];
        $results = [];
        
        foreach ($tasks as $task) {
            switch ($task) {
                case 'cleanup_logs':
                    $results[$task] = $this->cleanupLogs();
                    break;
                case 'optimize_database':
                    $results[$task] = $this->optimizeDatabase();
                    break;
                case 'clear_cache':
                    $results[$task] = $this->clearCache();
                    break;
                case 'cleanup_sessions':
                    $results[$task] = $this->cleanupSessions();
                    break;
                default:
                    $results[$task] = ['success' => false, 'message' => 'Unknown task'];
            }
        }
        
        $this->logActivity('system_maintenance', 'system', null);
        $this->jsonResponse(['success' => true, 'results' => $results]);
    }
    
    private function cleanupLogs() {
        try {
            // Clean old audit logs (older than 1 year)
            $deleted = $this->db->query(
                "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)"
            )->rowCount();
            
            return ['success' => true, 'message' => "Deleted {$deleted} old log entries"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Log cleanup failed: ' . $e->getMessage()];
        }
    }
    
    private function optimizeDatabase() {
        try {
            $tables = ['employees', 'payroll_transactions', 'attendance', 'audit_logs'];
            $optimized = 0;
            
            foreach ($tables as $table) {
                $this->db->query("OPTIMIZE TABLE {$table}");
                $optimized++;
            }
            
            return ['success' => true, 'message' => "Optimized {$optimized} tables"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database optimization failed: ' . $e->getMessage()];
        }
    }
    
    private function clearCache() {
        try {
            // Clear application cache
            $cacheDir = __DIR__ . '/../../cache/';
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            
            return ['success' => true, 'message' => 'Cache cleared successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Cache cleanup failed: ' . $e->getMessage()];
        }
    }
    
    private function cleanupSessions() {
        try {
            $securityManager = $this->loadModel('SecurityManager');
            $cleaned = $securityManager->cleanupExpiredSessions();
            
            return ['success' => true, 'message' => "Cleaned {$cleaned} expired sessions"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Session cleanup failed: ' . $e->getMessage()];
        }
    }
    
    private function checkDatabaseHealth() {
        try {
            $this->db->query("SELECT 1");
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }
    
    private function checkStorageHealth() {
        $uploadPath = __DIR__ . '/../../uploads/';
        $freeSpace = disk_free_space($uploadPath);
        $totalSpace = disk_total_space($uploadPath);
        $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        
        return [
            'status' => $usedPercent > 90 ? 'warning' : 'healthy',
            'used_percent' => round($usedPercent, 2),
            'free_space' => $this->formatBytes($freeSpace),
            'total_space' => $this->formatBytes($totalSpace)
        ];
    }
    
    private function checkMemoryUsage() {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        return [
            'current_usage' => $this->formatBytes($memoryUsage),
            'memory_limit' => $memoryLimit,
            'status' => 'healthy'
        ];
    }
    
    private function checkPerformanceMetrics() {
        // Simple performance check
        $start = microtime(true);
        $this->db->query("SELECT COUNT(*) FROM employees");
        $queryTime = (microtime(true) - $start) * 1000;
        
        return [
            'avg_query_time' => round($queryTime, 2) . 'ms',
            'status' => $queryTime > 100 ? 'warning' : 'healthy'
        ];
    }
    
    private function getDatabaseSize() {
        $result = $this->db->fetch(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
             FROM information_schema.tables
             WHERE table_schema = DATABASE()"
        );
        
        return $result['size_mb'] . ' MB';
    }
    
    private function getStorageUsed() {
        $uploadPath = __DIR__ . '/../../uploads/';
        $size = $this->getDirectorySize($uploadPath);
        return $this->formatBytes($size);
    }
    
    private function getDirectorySize($directory) {
        $size = 0;
        if (is_dir($directory)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }
    
    private function formatBytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    private function getSystemUptime() {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('uptime');
            return trim($uptime);
        }
        return 'N/A';
    }
    
    private function getLastBackupTime() {
        $lastBackup = $this->db->fetch(
            "SELECT created_at FROM backup_logs WHERE status = 'completed' ORDER BY created_at DESC LIMIT 1"
        );
        
        return $lastBackup ? $lastBackup['created_at'] : null;
    }
    
    private function getSecurityEventsToday() {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM security_logs WHERE DATE(created_at) = CURDATE()"
        );
        
        return $result['count'];
    }
    
    private function calculateSecurityScore($report) {
        $score = 100;
        
        // Deduct points for failed logins
        $failureRate = $report['login_statistics']['failed_logins'] / max(1, $report['login_statistics']['total_attempts']);
        $score -= ($failureRate * 20);
        
        // Deduct points for blocked IPs
        $score -= (count($report['blocked_ips']) * 2);
        
        return max(0, min(100, round($score)));
    }
    
    private function calculateLoginSuccessRate($report) {
        $total = $report['login_statistics']['total_attempts'];
        $successful = $report['login_statistics']['successful_logins'];
        
        return $total > 0 ? round(($successful / $total) * 100, 1) : 100;
    }
    
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    private function exportToExcel($data, $filename) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo "<table border='1'>";
        if (!empty($data)) {
            echo "<tr>";
            foreach (array_keys($data[0]) as $header) {
                echo "<th>" . ucwords(str_replace('_', ' ', $header)) . "</th>";
            }
            echo "</tr>";
            
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        exit;
    }
}