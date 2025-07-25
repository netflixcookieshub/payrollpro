<?php
/**
 * Arrears Management Model
 */

require_once __DIR__ . '/../core/Model.php';

class ArrearsManager extends Model {
    protected $table = 'employee_arrears';
    
    public function createArrears($data) {
        $rules = [
            'employee_id' => ['required' => true, 'type' => 'numeric'],
            'component_id' => ['required' => true, 'type' => 'numeric'],
            'amount' => ['required' => true, 'type' => 'numeric'],
            'effective_period_id' => ['required' => true, 'type' => 'numeric'],
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
            return ['success' => false, 'message' => 'Failed to create arrears'];
        }
    }
    
    public function getEmployeeArrears($employeeId, $status = 'pending') {
        $sql = "SELECT ea.*, sc.name as component_name, sc.code as component_code,
                       pp.period_name, e.emp_code, e.first_name, e.last_name
                FROM {$this->table} ea
                JOIN salary_components sc ON ea.component_id = sc.id
                JOIN payroll_periods pp ON ea.effective_period_id = pp.id
                JOIN employees e ON ea.employee_id = e.id
                WHERE ea.employee_id = :emp_id";
        
        $params = ['emp_id' => $employeeId];
        
        if (!empty($status)) {
            $sql .= " AND ea.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY ea.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getPendingArrears($periodId = null) {
        $sql = "SELECT ea.*, sc.name as component_name, sc.code as component_code,
                       pp.period_name, e.emp_code, e.first_name, e.last_name
                FROM {$this->table} ea
                JOIN salary_components sc ON ea.component_id = sc.id
                JOIN payroll_periods pp ON ea.effective_period_id = pp.id
                JOIN employees e ON ea.employee_id = e.id
                WHERE ea.status = 'pending'";
        
        $params = [];
        
        if ($periodId) {
            $sql .= " AND ea.effective_period_id = :period_id";
            $params['period_id'] = $periodId;
        }
        
        $sql .= " ORDER BY ea.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function approveArrears($id, $approvedBy) {
        try {
            $this->update($id, [
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => date('Y-m-d H:i:s')
            ]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to approve arrears'];
        }
    }
    
    public function rejectArrears($id, $rejectedBy, $reason = '') {
        try {
            $this->update($id, [
                'status' => 'rejected',
                'rejected_by' => $rejectedBy,
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason
            ]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to reject arrears'];
        }
    }
    
    public function getArrearsStats() {
        $sql = "SELECT 
                    COUNT(*) as total_arrears,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END) as processed_count,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = 'processed' THEN amount ELSE 0 END) as processed_amount
                FROM {$this->table}";
        
        return $this->db->fetch($sql);
    }
    
    public function bulkApproveArrears($ids, $approvedBy) {
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
            return ['success' => false, 'message' => 'Failed to bulk approve arrears'];
        }
    }
}