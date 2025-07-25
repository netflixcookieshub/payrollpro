<?php
/**
 * Employee Model
 */

require_once __DIR__ . '/../core/Model.php';

class Employee extends Model {
    protected $table = 'employees';
    
    public function getEmployeesWithDetails($conditions = '', $params = []) {
        $sql = "SELECT e.*, 
                       d.name as department_name,
                       des.name as designation_name,
                       cc.name as cost_center_name,
                       mgr.first_name as manager_first_name,
                       mgr.last_name as manager_last_name
                FROM {$this->table} e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN cost_centers cc ON e.cost_center_id = cc.id
                LEFT JOIN employees mgr ON e.reporting_manager_id = mgr.id";
        
        if (!empty($conditions)) {
            $sql .= " WHERE {$conditions}";
        }
        
        $sql .= " ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getEmployeeWithDetails($id) {
        $sql = "SELECT e.*, 
                       d.name as department_name, d.code as department_code,
                       des.name as designation_name, des.code as designation_code,
                       cc.name as cost_center_name, cc.code as cost_center_code,
                       mgr.first_name as manager_first_name,
                       mgr.last_name as manager_last_name
                FROM {$this->table} e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN cost_centers cc ON e.cost_center_id = cc.id
                LEFT JOIN employees mgr ON e.reporting_manager_id = mgr.id
                WHERE e.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    public function createEmployee($data) {
        // Generate employee code if not provided
        if (empty($data['emp_code'])) {
            $data['emp_code'] = $this->generateEmployeeCode();
        }
        
        // Validation rules
        $rules = [
            'emp_code' => ['required' => true, 'unique' => true],
            'first_name' => ['required' => true, 'max_length' => 50],
            'last_name' => ['required' => true, 'max_length' => 50],
            'email' => ['type' => 'email', 'unique' => true],
            'join_date' => ['required' => true, 'type' => 'date'],
            'department_id' => ['required' => true, 'type' => 'numeric'],
            'designation_id' => ['required' => true, 'type' => 'numeric']
        ];
        
        $errors = $this->validateData($data, $rules);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->create($data);
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create employee'];
        }
    }
    
    public function updateEmployee($id, $data) {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->update($id, $data);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update employee'];
        }
    }
    
    public function getActiveEmployees() {
        return $this->findAll('status = :status', ['status' => 'active'], 'first_name ASC');
    }
    
    public function searchEmployees($search, $department = '', $status = '') {
        $conditions = [];
        $params = [];
        
        if (!empty($search)) {
            $conditions[] = "(e.emp_code LIKE :search OR e.first_name LIKE :search OR e.last_name LIKE :search OR e.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($department)) {
            $conditions[] = "e.department_id = :department";
            $params['department'] = $department;
        }
        
        if (!empty($status)) {
            $conditions[] = "e.status = :status";
            $params['status'] = $status;
        }
        
        $whereClause = !empty($conditions) ? implode(' AND ', $conditions) : '';
        
        return $this->getEmployeesWithDetails($whereClause, $params);
    }
    
    public function getEmployeesByDepartment($departmentId) {
        return $this->getEmployeesWithDetails('e.department_id = :dept_id AND e.status = :status', [
            'dept_id' => $departmentId,
            'status' => 'active'
        ]);
    }
    
    public function getEmployeeSalaryStructure($employeeId) {
        $sql = "SELECT ss.*, sc.name as component_name, sc.code as component_code, 
                       sc.type as component_type, sc.display_order
                FROM salary_structures ss
                JOIN salary_components sc ON ss.component_id = sc.id
                WHERE ss.employee_id = :emp_id 
                AND (ss.end_date IS NULL OR ss.end_date >= CURDATE())
                ORDER BY sc.display_order ASC";
        
        return $this->db->fetchAll($sql, ['emp_id' => $employeeId]);
    }
    
    public function assignSalaryStructure($employeeId, $components) {
        try {
            $this->beginTransaction();
            
            // End current salary structure
            $this->db->query(
                "UPDATE salary_structures SET end_date = CURDATE() 
                 WHERE employee_id = :emp_id AND end_date IS NULL",
                ['emp_id' => $employeeId]
            );
            
            // Insert new salary structure
            foreach ($components as $component) {
                $this->db->insert('salary_structures', [
                    'employee_id' => $employeeId,
                    'component_id' => $component['component_id'],
                    'amount' => $component['amount'],
                    'effective_date' => $component['effective_date'] ?? date('Y-m-d')
                ]);
            }
            
            $this->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to assign salary structure'];
        }
    }
    
    private function generateEmployeeCode() {
        $sql = "SELECT emp_code FROM {$this->table} WHERE emp_code LIKE 'EMP%' ORDER BY emp_code DESC LIMIT 1";
        $result = $this->db->fetch($sql);
        
        if ($result) {
            $lastCode = $result['emp_code'];
            $number = intval(substr($lastCode, 3)) + 1;
            return 'EMP' . str_pad($number, 3, '0', STR_PAD_LEFT);
        } else {
            return 'EMP001';
        }
    }
    
    public function getEmployeeStats() {
        $stats = [];
        
        // Total employees
        $stats['total'] = $this->count();
        
        // Active employees
        $stats['active'] = $this->count('status = :status', ['status' => 'active']);
        
        // Department wise count
        $sql = "SELECT d.name, COUNT(e.id) as count 
                FROM departments d 
                LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
                WHERE d.status = 'active'
                GROUP BY d.id, d.name";
        $stats['departments'] = $this->db->fetchAll($sql);
        
        // Recent joinings (last 30 days)
        $stats['recent_joinings'] = $this->count(
            'join_date >= :date AND status = :status',
            ['date' => date('Y-m-d', strtotime('-30 days')), 'status' => 'active']
        );
        
        return $stats;
    }
    
    public function getEmployeesByStatus($status = 'active') {
        return $this->findAll('status = :status', ['status' => $status], 'first_name ASC');
    }
    
    public function getEmployeeCount($departmentId = null, $status = 'active') {
        $conditions = 'status = :status';
        $params = ['status' => $status];
        
        if ($departmentId) {
            $conditions .= ' AND department_id = :dept_id';
            $params['dept_id'] = $departmentId;
        }
        
        return $this->count($conditions, $params);
    }
}