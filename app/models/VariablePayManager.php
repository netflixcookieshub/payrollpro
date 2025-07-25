<?php
/**
 * Variable Pay Management Model
 */

require_once __DIR__ . '/../core/Model.php';

class VariablePayManager extends Model {
    protected $table = 'employee_variable_pay';
    
    public function createVariablePay($data) {
        $rules = [
            'employee_id' => ['required' => true, 'type' => 'numeric'],
            'component_id' => ['required' => true, 'type' => 'numeric'],
            'period_id' => ['required' => true, 'type' => 'numeric'],
            'amount' => ['required' => true, 'type' => 'numeric'],
            'description' => ['required' => true, 'max_length' => 255]
        ];
        
        $errors = $this->validateData($data, $rules);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $data['status'] = 'pending';
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->create($data);
            
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create variable pay'];
        }
    }
    
    public function getEmployeeVariablePay($employeeId, $periodId = null) {
        $sql = "SELECT evp.*, sc.name as component_name, sc.code as component_code,
                       pp.period_name, e.emp_code, e.first_name, e.last_name
                FROM {$this->table} evp
                JOIN salary_components sc ON evp.component_id = sc.id
                JOIN payroll_periods pp ON evp.period_id = pp.id
                JOIN employees e ON evp.employee_id = e.id
                WHERE evp.employee_id = :emp_id";
        
        $params = ['emp_id' => $employeeId];
        
        if ($periodId) {
            $sql .= " AND evp.period_id = :period_id";
            $params['period_id'] = $periodId;
        }
        
        $sql .= " ORDER BY evp.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getPendingVariablePay($periodId = null) {
        $sql = "SELECT evp.*, sc.name as component_name, sc.code as component_code,
                       pp.period_name, e.emp_code, e.first_name, e.last_name
                FROM {$this->table} evp
                JOIN salary_components sc ON evp.component_id = sc.id
                JOIN payroll_periods pp ON evp.period_id = pp.id
                JOIN employees e ON evp.employee_id = e.id
                WHERE evp.status = 'pending'";
        
        $params = [];
        
        if ($periodId) {
            $sql .= " AND evp.period_id = :period_id";
            $params['period_id'] = $periodId;
        }
        
        $sql .= " ORDER BY evp.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function approveVariablePay($id, $approvedBy) {
        try {
            $this->update($id, [
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => date('Y-m-d H:i:s')
            ]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to approve variable pay'];
        }
    }
    
    public function rejectVariablePay($id, $rejectedBy, $reason = '') {
        try {
            $this->update($id, [
                'status' => 'rejected',
                'rejected_by' => $rejectedBy,
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason
            ]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to reject variable pay'];
        }
    }
    
    public function getVariablePayStats() {
        $sql = "SELECT 
                    COUNT(*) as total_variable_pay,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END) as processed_count,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = 'processed' THEN amount ELSE 0 END) as processed_amount
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    public function bulkApproveVariablePay($ids, $approvedBy) {
        try {
            $this->beginTransaction();
            
            foreach ($ids as $id) {
                $this->update($id, [
                    'status' => 'approved',
                    'approved_by' => $approvedBy,
                    'approved_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->commit();
            return ['success' => true, 'count' => count($ids)];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to bulk approve variable pay'];
        }
    }
    
    public function calculatePerformanceBonus($employeeId, $periodId, $performanceRating, $baseAmount) {
        $multipliers = [
            'excellent' => 1.5,
            'good' => 1.2,
            'satisfactory' => 1.0,
            'needs_improvement' => 0.5,
            'poor' => 0.0
        ];
        
        $multiplier = $multipliers[$performanceRating] ?? 1.0;
        $bonusAmount = $baseAmount * $multiplier;
        
        return [
            'base_amount' => $baseAmount,
            'performance_rating' => $performanceRating,
            'multiplier' => $multiplier,
            'bonus_amount' => $bonusAmount
        ];
    }
}