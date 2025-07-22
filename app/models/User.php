<?php
/**
 * User Model
 */

require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    protected $table = 'users';
    
    public function authenticate($username, $password) {
        $user = $this->findBy('username', $username);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            
            // Get user role and permissions
            $role = $this->getRoleWithPermissions($user['role_id']);
            $user['role_name'] = $role['name'];
            $user['permissions'] = $role['permissions'];
            
            return $user;
        }
        
        return false;
    }
    
    public function createUser($data) {
        // Validate data
        $rules = [
            'username' => ['required' => true, 'min_length' => 3, 'max_length' => 50, 'unique' => true],
            'email' => ['required' => true, 'type' => 'email', 'unique' => true],
            'password' => ['required' => true, 'min_length' => 6],
            'full_name' => ['required' => true, 'max_length' => 100],
            'role_id' => ['required' => true, 'type' => 'numeric']
        ];
        
        $errors = $this->validateData($data, $rules);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        try {
            $id = $this->create($data);
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create user'];
        }
    }
    
    public function updateUser($id, $data) {
        // Remove password if empty
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        try {
            $this->update($id, $data);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update user'];
        }
    }
    
    public function getUsersWithRoles($conditions = '', $params = []) {
        $sql = "SELECT u.*, r.name as role_name 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id";
        
        if (!empty($conditions)) {
            $sql .= " WHERE {$conditions}";
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function getRoleWithPermissions($roleId) {
        $sql = "SELECT * FROM roles WHERE id = :role_id";
        return $this->db->fetch($sql, ['role_id' => $roleId]);
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->findById($userId);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->update($userId, ['password' => $hashedPassword]);
        
        return ['success' => true];
    }
    
    public function updateProfile($userId, $data) {
        $allowedFields = ['full_name', 'email'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($userId, $updateData);
        }
        
        return false;
    }
}