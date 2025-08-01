<?php
/**
 * Cron Job Entry Point
 * Handles all scheduled tasks and background jobs
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/utilities/CronManager.php';
require_once __DIR__ . '/app/models/NotificationManager.php';
require_once __DIR__ . '/app/models/SecurityManager.php';
require_once __DIR__ . '/app/models/BackupManager.php';

$frequency = $argv[1] ?? 'minute';

echo "Starting cron job: {$frequency}\n";

$cronManager = new CronManager();

switch ($frequency) {
    case 'minute':
        // Tasks that need to run every minute
        $cronManager->processScheduledNotifications();
        break;
        
    case 'hourly':
        // Tasks that run every hour
        $cronManager->cleanupExpiredSessions();
        $cronManager->cleanupExpiredBlocks();
        break;
        
    case 'daily':
        // Tasks that run daily
        $cronManager->processAutomaticBackups();
        $cronManager->generateScheduledReports();
        $cronManager->cleanupOldLogs();
        break;
        
    case 'weekly':
        // Tasks that run weekly
        $cronManager->generateWeeklyReports();
        $cronManager->performWeeklyMaintenance();
        break;
        
    case 'monthly':
        // Tasks that run monthly
        $cronManager->generateMonthlyReports();
        $cronManager->performMonthlyMaintenance();
        $cronManager->archiveOldData();
        break;
        
    default:
        // Run all scheduled tasks
        $cronManager->runScheduledTasks();
        break;
}

echo "Cron job completed: {$frequency}\n";