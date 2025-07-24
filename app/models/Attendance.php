<?php
/**
 * Attendance Model
 */

require_once __DIR__ . '/../core/Model.php';

class Attendance extends Model {
    protected $table = 'attendance';
    
    public function markAttendance($data) {
        $rules = [
            'employee_id' => ['required' => true, 'type' => 'numeric'],
            'attendance_date' => ['required' => true, 'type' => 'date'],
            'status' => ['required' => true]
        ];
        
        $errors = $this->validateData($data, $rules);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if attendance already exists
        $existing = $this->findBy('employee_id', $data['employee_id']);
        $existingForDate = $this->db->fetch(
            "SELECT id FROM {$this->table} WHERE employee_id = :emp_id AND attendance_date = :date",
            ['emp_id' => $data['employee_id'], 'date' => $data['attendance_date']]
        );
        
        try {
            if ($existingForDate) {
                // Update existing record
                $this->update($existingForDate['id'], $data);
                return ['success' => true, 'id' => $existingForDate['id']];
            } else {
                // Create new record
                $data['created_at'] = date('Y-m-d H:i:s');
                $id = $this->create($data);
                return ['success' => true, 'id' => $id];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to mark attendance'];
        }
    }
    
    public function bulkMarkAttendance($attendanceDate, $attendanceData) {
        try {
            $this->beginTransaction();
            
            $processed = 0;
            foreach ($attendanceData as $employeeId => $status) {
                if (!empty($status)) {
                    $data = [
                        'employee_id' => $employeeId,
                        'attendance_date' => $attendanceDate,
                        'status' => $status
                    ];
                    
                    $result = $this->markAttendance($data);
                    if ($result['success']) {
                        $processed++;
                    }
                }
            }
            
            $this->commit();
            return ['success' => true, 'processed' => $processed];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to process bulk attendance'];
        }
    }
    
    public function getMonthlyAttendance($month, $departmentId = null) {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $conditions = "a.attendance_date BETWEEN :start_date AND :end_date";
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        if (!empty($departmentId)) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT a.*, e.emp_code, e.first_name, e.last_name, d.name as department_name
                FROM {$this->table} a
                JOIN employees e ON a.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                WHERE {$conditions}
                ORDER BY e.emp_code, a.attendance_date";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getEmployeeAttendance($employeeId, $startDate, $endDate) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE employee_id = :emp_id 
                AND attendance_date BETWEEN :start_date AND :end_date
                ORDER BY attendance_date";
        
        return $this->db->fetchAll($sql, [
            'emp_id' => $employeeId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function getAttendanceSummary($employeeId, $month) {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'half_day' THEN 0.5 ELSE 0 END) as half_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    AVG(total_hours) as avg_hours
                FROM {$this->table}
                WHERE employee_id = :emp_id 
                AND attendance_date BETWEEN :start_date AND :end_date";
        
        return $this->db->fetch($sql, [
            'emp_id' => $employeeId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function getAttendanceReport($month, $departmentId = null) {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $conditions = "a.attendance_date BETWEEN :start_date AND :end_date";
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        if (!empty($departmentId)) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    e.emp_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    d.name as department_name,
                    COUNT(a.id) as total_marked_days,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN a.status = 'half_day' THEN 0.5 ELSE 0 END) as half_days,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                    AVG(a.total_hours) as avg_hours_per_day
                FROM employees e
                LEFT JOIN {$this->table} a ON e.id = a.employee_id AND {$conditions}
                JOIN departments d ON e.department_id = d.id
                WHERE e.status = 'active'
                GROUP BY e.id
                ORDER BY e.emp_code";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function importFromFile($filePath) {
        $fullPath = UPLOAD_PATH . $filePath;
        
        if (!file_exists($fullPath)) {
            return ['success' => false, 'message' => 'File not found'];
        }
        
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        
        if ($extension === 'csv') {
            return $this->importFromCSV($fullPath);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            return $this->importFromExcel($fullPath);
        } else {
            return ['success' => false, 'message' => 'Unsupported file format'];
        }
    }
    
    private function importFromCSV($filePath) {
        try {
            $this->beginTransaction();
            
            $handle = fopen($filePath, 'r');
            $headers = fgetcsv($handle); // Skip header row
            $imported = 0;
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 4) {
                    $data = [
                        'employee_id' => $this->getEmployeeIdByCode($row[0]),
                        'attendance_date' => $row[1],
                        'check_in' => !empty($row[2]) ? $row[2] : null,
                        'check_out' => !empty($row[3]) ? $row[3] : null,
                        'status' => $row[4] ?? 'present'
                    ];
                    
                    if ($data['employee_id']) {
                        $result = $this->markAttendance($data);
                        if ($result['success']) {
                            $imported++;
                        }
                    }
                }
            }
            
            fclose($handle);
            $this->commit();
            
            return ['success' => true, 'imported' => $imported];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Import failed: ' . $e->getMessage()];
        }
    }
    
    private function getEmployeeIdByCode($empCode) {
        $employee = $this->db->fetch("SELECT id FROM employees WHERE emp_code = :code", ['code' => $empCode]);
        return $employee ? $employee['id'] : null;
    }
    
    public function getWorkingDays($startDate, $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $workingDays = 0;
        
        // Get holidays in the date range
        $holidays = $this->db->fetchAll(
            "SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN :start_date AND :end_date",
            ['start_date' => $startDate, 'end_date' => $endDate]
        );
        
        $holidayDates = array_column($holidays, 'holiday_date');
        
        while ($start <= $end) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($start->format('w') != 0 && $start->format('w') != 6) {
                // Skip holidays
                if (!in_array($start->format('Y-m-d'), $holidayDates)) {
                    $workingDays++;
                }
            }
            $start->modify('+1 day');
        }
        
        return $workingDays;
    }
}