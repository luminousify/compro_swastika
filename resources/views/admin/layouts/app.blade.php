<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Admin Panel</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js for interactivity -->
    
</head>
<body class="bg-gray-50">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-indigo-600 text-white px-4 py-2 rounded-md">
        Skip to content
    </a>

    <div x-data="{ mobileMenuOpen: false, userMenuOpen: false }" class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg" aria-label="Main navigation">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Desktop Nav -->
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-indigo-600">
                                Admin Panel
                            </a>
                        </div>

                        <!-- Desktop Navigation -->
                        <div class="hidden md:ml-6 md:flex md:space-x-8">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="@if(request()->routeIs('admin.dashboard')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                               @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                                Dashboard
                            </a>

                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.settings.edit') }}" 
                                   class="@if(request()->routeIs('admin.settings.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Settings
                                </a>
                                <a href="{{ route('admin.users.index') }}" 
                                   class="@if(request()->routeIs('admin.users.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Users
                                </a>
                            @endif

                            <a href="{{ route('admin.divisions.index') }}" 
                               class="@if(request()->routeIs('admin.divisions.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Divisions
                            </a>
                            <a href="{{ route('admin.media.index') }}" 
                               class="@if(request()->routeIs('admin.media.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Media
                            </a>
                            <a href="{{ route('admin.milestones.index') }}" 
                               class="@if(request()->routeIs('admin.milestones.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Milestones
                            </a>
                            <a href="{{ route('admin.clients.index') }}" 
                               class="@if(request()->routeIs('admin.clients.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Clients
                            </a>
                            <a href="{{ route('admin.messages.index') }}" 
                               class="@if(request()->routeIs('admin.messages.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium relative">
                                Contact Messages
                                @if(isset($unhandledMessages) && $unhandledMessages > 0)
                                    <span class="badge absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ $unhandledMessages }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    </div>

                    <!-- Right side (User menu and mobile toggle) -->
                    <div class="flex items-center">
                        <!-- User Menu -->
                        <div class="hidden md:block">
                            <div class="ml-4 flex items-center md:ml-6">
                                <!-- User dropdown -->
                                <div class="ml-3 relative">
                                    <div>
                                        <button @click="userMenuOpen = !userMenuOpen" 
                                                type="button" 
                                                class="bg-white flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                id="user-menu-button" 
                                                aria-expanded="false" 
                                                aria-haspopup="true">
                                            <span class="sr-only">Open user menu</span>
                                            <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                                                {{ substr(auth()->user()->name, 0, 1) }}
                                            </div>
                                        </button>
                                    </div>

                                    <!-- Dropdown menu -->
                                    <div x-show="userMenuOpen" 
                                         @click.away="userMenuOpen = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                         role="menu" 
                                         aria-orientation="vertical" 
                                         aria-labelledby="user-menu-button" 
                                         tabindex="-1">
                                        <div class="px-4 py-2 text-sm text-gray-700 border-b">
                                            <div class="font-medium">{{ auth()->user()->name }}</div>
                                            <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                                        </div>
                                        <form method="POST" action="/logout">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="flex md:hidden">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" 
                                    type="button" 
                                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                                    aria-controls="mobile-menu" 
                                    id="mobile-menu-button"
                                    aria-expanded="false">
                                <span class="sr-only">Open main menu</span>
                                <!-- Icon when menu is closed -->
                                <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <!-- Icon when menu is open -->
                                <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-show="mobileMenuOpen" class="md:hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="@if(request()->routeIs('admin.dashboard')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Dashboard
                    </a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.settings.edit') }}" class="@if(request()->routeIs('admin.settings.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                            Settings
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="@if(request()->routeIs('admin.users.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                            Users
                        </a>
                    @endif

                    <a href="{{ route('admin.divisions.index') }}" class="@if(request()->routeIs('admin.divisions.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Divisions
                    </a>
                    <a href="{{ route('admin.media.index') }}" class="@if(request()->routeIs('admin.media.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Media
                    </a>
                    <a href="{{ route('admin.milestones.index') }}" class="@if(request()->routeIs('admin.milestones.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Milestones
                    </a>
                    <a href="{{ route('admin.clients.index') }}" class="@if(request()->routeIs('admin.clients.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Clients
                    </a>
                    <a href="{{ route('admin.messages.index') }}" class="@if(request()->routeIs('admin.messages.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Contact Messages
                        @if(isset($unhandledMessages) && $unhandledMessages > 0)
                            <span class="badge ml-2 bg-red-500 text-white text-xs rounded-full px-2 py-1">
                                {{ $unhandledMessages }}
                            </span>
                        @endif
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Breadcrumb -->
        @if(isset($breadcrumbs) || View::hasSection('breadcrumbs'))
            <nav aria-label="Breadcrumb" class="bg-white border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <ol class="flex space-x-4 py-3 text-sm">
                        <li class="flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                <span class="sr-only">Home</span>
                            </a>
                        </li>
                        @yield('breadcrumbs')
                    </ol>
                </div>
            </nav>
        @endif

        <!-- Main Content -->
        <main id="main-content" class="flex-1">
            @yield('content')
        </main>
    </div>

    <!-- Scripts Stack -->
    @stack('scripts')
</body>
</html>
