<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - {{ \App\Models\Setting::getValue('company_name', 'Company') }}</title>
    <meta name="description" content="The page you are looking for could not be found.">
    <meta name="robots" content="noindex, nofollow">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-9xl font-bold text-blue-600">404</h1>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Error 404 - Page Not Found
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    The page you are looking for could not be found.
                </p>
                
                <div class="mt-8 space-y-4">
                    <div>
                        <a href="/" 
                           class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Go to Homepage
                        </a>
                    </div>
                    
                    <div>
                        <a href="/contact" 
                           class="group relative w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Contact Us
                        </a>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 text-center">
                            {{ \App\Models\Setting::getValue('company_name', 'Company') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>