<?php 
$title = 'Data Import - System Integrations';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/integrations" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Bulk Data Import</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Import employee data, attendance records, and other information from CSV files
                </p>
            </div>
        </div>
    </div>

    <!-- Import Types -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <?php foreach ($import_types as $type => $name): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-upload text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900"><?php echo $name; ?></h3>
                        <p class="text-sm text-gray-500">Import <?php echo strtolower($name); ?> from CSV</p>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <button onclick="downloadTemplate('<?php echo $type; ?>')" 
                            class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-download mr-2"></i>
                        Download Template
                    </button>
                    <button onclick="openImportModal('<?php echo $type; ?>', '<?php echo $name; ?>')" 
                            class="w-full btn btn-primary btn-sm">
                        <i class="fas fa-upload mr-2"></i>
                        Import Data
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Import Guidelines -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Import Guidelines</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">File Requirements</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• File format: CSV (Comma Separated Values)</li>
                        <li>• Maximum file size: 10MB</li>
                        <li>• First row must contain column headers</li>
                        <li>• Use UTF-8 encoding for special characters</li>
                        <li>• Date format: YYYY-MM-DD</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Data Validation</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Required fields must not be empty</li>
                        <li>• Email addresses must be valid</li>
                        <li>• Employee codes must be unique</li>
                        <li>• Departments and designations must exist</li>
                        <li>• Numeric fields must contain valid numbers</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800">Important Notes</h4>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Always backup your data before importing</li>
                                <li>Test with a small file first</li>
                                <li>Existing records will be updated if employee code matches</li>
                                <li>Invalid rows will be skipped and reported</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="import-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="import-modal-title" class="text-lg font-medium text-gray-900 mb-4">Import Data</h3>
            <form id="import-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="import_type" id="import_type">
                
                <div class="mb-4">
                    <label for="import_file" class="block text-sm font-medium text-gray-700 mb-2">Select CSV File</label>
                    <input type="file" name="import_file" id="import_file" accept=".csv" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="validate_only" class="form-checkbox">
                        <span class="ml-2 text-sm text-gray-700">Validate only (don't import)</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeImportModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Results Modal -->
<div id="results-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Import Results</h3>
            <div id="import-results-content">
                <!-- Results will be displayed here -->
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeResultsModal()" class="btn btn-primary">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function downloadTemplate(type) {
    window.location.href = `/integrations/template/${type}`;
}

function openImportModal(type, name) {
    document.getElementById('import-modal-title').textContent = `Import ${name}`;
    document.getElementById('import_type').value = type;
    document.getElementById('import-modal').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('import-modal').classList.add('hidden');
    document.getElementById('import-form').reset();
}

function closeResultsModal() {
    document.getElementById('results-modal').classList.add('hidden');
}

// Import form submission
document.getElementById('import-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    showLoading();
    
    fetch('/integrations/import', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        closeImportModal();
        
        if (data.success) {
            displayImportResults(data);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Import failed', 'error');
    });
});

function displayImportResults(data) {
    const content = document.getElementById('import-results-content');
    
    let html = `
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <div class="flex">
                <i class="fas fa-check-circle text-green-400 mr-2"></i>
                <div>
                    <h4 class="text-green-800 font-medium">Import Completed</h4>
                    <p class="text-green-700 text-sm">
                        Successfully imported ${data.imported} out of ${data.total_rows} records
                    </p>
                </div>
            </div>
        </div>
    `;
    
    if (data.errors && data.errors.length > 0) {
        html += `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="text-red-800 font-medium mb-2">Errors (${data.errors.length})</h4>
                <div class="max-h-48 overflow-y-auto">
                    <ul class="text-red-700 text-sm space-y-1">
        `;
        
        data.errors.forEach(error => {
            html += `<li>• ${error}</li>`;
        });
        
        html += `
                    </ul>
                </div>
            </div>
        `;
    }
    
    content.innerHTML = html;
    document.getElementById('results-modal').classList.remove('hidden');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>