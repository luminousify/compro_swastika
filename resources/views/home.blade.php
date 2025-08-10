<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] ?? 'Home' }}</title>
    <meta name="description" content="{{ $seo['description'] ?? '' }}">
    
    <!-- Mobile Safari specific meta tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ \App\Models\Setting::getValue('company_name', 'Company') }}">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Performance hints -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="{{ $seo['title'] ?? 'Home' }}">
    <meta property="og:description" content="{{ $seo['description'] ?? '' }}">
    @if(!empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <!-- Skip to main content link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded z-50">
        Skip to main content
    </a>
    
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
                        <a href="/" class="nav-link nav-link-active">Home</a>
                        <a href="/visi-misi" class="nav-link">Visi & Misi</a>
                        <a href="/milestones" class="nav-link">Milestones</a>
                        <a href="/line-of-business" class="nav-link">Line of Business</a>
                        <a href="/contact" class="nav-link">Contact</a>
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
                <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-blue-600 bg-blue-50">Home</a>
                <a href="/visi-misi" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Visi & Misi</a>
                <a href="/milestones" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Milestones</a>
                <a href="/line-of-business" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Line of Business</a>
                <a href="/contact" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Contact</a>
            </div>
        </div>
    </nav>
    
    <main id="main-content">
    <!-- Hero Section with Background Image Support -->
    <section class="hero relative min-h-[600px] flex items-center overflow-hidden">
        <!-- Background Image Layer -->
        <div class="absolute inset-0">
            <!-- Option 1: Use a static image (uncomment and add your image) -->
            <!-- <img src="/images/hero-bg.jpg" alt="Hero Background" class="w-full h-full object-cover"> -->
            
            <!-- Option 2: Use Unsplash for a professional placeholder image -->
            <img src="https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?q=80&w=2070&auto=format&fit=crop" 
                 alt="Industrial Engineering Background" 
                 class="w-full h-full object-cover">
            
            <!-- Dark Overlay for better text contrast -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/60"></div>
        </div>
        
        <!-- Optional Pattern Overlay for texture -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        
        <div class="container-custom relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <!-- Professional Badge -->
                <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
                    <span class="text-white font-medium">Excellence in Engineering</span>
                </div>
                
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-heading font-bold text-white mb-6 animate-fade-in-up">
                    <span class="block">{{ $hero['headline'] }}</span>
                </h1>
                
                <p class="text-xl md:text-2xl text-white/90 mb-10 animate-fade-in-up animate-delay-200">
                    {{ $hero['subheadline'] }}
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up animate-delay-300">
                    <a href="/contact" class="btn bg-white text-blue-900 hover:bg-gray-100 text-lg px-8 py-4 shadow-lg">
                        <span class="mr-2">Get Started</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#divisions" class="btn bg-transparent text-white border-2 border-white hover:bg-white hover:text-blue-900 text-lg px-8 py-4">
                        Explore Services
                    </a>
                </div>
                
                <!-- Trust Indicators -->
                <div class="mt-12 flex flex-wrap items-center justify-center gap-8 text-white/80 animate-fade-in animate-delay-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H9a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 011-1h.01a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h.01a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h.01a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>25+ Years Experience</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <span>500+ Happy Clients</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>ISO 9001 Certified</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce-slow">
            <svg class="w-6 h-6 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </div>
    </section>

    <!-- Slider Section -->
    @if($sliders->count() > 0)
    <section class="slider py-0">
        <div class="container mx-auto px-4">
            <!-- Swiper -->
            <div class="swiper-container h-64 md:h-96 lg:h-[500px] rounded-lg overflow-hidden shadow-lg" 
                 data-swiper-autoplay="5000" 
                 data-swiper-pause-on-hover="true"
                 data-swiper-keyboard="true"
                 data-swiper-touch="true"
                 data-swiper-simulate-touch="true"
                 style="touch-action: pan-y; background-color: #f9fafb;"
                 tabindex="0"
                 role="region"
                 aria-roledescription="carousel"
                 aria-label="Image carousel"
                 aria-live="polite">
                
                <!-- Loading State -->
                <div class="swiper-loading bg-gray-200 animate-pulse w-full h-full absolute inset-0 z-10 flex items-center justify-center">
                    <div class="text-gray-400">Loading...</div>
                </div>
                
                <div class="swiper-wrapper">
                    @foreach($sliders as $slider)
                        <div class="swiper-slide">
                            <div class="relative w-full h-full">
                                <img src="{{ Storage::url($slider->path_or_embed) }}" 
                                     alt="{{ $slider->caption ?? 'Slide image' }}" 
                                     class="w-full h-full object-cover"
                                     loading="lazy"
                                     decoding="async"
                                     onload="this.parentElement.parentElement.parentElement.querySelector('.swiper-loading')?.remove()">
                                @if($slider->caption)
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                                        <p class="text-white text-lg font-medium">{{ $slider->caption }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                
                <!-- Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>
    @else
    <!-- Empty State for Slider -->
    <section class="slider-empty hidden">
        <div class="h-64 md:h-96 lg:h-[500px] bg-gray-100 flex items-center justify-center">
            <p class="text-gray-400">No slider images available</p>
        </div>
    </section>
    @endif

    <!-- About Section -->
    @if($about_snippet)
    <section class="about section relative overflow-hidden bg-gray-50">
        <div class="container-custom relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-block mb-4">
                    <span class="text-blue-600 font-semibold uppercase tracking-wider text-sm">Who We Are</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-heading font-bold mb-6 text-gray-900">About Us</h2>
                <p class="text-lg md:text-xl text-dark-600 leading-relaxed">
                    {{ $about_snippet }}
                </p>
                
                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-12">
                    <div class="text-center">
                        <div class="text-4xl font-bold gradient-text-accent mb-2">25+</div>
                        <div class="text-dark-500">Years</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold gradient-text-accent mb-2">500+</div>
                        <div class="text-dark-500">Projects</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold gradient-text-accent mb-2">50+</div>
                        <div class="text-dark-500">Experts</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold gradient-text-accent mb-2">100%</div>
                        <div class="text-dark-500">Success</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Divisions Section -->
    @if($divisions->count() > 0)
    <section id="divisions" class="divisions section bg-white">
        <div class="container-custom">
            <div class="text-center mb-12">
                <span class="text-blue-600 font-semibold uppercase tracking-wider text-sm">What We Do</span>
                <h2 class="text-4xl md:text-5xl font-heading font-bold mt-4 mb-4 text-gray-900">Our Divisions</h2>
                <p class="text-dark-600 text-lg max-w-2xl mx-auto">Explore our specialized divisions delivering excellence across industries</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($divisions as $index => $division)
                    <div class="group card card-hover bg-white border border-gray-200 relative overflow-hidden" style="animation-delay: {{ $index * 100 }}ms">
                        <!-- Icon/Number -->
                        <div class="absolute top-6 right-6 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        
                        <div class="relative p-8">
                            <h3 class="text-2xl font-heading font-semibold mb-3 text-dark-800 group-hover:text-primary-600 transition-colors">
                                {{ $division->name }}
                            </h3>
                            
                            @if($division->description)
                                <p class="text-dark-600 leading-relaxed mb-6">
                                    {{ Str::limit($division->description, 120) }}
                                </p>
                            @endif
                            
                            <a href="{{ route('divisions.show', $division->slug) }}" class="inline-flex items-center text-blue-600 font-medium hover:text-blue-700 transition-all group-hover:translate-x-2">
                                <span>Learn More</span>
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                        
                        <!-- Bottom Border Accent -->
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Milestones Section -->
    @if($milestones->count() > 0)
    <section class="milestones section relative bg-gray-50">
        <div class="container-custom">
            <div class="text-center mb-12">
                <span class="text-blue-600 font-semibold uppercase tracking-wider text-sm">Our History</span>
                <h2 class="text-4xl md:text-5xl font-heading font-bold mt-4 mb-4 text-gray-900">Milestones & Achievements</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">A journey of innovation and excellence</p>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <!-- Timeline -->
                <div class="relative">
                    <!-- Vertical Line -->
                    <div class="absolute left-0 md:left-1/2 transform md:-translate-x-1/2 w-0.5 h-full bg-gray-300"></div>
                    
                    @foreach($milestones as $index => $milestone)
                        <div class="relative flex items-center mb-8 {{ $index % 2 == 0 ? 'md:flex-row' : 'md:flex-row-reverse' }}">
                            <!-- Content -->
                            <div class="flex-1 {{ $index % 2 == 0 ? 'md:pr-12 md:text-right' : 'md:pl-12' }}">
                                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow ml-12 md:ml-0">
                                    <div class="text-3xl font-bold text-blue-600 mb-3">{{ $milestone->year }}</div>
                                    <div class="text-gray-600 leading-relaxed">{!! $milestone->text !!}</div>
                                </div>
                            </div>
                            
                            <!-- Center Dot -->
                            <div class="absolute left-0 md:left-1/2 transform md:-translate-x-1/2 w-4 h-4 bg-white border-4 border-blue-600 rounded-full"></div>
                            
                            <!-- Empty Space for Alternating Layout -->
                            <div class="hidden md:block flex-1"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Clients Section -->
    @if($clients->count() > 0)
    <section class="clients section bg-gray-50">
        <div class="container-custom">
            <div class="text-center mb-12">
                <span class="text-blue-600 font-semibold uppercase tracking-wider text-sm">Trusted By</span>
                <h2 class="text-4xl md:text-5xl font-heading font-bold mt-4 mb-4 text-gray-900">Our Valued Clients</h2>
                <p class="text-dark-600 text-lg max-w-2xl mx-auto">Proud to work with industry leaders</p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8">
                @foreach($clients as $index => $client)
                    <div class="group animate-fade-in" style="animation-delay: {{ $index * 50 }}ms">
                        <div class="bg-white rounded-xl p-6 shadow-soft hover:shadow-soft-lg transition-all duration-300 hover:-translate-y-1">
                            @if($client->logo_path)
                                <img src="{{ Storage::url($client->logo_path) }}" 
                                     alt="{{ $client->name }}" 
                                     class="h-16 w-full object-contain filter grayscale hover:grayscale-0 transition-all duration-300" 
                                     loading="lazy" 
                                     decoding="async">
                            @else
                                <div class="h-16 flex items-center justify-center">
                                    <span class="text-dark-400 font-medium">{{ $client->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Contact CTA -->
    <section class="contact-cta relative py-24 overflow-hidden bg-blue-900">
        <!-- Pattern Background -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        
        <div class="container-custom relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
                    <span class="text-white font-medium">Let's Connect</span>
                </div>
                
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-heading font-bold text-white mb-6">
                    Ready to Transform Your Business?
                </h2>
                <p class="text-xl md:text-2xl text-white/90 mb-10">
                    Let's discuss how we can help you achieve your goals and drive innovation.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/contact" class="btn bg-white text-blue-900 hover:bg-gray-100 text-lg px-10 py-4 shadow-lg transform hover:scale-105">
                        <span class="mr-2">Start Your Project</span>
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="tel:+1234567890" class="btn bg-transparent text-white border-2 border-white hover:bg-white hover:text-blue-900 text-lg px-10 py-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Call Us Now
                    </a>
                </div>
            </div>
        </div>
    </section>
    </main>
</body>
</html>