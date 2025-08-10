@extends('admin.layouts.app')

@section('title', 'Settings')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">Settings</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900">Company Settings</h1>
            <p class="mt-1 text-sm text-gray-600">
                Manage your company profile, branding, and website settings.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Please fix the following errors:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Company Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Company Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                            <input type="text" name="company_name" id="company_name" 
                                   value="{{ old('company_name', $settings->getCompanyName()) }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="company_email" class="block text-sm font-medium text-gray-700">Company Email</label>
                            <input type="email" name="company_email" id="company_email" 
                                   value="{{ old('company_email', $settings->getCompanyEmail()) }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="company_address" class="block text-sm font-medium text-gray-700">Company Address</label>
                            <textarea name="company_address" id="company_address" rows="3"
                                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('company_address', $settings->getCompanyAddress()) }}</textarea>
                        </div>

                        <div>
                            <label for="company_phone" class="block text-sm font-medium text-gray-700">Company Phone</label>
                            <input type="text" name="company_phone" id="company_phone" 
                                   value="{{ old('company_phone', $settings->getCompanyPhone()) }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="company_website" class="block text-sm font-medium text-gray-700">Company Website</label>
                            <input type="url" name="company_website" id="company_website" 
                                   value="{{ old('company_website', $settings->data['company_website'] ?? '') }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="established_date" class="block text-sm font-medium text-gray-700">Established Date</label>
                            <input type="date" name="established_date" id="established_date" 
                                   value="{{ old('established_date', $settings->data['established_date'] ?? '') }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="director" class="block text-sm font-medium text-gray-700">Director</label>
                            <input type="text" name="director" id="director" 
                                   value="{{ old('director', $settings->data['director'] ?? '') }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="npwp" class="block text-sm font-medium text-gray-700">NPWP</label>
                            <input type="text" name="npwp" id="npwp" 
                                   value="{{ old('npwp', $settings->data['npwp'] ?? '') }}"
                                   placeholder="12.345.678.9-123.456"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('npwp')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nib" class="block text-sm font-medium text-gray-700">NIB</label>
                            <input type="text" name="nib" id="nib" 
                                   value="{{ old('nib', $settings->data['nib'] ?? '') }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logo and Favicon -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Branding</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="logo" class="block text-sm font-medium text-gray-700">Company Logo</label>
                            @if(!empty($settings->data['logo']))
                                <div class="mt-2 mb-2">
                                    <img src="{{ Storage::url($settings->data['logo']) }}" alt="Current Logo" class="h-20">
                                </div>
                            @endif
                            <input type="file" name="logo" id="logo" accept="image/*"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG, SVG up to 2MB</p>
                        </div>

                        <div>
                            <label for="favicon" class="block text-sm font-medium text-gray-700">Favicon</label>
                            @if(!empty($settings->data['favicon']))
                                <div class="mt-2 mb-2">
                                    <img src="{{ Storage::url($settings->data['favicon']) }}" alt="Current Favicon" class="h-8">
                                </div>
                            @endif
                            <input type="file" name="favicon" id="favicon" accept=".ico,.png"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">ICO or PNG up to 512KB</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vision & Mission -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Vision & Mission</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="visi" class="block text-sm font-medium text-gray-700">Vision</label>
                            <textarea name="visi" id="visi" rows="3"
                                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('visi', $settings->data['visi'] ?? '') }}</textarea>
                        </div>

                        <div>
                            <label for="misi" class="block text-sm font-medium text-gray-700">Mission</label>
                            <textarea name="misi" id="misi" rows="3"
                                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('misi', $settings->data['misi'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Home Hero Content -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Home Page Hero</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="home_hero_headline" class="block text-sm font-medium text-gray-700">Hero Headline</label>
                            <input type="text" name="home_hero_headline" id="home_hero_headline" 
                                   value="{{ old('home_hero_headline', $settings->getHomeHero()['headline'] ?? '') }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="home_hero_subheadline" class="block text-sm font-medium text-gray-700">Hero Subheadline</label>
                            <input type="text" name="home_hero_subheadline" id="home_hero_subheadline" 
                                   value="{{ old('home_hero_subheadline', $settings->getHomeHero()['subheadline'] ?? '') }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="home_hero_cta_text" class="block text-sm font-medium text-gray-700">CTA Text</label>
                            <input type="text" name="home_hero_cta_text" id="home_hero_cta_text" 
                                   value="{{ old('home_hero_cta_text', $settings->getHomeHero()['cta_text'] ?? '') }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Snippet -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">About Section</h3>
                    <div>
                        <label for="about_snippet" class="block text-sm font-medium text-gray-700">About Snippet</label>
                        <textarea name="about_snippet" id="about_snippet" rows="4"
                                  class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('about_snippet', $settings->data['about_snippet'] ?? '') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Brief description about the company for the home page</p>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Social Media</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="facebook" class="block text-sm font-medium text-gray-700">Facebook</label>
                            <input type="url" name="facebook" id="facebook" 
                                   value="{{ old('facebook', $settings->data['social_media']['facebook'] ?? '') }}"
                                   placeholder="https://facebook.com/yourpage"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="instagram" class="block text-sm font-medium text-gray-700">Instagram</label>
                            <input type="url" name="instagram" id="instagram" 
                                   value="{{ old('instagram', $settings->data['social_media']['instagram'] ?? '') }}"
                                   placeholder="https://instagram.com/yourpage"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="linkedin" class="block text-sm font-medium text-gray-700">LinkedIn</label>
                            <input type="url" name="linkedin" id="linkedin" 
                                   value="{{ old('linkedin', $settings->data['social_media']['linkedin'] ?? '') }}"
                                   placeholder="https://linkedin.com/company/yourcompany"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="twitter" class="block text-sm font-medium text-gray-700">Twitter</label>
                            <input type="url" name="twitter" id="twitter" 
                                   value="{{ old('twitter', $settings->data['social_media']['twitter'] ?? '') }}"
                                   placeholder="https://twitter.com/yourhandle"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="youtube" class="block text-sm font-medium text-gray-700">YouTube</label>
                            <input type="url" name="youtube" id="youtube" 
                                   value="{{ old('youtube', $settings->data['social_media']['youtube'] ?? '') }}"
                                   placeholder="https://youtube.com/c/yourchannel"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Maps -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Google Maps</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="google_maps_embed_url" class="block text-sm font-medium text-gray-700">Google Maps Embed URL</label>
                            <textarea name="google_maps_embed_url" id="google_maps_embed_url" rows="3"
                                      placeholder="Paste the Google Maps iframe embed code or URL here"
                                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('google_maps_embed_url', $settings->data['google_maps']['embed_url'] ?? '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">You can paste the entire iframe code, we'll extract the URL</p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                                <input type="number" name="latitude" id="latitude" step="any"
                                       value="{{ old('latitude', $settings->data['google_maps']['latitude'] ?? '') }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                                <input type="number" name="longitude" id="longitude" step="any"
                                       value="{{ old('longitude', $settings->data['google_maps']['longitude'] ?? '') }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">SEO Settings</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="seo_default_title" class="block text-sm font-medium text-gray-700">Default Title</label>
                            <input type="text" name="seo_default_title" id="seo_default_title" maxlength="60"
                                   value="{{ old('seo_default_title', $settings->data['seo']['default_title'] ?? '') }}"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Maximum 60 characters</p>
                        </div>

                        <div>
                            <label for="seo_default_description" class="block text-sm font-medium text-gray-700">Default Description</label>
                            <textarea name="seo_default_description" id="seo_default_description" rows="3" maxlength="160"
                                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('seo_default_description', $settings->data['seo']['default_description'] ?? '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Maximum 160 characters</p>
                        </div>

                        <div>
                            <label for="seo_default_keywords" class="block text-sm font-medium text-gray-700">Default Keywords</label>
                            <input type="text" name="seo_default_keywords" id="seo_default_keywords" 
                                   value="{{ old('seo_default_keywords', $settings->data['seo']['default_keywords'] ?? '') }}"
                                   placeholder="keyword1, keyword2, keyword3"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-1 text-xs text-gray-500">Comma-separated keywords</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection