<?php
/**
 * Salary Component Model
 */

require_once __DIR__ . '/../core/Model.php';

class SalaryComponent extends Model {
    protected $table = 'salary_components';
    
    public function getActiveComponents() {
        return $this->findAll('status = :status', ['status' => 'active'], 'display_order ASC');
    }
    
    public function getEarningComponents() {
        return $this->findAll(
            'status = :status AND type = :type',
            ['status' => 'active', 'type' => 'earning'],
            'display_order ASC'
        );
    }
    
    public function getDeductionComponents() {
        return $this->findAll(
            'status = :status AND type = :type',
            ['status' => 'active', 'type' => 'deduction'],
            'display_order ASC'
        );
    }
    
    public function createComponent($data) {
        $rules = [
            'name' => ['required' => true, 'max_length' => 100],
            'code' => ['required' => true, 'max_length' => 20, 'unique' => true],
            'type' => ['required' => true],
            'display_order' => ['type' => 'numeric']
        ];
        
        $errors = $this->validateData($data, $rules);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Set default display order if not provided
        if (empty($data['display_order'])) {
            $maxOrder = $this->db->fetch("SELECT MAX(display_order) as max_order FROM {$this->table}");
            $data['display_order'] = ($maxOrder['max_order'] ?? 0) + 1;
        }
        
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->create($data);
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create salary component'];
        }
    }
    
    public function updateComponent($id, $data) {
        try {
            $this->update($id, $data);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update salary component'];
        }
    }
    
    public function getComponentUsage($componentId) {
        $sql = "SELECT COUNT(DISTINCT employee_id) as employee_count,
                       COUNT(*) as transaction_count
                FROM salary_structures 
                WHERE component_id = :component_id 
                AND (end_date IS NULL OR end_date >= CURDATE())";
        
        return $this->db->fetch($sql, ['component_id' => $componentId]);
    }
    
    public function validateFormula($formula) {
        // Basic formula validation
        if (empty($formula)) {
            return ['valid' => true];
        }
        
        // Check for valid operators and component codes
        $allowedPattern = '/^[A-Z_0-9\+\-\*\/\(\)\.\s]+$/';
        
        if (!preg_match($allowedPattern, $formula)) {
            return ['valid' => false, 'message' => 'Formula contains invalid characters'];
        }
        
        // Check for balanced parentheses
        $openCount = substr_count($formula, '(');
        $closeCount = substr_count($formula, ')');
        
        if ($openCount !== $closeCount) {
            return ['valid' => false, 'message' => 'Unbalanced parentheses in formula'];
        }
        
        return ['valid' => true];
    }
    
    public function getFormulaComponents($formula) {
        // Extract component codes from formula
        preg_match_all('/[A-Z_]+/', $formula, $matches);
        
        $componentCodes = array_unique($matches[0]);
        $validCodes = [];
        
        foreach ($componentCodes as $code) {
            $component = $this->findBy('code', $code);
            if ($component) {
                $validCodes[] = $code;
            }
        }
        
        return $validCodes;
    }
    
    public function reorderComponents($componentIds) {
        try {
            $this->beginTransaction();
            
            foreach ($componentIds as $index => $componentId) {
                $this->update($componentId, ['display_order' => $index + 1]);
            }
            
            $this->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to reorder components'];
        }
    }
    
    public function getComponentStats() {
        $stats = [];
        
        // Total components by type
        $sql = "SELECT type, COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'active' 
                GROUP BY type";
        $stats['by_type'] = $this->db->fetchAll($sql);
        
        // Most used components
        $sql = "SELECT sc.name, sc.code, COUNT(ss.id) as usage_count
                FROM {$this->table} sc
                LEFT JOIN salary_structures ss ON sc.id = ss.component_id
                WHERE sc.status = 'active'
                GROUP BY sc.id
                ORDER BY usage_count DESC
                LIMIT 10";
        $stats['most_used'] = $this->db->fetchAll($sql);
        
        return $stats;
    }
}