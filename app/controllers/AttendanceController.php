<?php
/**
 * Attendance Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class AttendanceController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('employees');
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $departmentId = $_GET['department'] ?? '';
        
        $attendanceModel = $this->loadModel('Attendance');
        $attendance = $attendanceModel->getAttendanceByDate($date, $departmentId);
        
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('attendance/index', [
            'attendance' => $attendance,
            'departments' => $departments,
            'selected_date' => $date,
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
        }
    }
    
    public function report() {
        $this->checkAuth();
        $this->checkPermission('reports');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateAttendanceReport();
        } else {
            $this->showAttendanceReportForm();
        }
    }
    
    private function handleMarkAttendance() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $attendanceModel = $this->loadModel('Attendance');
        $result = $attendanceModel->markAttendance($data);
        
        if ($result['success']) {
            $this->logActivity('mark_attendance', 'attendance', $result['id']);
            $this->jsonResponse(['success' => true, 'message' => 'Attendance marked successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
        }
    }
    
    private function handleBulkMarkAttendance() {
        $attendanceData = $_POST['attendance'] ?? [];
        $date = $_POST['date'] ?? date('Y-m-d');
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $attendanceModel = $this->loadModel('Attendance');
        $result = $attendanceModel->bulkMarkAttendance($attendanceData, $date);
        
        if ($result['success']) {
            $this->logActivity('bulk_mark_attendance', 'attendance', null);
            $this->jsonResponse(['success' => true, 'message' => "Attendance marked for {$result['count']} employees"]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
        }
    }
    
    private function showMarkAttendanceForm() {
        $employees = $this->db->fetchAll(
            "SELECT e.*, d.name as department_name 
             FROM employees e 
             JOIN departments d ON e.department_id = d.id 
             WHERE e.status = 'active' 
             ORDER BY e.emp_code ASC"
        );
        
        $this->loadView('attendance/mark', [
            'employees' => $employees,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function generateAttendanceReport() {
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $departmentId = $_POST['department_id'] ?? '';
        $format = $_POST['format'] ?? 'excel';
        
        if (empty($startDate) || empty($endDate)) {
            $this->jsonResponse(['success' => false, 'message' => 'Date range is required'], 400);
            return;
        }
        
        $attendanceModel = $this->loadModel('Attendance');
        $data = $attendanceModel->getAttendanceReport($startDate, $endDate, $departmentId);
        
        if ($format === 'excel') {
            $this->exportToExcel($data, 'attendance_report_' . date('Y-m-d') . '.xlsx', 'Attendance Report');
        } else {
            $this->exportToCSV($data, 'attendance_report_' . date('Y-m-d') . '.csv');
        }
    }
    
    private function showAttendanceReportForm() {
        $departments = $this->db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
        
        $this->loadView('attendance/report', [
            'departments' => $departments,
            'csrf_token' => $this->generateCSRFToken()
        ]);
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