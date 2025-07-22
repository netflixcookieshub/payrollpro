<?php
/**
 * Audit Log Model
 */

require_once __DIR__ . '/../core/Model.php';

class AuditLog extends Model {
    protected $table = 'audit_logs';
    
    public function log($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null, $ipAddress = null, $userAgent = null) {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }
    
    public function getRecentActivities($limit = 50) {
        $sql = "SELECT al.*, u.full_name, u.username
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getUserActivities($userId, $limit = 100) {
        return $this->findAll(
            'user_id = :user_id',
            ['user_id' => $userId],
            'created_at DESC',
            $limit
        );
    }
    
    public function getTableActivities($tableName, $recordId = null, $limit = 50) {
        $conditions = 'table_name = :table_name';
        $params = ['table_name' => $tableName];
        
        if ($recordId !== null) {
            $conditions .= ' AND record_id = :record_id';
            $params['record_id'] = $recordId;
        }
        
        return $this->findAll($conditions, $params, 'created_at DESC', $limit);
    }
    
    public function getActivitiesByDateRange($startDate, $endDate, $userId = null) {
        $conditions = 'DATE(created_at) BETWEEN :start_date AND :end_date';
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        if ($userId) {
            $conditions .= ' AND user_id = :user_id';
            $params['user_id'] = $userId;
        }
        
        return $this->findAll($conditions, $params, 'created_at DESC');
    }
    
    public function getActivityStats($days = 30) {
        $sql = "SELECT 
                    DATE(created_at) as activity_date,
                    COUNT(*) as activity_count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY activity_date DESC";
        
        return $this->db->fetchAll($sql, ['days' => $days]);
    }
    
    public function getMostActiveUsers($limit = 10, $days = 30) {
        $sql = "SELECT 
                    u.full_name, u.username,
                    COUNT(al.id) as activity_count
                FROM {$this->table} al
                JOIN users u ON al.user_id = u.id
                WHERE al.created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY al.user_id
                ORDER BY activity_count DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function cleanOldLogs($days = 365) {
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < DATE_SUB(CURDATE(), INTERVAL :days DAY)";
        
        return $this->db->query($sql, ['days' => $days]);
    }
    
    public function searchLogs($searchTerm, $limit = 100) {
        $sql = "SELECT al.*, u.full_name, u.username
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.action LIKE :search 
                   OR al.table_name LIKE :search
                   OR u.full_name LIKE :search
                   OR u.username LIKE :search
                ORDER BY al.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $searchParam = "%{$searchTerm}%";
        $stmt->bindValue(':search', $searchParam);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}