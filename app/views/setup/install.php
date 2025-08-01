<?php 
$title = 'Installation - Payroll Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <i class="fas fa-calculator text-blue-500 text-6xl mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">PayrollPro Installation</h2>
                <p class="mt-2 text-sm text-gray-600">Configure your payroll management system</p>
            </div>
            
            <!-- Installation Form -->
            <div class="bg-white shadow rounded-lg">
                <form id="installation-form" class="space-y-6 p-6">
                    <!-- Database Configuration -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Database Configuration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="db_host" class="block text-sm font-medium text-gray-700 mb-2">Host</label>
                                <input type="text" name="database[host]" id="db_host" value="localhost" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="db_port" class="block text-sm font-medium text-gray-700 mb-2">Port</label>
                                <input type="number" name="database[port]" id="db_port" value="3306" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="db_name" class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
                                <input type="text" name="database[database]" id="db_name" value="payroll_system" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="db_username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                <input type="text" name="database[username]" id="db_username" value="root" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label for="db_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                <input type="password" name="database[password]" id="db_password"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" onclick="testDatabase()" class="btn btn-outline">
                                <i class="fas fa-check-circle mr-2"></i>Test Connection
                            </button>
                        </div>
                    </div>
                    
                    <!-- Admin User -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Administrator Account</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                <input type="text" name="admin[username]" id="admin_username" value="admin" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="admin[email]" id="admin_email" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="admin_full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="admin[full_name]" id="admin_full_name" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                <input type="password" name="admin[password]" id="admin_password" required minlength="6"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Settings -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">System Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="app_name" class="block text-sm font-medium text-gray-700 mb-2">Application Name</label>
                                <input type="text" name="settings[app_name]" id="app_name" value="PayrollPro" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="base_url" class="block text-sm font-medium text-gray-700 mb-2">Base URL</label>
                                <input type="url" name="settings[base_url]" id="base_url" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                <select name="settings[timezone]" id="timezone" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="Asia/Kolkata">Asia/Kolkata</option>
                                    <option value="UTC">UTC</option>
                                    <option value="America/New_York">America/New_York</option>
                                    <option value="Europe/London">Europe/London</option>
                                </select>
                            </div>
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                <select name="settings[currency]" id="currency" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="INR">INR (₹)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Installation Progress -->
                    <div id="installation-progress" class="hidden">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Installation Progress</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <div id="step1-icon" class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <i class="fas fa-clock text-gray-500 text-xs"></i>
                                </div>
                                <span class="text-sm text-gray-700">Testing database connection...</span>
                            </div>
                            <div class="flex items-center">
                                <div id="step2-icon" class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <i class="fas fa-clock text-gray-500 text-xs"></i>
                                </div>
                                <span class="text-sm text-gray-700">Running database migrations...</span>
                            </div>
                            <div class="flex items-center">
                                <div id="step3-icon" class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <i class="fas fa-clock text-gray-500 text-xs"></i>
                                </div>
                                <span class="text-sm text-gray-700">Creating admin user...</span>
                            </div>
                            <div class="flex items-center">
                                <div id="step4-icon" class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <i class="fas fa-clock text-gray-500 text-xs"></i>
                                </div>
                                <span class="text-sm text-gray-700">Configuring system...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-rocket mr-2"></i>
                            Install PayrollPro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Set default base URL
        document.getElementById('base_url').value = window.location.origin;
        
        function testDatabase() {
            const formData = new FormData();
            formData.append('host', document.getElementById('db_host').value);
            formData.append('port', document.getElementById('db_port').value);
            formData.append('database', document.getElementById('db_name').value);
            formData.append('username', document.getElementById('db_username').value);
            formData.append('password', document.getElementById('db_password').value);
            
            fetch('/setup/database', {
                method: 'POST',
                body: JSON.stringify(Object.fromEntries(formData)),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Database connection successful!');
                } else {
                    alert('Database connection failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error testing database connection');
            });
        }
        
        document.getElementById('installation-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show progress
            document.getElementById('installation-progress').classList.remove('hidden');
            
            const formData = new FormData(this);
            const data = {};
            
            // Group form data
            for (let [key, value] of formData.entries()) {
                const keys = key.split('[');
                if (keys.length > 1) {
                    const group = keys[0];
                    const field = keys[1].replace(']', '');
                    if (!data[group]) data[group] = {};
                    data[group][field] = value;
                } else {
                    data[key] = value;
                }
            }
            
            fetch('/setup/install', {
                method: 'POST',
                body: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update progress icons
                    ['step1-icon', 'step2-icon', 'step3-icon', 'step4-icon'].forEach(id => {
                        const icon = document.getElementById(id);
                        icon.className = 'w-6 h-6 rounded-full bg-green-500 flex items-center justify-center mr-3';
                        icon.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
                    });
                    
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                    alert('Installation failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('Installation error occurred');
            });
        });
    </script>
</body>
</html>