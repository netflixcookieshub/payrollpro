<?php
/**
 * Tax Slab Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class TaxSlabController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('payroll');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleTaxSlabAction();
        } else {
            $this->showTaxSlabs();
        }
    }
    
    private function handleTaxSlabAction() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $csrfToken = $input['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createTaxSlab($input);
                break;
            case 'update':
                $this->updateTaxSlab($input);
                break;
            case 'delete':
                $this->deleteTaxSlab($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
    }
    
    private function createTaxSlab($data) {
        $rules = [
            'financial_year' => ['required' => true, 'max_length' => 9],
            'min_amount' => ['required' => true, 'type' => 'numeric'],
            'tax_rate' => ['required' => true, 'type' => 'numeric']
        ];
        
        $errors = $this->validateInput($data, $rules);
        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        try {
            $insertData = [
                'financial_year' => $data['financial_year'],
                'min_amount' => $data['min_amount'],
                'max_amount' => !empty($data['max_amount']) ? $data['max_amount'] : null,
                'tax_rate' => $data['tax_rate'],
                'surcharge_rate' => $data['surcharge_rate'] ?? 0,
                'cess_rate' => $data['cess_rate'] ?? 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $this->db->insert('tax_slabs', $insertData);
            
            $this->logActivity('create_tax_slab', 'tax_slabs', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Tax slab created successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create tax slab'], 500);
        }
    }
    
    private function updateTaxSlab($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Tax slab ID is required'], 400);
            return;
        }
        
        try {
            $updateData = [
                'financial_year' => $data['financial_year'],
                'min_amount' => $data['min_amount'],
                'max_amount' => !empty($data['max_amount']) ? $data['max_amount'] : null,
                'tax_rate' => $data['tax_rate'],
                'surcharge_rate' => $data['surcharge_rate'] ?? 0,
                'cess_rate' => $data['cess_rate'] ?? 0
            ];
            
            $this->db->update('tax_slabs', $updateData, 'id = :id', ['id' => $id]);
            
            $this->logActivity('update_tax_slab', 'tax_slabs', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Tax slab updated successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update tax slab'], 500);
        }
    }
    
    private function deleteTaxSlab($data) {
        $id = $data['id'] ?? '';
        
        if (empty($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Tax slab ID is required'], 400);
            return;
        }
        
        try {
            $this->db->delete('tax_slabs', 'id = :id', ['id' => $id]);
            
            $this->logActivity('delete_tax_slab', 'tax_slabs', $id);
            $this->jsonResponse(['success' => true, 'message' => 'Tax slab deleted successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete tax slab'], 500);
        }
    }
    
    private function showTaxSlabs() {
        $currentFY = $this->getCurrentFinancialYear();
        $taxSlabs = $this->db->fetchAll(
            "SELECT * FROM tax_slabs 
             WHERE financial_year = :fy 
             ORDER BY min_amount ASC",
            ['fy' => $currentFY]
        );
        
        $this->loadView('masters/tax-slabs', [
            'tax_slabs' => $taxSlabs,
            'current_fy' => $currentFY,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function getCurrentFinancialYear() {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
}