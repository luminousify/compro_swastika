@extends('admin.layouts.app')

@section('title', 'Edit Technology')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <a href="{{ route('admin.divisions.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Divisions</a>
</li>
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <a href="{{ route('admin.divisions.show', $division) }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ $division->name }}</a>
</li>
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">Edit {{ $technology->name }}</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Technology Information</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Edit the technology <strong>{{ $technology->name }}</strong> in the {{ $division->name }} division.
                    </p>
                </div>
            </div>
            
            <div class="mt-5 md:mt-0 md:col-span-2">
                @if($errors->any())
                    <div class="alert alert-error mb-6" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.technologies.update', [$division, $technology]) }}" class="space-y-6" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-100">
                        <div class="px-6 py-8 space-y-8">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name" class="form-label required">Technology Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name', $technology->name) }}"
                                       required
                                       maxlength="255"
                                       class="form-input @error('name') error @enderror"
                                       placeholder="Enter technology name"
                                       aria-describedby="name-help @error('name') name-error @enderror"
                                       @error('name') aria-invalid="true" @enderror>
                                <div id="name-help" class="form-help">
                                    Choose a clear, descriptive name for this technology
                                </div>
                                @error('name')
                                    <div id="name-error" class="form-error" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group">
                                <label for="description" class="form-label required">Technology Description</label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="6"
                                          required
                                          maxlength="2000"
                                          class="form-textarea @error('description') error @enderror"
                                          placeholder="Provide a detailed description of the technology, including capabilities, applications, and advantages"
                                          aria-describedby="description-help @error('description') description-error @enderror"
                                          @error('description') aria-invalid="true" @enderror>{{ old('description', $technology->description) }}</textarea>
                                <div id="description-help" class="form-help">
                                    Include capabilities, applications, and competitive advantages. Maximum 2000 characters.
                                </div>
                                @error('description')
                                    <div id="description-error" class="form-error" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                            <a href="{{ route('admin.divisions.show', $division) }}" 
                               class="btn btn-ghost">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Update Technology
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Delete Section -->
                <div class="mt-8 bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-red-900">Danger Zone</h3>
                    </div>
                    <p class="text-red-800 mb-4">
                        Permanently delete this technology. This action cannot be undone and will remove all associated data.
                    </p>
                    <form method="POST" action="{{ route('admin.technologies.destroy', [$division, $technology]) }}" 
                          onsubmit="return confirm('⚠️ WARNING: You are about to permanently delete the technology \&quot;{{ $technology->name }}\&quot;.\n\nThis action CANNOT be undone and will remove:\n• The technology record\n• All associated data\n\nType \&quot;DELETE\&quot; to confirm you want to proceed.')" 
                          class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Technology
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection