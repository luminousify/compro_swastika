@extends('admin.layouts.app')

@section('title', 'Edit Product')

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
    <span class="ml-4 text-sm font-medium text-gray-900">Edit {{ $product->name }}</span>
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
                        Edit the product <strong>{{ $product->name }}</strong> in the {{ $division->name }} division.
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

                <form method="POST" action="{{ route('admin.products.update', [$division, $product]) }}" class="space-y-6" novalidate data-skip-form-enhancement="true" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-100">
                        <div class="px-6 py-8 space-y-8">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name" class="form-label required">Product Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name', $product->name) }}"
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
                                          @error('description') aria-invalid="true" @enderror>{{ old('description', $product->description) }}</textarea>
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
                                                Product images are now managed at the division level. To add images that appear in the product section slider, 
                                                go to <strong>Divisions → Edit → Products Section Images</strong>.
                                            </p>
                                            <a href="{{ route('admin.divisions.edit', $product->division) }}" class="inline-flex items-center mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Update Product
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
                        Permanently delete this product. This action cannot be undone and will remove all associated data.
                    </p>
                    <form method="POST" action="{{ route('admin.products.destroy', [$division, $product]) }}" 
                          onsubmit="return confirm('⚠️ WARNING: You are about to permanently delete the product \&quot;{{ $product->name }}\&quot;.\n\nThis action CANNOT be undone and will remove:\n• The product record\n• All associated data\n\nType \&quot;DELETE\&quot; to confirm you want to proceed.')" 
                          class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('images');
    const previewArea = document.getElementById('image-preview');
    let removedImages = [];
    
    if (imageInput && previewArea) {
        // Same image preview functionality as create form
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
        
        // Drag and drop functionality (same as create form)
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
    
    // Update file input
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

function removeExistingImage(mediaId, button) {
    if (confirm('Are you sure you want to remove this image? This will be saved when you update the product.')) {
        // Add to removal list
        const removeInput = document.getElementById('remove_images');
        const currentValue = removeInput.value;
        const newValue = currentValue ? currentValue + ',' + mediaId : mediaId;
        removeInput.value = newValue;
        
        // Add visual feedback - mark as "to be deleted"
        const imageDiv = button.closest('.relative');
        imageDiv.style.opacity = '0.5';
        imageDiv.style.position = 'relative';
        
        // Add "Will be deleted" overlay
        const overlay = document.createElement('div');
        overlay.className = 'absolute inset-0 bg-red-500 bg-opacity-70 flex items-center justify-center text-white text-sm font-medium rounded-lg';
        overlay.innerHTML = '<div class="text-center"><svg class="w-6 h-6 mx-auto mb-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zM8 8a1 1 0 012 0v3a1 1 0 11-2 0V8zm4 0a1 1 0 10-2 0v3a1 1 0 002 0V8z" clip-rule="evenodd"></path></svg>Will be deleted on save</div>';
        
        // Remove the delete button
        button.remove();
        
        // Add undo button
        const undoButton = document.createElement('button');
        undoButton.type = 'button';
        undoButton.className = 'absolute top-1 right-1 bg-blue-600 text-white p-1 rounded-full hover:bg-blue-700 transition-all duration-200';
        undoButton.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
        undoButton.onclick = function() { undoImageRemoval(mediaId, imageDiv, overlay, undoButton); };
        
        imageDiv.appendChild(overlay);
        imageDiv.appendChild(undoButton);
        
        // Show save reminder
        showSaveReminder();
    }
}

function undoImageRemoval(mediaId, imageDiv, overlay, undoButton) {
    // Remove from removal list
    const removeInput = document.getElementById('remove_images');
    const currentValue = removeInput.value;
    const ids = currentValue.split(',').filter(id => id !== mediaId.toString());
    removeInput.value = ids.join(',');
    
    // Restore visual state
    imageDiv.style.opacity = '1';
    overlay.remove();
    undoButton.remove();
    
    // Add back the delete button
    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'opacity-0 group-hover:opacity-100 bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition-all duration-200';
    deleteButton.onclick = function() { removeExistingImage(mediaId, deleteButton); };
    deleteButton.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
    
    const buttonContainer = imageDiv.querySelector('.absolute.inset-0.bg-black');
    buttonContainer.appendChild(deleteButton);
    
    hideSaveReminder();
}

function showSaveReminder() {
    let reminder = document.getElementById('save-reminder');
    if (!reminder) {
        reminder = document.createElement('div');
        reminder.id = 'save-reminder';
        reminder.className = 'fixed top-4 right-4 bg-yellow-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-pulse';
        reminder.innerHTML = '<div class="flex items-center"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>Don\'t forget to save your changes!</div>';
        document.body.appendChild(reminder);
    }
    reminder.style.display = 'block';
}

function hideSaveReminder() {
    const removeInput = document.getElementById('remove_images');
    if (!removeInput.value) {
        const reminder = document.getElementById('save-reminder');
        if (reminder) {
            reminder.style.display = 'none';
        }
    }
}
</script>
@endpush

@endsection