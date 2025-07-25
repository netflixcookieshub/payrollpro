<?php 
$title = 'Custom Query Builder - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/formula-editor" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Custom Query Builder</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Create custom reports with advanced SQL queries
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Database Schema -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Tables -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Database Tables</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <?php foreach ($tables as $tableName => $tableInfo): ?>
                            <div class="table-item border rounded-lg">
                                <div class="p-2 bg-gray-50 cursor-pointer" onclick="toggleTable('<?php echo $tableName; ?>')">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900"><?php echo $tableInfo['name']; ?></span>
                                        <i class="fas fa-chevron-down text-gray-400" id="icon-<?php echo $tableName; ?>"></i>
                                    </div>
                                </div>
                                <div id="fields-<?php echo $tableName; ?>" class="hidden p-2 border-t">
                                    <?php foreach ($tableInfo['fields'] as $field): ?>
                                        <div class="field-item p-1 text-xs text-gray-600 hover:bg-gray-100 rounded cursor-pointer"
                                             onclick="insertField('<?php echo $tableName; ?>', '<?php echo $field; ?>')">
                                            <i class="fas fa-database mr-1"></i><?php echo $field; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Query Templates -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Query Templates</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-2">
                        <button onclick="loadTemplate('employee_list')" class="w-full text-left p-2 text-xs bg-blue-50 rounded hover:bg-blue-100">
                            Employee List
                        </button>
                        <button onclick="loadTemplate('salary_summary')" class="w-full text-left p-2 text-xs bg-blue-50 rounded hover:bg-blue-100">
                            Salary Summary
                        </button>
                        <button onclick="loadTemplate('attendance_report')" class="w-full text-left p-2 text-xs bg-blue-50 rounded hover:bg-blue-100">
                            Attendance Report
                        </button>
                        <button onclick="loadTemplate('department_wise')" class="w-full text-left p-2 text-xs bg-blue-50 rounded hover:bg-blue-100">
                            Department Wise
                        </button>
                    </div>
                </div>
            </div>

            <!-- Saved Queries -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Saved Queries</h3>
                </div>
                <div class="p-4">
                    <div id="saved-queries" class="space-y-2 max-h-48 overflow-y-auto">
                        <?php if (!empty($saved_queries)): ?>
                            <?php foreach ($saved_queries as $query): ?>
                                <div class="p-2 bg-gray-50 rounded cursor-pointer hover:bg-gray-100"
                                     onclick="loadSavedQuery(<?php echo $query['id']; ?>)">
                                    <div class="text-xs font-medium text-gray-900"><?php echo htmlspecialchars($query['name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($query['created_at'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-xs text-gray-500 text-center py-4">No saved queries</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Query Editor -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Editor Panel -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">SQL Query Editor</h3>
                        <div class="flex items-center space-x-2">
                            <button onclick="formatQuery()" class="btn btn-outline btn-sm">
                                <i class="fas fa-code mr-2"></i>Format
                            </button>
                            <button onclick="validateQuery()" class="btn btn-outline btn-sm">
                                <i class="fas fa-check mr-2"></i>Validate
                            </button>
                            <button onclick="executeQuery()" class="btn btn-primary btn-sm">
                                <i class="fas fa-play mr-2"></i>Execute
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label for="query-name" class="block text-sm font-medium text-gray-700 mb-2">Query Name</label>
                        <input type="text" id="query-name" class="form-input" placeholder="Enter query name">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">SQL Query</label>
                        <div class="border rounded-lg">
                            <textarea id="query-editor" class="w-full h-48 p-3 font-mono text-sm border-0 rounded-lg resize-none" 
                                      placeholder="SELECT * FROM employees WHERE status = 'active'"></textarea>
                        </div>
                    </div>
                    
                    <!-- Query Help -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-semibold text-yellow-900 mb-2">Security Notice:</h4>
                        <div class="text-xs text-yellow-800 space-y-1">
                            <div>• Only SELECT queries are allowed</div>
                            <div>• No data modification operations (INSERT, UPDATE, DELETE)</div>
                            <div>• Query execution is limited to read-only operations</div>
                        </div>
                    </div>
                    
                    <!-- Parameters -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Query Parameters</label>
                        <div id="query-parameters" class="space-y-2">
                            <div class="text-xs text-gray-500">Parameters will appear here when you use :parameter_name in your query</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Panel -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Query Results</h3>
                        <div class="flex items-center space-x-2">
                            <span id="result-count" class="text-sm text-gray-500"></span>
                            <button onclick="exportResults()" class="btn btn-outline btn-sm" id="export-btn" style="display: none;">
                                <i class="fas fa-download mr-2"></i>Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div id="query-results" class="overflow-x-auto">
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-database text-4xl mb-4"></i>
                            <p class="text-lg font-medium">No results</p>
                            <p class="text-sm">Execute a query to see results here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Query Panel -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Save Query</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="query-category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="query-category" class="form-select">
                                <option value="report">Report</option>
                                <option value="analysis">Analysis</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button onclick="saveQuery()" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Query
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let queryEditor;
let currentResults = [];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize CodeMirror for SQL
    queryEditor = CodeMirror.fromTextArea(document.getElementById('query-editor'), {
        mode: 'text/x-sql',
        theme: 'material',
        lineNumbers: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        indentUnit: 2,
        tabSize: 2,
        lineWrapping: true
    });
    
    // Watch for parameter changes
    queryEditor.on('change', function() {
        updateParameters();
    });
});

function toggleTable(tableName) {
    const fieldsDiv = document.getElementById(`fields-${tableName}`);
    const icon = document.getElementById(`icon-${tableName}`);
    
    fieldsDiv.classList.toggle('hidden');
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
}

function insertField(tableName, fieldName) {
    const cursor = queryEditor.getCursor();
    queryEditor.replaceRange(`${tableName}.${fieldName}`, cursor);
    queryEditor.focus();
}

function loadTemplate(templateName) {
    const templates = {
        employee_list: `SELECT 
    e.emp_code,
    CONCAT(e.first_name, ' ', e.last_name) as full_name,
    d.name as department,
    des.name as designation,
    e.join_date,
    e.status
FROM employees e
JOIN departments d ON e.department_id = d.id
JOIN designations des ON e.designation_id = des.id
WHERE e.status = 'active'
ORDER BY e.emp_code`,

        salary_summary: `SELECT 
    e.emp_code,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    pp.period_name,
    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE 0 END) as total_earnings,
    SUM(CASE WHEN sc.type = 'deduction' THEN ABS(pt.amount) ELSE 0 END) as total_deductions,
    SUM(CASE WHEN sc.type = 'earning' THEN pt.amount ELSE pt.amount END) as net_salary
FROM payroll_transactions pt
JOIN employees e ON pt.employee_id = e.id
JOIN salary_components sc ON pt.component_id = sc.id
JOIN payroll_periods pp ON pt.period_id = pp.id
WHERE pp.period_name = :period_name
GROUP BY pt.employee_id, pt.period_id
ORDER BY e.emp_code`,

        attendance_report: `SELECT 
    e.emp_code,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    COUNT(a.id) as total_days,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
    ROUND(AVG(a.total_hours), 2) as avg_hours
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id 
    AND a.attendance_date BETWEEN :start_date AND :end_date
WHERE e.status = 'active'
GROUP BY e.id
ORDER BY e.emp_code`,

        department_wise: `SELECT 
    d.name as department,
    COUNT(e.id) as employee_count,
    AVG(ss.amount) as avg_basic_salary,
    SUM(CASE WHEN e.status = 'active' THEN 1 ELSE 0 END) as active_employees
FROM departments d
LEFT JOIN employees e ON d.id = e.department_id
LEFT JOIN salary_structures ss ON e.id = ss.employee_id 
    AND ss.component_id = 1 AND ss.end_date IS NULL
GROUP BY d.id, d.name
ORDER BY employee_count DESC`
    };
    
    if (templates[templateName]) {
        queryEditor.setValue(templates[templateName]);
        updateParameters();
    }
}

function updateParameters() {
    const query = queryEditor.getValue();
    const paramMatches = query.match(/:(\w+)/g);
    const container = document.getElementById('query-parameters');
    
    if (paramMatches) {
        const uniqueParams = [...new Set(paramMatches)];
        container.innerHTML = uniqueParams.map(param => {
            const paramName = param.substring(1);
            return `
                <div class="flex items-center space-x-2">
                    <label class="w-32 text-sm text-gray-600">${paramName}:</label>
                    <input type="text" id="param-${paramName}" class="form-input flex-1" placeholder="Enter value">
                </div>
            `;
        }).join('');
    } else {
        container.innerHTML = '<div class="text-xs text-gray-500">No parameters found</div>';
    }
}

function validateQuery() {
    const query = queryEditor.getValue();
    
    if (!query.trim()) {
        showMessage('Please enter a query', 'warning');
        return;
    }
    
    // Basic validation
    if (!query.trim().toUpperCase().startsWith('SELECT')) {
        showMessage('Only SELECT queries are allowed', 'error');
        return;
    }
    
    const dangerousKeywords = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE'];
    const upperQuery = query.toUpperCase();
    
    for (const keyword of dangerousKeywords) {
        if (upperQuery.includes(keyword)) {
            showMessage(`Dangerous keyword "${keyword}" is not allowed`, 'error');
            return;
        }
    }
    
    showMessage('Query validation passed', 'success');
}

function executeQuery() {
    const query = queryEditor.getValue();
    
    if (!query.trim()) {
        showMessage('Please enter a query', 'warning');
        return;
    }
    
    // Get parameters
    const parameters = {};
    document.querySelectorAll('[id^="param-"]').forEach(input => {
        const paramName = input.id.replace('param-', '');
        parameters[paramName] = input.value;
    });
    
    showLoading();
    
    fetch('/formula-editor/custom-query', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            query: query,
            parameters: parameters,
            csrf_token: '<?php echo $csrf_token; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            displayResults(data.data);
            document.getElementById('result-count').textContent = `${data.count} rows`;
            document.getElementById('export-btn').style.display = 'inline-flex';
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Query execution failed', 'error');
    });
}

function displayResults(data) {
    const container = document.getElementById('query-results');
    currentResults = data;
    
    if (data.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-search text-4xl mb-4"></i>
                <p class="text-lg font-medium">No results found</p>
                <p class="text-sm">Your query returned no data</p>
            </div>
        `;
        return;
    }
    
    const headers = Object.keys(data[0]);
    
    container.innerHTML = `
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    ${headers.map(header => `
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ${header.replace(/_/g, ' ')}
                        </th>
                    `).join('')}
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                ${data.map(row => `
                    <tr class="hover:bg-gray-50">
                        ${headers.map(header => `
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                ${row[header] || '-'}
                            </td>
                        `).join('')}
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function formatQuery() {
    const query = queryEditor.getValue();
    
    // Basic SQL formatting
    const formatted = query
        .replace(/\bSELECT\b/gi, 'SELECT')
        .replace(/\bFROM\b/gi, '\nFROM')
        .replace(/\bJOIN\b/gi, '\nJOIN')
        .replace(/\bLEFT JOIN\b/gi, '\nLEFT JOIN')
        .replace(/\bRIGHT JOIN\b/gi, '\nRIGHT JOIN')
        .replace(/\bINNER JOIN\b/gi, '\nINNER JOIN')
        .replace(/\bWHERE\b/gi, '\nWHERE')
        .replace(/\bGROUP BY\b/gi, '\nGROUP BY')
        .replace(/\bHAVING\b/gi, '\nHAVING')
        .replace(/\bORDER BY\b/gi, '\nORDER BY')
        .replace(/\bLIMIT\b/gi, '\nLIMIT');
    
    queryEditor.setValue(formatted);
}

function saveQuery() {
    const query = queryEditor.getValue();
    const name = document.getElementById('query-name').value;
    const category = document.getElementById('query-category').value;
    
    if (!query.trim() || !name.trim()) {
        showMessage('Please enter query name and SQL', 'warning');
        return;
    }
    
    showLoading();
    
    fetch('/formula-editor/save-query', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: name,
            query: query,
            category: category,
            csrf_token: '<?php echo $csrf_token; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage('Query saved successfully', 'success');
            document.getElementById('query-name').value = '';
        } else {
            showMessage(data.message || 'Failed to save query', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Save failed', 'error');
    });
}

function exportResults() {
    if (currentResults.length === 0) {
        showMessage('No results to export', 'warning');
        return;
    }
    
    // Convert to CSV
    const headers = Object.keys(currentResults[0]);
    const csvContent = [
        headers.join(','),
        ...currentResults.map(row => headers.map(header => `"${row[header] || ''}"`).join(','))
    ].join('\n');
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `query_results_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showMessage('Results exported successfully', 'success');
}

function loadSavedQuery(queryId) {
    // Implementation for loading saved queries
    showMessage('Loading saved query...', 'info');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>