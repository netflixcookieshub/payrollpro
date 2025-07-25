<?php
/**
 * Data Export Model
 * Handles bulk data export operations
 */

require_once __DIR__ . '/../core/Model.php';

class DataExport extends Model {
    
    public function exportData($tables, $format = 'json') {
        try {
            $exportData = [];
            
            foreach ($tables as $table) {
                if ($this->isTableAllowed($table)) {
                    $exportData[$table] = $this->exportTable($table);
                }
            }
            
            switch ($format) {
                case 'json':
                    return ['success' => true, 'data' => json_encode($exportData, JSON_PRETTY_PRINT)];
                case 'csv':
                    return ['success' => true, 'data' => $this->convertToCSV($exportData)];
                case 'xml':
                    return ['success' => true, 'data' => $this->convertToXML($exportData)];
                default:
                    return ['success' => false, 'message' => 'Unsupported format'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function exportEmployees($filters = []) {
        $conditions = [];
        $params = [];
        
        if (!empty($filters['department_id'])) {
            $conditions[] = 'e.department_id = :dept_id';
            $params['dept_id'] = $filters['department_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = 'e.status = :status';
            $params['status'] = $filters['status'];
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT e.*, d.name as department_name, des.name as designation_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                {$whereClause}
                ORDER BY e.emp_code";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function exportPayrollData($periodId, $format = 'excel') {
        $sql = "SELECT 
                    e.emp_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    d.name as department,
                    des.name as designation,
                    sc.name as component_name,
                    sc.type as component_type,
                    pt.amount,
                    pp.period_name
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                JOIN departments d ON e.department_id = d.id
                JOIN designations des ON e.designation_id = des.id
                JOIN payroll_periods pp ON pt.period_id = pp.id
                WHERE pt.period_id = :period_id
                ORDER BY e.emp_code, sc.display_order";
        
        $data = $this->db->fetchAll($sql, ['period_id' => $periodId]);
        
        if ($format === 'excel') {
            return $this->generateExcel($data, 'payroll_data');
        } else {
            return $this->generateCSV($data, 'payroll_data');
        }
    }
    
    public function exportAttendanceData($startDate, $endDate, $departmentId = null) {
        $conditions = "a.attendance_date BETWEEN :start_date AND :end_date";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if ($departmentId) {
            $conditions .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }
        
        $sql = "SELECT 
                    e.emp_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    d.name as department,
                    a.attendance_date,
                    a.check_in,
                    a.check_out,
                    a.total_hours,
                    a.overtime_hours,
                    a.status
                FROM attendance a
                JOIN employees e ON a.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                WHERE {$conditions}
                ORDER BY e.emp_code, a.attendance_date";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function exportSalaryStructures($employeeId = null) {
        $conditions = "ss.end_date IS NULL";
        $params = [];
        
        if ($employeeId) {
            $conditions .= " AND ss.employee_id = :emp_id";
            $params['emp_id'] = $employeeId;
        }
        
        $sql = "SELECT 
                    e.emp_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    sc.name as component_name,
                    sc.code as component_code,
                    sc.type as component_type,
                    ss.amount,
                    ss.effective_date
                FROM salary_structures ss
                JOIN employees e ON ss.employee_id = e.id
                JOIN salary_components sc ON ss.component_id = sc.id
                WHERE {$conditions}
                ORDER BY e.emp_code, sc.display_order";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function exportBankTransferFile($periodId, $bankFormat = 'generic') {
        $sql = "SELECT 
                    e.emp_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    e.bank_account_number,
                    e.bank_name,
                    e.bank_ifsc,
                    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE pt.amount END) as net_amount
                FROM payroll_transactions pt
                JOIN employees e ON pt.employee_id = e.id
                JOIN salary_components sc ON pt.component_id = sc.id
                WHERE pt.period_id = :period_id
                GROUP BY pt.employee_id
                HAVING net_amount > 0
                ORDER BY e.emp_code";
        
        $data = $this->db->fetchAll($sql, ['period_id' => $periodId]);
        
        switch ($bankFormat) {
            case 'sbi':
                return $this->generateSBIFormat($data);
            case 'hdfc':
                return $this->generateHDFCFormat($data);
            case 'icici':
                return $this->generateICICIFormat($data);
            default:
                return $this->generateGenericBankFormat($data);
        }
    }
    
    private function exportTable($table) {
        $allowedTables = [
            'employees', 'departments', 'designations', 'salary_components',
            'payroll_periods', 'loan_types', 'leave_types', 'holidays'
        ];
        
        if (!in_array($table, $allowedTables)) {
            return [];
        }
        
        return $this->db->fetchAll("SELECT * FROM {$table} ORDER BY id");
    }
    
    private function isTableAllowed($table) {
        $allowedTables = [
            'employees', 'departments', 'designations', 'salary_components',
            'payroll_periods', 'loan_types', 'leave_types', 'holidays'
        ];
        
        return in_array($table, $allowedTables);
    }
    
    private function convertToCSV($data) {
        $csv = '';
        
        foreach ($data as $table => $records) {
            $csv .= "Table: {$table}\n";
            
            if (!empty($records)) {
                // Headers
                $csv .= implode(',', array_keys($records[0])) . "\n";
                
                // Data
                foreach ($records as $record) {
                    $csv .= implode(',', array_map(function($value) {
                        return '"' . str_replace('"', '""', $value) . '"';
                    }, $record)) . "\n";
                }
            }
            
            $csv .= "\n";
        }
        
        return $csv;
    }
    
    private function convertToXML($data) {
        $xml = new SimpleXMLElement('<payroll_export/>');
        
        foreach ($data as $table => $records) {
            $tableNode = $xml->addChild($table);
            
            foreach ($records as $record) {
                $recordNode = $tableNode->addChild('record');
                
                foreach ($record as $field => $value) {
                    $recordNode->addChild($field, htmlspecialchars($value));
                }
            }
        }
        
        return $xml->asXML();
    }
    
    private function generateExcel($data, $filename) {
        // For simplicity, generate CSV with Excel headers
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        
        return $this->generateCSV($data, $filename);
    }
    
    private function generateCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
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
    
    private function generateSBIFormat($data) {
        // SBI specific bank transfer format
        $content = '';
        
        foreach ($data as $record) {
            $content .= sprintf(
                "%-20s%-50s%-20s%-11s%15.2f\n",
                $record['emp_code'],
                $record['employee_name'],
                $record['bank_account_number'],
                $record['bank_ifsc'],
                $record['net_amount']
            );
        }
        
        return $content;
    }
    
    private function generateHDFCFormat($data) {
        // HDFC specific format
        $csv = "Employee Code,Employee Name,Account Number,IFSC Code,Amount\n";
        
        foreach ($data as $record) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%.2f\n",
                $record['emp_code'],
                $record['employee_name'],
                $record['bank_account_number'],
                $record['bank_ifsc'],
                $record['net_amount']
            );
        }
        
        return $csv;
    }
    
    private function generateICICIFormat($data) {
        // ICICI specific format
        return $this->generateHDFCFormat($data); // Similar to HDFC
    }
    
    private function generateGenericBankFormat($data) {
        // Generic bank transfer format
        return $this->generateHDFCFormat($data);
    }
}