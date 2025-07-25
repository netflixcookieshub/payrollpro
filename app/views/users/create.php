<?php 
$title = 'Add User - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-2xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="/users" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New User</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Create a new system user with appropriate role and permissions
                </p>
            </div>
        </div>
    </div>

    <!-- User Form -->
    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">User Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                        <input type="text" name="username" id="username" required
                               class="form-input <?php echo isset($errors['username']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['username'])): ?>
                            <p class="error-message"><?php echo $errors['username']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" id="email" required
                               class="form-input <?php echo isset($errors['email']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['email'])): ?>
                            <p class="error-message"><?php echo $errors['email']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" required
                               class="form-input <?php echo isset($errors['full_name']) ? 'border-red-300' : ''; ?>">
                        <?php if (isset($errors['full_name'])): ?>
                            <p class="error-message"><?php echo $errors['full_name']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input type="password" name="password" id="password" required minlength="6"
                               class="form-input <?php echo isset($errors['password']) ? 'border-red-300' : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                        <?php if (isset($errors['password'])): ?>
                            <p class="error-message"><?php echo $errors['password']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                        <select name="role_id" id="role_id" required class="form-select">
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>">
                                    <?php echo htmlspecialchars($role['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['role_id'])): ?>
                            <p class="error-message"><?php echo $errors['role_id']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="/users" class="btn btn-outline">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Create User
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>