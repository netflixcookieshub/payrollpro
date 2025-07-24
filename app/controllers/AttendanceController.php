<?php
/**
 * Attendance Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class AttendanceController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        $month = $_GET['month'] ?? date('Y-m');
        $departmentId = $_GET['department'] ?? '';
        
        $attendanceModel = $this->loadModel('Attendance');
        $employeeModel = $this->loadModel('Employee');
        
        // Get employees for the selected department
        $conditions = "status = 'active'";
        $params = [];
        
        if (!empty($departmentId)) {
            $conditions .= " AND department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $employees = $employeeModel->findAll($conditions, $params, 'emp_code ASC');
        
        // Get attendance data for the month
        $attendanceData = $attendanceModel->getMonthlyAttendance($month, $departmentId);
        
        // Get departments for filter
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('attendance/index', [
            'employees' => $employees,
            'attendance_data' => $attendanceData,
            'departments' => $departments,
            'selected_month' => $month,
            'selected_department' => $departmentId
        ]);
    }
    
    public function mark() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleMarkAttendance();
        } else {
            $this->showMarkAttendanceForm();
        }
    }
    
    public function bulkMark() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleBulkMarkAttendance();
        } else {
            $this->showBulkMarkForm();
        }
    }
    
    public function import() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleImportAttendance();
        } else {
            $this->showImportForm();
        }
    }
    
    public function export() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        $month = $_GET['month'] ?? date('Y-m');
        $departmentId = $_GET['department'] ?? '';
        $format = $_GET['format'] ?? 'excel';
        
        $attendanceModel = $this->loadModel('Attendance');
        $data = $attendanceModel->getAttendanceReport($month, $departmentId);
        
        if ($format === 'excel') {
            $this->exportToExcel($data, "attendance_report_{$month}.xlsx", 'Attendance Report');
        } else {
            $this->exportToCSV($data, "attendance_report_{$month}.csv");
        }
    }
    
    private function handleMarkAttendance() {
        $employeeId = $_POST['employee_id'] ?? '';
        $attendanceDate = $_POST['attendance_date'] ?? '';
        $status = $_POST['status'] ?? 'present';
        $checkIn = $_POST['check_in'] ?? '';
        $checkOut = $_POST['check_out'] ?? '';
        $remarks = $_POST['remarks'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        if (empty($employeeId) || empty($attendanceDate)) {
            $this->jsonResponse(['success' => false, 'message' => 'Employee and date are required'], 400);
            return;
        }
        
        $attendanceModel = $this->loadModel('Attendance');
        
        // Calculate total hours if check-in and check-out are provided
        $totalHours = null;
        if (!empty($checkIn) && !empty($checkOut)) {
            $checkInTime = new DateTime($checkIn);
            $checkOutTime = new DateTime($checkOut);
            $interval = $checkInTime->diff($checkOutTime);
            $totalHours = $interval->h + ($interval->i / 60);
        }
        
        $data = [
            'employee_id' => $employeeId,
            'attendance_date' => $attendanceDate,
            'check_in' => !empty($checkIn) ? $checkIn : null,
            'check_out' => !empty($checkOut) ? $checkOut : null,
            'total_hours' => $totalHours,
            'status' => $status,
            'remarks' => $remarks
        ];
        
        $result = $attendanceModel->markAttendance($data);
        
        if ($result['success']) {
            $this->logActivity('mark_attendance', 'attendance', $result['id']);
            $this->jsonResponse(['success' => true, 'message' => 'Attendance marked successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
        }
    }
    
    private function handleBulkMarkAttendance() {
        $attendanceDate = $_POST['attendance_date'] ?? '';
        $attendanceData = $_POST['attendance'] ?? [];
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        if (empty($attendanceDate) || empty($attendanceData)) {
            $this->jsonResponse(['success' => false, 'message' => 'Date and attendance data are required'], 400);
            return;
        }
        
        $attendanceModel = $this->loadModel('Attendance');
        $result = $attendanceModel->bulkMarkAttendance($attendanceDate, $attendanceData);
        
        if ($result['success']) {
            $this->logActivity('bulk_mark_attendance', 'attendance', null);
            $this->jsonResponse([
                'success' => true, 
                'message' => "Attendance marked for {$result['processed']} employees"
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
        }
    }
    
    private function showMarkAttendanceForm() {
        $employeeModel = $this->loadModel('Employee');
        $employees = $employeeModel->getActiveEmployees();
        
        $this->loadView('attendance/mark', [
            'employees' => $employees,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showBulkMarkForm() {
        $employeeModel = $this->loadModel('Employee');
        $employees = $employeeModel->getActiveEmployees();
        
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('attendance/bulk-mark', [
            'employees' => $employees,
            'departments' => $departments,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showImportForm() {
        $this->loadView('attendance/import', [
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function handleImportAttendance() {
        if (empty($_FILES['attendance_file']['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Please select a file'], 400);
            return;
        }
        
        try {
            $filePath = $this->uploadFile($_FILES['attendance_file'], 'attendance');
            $attendanceModel = $this->loadModel('Attendance');
            $result = $attendanceModel->importFromFile($filePath);
            
            if ($result['success']) {
                $this->logActivity('import_attendance', 'attendance', null);
                $this->jsonResponse([
                    'success' => true, 
                    'message' => "Imported {$result['imported']} attendance records"
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    private function exportToExcel($data, $filename, $title = 'Report') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo "<table border='1'>";
        echo "<tr><th colspan='" . count($data[0] ?? []) . "'><h2>$title</h2></th></tr>";
        
        if (!empty($data)) {
            // Headers
            echo "<tr>";
            foreach (array_keys($data[0]) as $header) {
                echo "<th>" . ucwords(str_replace('_', ' ', $header)) . "</th>";
            }
            echo "</tr>";
            
            // Data
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        exit;
    }
    
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Headers
            fputcsv($output, array_keys($data[0]));
            
            // Data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}