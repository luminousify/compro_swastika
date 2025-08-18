@extends('admin.layouts.app')

@section('title', 'Add Client')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <a href="{{ route('admin.clients.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Clients</a>
</li>
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">Add</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Client Information</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Add a new client with their logo and website.
                    </p>
                    <p class="mt-3 text-sm text-gray-600">
                        Logo requirements:<br>
                        • Format: PNG, JPG, GIF<br>
                        • Max size: 2MB<br>
                        • Recommended: Transparent PNG
                    </p>
                </div>
            </div>
            
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form method="POST" action="{{ route('admin.clients.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name" class="form-label required">
                                    Client Name
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                       class="form-input @error('name') error @enderror"
                                       required>
                                @error('name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- URL -->
                            <div class="form-group">
                                <label for="url" class="form-label">
                                    Website URL
                                </label>
                                <input type="url" name="url" id="url" value="{{ old('url') }}"
                                       placeholder="https://example.com"
                                       class="form-input @error('url') error @enderror">
                                @error('url')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Optional - Include https://</div>
                            </div>
                            
                            <!-- Logo Upload -->
                            <div class="form-group">
                                <label for="logo" class="form-label">
                                    Client Logo
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="logo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload a file</span>
                                                <input id="logo" name="logo" type="file" class="sr-only" accept="image/*" onchange="previewLogo(this)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            PNG, JPG, GIF up to 2MB
                                        </p>
                                    </div>
                                </div>
                                <div id="logo-preview" class="mt-4 hidden">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                                    <img id="preview-img" src="" alt="Logo preview" class="h-20 object-contain">
                                </div>
                                @error('logo')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary ml-3">
                                Add Client
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewLogo(input) {
    const preview = document.getElementById('logo-preview');
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