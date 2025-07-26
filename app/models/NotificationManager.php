<?php
/**
 * Notification Manager
 * Handles email, SMS, and in-app notifications
 */

require_once __DIR__ . '/../core/Model.php';

class NotificationManager extends Model {
    
    private $emailConfig;
    private $smsConfig;
    
    public function __construct($database) {
        parent::__construct($database);
        $this->loadConfigurations();
    }
    
    public function sendPayslipNotification($employeeId, $periodId, $method = 'email') {
        $employee = $this->getEmployeeDetails($employeeId);
        $period = $this->getPeriodDetails($periodId);
        
        if (!$employee || !$period) {
            return ['success' => false, 'message' => 'Invalid employee or period'];
        }
        
        switch ($method) {
            case 'email':
                return $this->sendPayslipEmail($employee, $period);
            case 'sms':
                return $this->sendPayslipSMS($employee, $period);
            case 'both':
                $emailResult = $this->sendPayslipEmail($employee, $period);
                $smsResult = $this->sendPayslipSMS($employee, $period);
                return [
                    'success' => $emailResult['success'] || $smsResult['success'],
                    'email' => $emailResult,
                    'sms' => $smsResult
                ];
            default:
                return ['success' => false, 'message' => 'Invalid notification method'];
        }
    }
    
    public function sendBulkPayslipNotifications($periodId, $employeeIds = [], $method = 'email') {
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        
        if (empty($employeeIds)) {
            // Get all employees for the period
            $employeeIds = $this->getEmployeesForPeriod($periodId);
        }
        
        foreach ($employeeIds as $employeeId) {
            $result = $this->sendPayslipNotification($employeeId, $periodId, $method);
            $results[] = [
                'employee_id' => $employeeId,
                'result' => $result
            ];
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }
        
        return [
            'success' => true,
            'total' => count($employeeIds),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results
        ];
    }
    
    public function sendSystemAlert($type, $message, $recipients = []) {
        $alertData = [
            'type' => $type,
            'message' => $message,
            'severity' => $this->getAlertSeverity($type),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Store alert in database
        $alertId = $this->db->insert('system_alerts', $alertData);
        
        // Send notifications to recipients
        $results = [];
        foreach ($recipients as $recipient) {
            if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $results[] = $this->sendAlertEmail($recipient, $alertData);
            } else {
                $results[] = $this->sendAlertSMS($recipient, $alertData);
            }
        }
        
        return [
            'success' => true,
            'alert_id' => $alertId,
            'notification_results' => $results
        ];
    }
    
    public function scheduleNotification($type, $data, $scheduleTime) {
        $notificationData = [
            'type' => $type,
            'data' => json_encode($data),
            'schedule_time' => $scheduleTime,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = $this->db->insert('scheduled_notifications', $notificationData);
        
        return ['success' => true, 'id' => $id];
    }
    
    public function processScheduledNotifications() {
        $pendingNotifications = $this->db->fetchAll(
            "SELECT * FROM scheduled_notifications 
             WHERE status = 'pending' 
             AND schedule_time <= NOW()
             ORDER BY schedule_time ASC"
        );
        
        $processedCount = 0;
        
        foreach ($pendingNotifications as $notification) {
            try {
                $data = json_decode($notification['data'], true);
                $result = $this->processNotification($notification['type'], $data);
                
                $this->db->update('scheduled_notifications', 
                    [
                        'status' => $result['success'] ? 'sent' : 'failed',
                        'processed_at' => date('Y-m-d H:i:s'),
                        'result' => json_encode($result)
                    ],
                    'id = :id',
                    ['id' => $notification['id']]
                );
                
                $processedCount++;
            } catch (Exception $e) {
                $this->db->update('scheduled_notifications',
                    [
                        'status' => 'failed',
                        'processed_at' => date('Y-m-d H:i:s'),
                        'error_message' => $e->getMessage()
                    ],
                    'id = :id',
                    ['id' => $notification['id']]
                );
            }
        }
        
        return ['processed' => $processedCount];
    }
    
    private function sendPayslipEmail($employee, $period) {
        if (empty($employee['email'])) {
            return ['success' => false, 'message' => 'Employee email not available'];
        }
        
        $subject = "Payslip for {$period['period_name']}";
        $body = $this->generatePayslipEmailBody($employee, $period);
        
        // Generate payslip PDF attachment
        $payslipPDF = $this->generatePayslipPDF($employee['id'], $period['id']);
        
        return $this->sendEmail($employee['email'], $subject, $body, [$payslipPDF]);
    }
    
    private function sendPayslipSMS($employee, $period) {
        if (empty($employee['phone'])) {
            return ['success' => false, 'message' => 'Employee phone not available'];
        }
        
        $message = "Dear {$employee['first_name']}, your payslip for {$period['period_name']} is ready. Please check your email or contact HR.";
        
        return $this->sendSMS($employee['phone'], $message);
    }
    
    private function sendEmail($to, $subject, $body, $attachments = []) {
        if (!$this->emailConfig) {
            return ['success' => false, 'message' => 'Email not configured'];
        }
        
        try {
            // Using PHPMailer or similar email library
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->emailConfig['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->emailConfig['username'];
            $mail->Password = $this->emailConfig['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->emailConfig['port'];
            
            // Recipients
            $mail->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Attachments
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment['path'], $attachment['name']);
            }
            
            $mail->send();
            
            // Log email
            $this->logNotification('email', $to, $subject, 'sent');
            
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            $this->logNotification('email', $to, $subject, 'failed', $e->getMessage());
            return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
        }
    }
    
    private function sendSMS($to, $message) {
        if (!$this->smsConfig) {
            return ['success' => false, 'message' => 'SMS not configured'];
        }
        
        try {
            // SMS API integration
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->smsConfig['api_url']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'to' => $to,
                'message' => $message,
                'api_key' => $this->smsConfig['api_key']
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $this->logNotification('sms', $to, $message, 'sent');
                return ['success' => true, 'message' => 'SMS sent successfully'];
            } else {
                $this->logNotification('sms', $to, $message, 'failed', $response);
                return ['success' => false, 'message' => 'SMS sending failed'];
            }
        } catch (Exception $e) {
            $this->logNotification('sms', $to, $message, 'failed', $e->getMessage());
            return ['success' => false, 'message' => 'SMS sending failed: ' . $e->getMessage()];
        }
    }
    
    private function generatePayslipEmailBody($employee, $period) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Payslip for {$period['period_name']}</h2>
            <p>Dear {$employee['first_name']} {$employee['last_name']},</p>
            <p>Please find attached your payslip for the period {$period['period_name']}.</p>
            <p>If you have any questions regarding your payslip, please contact the HR department.</p>
            <br>
            <p>Best regards,<br>HR Team</p>
        </body>
        </html>
        ";
    }
    
    private function generatePayslipPDF($employeeId, $periodId) {
        // Generate PDF payslip (simplified implementation)
        $filename = "payslip_{$employeeId}_{$periodId}.pdf";
        $filepath = UPLOAD_PATH . 'payslips/' . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        // Generate PDF content (using TCPDF or similar library)
        // For now, return a placeholder
        file_put_contents($filepath, "PDF Payslip Content");
        
        return [
            'path' => $filepath,
            'name' => $filename
        ];
    }
    
    private function loadConfigurations() {
        // Load email configuration
        $emailIntegration = $this->db->fetch(
            "SELECT config FROM integrations WHERE type = 'email' AND status = 'active'"
        );
        
        if ($emailIntegration) {
            $this->emailConfig = json_decode($emailIntegration['config'], true);
        }
        
        // Load SMS configuration
        $smsIntegration = $this->db->fetch(
            "SELECT config FROM integrations WHERE type = 'sms' AND status = 'active'"
        );
        
        if ($smsIntegration) {
            $this->smsConfig = json_decode($smsIntegration['config'], true);
        }
    }
    
    private function getEmployeeDetails($employeeId) {
        return $this->db->fetch(
            "SELECT * FROM employees WHERE id = :id",
            ['id' => $employeeId]
        );
    }
    
    private function getPeriodDetails($periodId) {
        return $this->db->fetch(
            "SELECT * FROM payroll_periods WHERE id = :id",
            ['id' => $periodId]
        );
    }
    
    private function getEmployeesForPeriod($periodId) {
        $employees = $this->db->fetchAll(
            "SELECT DISTINCT employee_id FROM payroll_transactions WHERE period_id = :period_id",
            ['period_id' => $periodId]
        );
        
        return array_column($employees, 'employee_id');
    }
    
    private function getAlertSeverity($type) {
        $severityMap = [
            'system_error' => 'high',
            'payroll_failure' => 'high',
            'integration_failure' => 'medium',
            'backup_failure' => 'medium',
            'login_failure' => 'low',
            'data_import' => 'low'
        ];
        
        return $severityMap[$type] ?? 'medium';
    }
    
    private function logNotification($type, $recipient, $content, $status, $error = null) {
        $this->db->insert('notification_logs', [
            'type' => $type,
            'recipient' => $recipient,
            'content' => $content,
            'status' => $status,
            'error_message' => $error,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getNotificationStats($days = 30) {
        return $this->db->fetch(
            "SELECT 
                COUNT(*) as total_notifications,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN type = 'email' THEN 1 ELSE 0 END) as email_count,
                SUM(CASE WHEN type = 'sms' THEN 1 ELSE 0 END) as sms_count
             FROM notification_logs
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)",
            ['days' => $days]
        );
    }
}