<?php 
$title = 'Page Not Found - Payroll Management System';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <i class="fas fa-exclamation-triangle text-yellow-400 text-6xl mb-4"></i>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">404</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
            <p class="text-gray-600 mb-8">
                Sorry, the page you are looking for doesn't exist or has been moved.
            </p>
        </div>
        
        <div class="space-y-4">
            <a href="/dashboard" 
               class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <i class="fas fa-home mr-2"></i>
                Back to Dashboard
            </a>
            
            <button onclick="history.back()" 
                    class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Go Back
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>