<?php
/**
 * Data Import Model
 * Handles bulk data import operations
 */

require_once __DIR__ . '/../core/Model.php';

class DataImport extends Model {
    
    public function processImport($type, $filePath) {
        try {
            $this->beginTransaction();
            
            switch ($type) {
                case 'employees':
                    $result = $this->importEmployees($filePath);
                    break;
                case 'attendance':
                    $result = $this->importAttendance($filePath);
                    break;
                case 'salary_structures':
                    $result = $this->importSalaryStructures($filePath);
                    break;
                case 'loans':
                    $result = $this->importLoans($filePath);
                    break;
                default:
                    throw new Exception('Unsupported import type');
            }
            
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function importEmployees($filePath) {
        $data = $this->parseCSV($filePath);
        $imported = 0;
        $errors = [];
        
        foreach ($data as $row => $employee) {
            try {
                // Validate required fields
                if (empty($employee['first_name']) || empty($employee['last_name'])) {
                    $errors[] = "Row {$row}: First name and last name are required";
                    continue;
                }
                
                // Generate employee code if not provided
                if (empty($employee['emp_code'])) {
                    $employee['emp_code'] = $this->generateEmployeeCode();
                }
                
                // Map department and designation
                $employee['department_id'] = $this->getDepartmentId($employee['department'] ?? '');
                $employee['designation_id'] = $this->getDesignationId($employee['designation'] ?? '');
                
                if (!$employee['department_id'] || !$employee['designation_id']) {
                    $errors[] = "Row {$row}: Invalid department or designation";
                    continue;
                }
                
                // Set default values
                $employee['join_date'] = $employee['join_date'] ?? date('Y-m-d');
                $employee['status'] = 'active';
                $employee['created_at'] = date('Y-m-d H:i:s');
                
                // Remove non-database fields
                unset($employee['department'], $employee['designation']);
                
                $this->db->insert('employees', $employee);
                $imported++;
            } catch (Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'total_rows' => count($data)
        ];
    }
    
    private function importAttendance($filePath) {
        $data = $this->parseCSV($filePath);
        $imported = 0;
        $errors = [];
        
        foreach ($data as $row => $attendance) {
            try {
                // Get employee ID by code
                $employeeId = $this->getEmployeeIdByCode($attendance['emp_code'] ?? '');
                
                if (!$employeeId) {
                    $errors[] = "Row {$row}: Employee not found";
                    continue;
                }
                
                $attendanceData = [
                    'employee_id' => $employeeId,
                    'attendance_date' => $attendance['date'],
                    'check_in' => $attendance['check_in'] ?? null,
                    'check_out' => $attendance['check_out'] ?? null,
                    'status' => $attendance['status'] ?? 'present',
                    'total_hours' => $attendance['total_hours'] ?? null,
                    'overtime_hours' => $attendance['overtime_hours'] ?? 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Check if record already exists
                $existing = $this->db->fetch(
                    "SELECT id FROM attendance WHERE employee_id = :emp_id AND attendance_date = :date",
                    ['emp_id' => $employeeId, 'date' => $attendanceData['attendance_date']]
                );
                
                if ($existing) {
                    $this->db->update('attendance', $attendanceData, 'id = :id', ['id' => $existing['id']]);
                } else {
                    $this->db->insert('attendance', $attendanceData);
                }
                
                $imported++;
            } catch (Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'total_rows' => count($data)
        ];
    }
    
    private function importSalaryStructures($filePath) {
        $data = $this->parseCSV($filePath);
        $imported = 0;
        $errors = [];
        
        foreach ($data as $row => $structure) {
            try {
                $employeeId = $this->getEmployeeIdByCode($structure['emp_code'] ?? '');
                $componentId = $this->getComponentIdByCode($structure['component_code'] ?? '');
                
                if (!$employeeId || !$componentId) {
                    $errors[] = "Row {$row}: Employee or component not found";
                    continue;
                }
                
                $structureData = [
                    'employee_id' => $employeeId,
                    'component_id' => $componentId,
                    'amount' => floatval($structure['amount'] ?? 0),
                    'effective_date' => $structure['effective_date'] ?? date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // End previous structure for this component
                $this->db->query(
                    "UPDATE salary_structures SET end_date = DATE_SUB(:effective_date, INTERVAL 1 DAY) 
                     WHERE employee_id = :emp_id AND component_id = :comp_id AND end_date IS NULL",
                    [
                        'emp_id' => $employeeId,
                        'comp_id' => $componentId,
                        'effective_date' => $structureData['effective_date']
                    ]
                );
                
                $this->db->insert('salary_structures', $structureData);
                $imported++;
            } catch (Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'total_rows' => count($data)
        ];
    }
    
    private function importLoans($filePath) {
        $data = $this->parseCSV($filePath);
        $imported = 0;
        $errors = [];
        
        foreach ($data as $row => $loan) {
            try {
                $employeeId = $this->getEmployeeIdByCode($loan['emp_code'] ?? '');
                $loanTypeId = $this->getLoanTypeIdByCode($loan['loan_type_code'] ?? '');
                
                if (!$employeeId || !$loanTypeId) {
                    $errors[] = "Row {$row}: Employee or loan type not found";
                    continue;
                }
                
                $loanAmount = floatval($loan['loan_amount'] ?? 0);
                $interestRate = floatval($loan['interest_rate'] ?? 0);
                $tenureMonths = intval($loan['tenure_months'] ?? 12);
                
                // Calculate EMI
                $emiAmount = $this->calculateEMI($loanAmount, $interestRate, $tenureMonths);
                
                $loanData = [
                    'employee_id' => $employeeId,
                    'loan_type_id' => $loanTypeId,
                    'loan_amount' => $loanAmount,
                    'interest_rate' => $interestRate,
                    'tenure_months' => $tenureMonths,
                    'emi_amount' => $emiAmount,
                    'disbursed_date' => $loan['disbursed_date'] ?? date('Y-m-d'),
                    'first_emi_date' => $loan['first_emi_date'] ?? date('Y-m-d', strtotime('+1 month')),
                    'outstanding_amount' => $loanAmount,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('employee_loans', $loanData);
                $imported++;
            } catch (Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'total_rows' => count($data)
        ];
    }
    
    private function parseCSV($filePath) {
        $data = [];
        $headers = [];
        
        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            $rowIndex = 0;
            
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if ($rowIndex === 0) {
                    $headers = array_map('trim', $row);
                } else {
                    $rowData = [];
                    foreach ($row as $index => $value) {
                        $header = $headers[$index] ?? "column_{$index}";
                        $rowData[$header] = trim($value);
                    }
                    $data[$rowIndex] = $rowData;
                }
                $rowIndex++;
            }
            
            fclose($handle);
        }
        
        return $data;
    }
    
    private function generateEmployeeCode() {
        $lastEmployee = $this->db->fetch(
            "SELECT emp_code FROM employees WHERE emp_code LIKE 'EMP%' ORDER BY emp_code DESC LIMIT 1"
        );
        
        if ($lastEmployee) {
            $number = intval(substr($lastEmployee['emp_code'], 3)) + 1;
            return 'EMP' . str_pad($number, 3, '0', STR_PAD_LEFT);
        } else {
            return 'EMP001';
        }
    }
    
    private function getDepartmentId($departmentName) {
        if (empty($departmentName)) return null;
        
        $dept = $this->db->fetch(
            "SELECT id FROM departments WHERE name = :name OR code = :code",
            ['name' => $departmentName, 'code' => $departmentName]
        );
        
        return $dept ? $dept['id'] : null;
    }
    
    private function getDesignationId($designationName) {
        if (empty($designationName)) return null;
        
        $designation = $this->db->fetch(
            "SELECT id FROM designations WHERE name = :name OR code = :code",
            ['name' => $designationName, 'code' => $designationName]
        );
        
        return $designation ? $designation['id'] : null;
    }
    
    private function getEmployeeIdByCode($empCode) {
        if (empty($empCode)) return null;
        
        $employee = $this->db->fetch(
            "SELECT id FROM employees WHERE emp_code = :code",
            ['code' => $empCode]
        );
        
        return $employee ? $employee['id'] : null;
    }
    
    private function getComponentIdByCode($componentCode) {
        if (empty($componentCode)) return null;
        
        $component = $this->db->fetch(
            "SELECT id FROM salary_components WHERE code = :code",
            ['code' => $componentCode]
        );
        
        return $component ? $component['id'] : null;
    }
    
    private function getLoanTypeIdByCode($loanTypeCode) {
        if (empty($loanTypeCode)) return null;
        
        $loanType = $this->db->fetch(
            "SELECT id FROM loan_types WHERE code = :code",
            ['code' => $loanTypeCode]
        );
        
        return $loanType ? $loanType['id'] : null;
    }
    
    private function calculateEMI($principal, $rate, $tenure) {
        if ($rate == 0) {
            return $principal / $tenure;
        }
        
        $monthlyRate = $rate / (12 * 100);
        $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $tenure)) / 
               (pow(1 + $monthlyRate, $tenure) - 1);
        
        return round($emi, 2);
    }
    
    public function generateImportTemplate($type) {
        $templates = [
            'employees' => [
                'emp_code', 'first_name', 'last_name', 'email', 'phone',
                'date_of_birth', 'gender', 'join_date', 'department', 'designation',
                'pan_number', 'aadhaar_number', 'bank_account_number', 'bank_name', 'bank_ifsc'
            ],
            'attendance' => [
                'emp_code', 'date', 'check_in', 'check_out', 'status', 'total_hours', 'overtime_hours'
            ],
            'salary_structures' => [
                'emp_code', 'component_code', 'amount', 'effective_date'
            ],
            'loans' => [
                'emp_code', 'loan_type_code', 'loan_amount', 'interest_rate', 
                'tenure_months', 'disbursed_date', 'first_emi_date'
            ]
        ];
        
        $headers = $templates[$type] ?? [];
        
        if (empty($headers)) {
            return ['success' => false, 'message' => 'Invalid template type'];
        }
        
        // Generate CSV template
        $filename = "import_template_{$type}.csv";
        $filepath = UPLOAD_PATH . 'templates/' . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $file = fopen($filepath, 'w');
        fputcsv($file, $headers);
        
        // Add sample data row
        $sampleData = array_fill(0, count($headers), 'sample_data');
        fputcsv($file, $sampleData);
        
        fclose($file);
        
        return ['success' => true, 'filepath' => $filepath, 'filename' => $filename];
    }
}