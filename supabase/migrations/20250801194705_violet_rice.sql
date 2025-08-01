-- Complete System Tables for Enterprise Payroll Management

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

-- Scheduled Reports Table
CREATE TABLE IF NOT EXISTS `scheduled_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `parameters` text,
  `frequency` enum('daily','weekly','monthly','quarterly') NOT NULL,
  `schedule_time` time NOT NULL,
  `recipients` text,
  `format` enum('pdf','excel','csv') DEFAULT 'pdf',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `next_run` (`next_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Performance Metrics Table
CREATE TABLE IF NOT EXISTS `performance_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(15,4) NOT NULL,
  `metric_type` enum('counter','gauge','histogram') DEFAULT 'gauge',
  `tags` text,
  `recorded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `metric_name` (`metric_name`),
  KEY `recorded_at` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee Documents Table
CREATE TABLE IF NOT EXISTS `employee_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `document_type` (`document_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Workflow Approvals Table
CREATE TABLE IF NOT EXISTS `workflow_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_type` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `current_step` int(11) DEFAULT 1,
  `total_steps` int(11) DEFAULT 1,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `requested_by` int(11) NOT NULL,
  `current_approver` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `workflow_type` (`workflow_type`),
  KEY `status` (`status`),
  KEY `requested_by` (`requested_by`)
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

-- Insert sample formula templates
INSERT INTO `formula_templates` (`name`, `formula`, `description`, `category`, `variables`, `created_by`, `is_public`) VALUES
('HRA Calculation', 'BASIC * 0.4', 'Standard HRA calculation as 40% of basic salary', 'earning', '["BASIC"]', 1, 1),
('PF Calculation', 'MIN(BASIC * 0.12, 1800)', 'PF calculation with ceiling of ₹1,800', 'deduction', '["BASIC"]', 1, 1),
('Conditional Allowance', 'IF(BASIC > 50000, BASIC * 0.1, 0)', 'Special allowance for high earners', 'earning', '["BASIC"]', 1, 1),
('Pro-rata Calculation', 'BASIC * (PRESENT_DAYS / WORKING_DAYS)', 'Pro-rata salary based on attendance', 'earning', '["BASIC", "PRESENT_DAYS", "WORKING_DAYS"]', 1, 1),
('Overtime Calculation', 'ROUND((BASIC / (WORKING_DAYS * 8)) * OVERTIME_HOURS * 2, 2)', 'Overtime pay at double rate', 'earning', '["BASIC", "WORKING_DAYS", "OVERTIME_HOURS"]', 1, 1);

-- Add foreign key constraints
ALTER TABLE `employee_arrears`
  ADD CONSTRAINT `fk_arrears_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_arrears_component` FOREIGN KEY (`component_id`) REFERENCES `salary_components` (`id`),
  ADD CONSTRAINT `fk_arrears_period` FOREIGN KEY (`effective_period_id`) REFERENCES `payroll_periods` (`id`);

ALTER TABLE `employee_variable_pay`
  ADD CONSTRAINT `fk_variable_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_variable_component` FOREIGN KEY (`component_id`) REFERENCES `salary_components` (`id`),
  ADD CONSTRAINT `fk_variable_period` FOREIGN KEY (`period_id`) REFERENCES `payroll_periods` (`id`);

ALTER TABLE `tax_declarations`
  ADD CONSTRAINT `fk_tax_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tax_section` FOREIGN KEY (`section_id`) REFERENCES `declaration_sections` (`id`);

ALTER TABLE `formula_templates`
  ADD CONSTRAINT `fk_formula_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `saved_queries`
  ADD CONSTRAINT `fk_query_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `query_executions`
  ADD CONSTRAINT `fk_execution_query` FOREIGN KEY (`query_id`) REFERENCES `saved_queries` (`id`),
  ADD CONSTRAINT `fk_execution_user` FOREIGN KEY (`executed_by`) REFERENCES `users` (`id`);

ALTER TABLE `scheduled_reports`
  ADD CONSTRAINT `fk_scheduled_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `employee_documents`
  ADD CONSTRAINT `fk_doc_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_doc_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

ALTER TABLE `workflow_approvals`
  ADD CONSTRAINT `fk_workflow_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_workflow_approver` FOREIGN KEY (`current_approver`) REFERENCES `users` (`id`);