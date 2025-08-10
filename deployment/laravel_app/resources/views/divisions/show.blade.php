@extends('layouts.public', ['active' => 'divisions'])

@section('title', $seo['title'] ?? $division->name)
@section('description', $seo['description'] ?? '')

@section('content')
    <!-- Hero Section -->
    <section class="bg-blue-900 text-white py-16">
        <div class="container-custom">
            <div class="flex items-center justify-center mb-4">
                <a href="/line-of-business" class="text-blue-200 hover:text-white mr-2">Line of Business</a>
                <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="ml-2">{{ $division->name }}</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-heading font-bold text-center">{{ $division->name }}</h1>
            @if($division->description)
                <p class="text-center text-blue-200 mt-4 max-w-3xl mx-auto">{{ $division->description }}</p>
            @endif
        </div>
    </section>

    <!-- Media Gallery -->
    @if($division->media->count() > 0)
        <section class="py-8 bg-gray-50">
            <div class="container-custom">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($division->media as $media)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <img src="{{ Storage::url($media->file_path) }}" 
                                 alt="{{ $media->caption ?? $division->name }}" 
                                 class="w-full h-64 object-cover">
                            @if($media->caption)
                                <div class="p-4">
                                    <p class="text-sm text-gray-600">{{ $media->caption }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Content Sections -->
    <section class="py-16">
        <div class="container-custom">
            <!-- Products Section -->
            @if($division->products->count() > 0)
                <div class="mb-16">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-heading font-bold text-gray-900">Our Products</h2>
                        <p class="text-gray-600 mt-2">Explore our comprehensive range of products</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($division->products as $product)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $product->name }}</h3>
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                @if($product->description)
                                    <p class="text-gray-600 leading-relaxed">{{ $product->description }}</p>
                                @endif
                                @if($product->specifications)
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="text-sm text-gray-500">{{ Str::limit($product->specifications, 100) }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Technologies Section -->
            @if($division->technologies->count() > 0)
                <div class="mb-16">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-heading font-bold text-gray-900">Technologies</h2>
                        <p class="text-gray-600 mt-2">Advanced technologies powering our solutions</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($division->technologies as $technology)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $technology->name }}</h3>
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                @if($technology->description)
                                    <p class="text-gray-600 leading-relaxed">{{ $technology->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Machines Section -->
            @if($division->machines->count() > 0)
                <div class="mb-16">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-heading font-bold text-gray-900">Machinery & Equipment</h2>
                        <p class="text-gray-600 mt-2">State-of-the-art machinery for optimal performance</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($division->machines as $machine)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $machine->name }}</h3>
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                @if($machine->description)
                                    <p class="text-gray-600 leading-relaxed">{{ $machine->description }}</p>
                                @endif
                                @if($machine->specifications)
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="text-sm text-gray-500">{{ Str::limit($machine->specifications, 100) }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Call to Action -->
            <div class="text-center mt-12 p-8 bg-gray-50 rounded-lg">
                <h3 class="text-2xl font-heading font-bold text-gray-900 mb-4">Interested in {{ $division->name }}?</h3>
                <p class="text-gray-600 mb-6">Get in touch with us to learn more about our solutions</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/contact" class="btn btn-primary">
                        <span class="mr-2">Contact Us</span>
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="/line-of-business" class="btn bg-gray-200 text-gray-700 hover:bg-gray-300">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                        </svg>
                        <span>View All Divisions</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection