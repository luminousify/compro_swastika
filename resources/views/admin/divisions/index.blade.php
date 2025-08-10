@extends('admin.layouts.app')

@section('title', 'Divisions')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">Divisions</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Division Management</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Manage company divisions and their content
                </p>
            </div>
            <a href="{{ route('admin.divisions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Division
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Search -->
        <div class="mb-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" action="{{ route('admin.divisions.index') }}" class="sm:flex sm:items-center">
                    <div class="flex-1">
                        <label for="search" class="sr-only">Search divisions</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Search by name or description..."
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mt-3 sm:mt-0 sm:ml-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                    </div>
                    @if(request('search'))
                        <div class="mt-3 sm:mt-0 sm:ml-2">
                            <a href="{{ route('admin.divisions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Clear
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Divisions Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3" id="divisions-grid">
            @forelse($divisions as $division)
                <div class="bg-white overflow-hidden shadow rounded-lg division-card" data-division-id="{{ $division->id }}">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $division->name }}
                            </h3>
                            <div class="flex items-center space-x-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Order: {{ $division->order }}
                                </span>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                            {{ Str::limit($division->description, 100) }}
                        </p>
                        
                        <!-- Content Counts -->
                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div class="text-center">
                                <div class="text-2xl font-semibold text-indigo-600">{{ $division->products_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Products</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-semibold text-green-600">{{ $division->technologies_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Technologies</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-semibold text-purple-600">{{ $division->machines_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500">Machines</div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.divisions.show', $division) }}" class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                View
                            </a>
                            <a href="{{ route('admin.divisions.edit', $division) }}" class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-indigo-300 shadow-sm text-sm font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.divisions.destroy', $division) }}" 
                                  class="flex-1"
                                  onsubmit="return confirm('Are you sure you want to delete this division?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No divisions</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new division.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.divisions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Create Division
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection