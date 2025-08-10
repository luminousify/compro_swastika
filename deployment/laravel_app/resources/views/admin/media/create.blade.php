@extends('admin.layouts.app')

@section('title', 'Upload Media')

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
    <span class="ml-4 text-sm font-medium text-gray-900">Upload</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Tab Navigation -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button onclick="showTab('single')" id="single-tab" class="tab-button border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Single Upload
                    </button>
                    <button onclick="showTab('bulk')" id="bulk-tab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Bulk Upload
                    </button>
                    <button onclick="showTab('video')" id="video-tab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Video
                    </button>
                </nav>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Single Upload Tab -->
        <div id="single-content" class="tab-content">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Single Image Upload</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Upload a single image with caption and entity assignment.
                        </p>
                        <p class="mt-3 text-sm text-gray-600">
                            Supported formats: JPG, PNG, GIF, WebP<br>
                            Max size: 10MB<br>
                            Max dimensions: 4096x4096
                        </p>
                    </div>
                </div>
                
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="shadow sm:rounded-md sm:overflow-hidden">
                            <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                                <!-- File Upload -->
                                <div>
                                    <label for="file" class="block text-sm font-medium text-gray-700">
                                        Image File <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload a file</span>
                                                    <input id="file" name="file" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PNG, JPG, GIF, WebP up to 10MB
                                            </p>
                                        </div>
                                    </div>
                                    <div id="image-preview" class="mt-4 hidden">
                                        <img id="preview-img" src="" alt="Preview" class="max-w-full h-auto rounded-md">
                                    </div>
                                </div>
                                
                                <!-- Entity Selection -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="entity_type" class="block text-sm font-medium text-gray-700">
                                            Entity Type <span class="text-red-500">*</span>
                                        </label>
                                        <select name="entity_type" id="entity_type" onchange="updateEntityOptions()" 
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Select Type</option>
                                            @foreach($entities as $group => $items)
                                                @if($items->count() > 0)
                                                    <option value="{{ $items->first()['type'] }}">{{ $group }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="entity_id" class="block text-sm font-medium text-gray-700">
                                            Select Item <span class="text-red-500">*</span>
                                        </label>
                                        <select name="entity_id" id="entity_id" 
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Select entity type first</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Image Type -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">
                                        Image Type
                                    </label>
                                    <select name="type" id="type" 
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="general">General</option>
                                        <option value="hero">Hero (16:9)</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                                
                                <!-- Caption -->
                                <div>
                                    <label for="caption" class="block text-sm font-medium text-gray-700">
                                        Caption / Alt text
                                    </label>
                                    <input type="text" name="caption" id="caption" 
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                           placeholder="Optional image caption">
                                </div>
                            </div>
                            
                            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                <a href="{{ route('admin.media.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Upload Image
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Tab -->
        <div id="bulk-content" class="tab-content hidden">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Bulk Image Upload</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Upload multiple images at once to the same entity.
                        </p>
                    </div>
                </div>
                
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form method="POST" action="{{ route('admin.media.bulk') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="shadow sm:rounded-md sm:overflow-hidden">
                            <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                                <!-- Multiple Files -->
                                <div>
                                    <label for="files" class="block text-sm font-medium text-gray-700">
                                        Image Files <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="files[]" id="files" multiple accept="image/*"
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <p class="mt-1 text-xs text-gray-500">Select multiple images (Ctrl/Cmd + Click)</p>
                                </div>
                                
                                <!-- Entity Selection -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="bulk_entity_type" class="block text-sm font-medium text-gray-700">
                                            Entity Type <span class="text-red-500">*</span>
                                        </label>
                                        <select name="entity_type" id="bulk_entity_type" onchange="updateBulkEntityOptions()"
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Select Type</option>
                                            @foreach($entities as $group => $items)
                                                @if($items->count() > 0)
                                                    <option value="{{ $items->first()['type'] }}">{{ $group }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="bulk_entity_id" class="block text-sm font-medium text-gray-700">
                                            Select Item <span class="text-red-500">*</span>
                                        </label>
                                        <select name="entity_id" id="bulk_entity_id"
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Select entity type first</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                <a href="{{ route('admin.media.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Upload Images
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Video Tab -->
        <div id="video-content" class="tab-content hidden">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Video Upload</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Upload a video file or add a YouTube/Vimeo URL.
                        </p>
                        <p class="mt-3 text-sm text-gray-600">
                            Video files: MP4, MPEG, QuickTime<br>
                            Max size: 100MB<br>
                            Or use YouTube/Vimeo URLs
                        </p>
                    </div>
                </div>
                
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="shadow sm:rounded-md sm:overflow-hidden">
                            <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                                <!-- Video URL -->
                                <div>
                                    <label for="video_url" class="block text-sm font-medium text-gray-700">
                                        Video URL (YouTube/Vimeo)
                                    </label>
                                    <input type="url" name="video_url" id="video_url"
                                           placeholder="https://www.youtube.com/watch?v=..."
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-300"></div>
                                    </div>
                                    <div class="relative flex justify-center text-sm">
                                        <span class="px-2 bg-white text-gray-500">Or upload a file</span>
                                    </div>
                                </div>
                                
                                <!-- Video File -->
                                <div>
                                    <label for="video_file" class="block text-sm font-medium text-gray-700">
                                        Video File
                                    </label>
                                    <input type="file" name="video_file" id="video_file" accept="video/*"
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                
                                <!-- Entity Selection -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="video_entity_type" class="block text-sm font-medium text-gray-700">
                                            Entity Type <span class="text-red-500">*</span>
                                        </label>
                                        <select name="entity_type" id="video_entity_type" onchange="updateVideoEntityOptions()"
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Select Type</option>
                                            @foreach($entities as $group => $items)
                                                @if($items->count() > 0)
                                                    <option value="{{ $items->first()['type'] }}">{{ $group }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="video_entity_id" class="block text-sm font-medium text-gray-700">
                                            Select Item <span class="text-red-500">*</span>
                                        </label>
                                        <select name="entity_id" id="video_entity_id"
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Select entity type first</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Caption -->
                                <div>
                                    <label for="video_caption" class="block text-sm font-medium text-gray-700">
                                        Caption
                                    </label>
                                    <input type="text" name="caption" id="video_caption"
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                           placeholder="Optional video caption">
                                </div>
                            </div>
                            
                            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                <a href="{{ route('admin.media.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Add Video
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Entity data
const entities = @json($entities);

function showTab(tab) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-indigo-500', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    });
    
    // Show selected tab content
    document.getElementById(tab + '-content').classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById(tab + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    activeTab.classList.add('border-indigo-500', 'text-indigo-600');
}

function updateEntityOptions() {
    const typeSelect = document.getElementById('entity_type');
    const idSelect = document.getElementById('entity_id');
    const selectedType = typeSelect.value;
    
    // Clear current options
    idSelect.innerHTML = '<option value="">Select an item</option>';
    
    if (selectedType) {
        // Find matching entities
        Object.keys(entities).forEach(group => {
            entities[group].forEach(item => {
                if (item.type === selectedType) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    idSelect.appendChild(option);
                }
            });
        });
    }
}

function updateBulkEntityOptions() {
    const typeSelect = document.getElementById('bulk_entity_type');
    const idSelect = document.getElementById('bulk_entity_id');
    const selectedType = typeSelect.value;
    
    // Clear current options
    idSelect.innerHTML = '<option value="">Select an item</option>';
    
    if (selectedType) {
        // Find matching entities
        Object.keys(entities).forEach(group => {
            entities[group].forEach(item => {
                if (item.type === selectedType) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    idSelect.appendChild(option);
                }
            });
        });
    }
}

function updateVideoEntityOptions() {
    const typeSelect = document.getElementById('video_entity_type');
    const idSelect = document.getElementById('video_entity_id');
    const selectedType = typeSelect.value;
    
    // Clear current options
    idSelect.innerHTML = '<option value="">Select an item</option>';
    
    if (selectedType) {
        // Find matching entities
        Object.keys(entities).forEach(group => {
            entities[group].forEach(item => {
                if (item.type === selectedType) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    idSelect.appendChild(option);
                }
            });
        });
    }
}

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}
</script>
@endsection