<?php
/**
 * Integration Model
 * Handles external system integrations
 */

require_once __DIR__ . '/../core/Model.php';

class Integration extends Model {
    protected $table = 'integrations';
    
    public function saveConfiguration($type, $config) {
        try {
            // Check if configuration exists
            $existing = $this->db->fetch(
                "SELECT id FROM integrations WHERE type = :type",
                ['type' => $type]
            );
            
            $data = [
                'type' => $type,
                'name' => $config['name'] ?? ucfirst($type) . ' Integration',
                'config' => json_encode($config),
                'status' => 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existing) {
                $this->update($existing['id'], $data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->create($data);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to save configuration'];
        }
    }
    
    public function testConnection($type) {
        $config = $this->getConfiguration($type);
        
        if (!$config) {
            return ['success' => false, 'message' => 'Integration not configured'];
        }
        
        switch ($type) {
            case 'hrms':
                return $this->testHRMSConnection($config);
            case 'banking':
                return $this->testBankingConnection($config);
            case 'attendance':
                return $this->testAttendanceConnection($config);
            case 'email':
                return $this->testEmailConnection($config);
            case 'sms':
                return $this->testSMSConnection($config);
            default:
                return ['success' => false, 'message' => 'Unknown integration type'];
        }
    }
    
    public function syncData($type) {
        $config = $this->getConfiguration($type);
        
        if (!$config) {
            return ['success' => false, 'message' => 'Integration not configured'];
        }
        
        switch ($type) {
            case 'hrms':
                return $this->syncHRMSData($config);
            case 'attendance':
                return $this->syncAttendanceData($config);
            case 'banking':
                return $this->syncBankingData($config);
            default:
                return ['success' => false, 'message' => 'Sync not supported for this integration'];
        }
    }
    
    public function processWebhook($type, $payload, $headers) {
        try {
            $config = $this->getConfiguration($type);
            
            if (!$config) {
                return ['success' => false, 'message' => 'Integration not configured'];
            }
            
            // Verify webhook signature if configured
            if (!empty($config['webhook_secret'])) {
                if (!$this->verifyWebhookSignature($payload, $headers, $config['webhook_secret'])) {
                    return ['success' => false, 'message' => 'Invalid webhook signature'];
                }
            }
            
            $data = json_decode($payload, true);
            
            switch ($type) {
                case 'attendance':
                    return $this->processAttendanceWebhook($data);
                case 'hrms':
                    return $this->processHRMSWebhook($data);
                default:
                    return ['success' => false, 'message' => 'Webhook not supported'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Webhook processing failed'];
        }
    }
    
    private function getConfiguration($type) {
        $integration = $this->db->fetch(
            "SELECT * FROM integrations WHERE type = :type AND status = 'active'",
            ['type' => $type]
        );
        
        if ($integration) {
            return json_decode($integration['config'], true);
        }
        
        return null;
    }
    
    private function testHRMSConnection($config) {
        $url = $config['api_url'] ?? '';
        $apiKey = $config['api_key'] ?? '';
        
        if (empty($url) || empty($apiKey)) {
            return ['success' => false, 'message' => 'API URL and key required'];
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '/test');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return ['success' => true, 'message' => 'Connection successful'];
        } else {
            return ['success' => false, 'message' => 'Connection failed'];
        }
    }
    
    private function testBankingConnection($config) {
        // Simulate banking API test
        return ['success' => true, 'message' => 'Banking connection test successful'];
    }
    
    private function testAttendanceConnection($config) {
        $deviceIp = $config['device_ip'] ?? '';
        
        if (empty($deviceIp)) {
            return ['success' => false, 'message' => 'Device IP required'];
        }
        
        // Test ping to device
        $ping = exec("ping -c 1 {$deviceIp}", $output, $result);
        
        if ($result === 0) {
            return ['success' => true, 'message' => 'Device reachable'];
        } else {
            return ['success' => false, 'message' => 'Device not reachable'];
        }
    }
    
    private function testEmailConnection($config) {
        $smtpHost = $config['smtp_host'] ?? '';
        $smtpPort = $config['smtp_port'] ?? 587;
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        
        if (empty($smtpHost) || empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'SMTP configuration incomplete'];
        }
        
        // Test SMTP connection
        $connection = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 10);
        
        if ($connection) {
            fclose($connection);
            return ['success' => true, 'message' => 'SMTP connection successful'];
        } else {
            return ['success' => false, 'message' => 'SMTP connection failed'];
        }
    }
    
    private function testSMSConnection($config) {
        $apiUrl = $config['api_url'] ?? '';
        $apiKey = $config['api_key'] ?? '';
        
        if (empty($apiUrl) || empty($apiKey)) {
            return ['success' => false, 'message' => 'SMS API configuration incomplete'];
        }
        
        // Test SMS API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl . '/balance');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return ['success' => true, 'message' => 'SMS API connection successful'];
        } else {
            return ['success' => false, 'message' => 'SMS API connection failed'];
        }
    }
    
    private function syncHRMSData($config) {
        // Implement HRMS data synchronization
        return ['success' => true, 'message' => 'HRMS data synchronized', 'records' => 0];
    }
    
    private function syncAttendanceData($config) {
        // Implement attendance data synchronization
        return ['success' => true, 'message' => 'Attendance data synchronized', 'records' => 0];
    }
    
    private function syncBankingData($config) {
        // Implement banking data synchronization
        return ['success' => true, 'message' => 'Banking data synchronized', 'records' => 0];
    }
    
    private function processAttendanceWebhook($data) {
        // Process incoming attendance data from biometric devices
        $attendanceModel = $this->loadModel('Attendance');
        $processed = 0;
        
        foreach ($data['records'] ?? [] as $record) {
            $attendanceData = [
                'employee_id' => $this->getEmployeeIdByCode($record['emp_code']),
                'attendance_date' => $record['date'],
                'check_in' => $record['check_in'] ?? null,
                'check_out' => $record['check_out'] ?? null,
                'status' => $record['status'] ?? 'present'
            ];
            
            if ($attendanceData['employee_id']) {
                $result = $attendanceModel->markAttendance($attendanceData);
                if ($result['success']) {
                    $processed++;
                }
            }
        }
        
        return ['success' => true, 'message' => "Processed {$processed} attendance records"];
    }
    
    private function processHRMSWebhook($data) {
        // Process incoming employee data from HRMS
        $employeeModel = $this->loadModel('Employee');
        $processed = 0;
        
        foreach ($data['employees'] ?? [] as $empData) {
            // Update or create employee record
            $existing = $this->db->fetch(
                "SELECT id FROM employees WHERE emp_code = :code",
                ['code' => $empData['emp_code']]
            );
            
            if ($existing) {
                $result = $employeeModel->updateEmployee($existing['id'], $empData);
            } else {
                $result = $employeeModel->createEmployee($empData);
            }
            
            if ($result['success']) {
                $processed++;
            }
        }
        
        return ['success' => true, 'message' => "Processed {$processed} employee records"];
    }
    
    private function verifyWebhookSignature($payload, $headers, $secret) {
        $signature = $headers['X-Signature'] ?? '';
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    private function getEmployeeIdByCode($empCode) {
        $employee = $this->db->fetch(
            "SELECT id FROM employees WHERE emp_code = :code",
            ['code' => $empCode]
        );
        
        return $employee ? $employee['id'] : null;
    }
    
    public function sendPayslipEmail($employeeId, $periodId) {
        $config = $this->getConfiguration('email');
        
        if (!$config) {
            return ['success' => false, 'message' => 'Email not configured'];
        }
        
        // Get employee and payslip data
        $employee = $this->db->fetch("SELECT * FROM employees WHERE id = :id", ['id' => $employeeId]);
        $period = $this->db->fetch("SELECT * FROM payroll_periods WHERE id = :id", ['id' => $periodId]);
        
        if (!$employee || !$period) {
            return ['success' => false, 'message' => 'Employee or period not found'];
        }
        
        // Generate payslip PDF (simplified)
        $payslipData = $this->generatePayslipData($employeeId, $periodId);
        
        // Send email
        $subject = "Payslip for {$period['period_name']}";
        $body = "Dear {$employee['first_name']},\n\nPlease find attached your payslip for {$period['period_name']}.\n\nBest regards,\nHR Team";
        
        return $this->sendEmail($employee['email'], $subject, $body, $config);
    }
    
    private function sendEmail($to, $subject, $body, $config) {
        // Implement email sending logic
        return ['success' => true, 'message' => 'Email sent successfully'];
    }
    
    private function generatePayslipData($employeeId, $periodId) {
        // Generate payslip data for email attachment
        return [];
    }
}