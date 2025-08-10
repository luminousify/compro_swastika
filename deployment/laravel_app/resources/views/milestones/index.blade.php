@extends('layouts.public', ['active' => 'milestones'])

@section('title', $seo['title'] ?? 'Our Journey')
@section('description', $seo['description'] ?? '')

@section('content')
    <!-- Hero Section -->
    <section class="bg-blue-900 text-white py-16">
        <div class="container-custom">
            <h1 class="text-4xl md:text-5xl font-heading font-bold text-center">Our Journey</h1>
            <p class="text-center text-blue-200 mt-4 max-w-2xl mx-auto">Tracing our path of growth, innovation, and excellence through the years</p>
        </div>
    </section>
    
    <!-- Milestones Section -->
    <section class="py-16 bg-gray-50">
        <div class="container-custom">
            @if($milestones->isEmpty())
                <div class="text-center py-12">
                    <p class="text-gray-500">No milestones to display yet.</p>
                </div>
            @else
                <div class="max-w-4xl mx-auto">
                    @foreach($groupedMilestones as $decade => $decadeMilestones)
                        <div class="mb-12">
                            <h2 class="text-3xl font-heading font-bold mb-6 text-blue-600">{{ $decade }}</h2>
                            
                            <div class="space-y-6 border-l-4 border-blue-200 pl-8 ml-2">
                                @foreach($decadeMilestones as $milestone)
                                    <div class="relative">
                                        <div class="absolute -left-10 w-5 h-5 bg-blue-600 rounded-full border-4 border-white"></div>
                                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                                            <div class="text-2xl font-bold text-gray-900 mb-3">{{ $milestone->year }}</div>
                                            <div class="prose prose-lg text-gray-600 max-w-none">
                                                {!! $milestone->text !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Call to Action -->
                    <div class="text-center mt-12">
                        <p class="text-gray-600 mb-6">Be part of our continuing journey</p>
                        <a href="/contact" class="btn btn-primary">
                            <span class="mr-2">Contact Us</span>
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection