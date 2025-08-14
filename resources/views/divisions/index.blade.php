@extends('layouts.public', ['active' => 'divisions'])

@section('title', $seo['title'] ?? 'Our Divisions')
@section('description', $seo['description'] ?? '')

@section('content')
    <!-- Hero Section -->
    <section class="bg-blue-900 text-white py-16">
        <div class="container-custom">
            <h1 class="text-4xl md:text-5xl font-heading font-bold text-center">Line of Business</h1>
            <p class="text-center text-blue-200 mt-4 max-w-2xl mx-auto">Explore our specialized divisions delivering comprehensive solutions across multiple industries</p>
        </div>
    </section>
    
    <!-- Divisions Grid -->
    <section class="py-16">
        <div class="container-custom">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($divisions as $index => $division)
                    <div class="group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300">
                        @if($division->media->first())
                            <div class="aspect-16-9 overflow-hidden bg-gray-100">
                                <img src="{{ $division->media->first()->url }}" 
                                     alt="{{ $division->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @else
                            <div class="aspect-16-9 bg-gray-200 flex items-center justify-center">
                                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h2 class="text-2xl font-heading font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                    {{ $division->name }}
                                </h2>
                                <span class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 text-blue-600 rounded-full text-sm font-bold">
                                    {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                            
                            @if($division->description)
                                <p class="text-gray-600 mb-4 line-clamp-3">{{ $division->description }}</p>
                            @endif
                            
                            <!-- Stats -->
                            <div class="flex items-center gap-6 mb-4 text-sm text-gray-500">
                                @if($division->products->count() > 0)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <span>{{ $division->products->count() }} Products</span>
                                    </div>
                                @endif
                                @if($division->technologies->count() > 0)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                        <span>{{ $division->technologies->count() }} Technologies</span>
                                    </div>
                                @endif
                            </div>
                            
                            <a href="{{ route('divisions.show', $division->slug) }}" 
                               class="inline-flex items-center text-blue-600 font-medium hover:text-blue-700 transition-all group-hover:translate-x-2">
                                <span>Explore Division</span>
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($divisions->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-gray-500 text-lg">No divisions available at the moment.</p>
                </div>
            @endif
        </div>
    </section>
@endsection