@props(['active' => 'home'])

<!-- Navigation -->
<nav role="navigation" aria-label="main navigation" class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container-custom">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="/" class="text-2xl font-bold text-gray-900">
                    {{ \App\Models\Setting::getValue('company_name', 'PT. Daya Swastika Perkasa') }}
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-6">
                    <a href="/" class="nav-link {{ $active === 'home' ? 'nav-link-active' : '' }}">Beranda</a>
                    <a href="/visi-misi" class="nav-link {{ $active === 'visi-misi' ? 'nav-link-active' : '' }}">Visi & Misi</a>
                    <a href="/milestones" class="nav-link {{ $active === 'milestones' ? 'nav-link-active' : '' }}">Sejarah</a>
                    <a href="/line-of-business" class="nav-link {{ $active === 'divisions' ? 'nav-link-active' : '' }}">Lini Bisnis</a>
                    <a href="/contact" class="nav-link {{ $active === 'contact' ? 'nav-link-active' : '' }}">Kontak</a>
                </div>
            </div>
            
            <!-- Mobile menu button -->
            <div class="-mr-2 flex md:hidden">
                <button type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" aria-controls="mobile-menu" aria-expanded="false" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                    <span class="sr-only">Open main menu</span>
                    <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu -->
    <div class="hidden md:hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white shadow-lg">
            <a href="/" class="block px-3 py-2 rounded-md text-base font-medium {{ $active === 'home' ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Beranda</a>
            <a href="/visi-misi" class="block px-3 py-2 rounded-md text-base font-medium {{ $active === 'visi-misi' ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Visi & Misi</a>
            <a href="/milestones" class="block px-3 py-2 rounded-md text-base font-medium {{ $active === 'milestones' ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Sejarah</a>
            <a href="/line-of-business" class="block px-3 py-2 rounded-md text-base font-medium {{ $active === 'divisions' ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Lini Bisnis</a>
            <a href="/contact" class="block px-3 py-2 rounded-md text-base font-medium {{ $active === 'contact' ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Kontak</a>
        </div>
    </div>
</nav>