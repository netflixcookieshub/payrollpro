<?php
// Attendance Overview Widget
$today = date('Y-m-d');

$attendanceStats = $this->db->fetch(
    "SELECT 
        COUNT(*) as total_marked,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day
     FROM attendance 
     WHERE attendance_date = :today",
    ['today' => $today]
);

$totalEmployees = $this->db->fetch("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")['count'];

echo json_encode([
    'total_employees' => $totalEmployees,
    'attendance_stats' => $attendanceStats,
    'attendance_percentage' => $totalEmployees > 0 ? round(($attendanceStats['present'] / $totalEmployees) * 100, 1) : 0
]);
?>