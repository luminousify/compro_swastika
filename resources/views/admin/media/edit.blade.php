@extends('admin.layouts.app')

@section('title', 'Edit Media')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <a href="{{ route('admin.media.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Media</a>
</li>
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">Edit</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Media Details</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Update media caption, flags, and ordering.
                    </p>
                    
                    <!-- Media Preview -->
                    <div class="mt-6">
                        @if($media->type->value === 'image')
                            <img src="{{ Storage::url($media->path_or_embed) }}" 
                                 alt="{{ $media->caption }}"
                                 class="w-full rounded-lg shadow">
                        @else
                            <div class="bg-gray-100 rounded-lg p-4">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600 text-center">
                                    Video File
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Media Info -->
                    <div class="mt-4 text-sm text-gray-600">
                        <p><strong>Type:</strong> {{ ucfirst($media->type->value) }}</p>
                        <p><strong>Entity:</strong> {{ class_basename($media->mediable_type) }}</p>
                        @if($media->mediable)
                            <p><strong>Item:</strong> {{ $media->mediable->name ?? $media->mediable->title ?? 'ID: ' . $media->mediable->id }}</p>
                        @endif
                        <p><strong>Created:</strong> {{ $media->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form method="POST" action="{{ route('admin.media.update', $media) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Caption -->
                            <div>
                                <label for="caption" class="block text-sm font-medium text-gray-700">
                                    Caption
                                </label>
                                <input type="text" name="caption" id="caption" value="{{ old('caption', $media->caption) }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('caption') border-red-500 @enderror"
                                       placeholder="Optional media caption">
                                @error('caption')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Flags -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Flags
                                </label>
                                <div class="space-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="flags[]" value="home_slider"
                                               {{ in_array('home_slider', old('flags', $media->flags ?? [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Home Slider</span>
                                    </label>
                                    <br>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="flags[]" value="featured"
                                               {{ in_array('featured', old('flags', $media->flags ?? [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Featured</span>
                                    </label>
                                </div>
                                @error('flags')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Check flags to mark this media for special display</p>
                            </div>
                            
                            <!-- Order -->
                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700">
                                    Display Order
                                </label>
                                <input type="number" name="order" id="order" value="{{ old('order', $media->order) }}"
                                       min="0"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('order') border-red-500 @enderror">
                                @error('order')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                            </div>
                        </div>
                        
                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('admin.media.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Media
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Delete Section -->
                <div class="mt-6">
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    Danger Zone
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>Deleting this media will permanently remove the file from storage.</p>
                                </div>
                                <div class="mt-4">
                                    <form method="POST" action="{{ route('admin.media.destroy', $media) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this media? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete Media
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection