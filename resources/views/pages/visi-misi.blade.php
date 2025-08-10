@extends('layouts.public', ['active' => 'visi-misi'])

@section('title', $seo['title'] ?? 'Visi & Misi')
@section('description', $seo['description'] ?? '')

@section('content')
    <!-- Hero Section -->
    <section class="bg-blue-900 text-white py-16">
        <div class="container-custom">
            <h1 class="text-4xl md:text-5xl font-heading font-bold text-center">Visi & Misi</h1>
            <p class="text-center text-blue-200 mt-4 max-w-2xl mx-auto">Our vision and mission guide us in delivering excellence and creating lasting value for our stakeholders</p>
        </div>
    </section>
    
    <!-- Content Section -->
    <section class="py-16">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                <!-- Vision -->
                <div class="mb-12">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                        <h2 class="text-3xl font-heading font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Visi
                        </h2>
                        <div class="prose prose-lg max-w-none text-gray-600">
                            {!! $visi !!}
                        </div>
                    </div>
                </div>
                
                <!-- Mission -->
                <div class="mb-12">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                        <h2 class="text-3xl font-heading font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Misi
                        </h2>
                        <div class="prose prose-lg max-w-none text-gray-600">
                            {!! $misi !!}
                        </div>
                    </div>
                </div>
                
                <!-- Call to Action -->
                <div class="text-center">
                    <a href="/contact" class="btn btn-primary">
                        <span class="mr-2">Start Your Journey With Us</span>
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection