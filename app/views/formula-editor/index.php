<?php 
$title = 'Formula Editor - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Advanced Formula Editor</h1>
        <p class="mt-1 text-sm text-gray-500">
            Create, test, and manage complex salary calculation formulas
        </p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-code text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Formula Builder</h3>
                    <p class="text-sm text-gray-500">Visual formula creation with drag & drop</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="/formula-editor/builder" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Open Builder →
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-database text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Query Builder</h3>
                    <p class="text-sm text-gray-500">Create custom reports with SQL queries</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="/formula-editor/custom-query" class="text-green-600 hover:text-green-800 text-sm font-medium">
                    Open Query Builder →
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-save text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Saved Templates</h3>
                    <p class="text-sm text-gray-500">Manage your formula templates</p>
                </div>
            </div>
            <div class="mt-4">
                <button onclick="loadTemplates()" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                    View Templates →
                </button>
            </div>
        </div>
    </div>

    <!-- Formula Templates -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Formula Templates</h3>
                <button onclick="refreshTemplates()" class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fas fa-refresh mr-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="p-6">
            <div id="templates-container">
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-code text-4xl mb-4"></i>
                    <p class="text-lg font-medium">No formula templates found</p>
                    <p class="text-sm">Create your first formula template to get started</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadTemplates() {
    fetch('/formula-editor/templates')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTemplates(data.templates);
            } else {
                showMessage('Failed to load templates', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error loading templates', 'error');
        });
}

function displayTemplates(templates) {
    const container = document.getElementById('templates-container');
    
    if (templates.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-code text-4xl mb-4"></i>
                <p class="text-lg font-medium">No formula templates found</p>
                <p class="text-sm">Create your first formula template to get started</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = templates.map(template => `
        <div class="border rounded-lg p-4 mb-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-medium text-gray-900">${template.name}</h4>
                    <p class="text-sm text-gray-500">${template.description || 'No description'}</p>
                    <code class="text-xs bg-gray-100 px-2 py-1 rounded mt-2 inline-block">${template.formula}</code>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        ${template.category}
                    </span>
                    <button onclick="editTemplate(${template.id})" class="text-blue-600 hover:text-blue-800" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="exportTemplate(${template.id})" class="text-green-600 hover:text-green-800" title="Export">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-400">
                Created by ${template.created_by_name} on ${new Date(template.created_at).toLocaleDateString()}
            </div>
        </div>
    `).join('');
}

function refreshTemplates() {
    loadTemplates();
}

function editTemplate(id) {
    window.location.href = `/formula-editor/builder?template=${id}`;
}

function exportTemplate(id) {
    window.location.href = `/formula-editor/export?id=${id}`;
}

// Load templates on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTemplates();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>