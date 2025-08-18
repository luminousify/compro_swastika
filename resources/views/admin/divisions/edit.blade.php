@extends('admin.layouts.app')

@section('title', 'Edit Division')

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
    <span class="ml-4 text-sm font-medium text-gray-900">Edit {{ $division->name }}</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Division Information</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Update the division details and manage its content.
                    </p>
                    <p class="mt-3 text-sm text-gray-600">
                        The slug is used for URLs and should be unique.
                    </p>
                </div>
            </div>
            
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form method="POST" action="{{ route('admin.divisions.update', $division) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Division Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name', $division->name) }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('name') border-red-500 @enderror"
                                       required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Slug -->
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700">
                                    URL Slug <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $division->slug) }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('slug') border-red-500 @enderror"
                                       required>
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Used in URLs, must be unique</p>
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Description <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" id="description" rows="4"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('description') border-red-500 @enderror"
                                          required>{{ old('description', $division->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Current Hero Image -->
                            @if($division->media->where('type', 'image')->first())
                                <div id="current-image-section">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Current Hero Image
                                    </label>
                                    <div class="flex items-start space-x-4">
                                        <div class="relative inline-block">
                                            <img src="{{ Storage::url($division->media->where('type', 'image')->first()->path_or_embed) }}" 
                                                 alt="{{ $division->name }}"
                                                 class="h-32 w-auto rounded-md shadow">
                                            <span class="absolute top-0 right-0 -mt-2 -mr-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Current
                                            </span>
                                        </div>
                                        <div class="flex flex-col space-y-2">
                                            <button type="button" 
                                                    onclick="removeCurrentImage({{ $division->media->where('type', 'image')->first()->id }})"
                                                    class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Remove Image
                                            </button>
                                            <p class="text-xs text-gray-500">
                                                This will remove the current hero image
                                            </p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="remove_current_image" id="remove_current_image" value="">
                                </div>
                            @endif
                            
                            <!-- Hero Image Upload -->
                            <div>
                                <label for="hero_image" class="block text-sm font-medium text-gray-700">
                                    {{ $division->media->where('type', 'image')->first() ? 'Replace' : 'Add' }} Hero Image
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="hero_image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a file</span>
                                                <input id="hero_image" name="hero_image" type="file" class="sr-only" accept="image/*">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            PNG, JPG, GIF up to 10MB (max 4096x4096)
                                        </p>
                                    </div>
                                </div>
                                @error('hero_image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Products Section Images -->
                            <div>
                                <label for="product_images" class="block text-sm font-medium text-gray-700">Products Section Images</label>
                                @php
                                    $productSectionMedia = $division->media->where('collection', 'products');
                                @endphp
                                
                                @if($productSectionMedia->count() > 0)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Current Product Section Images:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        @foreach($productSectionMedia as $media)
                                            <div class="relative group">
                                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                                    <img src="{{ $media->url }}" 
                                                         alt="Product section image" 
                                                         class="w-full h-full object-cover">
                                                </div>
                                                <button type="button" 
                                                        onclick="removeSectionImage({{ $media->id }}, this, 'products')"
                                                        class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full hover:bg-red-700 transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="product_images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload images</span>
                                                <input id="product_images" 
                                                       name="product_images[]" 
                                                       type="file" 
                                                       class="sr-only" 
                                                       multiple 
                                                       accept="image/*">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">Images for the Products section slider</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Upload 1-5 images that represent all products in this division.</p>
                                @error('product_images')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Technologies Section Images -->
                            <div>
                                <label for="technology_images" class="block text-sm font-medium text-gray-700">Technologies Section Images</label>
                                @php
                                    $technologySectionMedia = $division->media->where('collection', 'technologies');
                                @endphp
                                
                                @if($technologySectionMedia->count() > 0)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Current Technology Section Images:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        @foreach($technologySectionMedia as $media)
                                            <div class="relative group">
                                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                                    <img src="{{ $media->url }}" 
                                                         alt="Technology section image" 
                                                         class="w-full h-full object-cover">
                                                </div>
                                                <button type="button" 
                                                        onclick="removeSectionImage({{ $media->id }}, this, 'technologies')"
                                                        class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full hover:bg-red-700 transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="technology_images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload images</span>
                                                <input id="technology_images" 
                                                       name="technology_images[]" 
                                                       type="file" 
                                                       class="sr-only" 
                                                       multiple 
                                                       accept="image/*">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">Images for the Technologies section slider</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Upload 1-5 images that represent all technologies in this division.</p>
                                @error('technology_images')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Machines Section Images -->
                            <div>
                                <label for="machine_images" class="block text-sm font-medium text-gray-700">Machinery & Equipment Section Images</label>
                                @php
                                    $machineSectionMedia = $division->media->where('collection', 'machines');
                                @endphp
                                
                                @if($machineSectionMedia->count() > 0)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Current Machine Section Images:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        @foreach($machineSectionMedia as $media)
                                            <div class="relative group">
                                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                                    <img src="{{ $media->url }}" 
                                                         alt="Machine section image" 
                                                         class="w-full h-full object-cover">
                                                </div>
                                                <button type="button" 
                                                        onclick="removeSectionImage({{ $media->id }}, this, 'machines')"
                                                        class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full hover:bg-red-700 transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="machine_images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload images</span>
                                                <input id="machine_images" 
                                                       name="machine_images[]" 
                                                       type="file" 
                                                       class="sr-only" 
                                                       multiple 
                                                       accept="image/*">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">Images for the Machines section slider</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Upload 1-5 images that represent all machinery & equipment in this division.</p>
                                @error('machine_images')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Hidden inputs to track removed section images -->
                            <input type="hidden" name="remove_section_images" id="remove_section_images" value="">
                        </div>
                        
                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('admin.divisions.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Division
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function(e) {
    const slug = document.getElementById('slug');
    if (!slug.dataset.manual) {
        slug.value = e.target.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});

// Mark slug as manually edited
document.getElementById('slug').addEventListener('input', function(e) {
    e.target.dataset.manual = 'true';
});

// Preview uploaded image
document.getElementById('hero_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You can add image preview functionality here if needed
        };
        reader.readAsDataURL(file);
    }
});

// Remove current image function
function removeCurrentImage(mediaId) {
    if (confirm('Are you sure you want to remove the current hero image? This action cannot be undone.')) {
        // Set the hidden input value
        document.getElementById('remove_current_image').value = mediaId;
        
        // Hide the current image section
        const currentImageSection = document.getElementById('current-image-section');
        if (currentImageSection) {
            currentImageSection.style.display = 'none';
        }
        
        // Update the upload label text
        const uploadLabel = document.querySelector('label[for="hero_image"]');
        if (uploadLabel) {
            uploadLabel.textContent = 'Add Hero Image';
        }
        
        // Show a confirmation message
        const confirmationMessage = document.createElement('div');
        confirmationMessage.className = 'mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-md';
        confirmationMessage.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm text-yellow-800">Hero image will be removed when you save the form.</span>
            </div>
        `;
        
        // Insert the confirmation message after the current image section
        if (currentImageSection && currentImageSection.parentNode) {
            currentImageSection.parentNode.insertBefore(confirmationMessage, currentImageSection.nextSibling);
        }
    }
}

// Remove section image function
function removeSectionImage(mediaId, button, section) {
    if (confirm('Are you sure you want to remove this ' + section + ' section image?')) {
        // Add to removal list
        const removeInput = document.getElementById('remove_section_images');
        const currentValue = removeInput.value;
        const newValue = currentValue ? currentValue + ',' + mediaId : mediaId;
        removeInput.value = newValue;
        
        // Remove from display
        button.closest('.relative').remove();
        
        // Check if section has any images left
        const sectionContainer = button.closest('div[class*="grid"]');
        if (sectionContainer && sectionContainer.children.length === 0) {
            const parentDiv = sectionContainer.closest('div.mb-4');
            if (parentDiv) {
                parentDiv.style.display = 'none';
            }
        }
    }
}
</script>
@endsection