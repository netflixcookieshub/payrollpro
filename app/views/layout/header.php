<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Payroll Management System'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/formula-editor.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-calculator text-primary-500 text-2xl mr-2"></i>
                        <span class="font-bold text-xl text-gray-900">PayrollPro</span>
                    </div>
                    
                    <!-- Main Navigation -->
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                        <a href="/dashboard" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/dashboard') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Dashboard
                        </a>
                        
                        <?php if (isset($_SESSION['permissions']) && (str_contains($_SESSION['permissions'], 'employees') || $_SESSION['permissions'] === 'all')): ?>
                        <a href="/employees" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/employees') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-users mr-2"></i>
                            Employees
                        </a>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['permissions']) && (str_contains($_SESSION['permissions'], 'payroll') || $_SESSION['permissions'] === 'all')): ?>
                        <a href="/payroll" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/payroll') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-money-bill-wave mr-2"></i>
                            Payroll
                        </a>
                        
                        <a href="/reports" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/reports') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Reports
                        </a>
                        
                        <a href="/attendance" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/attendance') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Attendance
                        </a>
                        
                        <a href="/loans" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/loans') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-hand-holding-usd mr-2"></i>
                            Loans
                        </a>
                        
                        <a href="/formula-editor" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/formula-editor') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-code mr-2"></i>
                            Formulas
                        </a>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['permissions']) && (str_contains($_SESSION['permissions'], 'admin') || $_SESSION['permissions'] === 'all')): ?>
                        <a href="/integrations" class="nav-link <?php echo str_contains($_SERVER['REQUEST_URI'], '/integrations') ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-plug mr-2"></i>
                            Integrations
                        </a>
                        <?php endif; ?>
                        
                        <!-- Masters Dropdown -->
                        <div class="relative inline-block text-left">
                            <button type="button" class="nav-link border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" onclick="toggleDropdown('masters-dropdown')">
                                <i class="fas fa-cogs mr-2"></i>
                                Masters
                                <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                            
                            <div id="masters-dropdown" class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                <div class="py-1">
                                    <a href="/departments" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Departments</a>
                                    <a href="/designations" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Designations</a>
                                    <a href="/salary-components" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Salary Components</a>
                                    <a href="/loan-types" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Loan Types</a>
                                    <a href="/leave-types" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Leave Types</a>
                                    <a href="/holidays" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Holidays</a>
                                    <?php if (isset($_SESSION['permissions']) && (str_contains($_SESSION['permissions'], 'users') || $_SESSION['permissions'] === 'all')): ?>
                                        <div class="border-t border-gray-100"></div>
                                        <a href="/users" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">User Management</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center">
                    <div class="relative">
                        <button type="button" class="bg-white rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" onclick="toggleDropdown('user-menu')">
                            <span class="sr-only">Open user menu</span>
                            <div class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center">
                                <span class="text-white text-sm font-medium">
                                    <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?>
                                </span>
                            </div>
                            <div class="ml-3 hidden sm:block">
                                <div class="text-sm font-medium text-gray-700"><?php echo $_SESSION['full_name'] ?? 'User'; ?></div>
                                <div class="text-xs text-gray-500"><?php echo $_SESSION['role'] ?? 'User'; ?></div>
                            </div>
                        </button>
                        
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="/change-password" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-key mr-2"></i>Change Password
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="sm:hidden border-t border-gray-200">
            <div class="px-4 pt-2 pb-3 space-y-1">
                <a href="/dashboard" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">Dashboard</a>
                <a href="/employees" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">Employees</a>
                <a href="/payroll" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">Payroll</a>
                <a href="/attendance" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">Attendance</a>
                <a href="/loans" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">Loans</a>
                <a href="/reports" class="block text-gray-600 hover:text-gray-900 px-2 py-1 text-base font-medium">Reports</a>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <script>
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            dropdown.classList.toggle('hidden');
            
            // Close other dropdowns
            document.querySelectorAll('[id$="-dropdown"]').forEach(otherDropdown => {
                if (otherDropdown.id !== id) {
                    otherDropdown.classList.add('hidden');
                }
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('[id$="-dropdown"]');
            dropdowns.forEach(dropdown => {
                if (!dropdown.closest('.relative').contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            });
            
            // Close user menu when clicking outside
            const userMenu = document.getElementById('user-menu');
            if (userMenu && !userMenu.closest('.relative').contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
    </script>