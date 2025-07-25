<?php
/**
 * Attendance Model
 */

require_once __DIR__ . '/../core/Model.php';

class Attendance extends Model {
    protected $table = 'attendance';
    
    public function getAttendanceByDate($date, $departmentId = null) {
        $conditions = "a.attendance_date = :date";
        $params = ['date' => $date];
        
        if (!empty($departmentId)) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT a.*, e.emp_code, e.first_name, e.last_name, d.name as department_name
                FROM {$this->table} a
                RIGHT JOIN employees e ON a.employee_id = e.id AND a.attendance_date = :date
                JOIN departments d ON e.department_id = d.id
                WHERE e.status = 'active' AND ($conditions OR a.id IS NULL)
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
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
        
        try {
            // Check if attendance already exists
            $existing = $this->findBy('employee_id', $data['employee_id']);
            $existingForDate = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE employee_id = :emp_id AND attendance_date = :date",
                ['emp_id' => $data['employee_id'], 'date' => $data['attendance_date']]
            );
            
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
    
    public function bulkMarkAttendance($attendanceData, $date) {
        try {
            $this->beginTransaction();
            
            $count = 0;
            foreach ($attendanceData as $employeeId => $status) {
                $data = [
                    'employee_id' => $employeeId,
                    'attendance_date' => $date,
                    'status' => $status
                ];
                
                $result = $this->markAttendance($data);
                if ($result['success']) {
                    $count++;
                }
            }
            
            $this->commit();
            return ['success' => true, 'count' => $count];
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => 'Failed to mark bulk attendance'];
        }
    }
    
    public function getAttendanceReport($startDate, $endDate, $departmentId = null) {
        $conditions = "a.attendance_date BETWEEN :start_date AND :end_date";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if (!empty($departmentId)) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    e.emp_code,
                    e.first_name,
                    e.last_name,
                    d.name as department_name,
                    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
                    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
                    COUNT(CASE WHEN a.status = 'half_day' THEN 1 END) as half_days,
                    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
                    COUNT(a.id) as total_marked_days
                FROM employees e
                JOIN departments d ON e.department_id = d.id
                LEFT JOIN {$this->table} a ON e.id = a.employee_id AND {$conditions}
                WHERE e.status = 'active'
                GROUP BY e.id
                ORDER BY e.emp_code ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getEmployeeAttendance($employeeId, $month, $year) {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE employee_id = :emp_id 
                AND attendance_date BETWEEN :start_date AND :end_date
                ORDER BY attendance_date ASC";
        
        return $this->db->fetchAll($sql, [
            'emp_id' => $employeeId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
    
    public function getAttendanceStats($employeeId = null, $startDate = null, $endDate = null) {
        $conditions = [];
        $params = [];
        
        if ($employeeId) {
            $conditions[] = "employee_id = :emp_id";
            $params['emp_id'] = $employeeId;
        }
        
        if ($startDate && $endDate) {
            $conditions[] = "attendance_date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    COUNT(CASE WHEN status = 'present' THEN 1 END) as present_days,
                    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_days,
                    COUNT(CASE WHEN status = 'half_day' THEN 1 END) as half_days,
                    COUNT(CASE WHEN status = 'late' THEN 1 END) as late_days
                FROM {$this->table} 
                {$whereClause}";
        
        return $this->db->fetch($sql, $params);
    }
}