<?php
/**
 * Security Manager
 * Handles advanced security features and monitoring
 */

require_once __DIR__ . '/../core/Model.php';

class SecurityManager extends Model {
    
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes
    private $sessionTimeout = 1800; // 30 minutes
    
    public function validateLogin($username, $password, $ipAddress, $userAgent) {
        // Check if IP is blocked
        if ($this->isIPBlocked($ipAddress)) {
            return [
                'success' => false,
                'message' => 'IP address is temporarily blocked',
                'blocked' => true
            ];
        }
        
        // Check login attempts
        if ($this->isAccountLocked($username, $ipAddress)) {
            return [
                'success' => false,
                'message' => 'Account temporarily locked due to multiple failed attempts',
                'locked' => true
            ];
        }
        
        // Validate credentials
        $user = $this->authenticateUser($username, $password);
        
        if ($user) {
            // Successful login
            $this->clearLoginAttempts($username, $ipAddress);
            $this->logSecurityEvent('login_success', $user['id'], $ipAddress, $userAgent);
            
            // Update last login
            $this->db->update('users', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'id = :id', 
                ['id' => $user['id']]
            );
            
            return [
                'success' => true,
                'user' => $user
            ];
        } else {
            // Failed login
            $this->recordLoginAttempt($username, $ipAddress, $userAgent);
            $this->logSecurityEvent('login_failure', null, $ipAddress, $userAgent, ['username' => $username]);
            
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
    }
    
    public function validateSession($sessionId, $userId, $ipAddress) {
        $session = $this->db->fetch(
            "SELECT * FROM user_sessions WHERE session_id = :session_id AND user_id = :user_id AND status = 'active'",
            ['session_id' => $sessionId, 'user_id' => $userId]
        );
        
        if (!$session) {
            return ['valid' => false, 'reason' => 'Session not found'];
        }
        
        // Check session timeout
        $lastActivity = strtotime($session['last_activity']);
        if ((time() - $lastActivity) > $this->sessionTimeout) {
            $this->invalidateSession($sessionId);
            return ['valid' => false, 'reason' => 'Session expired'];
        }
        
        // Check IP address (optional security measure)
        if ($session['ip_address'] !== $ipAddress) {
            $this->logSecurityEvent('session_ip_mismatch', $userId, $ipAddress, '', [
                'original_ip' => $session['ip_address'],
                'current_ip' => $ipAddress
            ]);
            // Optionally invalidate session for security
        }
        
        // Update last activity
        $this->db->update('user_sessions',
            ['last_activity' => date('Y-m-d H:i:s')],
            'session_id = :session_id',
            ['session_id' => $sessionId]
        );
        
        return ['valid' => true, 'session' => $session];
    }
    
    public function createSecureSession($userId, $ipAddress, $userAgent) {
        $sessionId = bin2hex(random_bytes(32));
        
        $sessionData = [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s'),
            'last_activity' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];
        
        $this->db->insert('user_sessions', $sessionData);
        
        return $sessionId;
    }
    
    public function invalidateSession($sessionId) {
        $this->db->update('user_sessions',
            ['status' => 'expired', 'ended_at' => date('Y-m-d H:i:s')],
            'session_id = :session_id',
            ['session_id' => $sessionId]
        );
    }
    
    public function cleanupExpiredSessions() {
        $expiredTime = date('Y-m-d H:i:s', time() - $this->sessionTimeout);
        
        $expiredSessions = $this->db->fetchAll(
            "SELECT session_id FROM user_sessions 
             WHERE status = 'active' AND last_activity < :expired_time",
            ['expired_time' => $expiredTime]
        );
        
        foreach ($expiredSessions as $session) {
            $this->invalidateSession($session['session_id']);
        }
        
        return count($expiredSessions);
    }
    
    public function detectSuspiciousActivity() {
        $suspiciousActivities = [];
        
        // Multiple failed logins from same IP
        $failedLogins = $this->db->fetchAll(
            "SELECT ip_address, COUNT(*) as attempts
             FROM security_logs
             WHERE event_type = 'login_failure' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
             GROUP BY ip_address
             HAVING attempts >= 10"
        );
        
        foreach ($failedLogins as $login) {
            $suspiciousActivities[] = [
                'type' => 'multiple_failed_logins',
                'ip_address' => $login['ip_address'],
                'attempts' => $login['attempts'],
                'severity' => 'high'
            ];
        }
        
        // Unusual access patterns
        $unusualAccess = $this->db->fetchAll(
            "SELECT user_id, ip_address, COUNT(DISTINCT ip_address) as ip_count
             FROM security_logs
             WHERE event_type = 'login_success'
             AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
             GROUP BY user_id
             HAVING ip_count > 3"
        );
        
        foreach ($unusualAccess as $access) {
            $suspiciousActivities[] = [
                'type' => 'multiple_ip_access',
                'user_id' => $access['user_id'],
                'ip_count' => $access['ip_count'],
                'severity' => 'medium'
            ];
        }
        
        return $suspiciousActivities;
    }
    
    public function generateSecurityReport($days = 30) {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $report = [
            'period' => $days . ' days',
            'start_date' => $startDate,
            'end_date' => date('Y-m-d')
        ];
        
        // Login statistics
        $loginStats = $this->db->fetch(
            "SELECT 
                COUNT(*) as total_attempts,
                SUM(CASE WHEN event_type = 'login_success' THEN 1 ELSE 0 END) as successful_logins,
                SUM(CASE WHEN event_type = 'login_failure' THEN 1 ELSE 0 END) as failed_logins,
                COUNT(DISTINCT ip_address) as unique_ips,
                COUNT(DISTINCT user_id) as active_users
             FROM security_logs
             WHERE created_at >= :start_date",
            ['start_date' => $startDate]
        );
        
        $report['login_statistics'] = $loginStats;
        
        // Top failed login IPs
        $topFailedIPs = $this->db->fetchAll(
            "SELECT ip_address, COUNT(*) as attempts
             FROM security_logs
             WHERE event_type = 'login_failure' AND created_at >= :start_date
             GROUP BY ip_address
             ORDER BY attempts DESC
             LIMIT 10",
            ['start_date' => $startDate]
        );
        
        $report['top_failed_ips'] = $topFailedIPs;
        
        // Security events by type
        $eventTypes = $this->db->fetchAll(
            "SELECT event_type, COUNT(*) as count
             FROM security_logs
             WHERE created_at >= :start_date
             GROUP BY event_type
             ORDER BY count DESC",
            ['start_date' => $startDate]
        );
        
        $report['event_types'] = $eventTypes;
        
        // Blocked IPs
        $blockedIPs = $this->db->fetchAll(
            "SELECT * FROM blocked_ips WHERE status = 'active'"
        );
        
        $report['blocked_ips'] = $blockedIPs;
        
        return $report;
    }
    
    public function blockIP($ipAddress, $reason, $duration = 3600) {
        $blockData = [
            'ip_address' => $ipAddress,
            'reason' => $reason,
            'blocked_until' => date('Y-m-d H:i:s', time() + $duration),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('blocked_ips', $blockData);
        
        $this->logSecurityEvent('ip_blocked', null, $ipAddress, '', ['reason' => $reason]);
        
        return ['success' => true, 'message' => 'IP address blocked successfully'];
    }
    
    public function unblockIP($ipAddress) {
        $this->db->update('blocked_ips',
            ['status' => 'unblocked', 'unblocked_at' => date('Y-m-d H:i:s')],
            'ip_address = :ip AND status = :status',
            ['ip' => $ipAddress, 'status' => 'active']
        );
        
        $this->logSecurityEvent('ip_unblocked', null, $ipAddress);
        
        return ['success' => true, 'message' => 'IP address unblocked successfully'];
    }
    
    public function cleanupExpiredBlocks() {
        $expiredBlocks = $this->db->fetchAll(
            "SELECT ip_address FROM blocked_ips 
             WHERE status = 'active' AND blocked_until < NOW()"
        );
        
        foreach ($expiredBlocks as $block) {
            $this->unblockIP($block['ip_address']);
        }
        
        return count($expiredBlocks);
    }
    
    private function isIPBlocked($ipAddress) {
        $block = $this->db->fetch(
            "SELECT * FROM blocked_ips 
             WHERE ip_address = :ip AND status = 'active' AND blocked_until > NOW()",
            ['ip' => $ipAddress]
        );
        
        return !empty($block);
    }
    
    private function isAccountLocked($username, $ipAddress) {
        $attempts = $this->db->fetch(
            "SELECT COUNT(*) as count
             FROM login_attempts
             WHERE (username = :username OR ip_address = :ip)
             AND created_at >= DATE_SUB(NOW(), INTERVAL :duration SECOND)",
            [
                'username' => $username,
                'ip' => $ipAddress,
                'duration' => $this->lockoutDuration
            ]
        );
        
        return $attempts['count'] >= $this->maxLoginAttempts;
    }
    
    private function recordLoginAttempt($username, $ipAddress, $userAgent) {
        $this->db->insert('login_attempts', [
            'username' => $username,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function clearLoginAttempts($username, $ipAddress) {
        $this->db->delete('login_attempts',
            'username = :username OR ip_address = :ip',
            ['username' => $username, 'ip' => $ipAddress]
        );
    }
    
    private function authenticateUser($username, $password) {
        $user = $this->db->fetch(
            "SELECT u.*, r.name as role_name, r.permissions
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.username = :username AND u.status = 'active'",
            ['username' => $username]
        );
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    private function logSecurityEvent($eventType, $userId, $ipAddress, $userAgent = '', $additionalData = []) {
        $this->db->insert('security_logs', [
            'event_type' => $eventType,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'additional_data' => json_encode($additionalData),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}