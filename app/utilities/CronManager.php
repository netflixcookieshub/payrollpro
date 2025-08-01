<?php
/**
 * Cron Manager
 * Handles scheduled tasks and background jobs
 */

class CronManager {
    
    private $db;
    private $logFile;
    
    public function __construct() {
        $this->db = new Database();
        $this->logFile = __DIR__ . '/../../logs/cron.log';
        
        // Create logs directory if it doesn't exist
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }
    
    public function runScheduledTasks() {
        $this->log("Starting scheduled tasks execution");
        
        try {
            // Run all scheduled tasks
            $this->processScheduledNotifications();
            $this->cleanupExpiredSessions();
            $this->cleanupExpiredBlocks();
            $this->processAutomaticBackups();
            $this->generateScheduledReports();
            $this->cleanupOldLogs();
            
            $this->log("All scheduled tasks completed successfully");
        } catch (Exception $e) {
            $this->log("Error in scheduled tasks: " . $e->getMessage());
        }
    }
    
    private function processScheduledNotifications() {
        $this->log("Processing scheduled notifications");
        
        $notificationManager = new NotificationManager($this->db);
        $result = $notificationManager->processScheduledNotifications();
        
        $this->log("Processed {$result['processed']} scheduled notifications");
    }
    
    private function cleanupExpiredSessions() {
        $this->log("Cleaning up expired sessions");
        
        $securityManager = new SecurityManager($this->db);
        $cleaned = $securityManager->cleanupExpiredSessions();
        
        $this->log("Cleaned {$cleaned} expired sessions");
    }
    
    private function cleanupExpiredBlocks() {
        $this->log("Cleaning up expired IP blocks");
        
        $securityManager = new SecurityManager($this->db);
        $cleaned = $securityManager->cleanupExpiredBlocks();
        
        $this->log("Cleaned {$cleaned} expired IP blocks");
    }
    
    private function processAutomaticBackups() {
        $this->log("Processing automatic backups");
        
        $backupManager = new BackupManager($this->db);
        $result = $backupManager->scheduleAutomaticBackups();
        
        if ($result['success']) {
            $this->log("Automatic backup: " . $result['message']);
        } else {
            $this->log("Automatic backup failed: " . $result['message']);
        }
    }
    
    private function generateScheduledReports() {
        $this->log("Generating scheduled reports");
        
        // Get scheduled reports
        $scheduledReports = $this->db->fetchAll(
            "SELECT * FROM scheduled_reports 
             WHERE status = 'active' 
             AND next_run <= NOW()"
        );
        
        foreach ($scheduledReports as $report) {
            try {
                $this->generateReport($report);
                
                // Update next run time
                $nextRun = $this->calculateNextRun($report['frequency'], $report['schedule_time']);
                $this->db->update('scheduled_reports',
                    ['next_run' => $nextRun, 'last_run' => date('Y-m-d H:i:s')],
                    'id = :id',
                    ['id' => $report['id']]
                );
                
                $this->log("Generated scheduled report: {$report['name']}");
            } catch (Exception $e) {
                $this->log("Failed to generate report {$report['name']}: " . $e->getMessage());
            }
        }
    }
    
    private function cleanupOldLogs() {
        $this->log("Cleaning up old logs");
        
        $retentionDays = 90; // Keep logs for 90 days
        
        // Clean audit logs
        $deleted = $this->db->query(
            "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)",
            ['days' => $retentionDays]
        )->rowCount();
        
        $this->log("Deleted {$deleted} old audit log entries");
        
        // Clean security logs
        $deleted = $this->db->query(
            "DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)",
            ['days' => $retentionDays]
        )->rowCount();
        
        $this->log("Deleted {$deleted} old security log entries");
        
        // Clean notification logs
        $deleted = $this->db->query(
            "DELETE FROM notification_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)",
            ['days' => $retentionDays]
        )->rowCount();
        
        $this->log("Deleted {$deleted} old notification log entries");
    }
    
    private function generateReport($reportConfig) {
        // Implementation for generating scheduled reports
        // This would integrate with the existing report generation system
        
        $reportType = $reportConfig['report_type'];
        $parameters = json_decode($reportConfig['parameters'], true);
        
        // Generate report based on type
        switch ($reportType) {
            case 'salary_register':
                $this->generateSalaryRegisterReport($parameters);
                break;
            case 'attendance_summary':
                $this->generateAttendanceSummaryReport($parameters);
                break;
            case 'compliance_report':
                $this->generateComplianceReport($parameters);
                break;
        }
    }
    
    private function calculateNextRun($frequency, $scheduleTime) {
        $now = new DateTime();
        $next = new DateTime();
        
        switch ($frequency) {
            case 'daily':
                $next->modify('+1 day');
                break;
            case 'weekly':
                $next->modify('+1 week');
                break;
            case 'monthly':
                $next->modify('+1 month');
                break;
        }
        
        // Set the scheduled time
        $timeParts = explode(':', $scheduleTime);
        $next->setTime($timeParts[0], $timeParts[1], 0);
        
        return $next->format('Y-m-d H:i:s');
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public static function setupCronJobs() {
        // This method would help setup cron jobs on the server
        $cronCommands = [
            // Run every minute for time-sensitive tasks
            "* * * * * cd " . __DIR__ . "/../../ && php cron.php minute",
            
            // Run every hour for regular maintenance
            "0 * * * * cd " . __DIR__ . "/../../ && php cron.php hourly",
            
            // Run daily at 2 AM for heavy maintenance
            "0 2 * * * cd " . __DIR__ . "/../../ && php cron.php daily",
            
            // Run weekly on Sunday at 3 AM
            "0 3 * * 0 cd " . __DIR__ . "/../../ && php cron.php weekly",
            
            // Run monthly on 1st at 4 AM
            "0 4 1 * * cd " . __DIR__ . "/../../ && php cron.php monthly"
        ];
        
        return $cronCommands;
    }
}