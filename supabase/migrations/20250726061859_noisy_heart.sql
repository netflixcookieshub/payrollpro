-- Advanced System Tables for Complete Payroll System

-- Notification Logs Table
CREATE TABLE IF NOT EXISTS `notification_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('email','sms','push','in_app') NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `error_message` text,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Scheduled Notifications Table
CREATE TABLE IF NOT EXISTS `scheduled_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `data` text,
  `schedule_time` timestamp NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `result` text,
  `error_message` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `schedule_time` (`schedule_time`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Alerts Table
CREATE TABLE IF NOT EXISTS `system_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('active','acknowledged','resolved') DEFAULT 'active',
  `acknowledged_by` int(11) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `severity` (`severity`),
  KEY `status` (`status`),
  KEY `acknowledged_by` (`acknowledged_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Backup Logs Table
CREATE TABLE IF NOT EXISTS `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_name` varchar(255) NOT NULL,
  `backup_type` enum('full','database','files','incremental') DEFAULT 'full',
  `file_size` bigint(20) DEFAULT 0,
  `status` enum('pending','in_progress','completed','failed','deleted') DEFAULT 'pending',
  `error_message` text,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `backup_type` (`backup_type`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Security Logs Table
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `additional_data` text,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `user_id` (`user_id`),
  KEY `ip_address` (`ip_address`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blocked IPs Table
CREATE TABLE IF NOT EXISTS `blocked_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `reason` text,
  `blocked_until` timestamp NULL DEFAULT NULL,
  `status` enum('active','expired','unblocked') DEFAULT 'active',
  `blocked_by` int(11) DEFAULT NULL,
  `unblocked_by` int(11) DEFAULT NULL,
  `unblocked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`),
  KEY `status` (`status`),
  KEY `blocked_until` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Login Attempts Table
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `success` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `ip_address` (`ip_address`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Sessions Table
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `last_activity` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','expired','terminated') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee Arrears Table
CREATE TABLE IF NOT EXISTS `employee_arrears` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `effective_period_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text,
  `status` enum('pending','approved','processed','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `component_id` (`component_id`),
  KEY `effective_period_id` (`effective_period_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee Variable Pay Table
CREATE TABLE IF NOT EXISTS `employee_variable_pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text,
  `performance_rating` enum('excellent','good','satisfactory','needs_improvement','poor') DEFAULT NULL,
  `status` enum('pending','approved','processed','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `component_id` (`component_id`),
  KEY `period_id` (`period_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tax Declarations Table
CREATE TABLE IF NOT EXISTS `tax_declarations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `financial_year` varchar(9) NOT NULL,
  `section_id` int(11) NOT NULL,
  `declared_amount` decimal(10,2) NOT NULL,
  `proof_document` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `financial_year` (`financial_year`),
  KEY `section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Declaration Sections Table
CREATE TABLE IF NOT EXISTS `declaration_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `section` varchar(10) NOT NULL,
  `max_limit` decimal(10,2) DEFAULT NULL,
  `description` text,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `section` (`section`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Formula Templates Table
CREATE TABLE IF NOT EXISTS `formula_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `formula` text NOT NULL,
  `description` text,
  `category` varchar(50) DEFAULT 'custom',
  `variables` text,
  `created_by` int(11) NOT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Saved Queries Table
CREATE TABLE IF NOT EXISTS `saved_queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `query_sql` text NOT NULL,
  `description` text,
  `category` varchar(50) DEFAULT 'custom',
  `parameters` text,
  `created_by` int(11) NOT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Query Executions Table
CREATE TABLE IF NOT EXISTS `query_executions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` int(11) DEFAULT NULL,
  `executed_by` int(11) NOT NULL,
  `execution_time_ms` int(11) DEFAULT NULL,
  `result_count` int(11) DEFAULT NULL,
  `status` enum('success','failed') DEFAULT 'success',
  `error_message` text,
  `executed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `query_id` (`query_id`),
  KEY `executed_by` (`executed_by`),
  KEY `executed_at` (`executed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default declaration sections
INSERT INTO `declaration_sections` (`name`, `section`, `max_limit`, `description`, `display_order`) VALUES
('Life Insurance Premium', '80C', 150000.00, 'Life insurance premium payments', 1),
('Provident Fund', '80C', 150000.00, 'Employee PF contribution', 2),
('ELSS Mutual Funds', '80C', 150000.00, 'Equity Linked Savings Scheme', 3),
('PPF', '80C', 150000.00, 'Public Provident Fund', 4),
('NSC', '80C', 150000.00, 'National Savings Certificate', 5),
('Health Insurance Premium', '80D', 25000.00, 'Health insurance premium for self and family', 6),
('Medical Treatment of Senior Citizens', '80D', 50000.00, 'Medical treatment for senior citizen parents', 7),
('Interest on Home Loan', '24', NULL, 'Interest paid on home loan', 8),
('House Rent Allowance', 'HRA', NULL, 'HRA exemption calculation', 9);

-- Add foreign key constraints for new tables
ALTER TABLE `system_alerts`
  ADD CONSTRAINT `fk_alerts_user` FOREIGN KEY (`acknowledged_by`) REFERENCES `users` (`id`);

ALTER TABLE `security_logs`
  ADD CONSTRAINT `fk_security_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `blocked_ips`
  ADD CONSTRAINT `fk_blocked_by` FOREIGN KEY (`blocked_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_unblocked_by` FOREIGN KEY (`unblocked_by`) REFERENCES `users` (`id`);

ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `employee_arrears`
  ADD CONSTRAINT `fk_arrears_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_arrears_component` FOREIGN KEY (`component_id`) REFERENCES `salary_components` (`id`),
  ADD CONSTRAINT `fk_arrears_period` FOREIGN KEY (`effective_period_id`) REFERENCES `payroll_periods` (`id`),
  ADD CONSTRAINT `fk_arrears_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_arrears_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`);

ALTER TABLE `employee_variable_pay`
  ADD CONSTRAINT `fk_variable_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_variable_component` FOREIGN KEY (`component_id`) REFERENCES `salary_components` (`id`),
  ADD CONSTRAINT `fk_variable_period` FOREIGN KEY (`period_id`) REFERENCES `payroll_periods` (`id`),
  ADD CONSTRAINT `fk_variable_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_variable_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`);

ALTER TABLE `tax_declarations`
  ADD CONSTRAINT `fk_tax_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tax_section` FOREIGN KEY (`section_id`) REFERENCES `declaration_sections` (`id`),
  ADD CONSTRAINT `fk_tax_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

ALTER TABLE `formula_templates`
  ADD CONSTRAINT `fk_formula_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `saved_queries`
  ADD CONSTRAINT `fk_query_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `query_executions`
  ADD CONSTRAINT `fk_execution_query` FOREIGN KEY (`query_id`) REFERENCES `saved_queries` (`id`),
  ADD CONSTRAINT `fk_execution_user` FOREIGN KEY (`executed_by`) REFERENCES `users` (`id`);