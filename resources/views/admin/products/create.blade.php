@extends('admin.layouts.app')

@section('title', 'Create Product')

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
    <span class="ml-4 text-sm font-medium text-gray-900">Create Product</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Product Information</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Create a new product for the <strong>{{ $division->name }}</strong> division.
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

                <form method="POST" action="{{ route('admin.products.store', $division) }}" class="space-y-6" novalidate data-skip-form-enhancement="true" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-100">
                        <div class="px-6 py-8 space-y-8">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name" class="form-label required">Product Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name') }}"
                                       required
                                       maxlength="255"
                                       class="form-input @error('name') error @enderror"
                                       placeholder="Enter product name"
                                       aria-describedby="name-help @error('name') name-error @enderror"
                                       @error('name') aria-invalid="true" @enderror>
                                <div id="name-help" class="form-help">
                                    Choose a clear, descriptive name for this product
                                </div>
                                @error('name')
                                    <div id="name-error" class="form-error" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group">
                                <label for="description" class="form-label required">Product Description</label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="6"
                                          required
                                          maxlength="2000"
                                          class="form-textarea @error('description') error @enderror"
                                          placeholder="Provide a detailed description of the product, including features, specifications, and benefits"
                                          aria-describedby="description-help @error('description') description-error @enderror"
                                          @error('description') aria-invalid="true" @enderror>{{ old('description') }}</textarea>
                                <div id="description-help" class="form-help">
                                    Include key features, specifications, and benefits. Maximum 2000 characters.
                                </div>
                                @error('description')
                                    <div id="description-error" class="form-error" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Image Management Notice -->
                            <div class="form-group">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <h4 class="text-sm font-medium text-blue-900">Section Images</h4>
                                            <p class="text-sm text-blue-700 mt-1">
                                                Product images are managed at the division level. After creating this product, go to 
                                                <strong>Divisions → Edit → Products Section Images</strong> to add images for the product section slider.
                                            </p>
                                            <a href="{{ route('admin.divisions.edit', $division) }}" class="inline-flex items-center mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                Manage Division Images
                                            </a>
                                        </div>
                                    </div>
                                </div>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Create Product
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('images');
    const previewArea = document.getElementById('image-preview');
    
    if (imageInput && previewArea) {
        imageInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            previewArea.innerHTML = '';
            
            if (files.length > 0) {
                previewArea.classList.remove('hidden');
                
                files.forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewItem = document.createElement('div');
                            previewItem.className = 'relative group';
                            previewItem.innerHTML = `
                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                    <img src="${e.target.result}" 
                                         alt="Preview ${index + 1}" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 rounded-lg flex items-center justify-center">
                                    <button type="button" 
                                            class="opacity-0 group-hover:opacity-100 bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition-all duration-200"
                                            onclick="removePreview(this, ${index})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            `;
                            previewArea.appendChild(previewItem);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                previewArea.classList.add('hidden');
            }
        });
        
        // Drag and drop functionality
        const dropZone = imageInput.closest('.border-dashed');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        dropZone.addEventListener('drop', handleDrop, false);
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight(e) {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        }
        
        function unhighlight(e) {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            imageInput.files = files;
            imageInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
});

function removePreview(button, index) {
    const previewArea = document.getElementById('image-preview');
    const imageInput = document.getElementById('images');
    
    // Remove preview item
    button.closest('.relative').remove();
    
    // Update file input (create new FileList without the removed file)
    const dt = new DataTransfer();
    const files = Array.from(imageInput.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    imageInput.files = dt.files;
    
    // Hide preview area if no files
    if (imageInput.files.length === 0) {
        previewArea.classList.add('hidden');
    }
}
</script>
@endpush

@endsection