@extends('layouts.public', ['active' => 'contact'])

@section('title', $seo['title'] ?? 'Hubungi Kami')
@section('description', $seo['description'] ?? '')

@section('content')
    <!-- Hero Section -->
    <section class="bg-blue-900 text-white py-16">
        <div class="container-custom">
            <h1 class="text-4xl md:text-5xl font-heading font-bold text-center">Hubungi Kami</h1>
            <p class="text-center text-blue-200 mt-4 max-w-2xl mx-auto">Kami siap membantu dan menjawab pertanyaan Anda. Kami menantikan kabar dari Anda.</p>
        </div>
    </section>
    
    <!-- Contact Content -->
    <section class="py-16">
        <div class="container-custom">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Information -->
                <div>
                    <h2 class="text-3xl font-heading font-bold text-gray-900 mb-8">Hubungi Kami</h2>
                    
                    <div class="space-y-6">
                        @if($contact['company_name'])
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Perusahaan</h3>
                                    <p class="text-gray-600">{{ $contact['company_name'] }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($contact['address'])
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Alamat</h3>
                                    <p class="text-gray-600">{{ $contact['address'] }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($contact['phone'])
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Phone</h3>
                                    <p class="text-gray-600">
                                        <a href="tel:{{ $contact['phone'] }}" class="hover:text-blue-600 transition-colors">
                                            {{ $contact['phone'] }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        @endif
                        
                        @if($contact['email'])
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Email</h3>
                                    <p class="text-gray-600">
                                        <a href="mailto:{{ $contact['email'] }}" class="hover:text-blue-600 transition-colors">
                                            {{ $contact['email'] }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Embedded Map -->
                    @if($contact['map'])
                        <div class="mt-10">
                            <h3 class="text-2xl font-heading font-semibold text-gray-900 mb-4">Find Us</h3>
                            <div class="rounded-lg overflow-hidden shadow-lg border border-gray-200">
                                <iframe 
                                    src="{{ $contact['map'] }}" 
                                    width="100%" 
                                    height="350" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Contact Form -->
                <div>
                    <h2 class="text-3xl font-heading font-bold text-gray-900 mb-8">Send us a Message</h2>
                    
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ session('success') }}
                            </div>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Honeypot field for spam protection -->
                        <input type="text" name="website" style="display: none;" tabindex="-1" autocomplete="off">
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name *
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                required
                                aria-required="true"
                                aria-label="Full Name"
                                @error('name') aria-describedby="name-error" @enderror
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                placeholder="Your full name">
                            @error('name')
                                <p id="name-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address *
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                required
                                aria-required="true"
                                aria-label="Email Address"
                                @error('email') aria-describedby="email-error" @enderror
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                                placeholder="your.email@example.com">
                            @error('email')
                                <p id="email-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                Subject *
                            </label>
                            <input 
                                type="text" 
                                id="subject" 
                                name="subject" 
                                value="{{ old('subject') }}"
                                required
                                aria-required="true"
                                aria-label="Subject"
                                @error('subject') aria-describedby="subject-error" @enderror
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('subject') border-red-500 @enderror"
                                placeholder="What is this about?">
                            @error('subject')
                                <p id="subject-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                Message *
                            </label>
                            <textarea 
                                id="message" 
                                name="message" 
                                rows="5" 
                                required
                                aria-required="true"
                                aria-label="Message"
                                @error('message') aria-describedby="message-error" @enderror
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('message') border-red-500 @enderror"
                                placeholder="Tell us more about your inquiry...">{{ old('message') }}</textarea>
                            @error('message')
                                <p id="message-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <button 
                                type="submit" 
                                class="w-full btn btn-primary justify-center">
                                <span class="mr-2">Send Message</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection