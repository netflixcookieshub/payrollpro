/*
  # Formula Editor and Custom Query Tables

  1. New Tables
    - `formula_templates` - Store reusable formula templates
    - `saved_queries` - Store custom SQL queries
    - `query_executions` - Log query execution history

  2. Security
    - Enable RLS on all new tables
    - Add policies for user access control
*/

-- Formula Templates Table
CREATE TABLE IF NOT EXISTS formula_templates (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  formula TEXT NOT NULL,
  description TEXT,
  category VARCHAR(50) DEFAULT 'custom',
  variables JSONB,
  created_by INTEGER REFERENCES users(id),
  is_public BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Saved Queries Table
CREATE TABLE IF NOT EXISTS saved_queries (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  query_sql TEXT NOT NULL,
  description TEXT,
  category VARCHAR(50) DEFAULT 'custom',
  parameters JSONB,
  created_by INTEGER REFERENCES users(id),
  is_public BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);

-- Query Execution Log Table
CREATE TABLE IF NOT EXISTS query_executions (
  id SERIAL PRIMARY KEY,
  query_id INTEGER REFERENCES saved_queries(id),
  executed_by INTEGER REFERENCES users(id),
  execution_time_ms INTEGER,
  result_count INTEGER,
  status VARCHAR(20) DEFAULT 'success',
  error_message TEXT,
  executed_at TIMESTAMP DEFAULT NOW()
);

-- Enable RLS
ALTER TABLE formula_templates ENABLE ROW LEVEL SECURITY;
ALTER TABLE saved_queries ENABLE ROW LEVEL SECURITY;
ALTER TABLE query_executions ENABLE ROW LEVEL SECURITY;

-- RLS Policies for formula_templates
CREATE POLICY "Users can view their own formula templates"
  ON formula_templates
  FOR SELECT
  TO authenticated
  USING (created_by = auth.uid() OR is_public = true);

CREATE POLICY "Users can create formula templates"
  ON formula_templates
  FOR INSERT
  TO authenticated
  WITH CHECK (created_by = auth.uid());

CREATE POLICY "Users can update their own formula templates"
  ON formula_templates
  FOR UPDATE
  TO authenticated
  USING (created_by = auth.uid());

CREATE POLICY "Users can delete their own formula templates"
  ON formula_templates
  FOR DELETE
  TO authenticated
  USING (created_by = auth.uid());

-- RLS Policies for saved_queries
CREATE POLICY "Users can view their own saved queries"
  ON saved_queries
  FOR SELECT
  TO authenticated
  USING (created_by = auth.uid() OR is_public = true);

CREATE POLICY "Users can create saved queries"
  ON saved_queries
  FOR INSERT
  TO authenticated
  WITH CHECK (created_by = auth.uid());

CREATE POLICY "Users can update their own saved queries"
  ON saved_queries
  FOR UPDATE
  TO authenticated
  USING (created_by = auth.uid());

CREATE POLICY "Users can delete their own saved queries"
  ON saved_queries
  FOR DELETE
  TO authenticated
  USING (created_by = auth.uid());

-- RLS Policies for query_executions
CREATE POLICY "Users can view their own query executions"
  ON query_executions
  FOR SELECT
  TO authenticated
  USING (executed_by = auth.uid());

CREATE POLICY "Users can log query executions"
  ON query_executions
  FOR INSERT
  TO authenticated
  WITH CHECK (executed_by = auth.uid());

-- Insert sample formula templates
INSERT INTO formula_templates (name, formula, description, category, variables, created_by, is_public) VALUES
('HRA Calculation', 'BASIC * 0.4', 'Standard HRA calculation as 40% of basic salary', 'earning', '["BASIC"]', 1, true),
('PF Calculation', 'MIN(BASIC * 0.12, 1800)', 'PF calculation with ceiling of ₹1,800', 'deduction', '["BASIC"]', 1, true),
('Conditional Allowance', 'IF(BASIC > 50000, BASIC * 0.1, 0)', 'Special allowance for high earners', 'earning', '["BASIC"]', 1, true),
('Pro-rata Calculation', 'BASIC * (PRESENT_DAYS / WORKING_DAYS)', 'Pro-rata salary based on attendance', 'earning', '["BASIC", "PRESENT_DAYS", "WORKING_DAYS"]', 1, true),
('Overtime Calculation', 'ROUND((BASIC / (WORKING_DAYS * 8)) * OVERTIME_HOURS * 2, 2)', 'Overtime pay at double rate', 'earning', '["BASIC", "WORKING_DAYS", "OVERTIME_HOURS"]', 1, true);

-- Insert sample saved queries
INSERT INTO saved_queries (name, query_sql, description, category, created_by, is_public) VALUES
('Employee Salary Summary', 
'SELECT e.emp_code, CONCAT(e.first_name, '' '', e.last_name) as name, d.name as department, SUM(CASE WHEN sc.type = ''earning'' THEN pt.amount ELSE 0 END) as earnings FROM employees e JOIN payroll_transactions pt ON e.id = pt.employee_id JOIN salary_components sc ON pt.component_id = sc.id JOIN departments d ON e.department_id = d.id WHERE pt.period_id = :period_id GROUP BY e.id, d.name ORDER BY earnings DESC',
'Summary of employee salaries for a specific period',
'report',
1,
true),

('Department Wise Cost Analysis',
'SELECT d.name as department, COUNT(e.id) as employees, SUM(CASE WHEN sc.type = ''earning'' THEN pt.amount ELSE 0 END) as total_cost FROM departments d JOIN employees e ON d.id = e.department_id JOIN payroll_transactions pt ON e.id = pt.employee_id JOIN salary_components sc ON pt.component_id = sc.id WHERE pt.period_id = :period_id GROUP BY d.id, d.name ORDER BY total_cost DESC',
'Department-wise cost analysis for budget planning',
'analysis',
1,
true);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_formula_templates_category ON formula_templates(category);
CREATE INDEX IF NOT EXISTS idx_formula_templates_created_by ON formula_templates(created_by);
CREATE INDEX IF NOT EXISTS idx_saved_queries_category ON saved_queries(category);
CREATE INDEX IF NOT EXISTS idx_saved_queries_created_by ON saved_queries(created_by);
CREATE INDEX IF NOT EXISTS idx_query_executions_executed_by ON query_executions(executed_by);
CREATE INDEX IF NOT EXISTS idx_query_executions_executed_at ON query_executions(executed_at);