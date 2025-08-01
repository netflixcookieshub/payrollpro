<?php 
$title = 'Backup Manager - System Administration';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Backup Manager</h1>
        <p class="mt-1 text-sm text-gray-500">
            Manage system backups and data recovery operations
        </p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-database text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Create Backup</h3>
                <button onclick="createBackup('full')" class="btn btn-primary btn-sm">
                    Create Full Backup
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-upload text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Restore Backup</h3>
                <button onclick="openRestoreModal()" class="btn btn-outline btn-sm">
                    Restore from File
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Schedule Backup</h3>
                <button onclick="openScheduleModal()" class="btn btn-outline btn-sm">
                    Configure Schedule
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="text-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-trash text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Cleanup</h3>
                <button onclick="cleanupOldBackups()" class="btn btn-outline btn-sm">
                    Clean Old Backups
                </button>
            </div>
        </div>
    </div>

    <!-- Backup Status -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Backup Status</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Last Backup</p>
                    <p class="text-lg font-semibold text-gray-900" id="last-backup-time">
                        <?php echo $last_backup ? date('M j, Y H:i', strtotime($last_backup)) : 'Never'; ?>
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-500">Total Backups</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo $backup_count ?? 0; ?></p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-500">Storage Used</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo $storage_used ?? '0 MB'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup History -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Backup History</h3>
        </div>
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Backup Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Size
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($backup_history)): ?>
                        <?php foreach ($backup_history as $backup): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($backup['backup_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo ucfirst($backup['backup_type']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $this->formatFileSize($backup['file_size']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M j, Y H:i', strtotime($backup['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($backup['status']) {
                                            case 'completed': echo 'bg-green-100 text-green-800'; break;
                                            case 'failed': echo 'bg-red-100 text-red-800'; break;
                                            case 'in_progress': echo 'bg-yellow-100 text-yellow-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($backup['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($backup['status'] === 'completed'): ?>
                                            <button onclick="downloadBackup('<?php echo $backup['backup_name']; ?>')" 
                                                    class="text-blue-600 hover:text-blue-900" title="Download">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button onclick="restoreBackup('<?php echo $backup['backup_name']; ?>')" 
                                                    class="text-green-600 hover:text-green-900" title="Restore">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button onclick="validateBackup('<?php echo $backup['backup_name']; ?>')" 
                                                    class="text-yellow-600 hover:text-yellow-900" title="Validate">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteBackup('<?php echo $backup['id']; ?>')" 
                                                class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-database text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No backups found</p>
                                    <p class="text-sm">Create your first backup to get started</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div id="restore-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Restore from Backup</h3>
            <form id="restore-form" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="backup-file" class="block text-sm font-medium text-gray-700 mb-2">Select Backup File</label>
                    <input type="file" name="backup_file" id="backup-file" accept=".zip" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="confirm_restore" required class="form-checkbox">
                        <span class="ml-2 text-sm text-gray-700">I understand this will overwrite current data</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeRestoreModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo mr-2"></i>Restore
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div id="schedule-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule Automatic Backups</h3>
            <form id="schedule-form">
                <div class="mb-4">
                    <label for="backup-frequency" class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                    <select name="frequency" id="backup-frequency" class="form-select">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="backup-time" class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                    <input type="time" name="backup_time" id="backup-time" value="02:00" class="form-input">
                </div>
                
                <div class="mb-4">
                    <label for="retention-days" class="block text-sm font-medium text-gray-700 mb-2">Retention (Days)</label>
                    <input type="number" name="retention_days" id="retention-days" value="30" min="1" max="365" class="form-input">
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="include_files" checked class="form-checkbox">
                        <span class="ml-2 text-sm text-gray-700">Include uploaded files</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeScheduleModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function createBackup(type) {
    if (confirm('Are you sure you want to create a backup? This may take several minutes.')) {
        showLoading();
        
        fetch('/system/create-backup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                type: type,
                include_files: true,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage('Backup created successfully', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Backup creation failed', 'error');
        });
    }
}

function openRestoreModal() {
    document.getElementById('restore-modal').classList.remove('hidden');
}

function closeRestoreModal() {
    document.getElementById('restore-modal').classList.add('hidden');
    document.getElementById('restore-form').reset();
}

function openScheduleModal() {
    document.getElementById('schedule-modal').classList.remove('hidden');
}

function closeScheduleModal() {
    document.getElementById('schedule-modal').classList.add('hidden');
}

function cleanupOldBackups() {
    if (confirm('Are you sure you want to delete old backups? This action cannot be undone.')) {
        showLoading();
        
        fetch('/system/cleanup-backups', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Cleanup failed', 'error');
        });
    }
}

function downloadBackup(backupName) {
    window.location.href = `/system/download-backup?name=${encodeURIComponent(backupName)}`;
}

function restoreBackup(backupName) {
    if (confirm('WARNING: This will overwrite all current data. Are you sure you want to restore from this backup?')) {
        showLoading();
        
        fetch('/system/restore-backup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                backup_name: backupName,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage('Backup restored successfully', 'success');
                setTimeout(() => location.reload(), 3000);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Restore failed', 'error');
        });
    }
}

function validateBackup(backupName) {
    showLoading();
    
    fetch('/system/validate-backup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            backup_name: backupName,
            csrf_token: '<?php echo $csrf_token; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.valid) {
            showMessage('Backup file is valid', 'success');
        } else {
            showMessage('Backup validation failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Validation failed', 'error');
    });
}

// Form submissions
document.getElementById('restore-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    showLoading();
    
    fetch('/system/restore-from-file', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage('Restore completed successfully', 'success');
            closeRestoreModal();
            setTimeout(() => location.reload(), 3000);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Restore failed', 'error');
    });
});

document.getElementById('schedule-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.csrf_token = '<?php echo $csrf_token; ?>';
    
    showLoading();
    
    fetch('/system/schedule-backup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage('Backup schedule saved successfully', 'success');
            closeScheduleModal();
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Schedule save failed', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>