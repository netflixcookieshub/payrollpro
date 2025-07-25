<?php 
$title = 'Formula Builder - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/formula-editor" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Advanced Formula Builder</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Create complex salary calculation formulas with visual tools
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Component Library -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Salary Components -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Salary Components</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <?php foreach ($components as $component): ?>
                            <div class="component-item p-2 bg-gray-50 rounded cursor-pointer hover:bg-gray-100 transition-colors"
                                 draggable="true"
                                 data-type="component"
                                 data-code="<?php echo $component['code']; ?>"
                                 data-name="<?php echo htmlspecialchars($component['name']); ?>">
                                <div class="text-xs font-medium text-gray-900"><?php echo htmlspecialchars($component['name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo $component['code']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Functions -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Functions</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <?php foreach ($functions as $func => $details): ?>
                            <div class="function-item p-2 bg-blue-50 rounded cursor-pointer hover:bg-blue-100 transition-colors"
                                 draggable="true"
                                 data-type="function"
                                 data-syntax="<?php echo $details['syntax']; ?>"
                                 data-description="<?php echo htmlspecialchars($details['description']); ?>">
                                <div class="text-xs font-medium text-blue-900"><?php echo $func; ?></div>
                                <div class="text-xs text-blue-600"><?php echo $details['description']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- System Variables -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">System Variables</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <?php foreach ($variables as $var => $description): ?>
                            <div class="variable-item p-2 bg-green-50 rounded cursor-pointer hover:bg-green-100 transition-colors"
                                 draggable="true"
                                 data-type="variable"
                                 data-code="<?php echo $var; ?>"
                                 data-description="<?php echo htmlspecialchars($description); ?>">
                                <div class="text-xs font-medium text-green-900"><?php echo $var; ?></div>
                                <div class="text-xs text-green-600"><?php echo $description; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Operators -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Operators</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-4 gap-2">
                        <button class="operator-btn p-2 bg-gray-100 rounded text-center hover:bg-gray-200" data-op="+">+</button>
                        <button class="operator-btn p-2 bg-gray-100 rounded text-center hover:bg-gray-200" data-op="-">-</button>
                        <button class="operator-btn p-2 bg-gray-100 rounded text-center hover:bg-gray-200" data-op="*">×</button>
                        <button class="operator-btn p-2 bg-gray-100 rounded text-center hover:bg-gray-200" data-op="/">÷</button>
                        <button class="operator-btn p-2 bg-gray-100 rounded text-center hover:bg-gray-200" data-op="(">(</button>
                        <button class="operator-btn p-2 bg-gray-100 rounded text-center hover:bg-gray-200" data-op=")">)</button>
                        <button class="operator-btn p-2 bg-gray-100 rounded text-center hover:bg-gray-200" data-op="%">%</button>
                        <button class="operator-btn p-2 bg-gray-100 rounded text-center hover:bg-gray-200" data-op="^">^</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formula Editor -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Editor Panel -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Formula Editor</h3>
                        <div class="flex items-center space-x-2">
                            <button onclick="clearEditor()" class="btn btn-outline btn-sm">
                                <i class="fas fa-trash mr-2"></i>Clear
                            </button>
                            <button onclick="validateFormula()" class="btn btn-outline btn-sm">
                                <i class="fas fa-check mr-2"></i>Validate
                            </button>
                            <button onclick="testFormula()" class="btn btn-primary btn-sm">
                                <i class="fas fa-play mr-2"></i>Test
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label for="formula-name" class="block text-sm font-medium text-gray-700 mb-2">Formula Name</label>
                        <input type="text" id="formula-name" class="form-input" placeholder="Enter formula name">
                    </div>
                    
                    <div class="mb-4">
                        <label for="formula-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="formula-description" rows="2" class="form-textarea" placeholder="Describe what this formula calculates"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Formula Expression</label>
                        <div class="border rounded-lg">
                            <textarea id="formula-editor" class="w-full h-32 p-3 font-mono text-sm border-0 rounded-lg resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                      placeholder="Enter your formula here or drag components from the left panel..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Syntax Help -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">Syntax Examples:</h4>
                        <div class="text-xs text-blue-800 space-y-1">
                            <div><code>BASIC * 0.4</code> - 40% of basic salary</div>
                            <div><code>IF(BASIC > 50000, BASIC * 0.1, 0)</code> - Conditional calculation</div>
                            <div><code>ROUND((BASIC + HRA) * 0.12, 2)</code> - PF calculation with rounding</div>
                            <div><code>MIN(BASIC * 0.12, 1800)</code> - PF with ceiling</div>
                        </div>
                    </div>
                    
                    <!-- Validation Results -->
                    <div id="validation-results" class="hidden mb-4"></div>
                </div>
            </div>

            <!-- Test Panel -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Test Formula</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Test Data Input -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Test Data</h4>
                            <div id="test-data-inputs" class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <label class="w-20 text-sm text-gray-600">BASIC:</label>
                                    <input type="number" id="test-basic" class="form-input flex-1" value="30000" step="0.01">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <label class="w-20 text-sm text-gray-600">HRA:</label>
                                    <input type="number" id="test-hra" class="form-input flex-1" value="12000" step="0.01">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <label class="w-20 text-sm text-gray-600">TA:</label>
                                    <input type="number" id="test-ta" class="form-input flex-1" value="1600" step="0.01">
                                </div>
                            </div>
                            <button onclick="addTestVariable()" class="mt-3 text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-plus mr-1"></i>Add Variable
                            </button>
                        </div>
                        
                        <!-- Test Results -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Test Results</h4>
                            <div id="test-results" class="bg-gray-50 rounded-lg p-4 min-h-32">
                                <div class="text-center text-gray-500">
                                    <i class="fas fa-calculator text-2xl mb-2"></i>
                                    <p class="text-sm">Click "Test" to see results</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Panel -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Save Formula</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="formula-category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="formula-category" class="form-select">
                                <option value="earning">Earning</option>
                                <option value="deduction">Deduction</option>
                                <option value="custom">Custom</option>
                                <option value="conditional">Conditional</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button onclick="saveFormula()" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Formula Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let formulaEditor;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize CodeMirror
    formulaEditor = CodeMirror.fromTextArea(document.getElementById('formula-editor'), {
        mode: 'javascript',
        theme: 'material',
        lineNumbers: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        indentUnit: 2,
        tabSize: 2,
        lineWrapping: true
    });
    
    // Setup drag and drop
    setupDragAndDrop();
    
    // Setup operator buttons
    setupOperatorButtons();
});

function setupDragAndDrop() {
    // Make items draggable
    document.querySelectorAll('[draggable="true"]').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', JSON.stringify({
                type: this.dataset.type,
                code: this.dataset.code,
                syntax: this.dataset.syntax,
                name: this.dataset.name
            }));
        });
    });
    
    // Make editor droppable
    const editorElement = formulaEditor.getWrapperElement();
    editorElement.addEventListener('dragover', function(e) {
        e.preventDefault();
    });
    
    editorElement.addEventListener('drop', function(e) {
        e.preventDefault();
        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
        
        let insertText = '';
        switch (data.type) {
            case 'component':
            case 'variable':
                insertText = data.code;
                break;
            case 'function':
                insertText = data.syntax;
                break;
        }
        
        const cursor = formulaEditor.getCursor();
        formulaEditor.replaceRange(insertText, cursor);
        formulaEditor.focus();
    });
}

function setupOperatorButtons() {
    document.querySelectorAll('.operator-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const operator = this.dataset.op;
            const cursor = formulaEditor.getCursor();
            formulaEditor.replaceRange(operator, cursor);
            formulaEditor.focus();
        });
    });
}

function clearEditor() {
    formulaEditor.setValue('');
    document.getElementById('formula-name').value = '';
    document.getElementById('formula-description').value = '';
    document.getElementById('validation-results').classList.add('hidden');
    document.getElementById('test-results').innerHTML = `
        <div class="text-center text-gray-500">
            <i class="fas fa-calculator text-2xl mb-2"></i>
            <p class="text-sm">Click "Test" to see results</p>
        </div>
    `;
}

function validateFormula() {
    const formula = formulaEditor.getValue();
    
    if (!formula.trim()) {
        showMessage('Please enter a formula', 'warning');
        return;
    }
    
    showLoading();
    
    fetch('/formula-editor/validate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            formula: formula,
            context: getTestData()
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        displayValidationResults(data);
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Validation failed', 'error');
    });
}

function testFormula() {
    const formula = formulaEditor.getValue();
    
    if (!formula.trim()) {
        showMessage('Please enter a formula', 'warning');
        return;
    }
    
    showLoading();
    
    fetch('/formula-editor/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            formula: formula,
            test_data: getTestData()
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        displayTestResults(data);
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Test failed', 'error');
    });
}

function saveFormula() {
    const formula = formulaEditor.getValue();
    const name = document.getElementById('formula-name').value;
    const description = document.getElementById('formula-description').value;
    const category = document.getElementById('formula-category').value;
    
    if (!formula.trim() || !name.trim()) {
        showMessage('Please enter formula name and expression', 'warning');
        return;
    }
    
    showLoading();
    
    fetch('/formula-editor/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: name,
            formula: formula,
            description: description,
            category: category,
            variables: extractVariables(formula),
            csrf_token: '<?php echo $csrf_token; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage('Formula saved successfully', 'success');
            clearEditor();
        } else {
            showMessage(data.message || 'Failed to save formula', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Save failed', 'error');
    });
}

function getTestData() {
    const testData = {};
    
    document.querySelectorAll('#test-data-inputs input').forEach(input => {
        const label = input.previousElementSibling.textContent.replace(':', '');
        testData[label] = parseFloat(input.value) || 0;
    });
    
    return testData;
}

function displayValidationResults(data) {
    const container = document.getElementById('validation-results');
    container.classList.remove('hidden');
    
    if (data.valid) {
        container.innerHTML = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <span class="text-green-800 font-medium">Formula is valid</span>
                </div>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                    <span class="text-red-800 font-medium">Formula validation failed</span>
                </div>
                <p class="text-red-700 text-sm mt-2">${data.message}</p>
            </div>
        `;
    }
}

function displayTestResults(data) {
    const container = document.getElementById('test-results');
    
    if (data.success) {
        container.innerHTML = `
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600 mb-2">₹${data.formatted_result}</div>
                <div class="text-sm text-gray-500">Result: ${data.result}</div>
                <div class="mt-4 text-xs text-gray-400">
                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                    Formula executed successfully
                </div>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="text-center">
                <div class="text-red-600 mb-2">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div class="text-sm text-red-600">Error: ${data.message}</div>
            </div>
        `;
    }
}

function addTestVariable() {
    const container = document.getElementById('test-data-inputs');
    const varName = prompt('Enter variable name:');
    
    if (varName) {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2';
        div.innerHTML = `
            <label class="w-20 text-sm text-gray-600">${varName.toUpperCase()}:</label>
            <input type="number" class="form-input flex-1" value="0" step="0.01">
            <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
    }
}

function extractVariables(formula) {
    const variables = [];
    const matches = formula.match(/[A-Z_]+/g);
    
    if (matches) {
        matches.forEach(match => {
            if (!variables.includes(match)) {
                variables.push(match);
            }
        });
    }
    
    return variables;
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>