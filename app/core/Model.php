<?php
/**
 * Base Model Class
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct(Database $database) {
        $this->db = $database;
    }
    
    public function findAll($conditions = '', $params = [], $orderBy = '', $limit = '') {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE {$conditions}";
        }
        
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if (!empty($limit)) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    public function findBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value";
        return $this->db->fetch($sql, ['value' => $value]);
    }
    
    public function create($data) {
        return $this->db->insert($this->table, $data);
    }
    
    public function update($id, $data) {
        return $this->db->update($this->table, $data, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete($this->table, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    public function count($conditions = '', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE {$conditions}";
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
    
    public function paginate($page = 1, $perPage = RECORDS_PER_PAGE, $conditions = '', $params = [], $orderBy = '') {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE {$conditions}";
        }
        
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->db->fetchAll($sql, $params);
        $total = $this->count($conditions, $params);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_previous' => $page > 1,
                'has_next' => $page < ceil($total / $perPage)
            ]
        ];
    }
    
    public function execute($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
    
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    public function commit() {
        return $this->db->commit();
    }
    
    public function rollback() {
        return $this->db->rollback();
    }
    
    protected function validateData($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Required validation
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                continue;
            }
            
            // Skip other validations if field is empty and not required
            if (empty($value) && (!isset($rule['required']) || !$rule['required'])) {
                continue;
            }
            
            // Type validation
            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid email';
                        }
                        break;
                    case 'numeric':
                        if (!is_numeric($value)) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be numeric';
                        }
                        break;
                    case 'date':
                        if (!strtotime($value)) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date';
                        }
                        break;
                }
            }
            
            // Length validation
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $rule['min_length'] . ' characters';
            }
            
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must not exceed ' . $rule['max_length'] . ' characters';
            }
            
            // Unique validation
            if (isset($rule['unique']) && $rule['unique']) {
                $existing = $this->findBy($field, $value);
                if ($existing && (!isset($data[$this->primaryKey]) || $existing[$this->primaryKey] != $data[$this->primaryKey])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' already exists';
                }
            }
        }
        
        return $errors;
    }
}