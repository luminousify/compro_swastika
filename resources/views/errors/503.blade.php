<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - {{ \App\Models\Setting::getValue('company_name', 'Company') }}</title>
    <meta name="description" content="We are currently performing scheduled maintenance. Please check back soon.">
    <meta name="robots" content="noindex, nofollow">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-24 w-24 text-yellow-600">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Under Maintenance
                </h2>
                
                @if(isset($exception) && $exception->getMessage())
                    <p class="mt-2 text-sm text-gray-600">
                        {{ $exception->getMessage() }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-600">
                        We are currently performing scheduled maintenance. Please check back soon.
                    </p>
                @endif
                
                <div class="mt-8 space-y-4">
                    <div>
                        <button onclick="location.reload()" 
                               class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh Page
                        </button>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 text-center">
                            {{ \App\Models\Setting::getValue('company_name', 'Company') }}
                        </p>
                        <p class="text-xs text-gray-400 text-center mt-1">
                            Thank you for your patience
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>