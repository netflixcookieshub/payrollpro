<?php 
$title = 'Employee Profile - ' . $employee['first_name'] . ' ' . $employee['last_name'];
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <div class="flex items-center">
                <a href="/employees" class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php echo htmlspecialchars($employee['emp_code']); ?> • 
                        <?php echo htmlspecialchars($employee['designation_name']); ?> • 
                        <?php echo htmlspecialchars($employee['department_name']); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <a href="/employees/<?php echo $employee['id']; ?>/salary-structure" class="btn btn-outline">
                <i class="fas fa-rupee-sign mr-2"></i>
                Salary Structure
            </a>
            <a href="/employees/<?php echo $employee['id']; ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit mr-2"></i>
                Edit Profile
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Employee Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Employee Code</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['emp_code']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Full Name</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['email'] ?: 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['phone'] ?: 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Date of Birth</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <?php echo $employee['date_of_birth'] ? date('M j, Y', strtotime($employee['date_of_birth'])) : 'Not provided'; ?>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Gender</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo ucfirst($employee['gender'] ?: 'Not specified'); ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['address'] ?: 'Not provided'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Details -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Employment Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Join Date</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo date('M j, Y', strtotime($employee['join_date'])); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Employment Type</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo ucfirst($employee['employment_type']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Department</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['department_name']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Designation</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['designation_name']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Cost Center</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['cost_center_name'] ?: 'Not assigned'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                switch($employee['status']) {
                                    case 'active': echo 'bg-green-100 text-green-800'; break;
                                    case 'inactive': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'terminated': echo 'bg-red-100 text-red-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?php echo ucfirst($employee['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statutory Details -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Statutory Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">PAN Number</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?php echo htmlspecialchars($employee['pan_number'] ?: 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Aadhaar Number</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?php echo htmlspecialchars($employee['aadhaar_number'] ?: 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">UAN Number</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?php echo htmlspecialchars($employee['uan_number'] ?: 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">PF Number</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?php echo htmlspecialchars($employee['pf_number'] ?: 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">ESI Number</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?php echo htmlspecialchars($employee['esi_number'] ?: 'Not provided'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Bank Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Bank Account Number</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?php echo htmlspecialchars($employee['bank_account_number'] ?: 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Bank Name</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($employee['bank_name'] ?: 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">IFSC Code</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?php echo htmlspecialchars($employee['bank_ifsc'] ?: 'Not provided'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Profile Photo -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Profile Photo</h3>
                </div>
                <div class="p-6 text-center">
                    <?php if (!empty($employee['photo'])): ?>
                        <img class="mx-auto h-32 w-32 rounded-full object-cover" 
                             src="/uploads/<?php echo htmlspecialchars($employee['photo']); ?>" 
                             alt="Profile Photo">
                    <?php else: ?>
                        <div class="mx-auto h-32 w-32 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-4xl font-medium text-gray-600">
                                <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <button onclick="uploadPhoto()" class="mt-4 btn btn-outline btn-sm">
                        <i class="fas fa-camera mr-2"></i>
                        Upload Photo
                    </button>
                </div>
            </div>

            <!-- Current Salary Structure -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Current Salary Structure</h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($salary_structure)): ?>
                        <div class="space-y-3">
                            <?php 
                            $totalEarnings = 0;
                            $totalDeductions = 0;
                            foreach ($salary_structure as $component): 
                                if ($component['component_type'] === 'earning') {
                                    $totalEarnings += $component['amount'];
                                } else {
                                    $totalDeductions += $component['amount'];
                                }
                            ?>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600"><?php echo htmlspecialchars($component['component_name']); ?></span>
                                    <span class="text-sm font-medium <?php echo $component['component_type'] === 'earning' ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $component['component_type'] === 'earning' ? '+' : '-'; ?>₹<?php echo number_format($component['amount'], 2); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                            <div class="border-t pt-3">
                                <div class="flex justify-between items-center font-semibold">
                                    <span class="text-gray-900">Net Salary</span>
                                    <span class="text-blue-600">₹<?php echo number_format($totalEarnings - $totalDeductions, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 text-center">No salary structure assigned</p>
                        <a href="/employees/<?php echo $employee['id']; ?>/salary-structure" class="mt-4 btn btn-primary btn-sm w-full">
                            <i class="fas fa-plus mr-2"></i>
                            Assign Salary Structure
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Payslips -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Payslips</h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($recent_payslips)): ?>
                        <div class="space-y-3">
                            <?php foreach ($recent_payslips as $payslip): ?>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($payslip['period_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo date('M Y', strtotime($payslip['start_date'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">₹<?php echo number_format($payslip['earnings'] - $payslip['deductions'], 2); ?></p>
                                        <a href="/payroll/payslip/<?php echo $employee['id']; ?>/<?php echo $payslip['period_id']; ?>" 
                                           class="text-xs text-blue-600 hover:text-blue-800">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 text-center">No payslips available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="/employees/<?php echo $employee['id']; ?>/salary-structure" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-rupee-sign mr-2"></i>
                        Manage Salary
                    </a>
                    <button onclick="generatePayslip()" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Generate Payslip
                    </button>
                    <button onclick="uploadDocument()" class="w-full btn btn-outline btn-sm">
                        <i class="fas fa-upload mr-2"></i>
                        Upload Document
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div id="photo-upload-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Profile Photo</h3>
            <form id="photo-upload-form" enctype="multipart/form-data">
                <input type="hidden" name="document_type" value="photo">
                <input type="file" name="document" accept="image/*" class="form-input mb-4">
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closePhotoModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function uploadPhoto() {
    document.getElementById('photo-upload-modal').classList.remove('hidden');
}

function closePhotoModal() {
    document.getElementById('photo-upload-modal').classList.add('hidden');
}

document.getElementById('photo-upload-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/employees/<?php echo $employee['id']; ?>/upload-document', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage(data.message, 'error');
        }
        closePhotoModal();
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Failed to upload photo', 'error');
        closePhotoModal();
    });
});

function generatePayslip() {
    // This would open a modal to select period and generate payslip
    showMessage('Feature coming soon', 'info');
}

function uploadDocument() {
    // This would open a document upload modal
    showMessage('Feature coming soon', 'info');
}

// Success message from URL
<?php if (isset($_GET['success'])): ?>
    showMessage('<?php echo $_GET['success'] === 'updated' ? 'Employee updated successfully' : 'Operation completed successfully'; ?>', 'success');
<?php endif; ?>
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>