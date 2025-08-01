-- Enterprise Payroll Management System Database Schema
-- Complete database structure with sample data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Database: payroll_system
CREATE DATABASE IF NOT EXISTS `payroll_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `payroll_system`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `permissions` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `departments`
-- --------------------------------------------------------

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `head_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `designations`
-- --------------------------------------------------------

CREATE TABLE `designations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `department_id` int(11) NOT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `cost_centers`
-- --------------------------------------------------------

CREATE TABLE `cost_centers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `employees`
-- --------------------------------------------------------

CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_code` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text,
  `join_date` date NOT NULL,
  `leave_date` date DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `designation_id` int(11) NOT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `reporting_manager_id` int(11) DEFAULT NULL,
  `employment_type` enum('permanent','contract','temporary') DEFAULT 'permanent',
  `status` enum('active','inactive','terminated','deleted') DEFAULT 'active',
  `pan_number` varchar(10) DEFAULT NULL,
  `aadhaar_number` varchar(12) DEFAULT NULL,
  `uan_number` varchar(12) DEFAULT NULL,
  `pf_number` varchar(20) DEFAULT NULL,
  `esi_number` varchar(20) DEFAULT NULL,
  `bank_account_number` varchar(20) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_ifsc` varchar(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_code` (`emp_code`),
  KEY `department_id` (`department_id`),
  KEY `designation_id` (`designation_id`),
  KEY `cost_center_id` (`cost_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `salary_components`
-- --------------------------------------------------------

CREATE TABLE `salary_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `type` enum('earning','deduction','reimbursement','variable') NOT NULL,
  `formula` text DEFAULT NULL,
  `is_mandatory` tinyint(1) DEFAULT 0,
  `is_taxable` tinyint(1) DEFAULT 1,
  `is_pf_applicable` tinyint(1) DEFAULT 0,
  `is_esi_applicable` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `salary_structures`
-- --------------------------------------------------------

CREATE TABLE `salary_structures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `formula_result` decimal(10,2) DEFAULT NULL,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `component_id` (`component_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `payroll_periods`
-- --------------------------------------------------------

CREATE TABLE `payroll_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period_name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `financial_year` varchar(9) NOT NULL,
  `status` enum('open','processing','locked','closed') DEFAULT 'open',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `payroll_transactions`
-- --------------------------------------------------------

CREATE TABLE `payroll_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calculated_amount` decimal(10,2) DEFAULT NULL,
  `is_manual_override` tinyint(1) DEFAULT 0,
  `remarks` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `period_id` (`period_id`),
  KEY `component_id` (`component_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `loan_types`
-- --------------------------------------------------------

CREATE TABLE `loan_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `max_tenure_months` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `employee_loans`
-- --------------------------------------------------------

CREATE TABLE `employee_loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `loan_type_id` int(11) NOT NULL,
  `loan_amount` decimal(10,2) NOT NULL,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `tenure_months` int(11) NOT NULL,
  `emi_amount` decimal(10,2) NOT NULL,
  `disbursed_date` date NOT NULL,
  `first_emi_date` date NOT NULL,
  `outstanding_amount` decimal(10,2) NOT NULL,
  `status` enum('active','closed','defaulted') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `loan_type_id` (`loan_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `tax_slabs`
-- --------------------------------------------------------

CREATE TABLE `tax_slabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `financial_year` varchar(9) NOT NULL,
  `min_amount` decimal(10,2) NOT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `surcharge_rate` decimal(5,2) DEFAULT 0.00,
  `cess_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `attendance`
-- --------------------------------------------------------

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `total_hours` decimal(4,2) DEFAULT NULL,
  `overtime_hours` decimal(4,2) DEFAULT 0.00,
  `status` enum('present','absent','half_day','late','early_out') DEFAULT 'present',
  `remarks` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_date` (`employee_id`,`attendance_date`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `leave_types`
-- --------------------------------------------------------

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `is_paid` tinyint(1) DEFAULT 1,
  `max_days_per_year` int(11) DEFAULT NULL,
  `carry_forward` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `holidays`
-- --------------------------------------------------------

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `holiday_date` date NOT NULL,
  `type` enum('national','religious','optional','company') DEFAULT 'company',
  `description` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `audit_logs`
-- --------------------------------------------------------

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text,
  `new_values` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert sample data
-- --------------------------------------------------------

-- Insert roles
INSERT INTO `roles` (`id`, `name`, `description`, `permissions`) VALUES
(1, 'Super Admin', 'Full system access', 'all'),
(2, 'HR Admin', 'HR operations access', 'employees,payroll,reports'),
(3, 'Payroll Manager', 'Payroll processing access', 'payroll,reports'),
(4, 'Unit HR', 'Unit level HR access', 'employees,reports'),
(5, 'Viewer', 'Read-only access', 'view');

-- Insert users (password is 'password' for all users)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `full_name`) VALUES
(1, 'admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'System Administrator'),
(2, 'hradmin', 'hr@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'HR Administrator'),
(3, 'payroll', 'payroll@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'Payroll Manager');

-- Insert departments
INSERT INTO `departments` (`id`, `name`, `code`) VALUES
(1, 'Human Resources', 'HR'),
(2, 'Information Technology', 'IT'),
(3, 'Finance & Accounts', 'FIN'),
(4, 'Marketing', 'MKT'),
(5, 'Operations', 'OPS');

-- Insert designations
INSERT INTO `designations` (`id`, `name`, `code`, `department_id`, `grade`) VALUES
(1, 'Manager', 'MGR', 1, 'M1'),
(2, 'Senior Executive', 'SE', 1, 'E2'),
(3, 'Executive', 'EXE', 1, 'E1'),
(4, 'Software Engineer', 'SWE', 2, 'T2'),
(5, 'Senior Software Engineer', 'SSE', 2, 'T3'),
(6, 'Team Lead', 'TL', 2, 'T4'),
(7, 'Accountant', 'ACC', 3, 'F1'),
(8, 'Finance Manager', 'FM', 3, 'F2');

-- Insert cost centers
INSERT INTO `cost_centers` (`id`, `name`, `code`) VALUES
(1, 'Head Office', 'HO001'),
(2, 'Branch Office Delhi', 'BO001'),
(3, 'Branch Office Mumbai', 'BO002');

-- Insert salary components
INSERT INTO `salary_components` (`id`, `name`, `code`, `type`, `formula`, `is_mandatory`, `is_taxable`, `is_pf_applicable`, `display_order`) VALUES
(1, 'Basic Salary', 'BASIC', 'earning', NULL, 1, 1, 1, 1),
(2, 'House Rent Allowance', 'HRA', 'earning', 'BASIC * 0.4', 1, 1, 0, 2),
(3, 'Transport Allowance', 'TA', 'earning', '1600', 0, 1, 0, 3),
(4, 'Medical Allowance', 'MA', 'earning', '1250', 0, 1, 0, 4),
(5, 'Special Allowance', 'SA', 'earning', NULL, 0, 1, 0, 5),
(6, 'Provident Fund', 'PF', 'deduction', 'BASIC * 0.12', 1, 0, 0, 6),
(7, 'ESI Contribution', 'ESI', 'deduction', '(BASIC + HRA + TA + MA) * 0.0075', 0, 0, 0, 7),
(8, 'Professional Tax', 'PT', 'deduction', '200', 0, 0, 0, 8),
(9, 'Income Tax (TDS)', 'TDS', 'deduction', NULL, 0, 0, 0, 9);

-- Insert sample employees
INSERT INTO `employees` (`id`, `emp_code`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `gender`, `join_date`, `department_id`, `designation_id`, `cost_center_id`, `pan_number`, `uan_number`, `bank_account_number`, `bank_name`, `bank_ifsc`) VALUES
(1, 'EMP001', 'John', 'Doe', 'john.doe@company.com', '9876543210', '1990-05-15', 'male', '2023-01-01', 2, 4, 1, 'ABCDE1234F', '123456789012', '1234567890123456', 'State Bank of India', 'SBIN0001234'),
(2, 'EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '9876543211', '1992-08-20', 'female', '2023-02-01', 2, 5, 1, 'FGHIJ5678K', '123456789013', '1234567890123457', 'HDFC Bank', 'HDFC0001234'),
(3, 'EMP003', 'Mike', 'Johnson', 'mike.johnson@company.com', '9876543212', '1988-12-10', 'male', '2023-03-01', 1, 1, 1, 'KLMNO9012P', '123456789014', '1234567890123458', 'ICICI Bank', 'ICIC0001234'),
(4, 'EMP004', 'Sarah', 'Wilson', 'sarah.wilson@company.com', '9876543213', '1995-03-25', 'female', '2023-04-01', 3, 7, 1, 'PQRST3456U', '123456789015', '1234567890123459', 'Axis Bank', 'UTIB0001234'),
(5, 'EMP005', 'David', 'Brown', 'david.brown@company.com', '9876543214', '1987-11-08', 'male', '2023-05-01', 2, 6, 1, 'VWXYZ7890A', '123456789016', '1234567890123460', 'Kotak Bank', 'KKBK0001234');

-- Insert salary structures for sample employees
INSERT INTO `salary_structures` (`employee_id`, `component_id`, `amount`, `effective_date`) VALUES
-- John Doe (Software Engineer)
(1, 1, 30000.00, '2023-01-01'), -- Basic
(1, 2, 12000.00, '2023-01-01'), -- HRA
(1, 3, 1600.00, '2023-01-01'),  -- TA
(1, 4, 1250.00, '2023-01-01'),  -- MA
(1, 6, 3600.00, '2023-01-01'),  -- PF
(1, 7, 337.50, '2023-01-01'),   -- ESI
(1, 8, 200.00, '2023-01-01'),   -- PT
-- Jane Smith (Senior Software Engineer)
(2, 1, 45000.00, '2023-02-01'), -- Basic
(2, 2, 18000.00, '2023-02-01'), -- HRA
(2, 3, 1600.00, '2023-02-01'),  -- TA
(2, 4, 1250.00, '2023-02-01'),  -- MA
(2, 6, 5400.00, '2023-02-01'),  -- PF
(2, 8, 200.00, '2023-02-01'),   -- PT
-- Mike Johnson (Manager)
(3, 1, 60000.00, '2023-03-01'), -- Basic
(3, 2, 24000.00, '2023-03-01'), -- HRA
(3, 3, 1600.00, '2023-03-01'),  -- TA
(3, 4, 1250.00, '2023-03-01'),  -- MA
(3, 6, 7200.00, '2023-03-01'),  -- PF
(3, 8, 200.00, '2023-03-01'),   -- PT
-- Sarah Wilson (Accountant)
(4, 1, 35000.00, '2023-04-01'), -- Basic
(4, 2, 14000.00, '2023-04-01'), -- HRA
(4, 3, 1600.00, '2023-04-01'),  -- TA
(4, 4, 1250.00, '2023-04-01'),  -- MA
(4, 6, 4200.00, '2023-04-01'),  -- PF
(4, 8, 200.00, '2023-04-01'),   -- PT
-- David Brown (Team Lead)
(5, 1, 55000.00, '2023-05-01'), -- Basic
(5, 2, 22000.00, '2023-05-01'), -- HRA
(5, 3, 1600.00, '2023-05-01'),  -- TA
(5, 4, 1250.00, '2023-05-01'),  -- MA
(5, 6, 6600.00, '2023-05-01'),  -- PF
(5, 8, 200.00, '2023-05-01');   -- PT

-- Insert loan types
INSERT INTO `loan_types` (`id`, `name`, `code`, `max_amount`, `interest_rate`, `max_tenure_months`) VALUES
(1, 'Personal Loan', 'PL', 500000.00, 12.00, 60),
(2, 'Home Loan', 'HL', 5000000.00, 8.50, 240),
(3, 'Vehicle Loan', 'VL', 1000000.00, 10.00, 84),
(4, 'Emergency Loan', 'EL', 100000.00, 0.00, 12);

-- Insert sample loans
INSERT INTO `employee_loans` (`employee_id`, `loan_type_id`, `loan_amount`, `interest_rate`, `tenure_months`, `emi_amount`, `disbursed_date`, `first_emi_date`, `outstanding_amount`) VALUES
(1, 1, 100000.00, 12.00, 24, 4707.35, '2023-06-01', '2023-07-01', 85000.00),
(3, 2, 2000000.00, 8.50, 120, 24537.50, '2023-01-15', '2023-02-15', 1900000.00);

-- Insert tax slabs for FY 2024-25
INSERT INTO `tax_slabs` (`financial_year`, `min_amount`, `max_amount`, `tax_rate`) VALUES
('2024-2025', 0.00, 300000.00, 0.00),
('2024-2025', 300001.00, 700000.00, 5.00),
('2024-2025', 700001.00, 1000000.00, 10.00),
('2024-2025', 1000001.00, 1200000.00, 15.00),
('2024-2025', 1200001.00, 1500000.00, 20.00),
('2024-2025', 1500001.00, NULL, 30.00);

-- Insert leave types
INSERT INTO `leave_types` (`name`, `code`, `is_paid`, `max_days_per_year`) VALUES
('Casual Leave', 'CL', 1, 12),
('Sick Leave', 'SL', 1, 12),
('Earned Leave', 'EL', 1, 21),
('Maternity Leave', 'ML', 1, 183),
('Paternity Leave', 'PL', 1, 15);

-- Insert holidays for 2024
INSERT INTO `holidays` (`name`, `holiday_date`, `type`) VALUES
('New Year', '2024-01-01', 'national'),
('Republic Day', '2024-01-26', 'national'),
('Holi', '2024-03-25', 'religious'),
('Good Friday', '2024-03-29', 'religious'),
('Independence Day', '2024-08-15', 'national'),
('Gandhi Jayanti', '2024-10-02', 'national'),
('Diwali', '2024-11-12', 'religious'),
('Christmas', '2024-12-25', 'national');

-- Insert payroll periods
INSERT INTO `payroll_periods` (`period_name`, `start_date`, `end_date`, `financial_year`, `status`) VALUES
('January 2024', '2024-01-01', '2024-01-31', '2023-2024', 'locked'),
('February 2024', '2024-02-01', '2024-02-29', '2023-2024', 'locked'),
('March 2024', '2024-03-01', '2024-03-31', '2023-2024', 'locked'),
('April 2024', '2024-04-01', '2024-04-30', '2024-2025', 'locked'),
('May 2024', '2024-05-01', '2024-05-31', '2024-2025', 'locked'),
('June 2024', '2024-06-01', '2024-06-30', '2024-2025', 'locked'),
('July 2024', '2024-07-01', '2024-07-31', '2024-2025', 'open');

-- Add foreign key constraints
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_emp_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_emp_designation` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`),
  ADD CONSTRAINT `fk_emp_cost_center` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`);

ALTER TABLE `salary_structures`
  ADD CONSTRAINT `fk_sal_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sal_component` FOREIGN KEY (`component_id`) REFERENCES `salary_components` (`id`);

ALTER TABLE `payroll_transactions`
  ADD CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payroll_period` FOREIGN KEY (`period_id`) REFERENCES `payroll_periods` (`id`),
  ADD CONSTRAINT `fk_payroll_component` FOREIGN KEY (`component_id`) REFERENCES `salary_components` (`id`);

ALTER TABLE `employee_loans`
  ADD CONSTRAINT `fk_loan_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loan_type` FOREIGN KEY (`loan_type_id`) REFERENCES `loan_types` (`id`);

ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

-- Insert sample payroll transactions for demonstration
INSERT INTO `payroll_transactions` (`employee_id`, `period_id`, `component_id`, `amount`, `calculated_amount`) VALUES
-- John Doe - July 2024
(1, 7, 1, 30000.00, 30000.00), -- Basic
(1, 7, 2, 12000.00, 12000.00), -- HRA
(1, 7, 3, 1600.00, 1600.00),   -- TA
(1, 7, 4, 1250.00, 1250.00),   -- MA
(1, 7, 6, -3600.00, 3600.00),  -- PF
(1, 7, 7, -337.50, 337.50),    -- ESI
(1, 7, 8, -200.00, 200.00),    -- PT
-- Jane Smith - July 2024
(2, 7, 1, 45000.00, 45000.00), -- Basic
(2, 7, 2, 18000.00, 18000.00), -- HRA
(2, 7, 3, 1600.00, 1600.00),   -- TA
(2, 7, 4, 1250.00, 1250.00),   -- MA
(2, 7, 6, -5400.00, 5400.00),  -- PF
(2, 7, 8, -200.00, 200.00);    -- PT

-- Insert sample attendance data
INSERT INTO `attendance` (`employee_id`, `attendance_date`, `check_in`, `check_out`, `total_hours`, `status`) VALUES
(1, '2024-07-01', '09:00:00', '18:00:00', 8.00, 'present'),
(1, '2024-07-02', '09:15:00', '18:00:00', 7.75, 'late'),
(1, '2024-07-03', '09:00:00', '18:00:00', 8.00, 'present'),
(2, '2024-07-01', '09:00:00', '18:00:00', 8.00, 'present'),
(2, '2024-07-02', '09:00:00', '18:00:00', 8.00, 'present'),
(2, '2024-07-03', NULL, NULL, 0.00, 'absent');