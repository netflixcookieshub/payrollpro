-- Integration and API Management Tables

-- Integrations table
CREATE TABLE IF NOT EXISTS `integrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `config` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_sync` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- API Keys table
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `key_hash` varchar(255) NOT NULL,
  `permissions` varchar(255) DEFAULT 'read',
  `status` enum('active','revoked') DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `last_used` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- API Access Logs table
CREATE TABLE IF NOT EXISTS `api_access_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key_id` int(11) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `response_code` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `api_key_id` (`api_key_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Import/Export Logs table
CREATE TABLE IF NOT EXISTS `import_export_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('import','export') NOT NULL,
  `operation` varchar(50) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `records_processed` int(11) DEFAULT 0,
  `records_success` int(11) DEFAULT 0,
  `records_failed` int(11) DEFAULT 0,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `error_log` text,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Webhook Logs table
CREATE TABLE IF NOT EXISTS `webhook_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `integration_type` varchar(50) NOT NULL,
  `payload` text,
  `headers` text,
  `response` text,
  `status` enum('success','failed') DEFAULT 'success',
  `processed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `integration_type` (`integration_type`),
  KEY `processed_at` (`processed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Settings table
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('app_name', 'PayrollPro', 'string', 'Application name'),
('app_version', '1.0.0', 'string', 'Application version'),
('timezone', 'Asia/Kolkata', 'string', 'Default timezone'),
('currency', 'INR', 'string', 'Default currency'),
('date_format', 'Y-m-d', 'string', 'Default date format'),
('records_per_page', '25', 'number', 'Default pagination limit'),
('session_timeout', '1800', 'number', 'Session timeout in seconds'),
('max_file_size', '5242880', 'number', 'Maximum file upload size in bytes'),
('backup_retention_days', '30', 'number', 'Backup retention period in days'),
('email_notifications', 'true', 'boolean', 'Enable email notifications'),
('sms_notifications', 'false', 'boolean', 'Enable SMS notifications'),
('auto_backup', 'true', 'boolean', 'Enable automatic backups');

-- Add foreign key constraints
ALTER TABLE `api_keys`
  ADD CONSTRAINT `fk_api_keys_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `api_access_logs`
  ADD CONSTRAINT `fk_api_logs_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`);

ALTER TABLE `import_export_logs`
  ADD CONSTRAINT `fk_import_export_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);