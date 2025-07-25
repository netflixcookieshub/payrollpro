<?php
/**
 * Department Model
 */

require_once __DIR__ . '/../core/Model.php';

class Department extends Model {
    protected $table = 'departments';
    
    public function getActiveDepartments() {
        return $this->findAll('status = :status', ['status' => 'active'], 'name ASC');
    }
    
    public function getDepartmentsWithHeads() {
        $sql = "SELECT d.*, 
                       e.first_name as head_first_name,
                       e.last_name as head_last_name,
                       (SELECT COUNT(*) FROM employees WHERE department_id = d.id AND status = 'active') as employee_count
                FROM {$this->table} d
                LEFT JOIN employees e ON d.head_id = e.id
                WHERE d.status = 'active'
                ORDER BY d.name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function createDepartment($data) {
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'code' => ['required' => true, 'max_length' => 10, 'unique' => true],
            'head_id' => ['type' => 'numeric']
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
            return ['success' => false, 'message' => 'Failed to create department'];
        }
    }
    
    public function updateDepartment($id, $data) {
        try {
            $this->update($id, $data);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update department'];
        }
    }
    
    public function getDepartmentStats() {
        $sql = "SELECT 
                    d.name,
                    COUNT(e.id) as employee_count,
                    AVG(CASE WHEN ss.amount IS NOT NULL THEN ss.amount ELSE 0 END) as avg_salary
                FROM {$this->table} d
                LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
                LEFT JOIN salary_structures ss ON e.id = ss.employee_id AND ss.component_id = 1 AND ss.end_date IS NULL
                WHERE d.status = 'active'
                GROUP BY d.id, d.name
                ORDER BY employee_count DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function canDelete($id) {
        $employeeCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM employees WHERE department_id = :id AND status != 'deleted'",
            ['id' => $id]
        );
        
        return $employeeCount['count'] == 0;
    }
}