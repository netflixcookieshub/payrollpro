<?php
/**
 * Backup Manager
 * Handles automated backups and data recovery
 */

require_once __DIR__ . '/../core/Model.php';

class BackupManager extends Model {
    
    private $backupPath;
    
    public function __construct($database) {
        parent::__construct($database);
        $this->backupPath = __DIR__ . '/../../backups/';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    public function createFullBackup($includeFiles = true) {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backupName = "full_backup_{$timestamp}";
            
            // Create database backup
            $dbBackupFile = $this->createDatabaseBackup($backupName);
            
            $backupFiles = [$dbBackupFile];
            
            if ($includeFiles) {
                // Create files backup
                $filesBackupFile = $this->createFilesBackup($backupName);
                $backupFiles[] = $filesBackupFile;
            }
            
            // Create compressed archive
            $archiveFile = $this->createArchive($backupName, $backupFiles);
            
            // Log backup
            $this->logBackup($backupName, 'full', filesize($archiveFile), 'completed');
            
            // Clean up temporary files
            foreach ($backupFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            return [
                'success' => true,
                'backup_file' => $archiveFile,
                'size' => filesize($archiveFile),
                'message' => 'Full backup created successfully'
            ];
            
        } catch (Exception $e) {
            $this->logBackup($backupName ?? 'unknown', 'full', 0, 'failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }
    
    public function createDatabaseBackup($backupName) {
        $filename = $this->backupPath . $backupName . '_database.sql';
        
        // Get database configuration
        $config = $this->getDatabaseConfig();
        
        // Create mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($filename)
        );
        
        // Execute backup
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Database backup failed');
        }
        
        return $filename;
    }
    
    public function createFilesBackup($backupName) {
        $filename = $this->backupPath . $backupName . '_files.tar.gz';
        
        // Directories to backup
        $directories = [
            __DIR__ . '/../../uploads/',
            __DIR__ . '/../../config/',
            __DIR__ . '/../../logs/'
        ];
        
        $command = sprintf(
            'tar -czf %s %s',
            escapeshellarg($filename),
            implode(' ', array_map('escapeshellarg', $directories))
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Files backup failed');
        }
        
        return $filename;
    }
    
    public function createArchive($backupName, $files) {
        $archiveFile = $this->backupPath . $backupName . '.zip';
        
        $zip = new ZipArchive();
        if ($zip->open($archiveFile, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('Cannot create backup archive');
        }
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        
        $zip->close();
        
        return $archiveFile;
    }
    
    public function scheduleAutomaticBackups() {
        $settings = $this->getBackupSettings();
        
        if (!$settings['auto_backup_enabled']) {
            return ['success' => false, 'message' => 'Automatic backups are disabled'];
        }
        
        $lastBackup = $this->getLastBackupTime();
        $nextBackup = strtotime($lastBackup) + ($settings['backup_frequency'] * 3600);
        
        if (time() >= $nextBackup) {
            return $this->createFullBackup($settings['include_files']);
        }
        
        return [
            'success' => true,
            'message' => 'Next backup scheduled for ' . date('Y-m-d H:i:s', $nextBackup)
        ];
    }
    
    public function restoreFromBackup($backupFile) {
        try {
            if (!file_exists($backupFile)) {
                throw new Exception('Backup file not found');
            }
            
            // Extract backup archive
            $extractPath = $this->backupPath . 'restore_' . time() . '/';
            mkdir($extractPath, 0755, true);
            
            $zip = new ZipArchive();
            if ($zip->open($backupFile) !== TRUE) {
                throw new Exception('Cannot open backup file');
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Restore database
            $dbFile = $extractPath . 'database.sql';
            if (file_exists($dbFile)) {
                $this->restoreDatabase($dbFile);
            }
            
            // Restore files
            $filesArchive = $extractPath . 'files.tar.gz';
            if (file_exists($filesArchive)) {
                $this->restoreFiles($filesArchive);
            }
            
            // Clean up
            $this->removeDirectory($extractPath);
            
            $this->logBackup(basename($backupFile), 'restore', filesize($backupFile), 'completed');
            
            return [
                'success' => true,
                'message' => 'Backup restored successfully'
            ];
            
        } catch (Exception $e) {
            $this->logBackup(basename($backupFile ?? 'unknown'), 'restore', 0, 'failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ];
        }
    }
    
    public function cleanOldBackups($retentionDays = 30) {
        $cutoffDate = date('Y-m-d', strtotime("-{$retentionDays} days"));
        
        $oldBackups = $this->db->fetchAll(
            "SELECT * FROM backup_logs WHERE created_at < :cutoff_date AND status = 'completed'",
            ['cutoff_date' => $cutoffDate]
        );
        
        $deletedCount = 0;
        
        foreach ($oldBackups as $backup) {
            $backupFile = $this->backupPath . $backup['backup_name'] . '.zip';
            
            if (file_exists($backupFile)) {
                unlink($backupFile);
                $deletedCount++;
            }
            
            // Update backup log
            $this->db->update('backup_logs',
                ['status' => 'deleted'],
                'id = :id',
                ['id' => $backup['id']]
            );
        }
        
        return [
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => "Deleted {$deletedCount} old backups"
        ];
    }
    
    public function getBackupHistory($limit = 50) {
        return $this->db->fetchAll(
            "SELECT * FROM backup_logs ORDER BY created_at DESC LIMIT :limit",
            ['limit' => $limit]
        );
    }
    
    public function validateBackup($backupFile) {
        if (!file_exists($backupFile)) {
            return ['valid' => false, 'message' => 'Backup file not found'];
        }
        
        $zip = new ZipArchive();
        if ($zip->open($backupFile) !== TRUE) {
            return ['valid' => false, 'message' => 'Invalid backup file format'];
        }
        
        $requiredFiles = ['database.sql'];
        $foundFiles = [];
        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $foundFiles[] = $zip->getNameIndex($i);
        }
        
        $zip->close();
        
        $missingFiles = array_diff($requiredFiles, $foundFiles);
        
        if (!empty($missingFiles)) {
            return [
                'valid' => false,
                'message' => 'Missing required files: ' . implode(', ', $missingFiles)
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Backup file is valid',
            'files' => $foundFiles,
            'size' => filesize($backupFile)
        ];
    }
    
    private function restoreDatabase($sqlFile) {
        $config = $this->getDatabaseConfig();
        
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Database restore failed');
        }
    }
    
    private function restoreFiles($archiveFile) {
        $command = sprintf(
            'tar -xzf %s -C %s',
            escapeshellarg($archiveFile),
            escapeshellarg(__DIR__ . '/../../')
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Files restore failed');
        }
    }
    
    private function getDatabaseConfig() {
        // Get database configuration from config file
        return [
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'payroll_system'
        ];
    }
    
    private function getBackupSettings() {
        $settings = $this->db->fetchAll(
            "SELECT setting_key, setting_value FROM system_settings 
             WHERE setting_key IN ('auto_backup_enabled', 'backup_frequency', 'backup_retention_days', 'include_files_in_backup')"
        );
        
        $config = [];
        foreach ($settings as $setting) {
            $config[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return [
            'auto_backup_enabled' => ($config['auto_backup_enabled'] ?? 'false') === 'true',
            'backup_frequency' => intval($config['backup_frequency'] ?? 24), // hours
            'backup_retention_days' => intval($config['backup_retention_days'] ?? 30),
            'include_files' => ($config['include_files_in_backup'] ?? 'true') === 'true'
        ];
    }
    
    private function getLastBackupTime() {
        $lastBackup = $this->db->fetch(
            "SELECT created_at FROM backup_logs WHERE status = 'completed' ORDER BY created_at DESC LIMIT 1"
        );
        
        return $lastBackup ? $lastBackup['created_at'] : '1970-01-01 00:00:00';
    }
    
    private function logBackup($backupName, $type, $size, $status, $error = null) {
        $this->db->insert('backup_logs', [
            'backup_name' => $backupName,
            'backup_type' => $type,
            'file_size' => $size,
            'status' => $status,
            'error_message' => $error,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function removeDirectory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->removeDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}