<?php 
$title = 'Profile - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="max-w-2xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">User Profile</h1>
        <p class="mt-1 text-sm text-gray-500">
            Manage your account settings and personal information
        </p>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Profile Information</h3>
        </div>
        
        <div class="p-6">
            <form id="profile-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" id="username" 
                               value="<?php echo htmlspecialchars($user['username']); ?>"
                               class="form-input bg-gray-50" readonly>
                    </div>
                    
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" required
                               value="<?php echo htmlspecialchars($user['full_name']); ?>"
                               class="form-input">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" name="email" id="email" required
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               class="form-input">
                    </div>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <input type="text" name="role" id="role" 
                               value="<?php echo htmlspecialchars($_SESSION['role']); ?>"
                               class="form-input bg-gray-50" readonly>
                    </div>
                    
                    <div>
                        <label for="last_login" class="block text-sm font-medium text-gray-700 mb-2">Last Login</label>
                        <input type="text" name="last_login" id="last_login" 
                               value="<?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?>"
                               class="form-input bg-gray-50" readonly>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-between">
                    <a href="/change-password" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-key mr-1"></i>
                        Change Password
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Account Information -->
    <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Account Information</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Account Created</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500">Account Status</label>
                    <p class="mt-1">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    showLoading();
    
    fetch('/profile', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage(data.message, 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('An error occurred while updating profile', 'error');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>