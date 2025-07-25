<?php 
$title = 'Holidays - Master Data';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900">Holiday Management</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage company holidays and calendar events for <?php echo $current_year; ?>
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>
                Add Holiday
            </button>
        </div>
    </div>

    <!-- Year Navigation -->
    <div class="mb-6 flex items-center justify-center space-x-4">
        <a href="?year=<?php echo $current_year - 1; ?>" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-chevron-left mr-1"></i>
            <?php echo $current_year - 1; ?>
        </a>
        <span class="text-xl font-semibold text-gray-900"><?php echo $current_year; ?></span>
        <a href="?year=<?php echo $current_year + 1; ?>" class="text-gray-500 hover:text-gray-700">
            <?php echo $current_year + 1; ?>
            <i class="fas fa-chevron-right ml-1"></i>
        </a>
    </div>

    <!-- Holidays List -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Holiday Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Day
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($holidays)): ?>
                        <?php foreach ($holidays as $holiday): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($holiday['name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('M j, Y', strtotime($holiday['holiday_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('l', strtotime($holiday['holiday_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($holiday['type']) {
                                            case 'national': echo 'bg-red-100 text-red-800'; break;
                                            case 'religious': echo 'bg-purple-100 text-purple-800'; break;
                                            case 'optional': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'company': echo 'bg-blue-100 text-blue-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($holiday['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo htmlspecialchars($holiday['description'] ?? '-'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editHoliday(<?php echo $holiday['id']; ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteHoliday(<?php echo $holiday['id']; ?>)" 
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
                                    <i class="fas fa-calendar-times text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No holidays found for <?php echo $current_year; ?></p>
                                    <p class="text-sm">Add your first holiday to get started</p>
                                    <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Holiday
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Holiday Modal -->
<div id="holiday-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Add Holiday</h3>
            <form id="holiday-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="holiday_id">
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Holiday Name *</label>
                    <input type="text" name="name" id="name" required
                           class="form-input" placeholder="e.g., Independence Day">
                </div>
                
                <div class="mb-4">
                    <label for="holiday_date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input type="date" name="holiday_date" id="holiday_date" required class="form-input">
                </div>
                
                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                    <select name="type" id="type" required class="form-select">
                        <option value="">Select Type</option>
                        <option value="national">National Holiday</option>
                        <option value="religious">Religious Holiday</option>
                        <option value="optional">Optional Holiday</option>
                        <option value="company">Company Holiday</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="2" class="form-textarea" 
                              placeholder="Optional description"></textarea>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeHolidayModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submit-text">Add Holiday</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-title').textContent = 'Add Holiday';
    document.getElementById('submit-text').textContent = 'Add Holiday';
    document.getElementById('holiday-form').reset();
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('holiday_id').value = '';
    document.getElementById('holiday-modal').classList.remove('hidden');
}

function editHoliday(holidayId) {
    document.getElementById('modal-title').textContent = 'Edit Holiday';
    document.getElementById('submit-text').textContent = 'Update Holiday';
    document.querySelector('input[name="action"]').value = 'update';
    document.getElementById('holiday_id').value = holidayId;
    document.getElementById('holiday-modal').classList.remove('hidden');
    
    // In a real implementation, you would fetch and populate the form data
}

function closeHolidayModal() {
    document.getElementById('holiday-modal').classList.add('hidden');
}

function deleteHoliday(holidayId) {
    if (confirm('Are you sure you want to delete this holiday?')) {
        showLoading();
        
        fetch('/holidays', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                id: holidayId,
                csrf_token: '<?php echo $csrf_token; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('An error occurred while deleting the holiday', 'error');
        });
    }
}

// Form submission
document.getElementById('holiday-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    showLoading();
    
    fetch('/holidays', {
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
            showMessage(data.message, 'success');
            closeHolidayModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showMessage(data.errors[field], 'error');
                });
            } else {
                showMessage(data.message || 'Failed to save holiday', 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while saving the holiday', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>