<?php
/**
 * Workflow Controller
 * Handles approval workflows for various operations
 */

require_once __DIR__ . '/../core/Controller.php';

class WorkflowController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('admin');
        
        $pendingApprovals = $this->getPendingApprovals();
        $myApprovals = $this->getMyApprovals();
        
        $this->loadView('workflow/index', [
            'pending_approvals' => $pendingApprovals,
            'my_approvals' => $myApprovals,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    public function approve() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $workflowId = $input['workflow_id'] ?? '';
            $comments = $input['comments'] ?? '';
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            $result = $this->processApproval($workflowId, 'approved', $comments);
            $this->jsonResponse($result);
        }
    }
    
    public function reject() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $workflowId = $input['workflow_id'] ?? '';
            $reason = $input['reason'] ?? '';
            $csrfToken = $input['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            if (empty($reason)) {
                $this->jsonResponse(['success' => false, 'message' => 'Rejection reason is required'], 400);
                return;
            }
            
            $result = $this->processApproval($workflowId, 'rejected', $reason);
            $this->jsonResponse($result);
        }
    }
    
    public function createWorkflow($workflowType, $recordId, $requestedBy) {
        $workflowData = [
            'workflow_type' => $workflowType,
            'record_id' => $recordId,
            'requested_by' => $requestedBy,
            'current_approver' => $this->getNextApprover($workflowType, $requestedBy),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $id = $this->db->insert('workflow_approvals', $workflowData);
            
            // Send notification to approver
            $this->notifyApprover($id);
            
            return ['success' => true, 'workflow_id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create workflow'];
        }
    }
    
    private function processApproval($workflowId, $action, $comments) {
        try {
            $workflow = $this->db->fetch(
                "SELECT * FROM workflow_approvals WHERE id = :id",
                ['id' => $workflowId]
            );
            
            if (!$workflow) {
                return ['success' => false, 'message' => 'Workflow not found'];
            }
            
            if ($workflow['current_approver'] != $_SESSION['user_id']) {
                return ['success' => false, 'message' => 'You are not authorized to approve this workflow'];
            }
            
            $updateData = [
                'status' => $action,
                $action . '_by' => $_SESSION['user_id'],
                $action . '_at' => date('Y-m-d H:i:s')
            ];
            
            if ($action === 'rejected') {
                $updateData['rejection_reason'] = $comments;
            }
            
            $this->db->update('workflow_approvals', $updateData, 'id = :id', ['id' => $workflowId]);
            
            // Process the actual approval/rejection
            $this->executeWorkflowAction($workflow, $action);
            
            $this->logActivity($action . '_workflow', 'workflow_approvals', $workflowId);
            
            return ['success' => true, 'message' => ucfirst($action) . ' successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to process approval'];
        }
    }
    
    private function executeWorkflowAction($workflow, $action) {
        switch ($workflow['workflow_type']) {
            case 'salary_change':
                $this->processSalaryChangeApproval($workflow, $action);
                break;
            case 'loan_application':
                $this->processLoanApproval($workflow, $action);
                break;
            case 'variable_pay':
                $this->processVariablePayApproval($workflow, $action);
                break;
            case 'arrears':
                $this->processArrearsApproval($workflow, $action);
                break;
        }
    }
    
    private function getPendingApprovals() {
        return $this->db->fetchAll(
            "SELECT wa.*, u.full_name as requested_by_name,
                    CASE 
                        WHEN wa.workflow_type = 'salary_change' THEN CONCAT('Salary Change - ', e.first_name, ' ', e.last_name)
                        WHEN wa.workflow_type = 'loan_application' THEN CONCAT('Loan Application - ', e.first_name, ' ', e.last_name)
                        WHEN wa.workflow_type = 'variable_pay' THEN CONCAT('Variable Pay - ', e.first_name, ' ', e.last_name)
                        WHEN wa.workflow_type = 'arrears' THEN CONCAT('Arrears - ', e.first_name, ' ', e.last_name)
                        ELSE wa.workflow_type
                    END as workflow_title
             FROM workflow_approvals wa
             JOIN users u ON wa.requested_by = u.id
             LEFT JOIN employees e ON (
                 (wa.workflow_type = 'salary_change' AND e.id = wa.record_id) OR
                 (wa.workflow_type = 'loan_application' AND e.id = (SELECT employee_id FROM employee_loans WHERE id = wa.record_id)) OR
                 (wa.workflow_type = 'variable_pay' AND e.id = (SELECT employee_id FROM employee_variable_pay WHERE id = wa.record_id)) OR
                 (wa.workflow_type = 'arrears' AND e.id = (SELECT employee_id FROM employee_arrears WHERE id = wa.record_id))
             )
             WHERE wa.status = 'pending' AND wa.current_approver = :user_id
             ORDER BY wa.created_at DESC",
            ['user_id' => $_SESSION['user_id']]
        );
    }
    
    private function getMyApprovals() {
        return $this->db->fetchAll(
            "SELECT * FROM workflow_approvals 
             WHERE requested_by = :user_id 
             ORDER BY created_at DESC 
             LIMIT 20",
            ['user_id' => $_SESSION['user_id']]
        );
    }
    
    private function getNextApprover($workflowType, $requestedBy) {
        // Simple approval hierarchy - in production, this would be more sophisticated
        $approverHierarchy = [
            'salary_change' => $this->getHRManager(),
            'loan_application' => $this->getFinanceManager(),
            'variable_pay' => $this->getDepartmentHead($requestedBy),
            'arrears' => $this->getPayrollManager()
        ];
        
        return $approverHierarchy[$workflowType] ?? $this->getDefaultApprover();
    }
    
    private function getHRManager() {
        $hrManager = $this->db->fetch(
            "SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id 
             WHERE r.name = 'HR Admin' AND u.status = 'active' LIMIT 1"
        );
        return $hrManager ? $hrManager['id'] : 1;
    }
    
    private function getFinanceManager() {
        $financeManager = $this->db->fetch(
            "SELECT u.id FROM users u 
             WHERE u.full_name LIKE '%Finance%' AND u.status = 'active' LIMIT 1"
        );
        return $financeManager ? $financeManager['id'] : 1;
    }
    
    private function getDepartmentHead($userId) {
        // Get user's department head
        return 1; // Simplified - return admin
    }
    
    private function getPayrollManager() {
        $payrollManager = $this->db->fetch(
            "SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id 
             WHERE r.name = 'Payroll Manager' AND u.status = 'active' LIMIT 1"
        );
        return $payrollManager ? $payrollManager['id'] : 1;
    }
    
    private function getDefaultApprover() {
        return 1; // Default to admin
    }
    
    private function notifyApprover($workflowId) {
        // Implementation for sending notification to approver
        // This would integrate with the NotificationManager
    }
    
    private function processSalaryChangeApproval($workflow, $action) {
        if ($action === 'approved') {
            // Activate the salary change
            $this->db->update('salary_structures', 
                ['status' => 'active'], 
                'id = :id', 
                ['id' => $workflow['record_id']]
            );
        }
    }
    
    private function processLoanApproval($workflow, $action) {
        if ($action === 'approved') {
            $this->db->update('employee_loans', 
                ['status' => 'active'], 
                'id = :id', 
                ['id' => $workflow['record_id']]
            );
        } else {
            $this->db->update('employee_loans', 
                ['status' => 'rejected'], 
                'id = :id', 
                ['id' => $workflow['record_id']]
            );
        }
    }
    
    private function processVariablePayApproval($workflow, $action) {
        $this->db->update('employee_variable_pay', 
            ['status' => $action], 
            'id = :id', 
            ['id' => $workflow['record_id']]
        );
    }
    
    private function processArrearsApproval($workflow, $action) {
        $this->db->update('employee_arrears', 
            ['status' => $action], 
            'id = :id', 
            ['id' => $workflow['record_id']]
        );
    }
}