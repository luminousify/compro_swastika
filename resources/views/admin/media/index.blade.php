@extends('admin.layouts.app')

@section('title', 'Media Management')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">Media</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Media Management</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Manage images and videos across all content
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.media.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Upload Media
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filters -->
        <div class="mb-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" action="{{ route('admin.media.index') }}" class="sm:flex sm:items-center sm:space-x-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <label for="search" class="sr-only">Search media</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Search by caption..."
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    
                    <!-- Type Filter -->
                    <div class="mt-3 sm:mt-0">
                        <label for="type" class="sr-only">Filter by type</label>
                        <select name="type" id="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">All Types</option>
                            <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Images</option>
                            <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Videos</option>
                        </select>
                    </div>
                    
                    <!-- Entity Type Filter -->
                    <div class="mt-3 sm:mt-0">
                        <label for="entity_type" class="sr-only">Filter by entity</label>
                        <select name="entity_type" id="entity_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">All Entities</option>
                            <option value="{{ App\Models\Division::class }}" {{ request('entity_type') === App\Models\Division::class ? 'selected' : '' }}>Divisions</option>
                            <option value="{{ App\Models\Product::class }}" {{ request('entity_type') === App\Models\Product::class ? 'selected' : '' }}>Products</option>
                            <option value="{{ App\Models\Technology::class }}" {{ request('entity_type') === App\Models\Technology::class ? 'selected' : '' }}>Technologies</option>
                            <option value="{{ App\Models\Machine::class }}" {{ request('entity_type') === App\Models\Machine::class ? 'selected' : '' }}>Machines</option>
                        </select>
                    </div>
                    
                    <!-- View Mode -->
                    <div class="mt-3 sm:mt-0">
                        <div class="flex rounded-md shadow-sm">
                            <button type="submit" name="view" value="list" class="px-3 py-2 border {{ $viewMode === 'list' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border-gray-300 rounded-l-md text-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <button type="submit" name="view" value="gallery" class="px-3 py-2 border {{ $viewMode === 'gallery' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border-gray-300 rounded-r-md text-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3 sm:mt-0">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                    </div>
                    
                    @if(request('search') || request('type') || request('entity_type'))
                        <div class="mt-3 sm:mt-0">
                            <a href="{{ route('admin.media.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Clear
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        @if($viewMode === 'gallery')
            <!-- Gallery View -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 gallery-view">
                @forelse($media as $item)
                    <div class="relative group">
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                            @if($item->type->value === 'image')
                                <img src="{{ Storage::url($item->path_or_embed) }}" 
                                     alt="{{ $item->caption }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity rounded-lg">
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.media.edit', $item) }}" class="p-2 bg-white rounded-full text-gray-700 hover:text-gray-900">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.media.destroy', $item) }}" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this media?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-white rounded-full text-red-600 hover:text-red-800">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Caption -->
                        @if($item->caption)
                            <div class="mt-2">
                                <p class="text-sm text-gray-700 truncate">{{ $item->caption }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No media found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by uploading images or videos.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            <!-- List View -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Preview
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Caption
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Entity
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Flags
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($media as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="h-16 w-16 rounded-lg overflow-hidden bg-gray-100">
                                        @if($item->type->value === 'image')
                                            <img src="{{ Storage::url($item->path_or_embed) }}" 
                                                 alt="{{ $item->caption }}"
                                                 class="h-16 w-16 object-cover">
                                        @else
                                            <div class="h-16 w-16 flex items-center justify-center bg-gray-200">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $item->caption ?: '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $item->type->value === 'image' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst($item->type->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->mediable)
                                        <div class="text-sm text-gray-900">
                                            {{ class_basename($item->mediable_type) }}:
                                            {{ $item->mediable->name ?? $item->mediable->title ?? $item->mediable->id }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->flags && count($item->flags) > 0)
                                        @foreach($item->flags as $flag)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $flag }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.media.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this media?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                    No media found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Pagination -->
        @if($media->hasPages())
            <div class="mt-6">
                {{ $media->links() }}
            </div>
        @endif
    </div>
</div>
@endsection