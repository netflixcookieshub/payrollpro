<?php 
$title = 'Add Employee - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/employees" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New Employee</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Create a new employee profile with complete details
                </p>
            </div>
        </div>
    </div>

    <!-- Employee Form -->
    <form method="POST" enctype="multipart/form-data" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <!-- Personal Information -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="emp_code" class="block text-sm font-medium text-gray-700 mb-2">Employee Code</label>
                        <input type="text" name="emp_code" id="emp_code" 
                               class="form-input <?php echo isset($errors['emp_code']) ? 'border-red-300' : ''; ?>"
                               placeholder="Leave empty for auto-generation">
                        <?php if (isset($errors['emp_code'])): ?>
                            <p class="error-message"><?php echo $errors['emp_code']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" name="first_name" id="first_name" required
                               class="form-input <?php echo isset($errors['first_name']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['first_name'])): ?>
                            <p class="error-message"><?php echo $errors['first_name']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" name="last_name" id="last_name" required
                               class="form-input <?php echo isset($errors['last_name']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['last_name'])): ?>
                            <p class="error-message"><?php echo $errors['last_name']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-input <?php echo isset($errors['email']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['email'])): ?>
                            <p class="error-message"><?php echo $errors['email']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="phone" id="phone" class="form-input">
                    </div>
                    
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" class="form-input">
                    </div>
                    
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select name="gender" id="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" id="address" rows="3" class="form-textarea"></textarea>
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
                        <label for="join_date" class="block text-sm font-medium text-gray-700 mb-2">Join Date *</label>
                        <input type="date" name="join_date" id="join_date" required class="form-input">
                    </div>
                    
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                        <select name="department_id" id="department_id" required class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="designation_id" class="block text-sm font-medium text-gray-700 mb-2">Designation *</label>
                        <select name="designation_id" id="designation_id" required class="form-select">
                            <option value="">Select Designation</option>
                            <?php foreach ($designations as $designation): ?>
                                <option value="<?php echo $designation['id']; ?>">
                                    <?php echo htmlspecialchars($designation['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="cost_center_id" class="block text-sm font-medium text-gray-700 mb-2">Cost Center</label>
                        <select name="cost_center_id" id="cost_center_id" class="form-select">
                            <option value="">Select Cost Center</option>
                            <?php foreach ($cost_centers as $center): ?>
                                <option value="<?php echo $center['id']; ?>">
                                    <?php echo htmlspecialchars($center['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="reporting_manager_id" class="block text-sm font-medium text-gray-700 mb-2">Reporting Manager</label>
                        <select name="reporting_manager_id" id="reporting_manager_id" class="form-select">
                            <option value="">Select Manager</option>
                            <?php foreach ($managers as $manager): ?>
                                <option value="<?php echo $manager['id']; ?>">
                                    <?php echo htmlspecialchars($manager['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-2">Employment Type</label>
                        <select name="employment_type" id="employment_type" class="form-select">
                            <option value="permanent">Permanent</option>
                            <option value="contract">Contract</option>
                            <option value="temporary">Temporary</option>
                        </select>
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
                        <label for="pan_number" class="block text-sm font-medium text-gray-700 mb-2">PAN Number</label>
                        <input type="text" name="pan_number" id="pan_number" class="form-input" 
                               placeholder="ABCDE1234F" maxlength="10">
                    </div>
                    
                    <div>
                        <label for="aadhaar_number" class="block text-sm font-medium text-gray-700 mb-2">Aadhaar Number</label>
                        <input type="text" name="aadhaar_number" id="aadhaar_number" class="form-input" 
                               placeholder="1234 5678 9012" maxlength="12">
                    </div>
                    
                    <div>
                        <label for="uan_number" class="block text-sm font-medium text-gray-700 mb-2">UAN Number</label>
                        <input type="text" name="uan_number" id="uan_number" class="form-input" 
                               placeholder="123456789012" maxlength="12">
                    </div>
                    
                    <div>
                        <label for="pf_number" class="block text-sm font-medium text-gray-700 mb-2">PF Number</label>
                        <input type="text" name="pf_number" id="pf_number" class="form-input">
                    </div>
                    
                    <div>
                        <label for="esi_number" class="block text-sm font-medium text-gray-700 mb-2">ESI Number</label>
                        <input type="text" name="esi_number" id="esi_number" class="form-input">
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
                        <label for="bank_account_number" class="block text-sm font-medium text-gray-700 mb-2">Bank Account Number</label>
                        <input type="text" name="bank_account_number" id="bank_account_number" class="form-input">
                    </div>
                    
                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                        <input type="text" name="bank_name" id="bank_name" class="form-input">
                    </div>
                    
                    <div>
                        <label for="bank_ifsc" class="block text-sm font-medium text-gray-700 mb-2">IFSC Code</label>
                        <input type="text" name="bank_ifsc" id="bank_ifsc" class="form-input" 
                               placeholder="SBIN0001234" maxlength="11">
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="/employees" class="btn btn-outline">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Save Employee
            </button>
        </div>
    </form>
</div>

<script>
// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['first_name', 'last_name', 'join_date', 'department_id', 'designation_id'];
    let hasErrors = false;
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.classList.add('border-red-300');
            hasErrors = true;
        } else {
            input.classList.remove('border-red-300');
        }
    });
    
    if (hasErrors) {
        e.preventDefault();
        showMessage('Please fill in all required fields', 'error');
    }
});

// PAN number formatting
document.getElementById('pan_number').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});

// Aadhaar number formatting
document.getElementById('aadhaar_number').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');
    if (value.length > 4 && value.length <= 8) {
        value = value.slice(0, 4) + ' ' + value.slice(4);
    } else if (value.length > 8) {
        value = value.slice(0, 4) + ' ' + value.slice(4, 8) + ' ' + value.slice(8, 12);
    }
    this.value = value;
});

// IFSC code formatting
document.getElementById('bank_ifsc').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>