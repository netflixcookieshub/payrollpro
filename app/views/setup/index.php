<?php 
$title = 'System Setup - Payroll Management System';
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
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <i class="fas fa-calculator text-blue-500 text-6xl mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">
                    PayrollPro Setup
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Welcome! Let's set up your payroll management system
                </p>
            </div>
            
            <!-- Setup Steps -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 text-sm font-medium">1</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Database Configuration</h3>
                            <p class="text-xs text-gray-500">Configure database connection</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 text-sm font-medium">2</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-500">System Installation</h3>
                            <p class="text-xs text-gray-400">Install database tables and data</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 text-sm font-medium">3</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-500">Admin Account</h3>
                            <p class="text-xs text-gray-400">Create administrator account</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 text-sm font-medium">4</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-500">Configuration</h3>
                            <p class="text-xs text-gray-400">Configure system settings</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="/setup/install" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-play mr-2"></i>
                        Start Setup
                    </a>
                </div>
            </div>
            
            <!-- System Requirements -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">System Requirements</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        <span>PHP 8.0 or higher</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        <span>MySQL 8.0 or higher</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        <span>Apache/Nginx web server</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        <span>PDO MySQL extension</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>