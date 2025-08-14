@extends('admin.layouts.app')

@section('title', 'Settings')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">Pengaturan</span>
</li>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-900">Pengaturan Perusahaan</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Kelola profil perusahaan, branding, dan pengaturan website Anda
                </p>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada form:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Tab Navigation -->
            <div class="mb-8">
                <div class="sm:hidden">
                    <select id="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option>Informasi Perusahaan</option>
                        <option>Visi & Misi</option>
                        <option>Media Sosial</option>
                        <option>SEO</option>
                        <option>Lainnya</option>
                    </select>
                </div>
                <div class="hidden sm:block">
                    <nav class="flex space-x-8" aria-label="Tabs">
                        <a href="#company" class="tab-link active text-indigo-600 border-indigo-600">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Informasi Perusahaan
                        </a>
                        <a href="#vision" class="tab-link text-gray-500 hover:text-gray-700 border-transparent">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Visi & Misi
                        </a>
                        <a href="#social" class="tab-link text-gray-500 hover:text-gray-700 border-transparent">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                            </svg>
                            Media Sosial
                        </a>
                        <a href="#seo" class="tab-link text-gray-500 hover:text-gray-700 border-transparent">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            SEO
                        </a>
                        <a href="#others" class="tab-link text-gray-500 hover:text-gray-700 border-transparent">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Lainnya
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Company Information Section -->
            <div id="company-section" class="tab-content space-y-6">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Dasar</h3>
                        <p class="mt-1 text-sm text-gray-600">Informasi dasar tentang perusahaan Anda</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Perusahaan
                                </label>
                                <input type="text" name="company_name" id="company_name" 
                                       value="{{ old('company_name', $settings->getCompanyName()) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="company_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Perusahaan
                                </label>
                                <input type="email" name="company_email" id="company_email" 
                                       value="{{ old('company_email', $settings->getCompanyEmail()) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="company_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Telepon
                                </label>
                                <input type="text" name="company_phone" id="company_phone" 
                                       value="{{ old('company_phone', $settings->getCompanyPhone()) }}"
                                       placeholder="021-89444891"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="company_website" class="block text-sm font-medium text-gray-700 mb-2">
                                    Website
                                </label>
                                <input type="url" name="company_website" id="company_website" 
                                       value="{{ old('company_website', $settings->data['company_website'] ?? '') }}"
                                       placeholder="https://example.com"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="company_address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Alamat Perusahaan
                                </label>
                                <textarea name="company_address" id="company_address" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">{{ old('company_address', $settings->getCompanyAddress()) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Legal</h3>
                        <p class="mt-1 text-sm text-gray-600">Dokumen dan informasi legal perusahaan</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="director" class="block text-sm font-medium text-gray-700 mb-2">
                                    Direktur
                                </label>
                                <input type="text" name="director" id="director" 
                                       value="{{ old('director', $settings->data['director'] ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="established_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Berdiri
                                </label>
                                <input type="date" name="established_date" id="established_date" 
                                       value="{{ old('established_date', $settings->data['established_date'] ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="npwp" class="block text-sm font-medium text-gray-700 mb-2">
                                    NPWP
                                </label>
                                <input type="text" name="npwp" id="npwp" 
                                       value="{{ old('npwp', $settings->data['npwp'] ?? '') }}"
                                       placeholder="00.000.000.0-000.000"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="nib" class="block text-sm font-medium text-gray-700 mb-2">
                                    NIB
                                </label>
                                <input type="text" name="nib" id="nib" 
                                       value="{{ old('nib', $settings->data['nib'] ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vision & Mission Section -->
            <div id="vision-section" class="tab-content hidden space-y-6">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Visi & Misi Perusahaan</h3>
                        <p class="mt-1 text-sm text-gray-600">Tentukan visi dan misi perusahaan Anda</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div>
                            <label for="visi" class="block text-sm font-medium text-gray-700 mb-2">
                                Visi
                            </label>
                            <textarea name="visi" id="visi" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">{{ old('visi', $settings->getVisi()) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Maksimal 1000 karakter</p>
                        </div>

                        <div>
                            <label for="misi" class="block text-sm font-medium text-gray-700 mb-2">
                                Misi
                            </label>
                            <textarea name="misi" id="misi" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">{{ old('misi', $settings->getMisi()) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Maksimal 1000 karakter</p>
                        </div>

                        <div>
                            <label for="about_snippet" class="block text-sm font-medium text-gray-700 mb-2">
                                Tentang Perusahaan (Ringkas)
                            </label>
                            <textarea name="about_snippet" id="about_snippet" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">{{ old('about_snippet', $settings->data['about_snippet'] ?? '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Deskripsi singkat untuk halaman utama</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media Section -->
            <div id="social-section" class="tab-content hidden space-y-6">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Media Sosial</h3>
                        <p class="mt-1 text-sm text-gray-600">Hubungkan website dengan media sosial perusahaan</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="facebook" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        Facebook
                                    </span>
                                </label>
                                <input type="url" name="facebook" id="facebook" 
                                       value="{{ old('facebook', $settings->data['social_media']['facebook'] ?? '') }}"
                                       placeholder="https://facebook.com/yourpage"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
                                        </svg>
                                        Instagram
                                    </span>
                                </label>
                                <input type="url" name="instagram" id="instagram" 
                                       value="{{ old('instagram', $settings->data['social_media']['instagram'] ?? '') }}"
                                       placeholder="https://instagram.com/yourprofile"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="linkedin" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-blue-700" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                        LinkedIn
                                    </span>
                                </label>
                                <input type="url" name="linkedin" id="linkedin" 
                                       value="{{ old('linkedin', $settings->data['social_media']['linkedin'] ?? '') }}"
                                       placeholder="https://linkedin.com/company/yourcompany"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="youtube" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                        </svg>
                                        YouTube
                                    </span>
                                </label>
                                <input type="url" name="youtube" id="youtube" 
                                       value="{{ old('youtube', $settings->data['social_media']['youtube'] ?? '') }}"
                                       placeholder="https://youtube.com/c/yourchannel"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="twitter" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                        </svg>
                                        Twitter
                                    </span>
                                </label>
                                <input type="url" name="twitter" id="twitter" 
                                       value="{{ old('twitter', $settings->data['social_media']['twitter'] ?? '') }}"
                                       placeholder="https://twitter.com/yourhandle"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Section -->
            <div id="seo-section" class="tab-content hidden space-y-6">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Optimasi Mesin Pencari (SEO)</h3>
                        <p class="mt-1 text-sm text-gray-600">Pengaturan SEO untuk meningkatkan visibilitas website</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div>
                            <label for="seo_default_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Judul Default
                            </label>
                            <input type="text" name="seo_default_title" id="seo_default_title" maxlength="60"
                                   value="{{ old('seo_default_title', $settings->data['seo_title'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            <div class="mt-1 flex justify-between">
                                <p class="text-xs text-gray-500">Maksimal 60 karakter</p>
                                <p class="text-xs text-gray-500">
                                    <span id="title-count">0</span>/60
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="seo_default_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi Default
                            </label>
                            <textarea name="seo_default_description" id="seo_default_description" rows="3" maxlength="160"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">{{ old('seo_default_description', $settings->data['seo_description'] ?? '') }}</textarea>
                            <div class="mt-1 flex justify-between">
                                <p class="text-xs text-gray-500">Maksimal 160 karakter</p>
                                <p class="text-xs text-gray-500">
                                    <span id="description-count">0</span>/160
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="seo_default_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                                Kata Kunci Default
                            </label>
                            <input type="text" name="seo_default_keywords" id="seo_default_keywords" 
                                   value="{{ old('seo_default_keywords', $settings->data['seo_keywords'] ?? '') }}"
                                   placeholder="kata kunci 1, kata kunci 2, kata kunci 3"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            <p class="mt-1 text-xs text-gray-500">Pisahkan dengan koma</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Others Section -->
            <div id="others-section" class="tab-content hidden space-y-6">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Pengaturan Tambahan</h3>
                        <p class="mt-1 text-sm text-gray-600">Pengaturan lainnya untuk website</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div>
                            <label for="google_maps_embed_url" class="block text-sm font-medium text-gray-700 mb-2">
                                Google Maps Embed URL
                            </label>
                            <textarea name="google_maps_embed_url" id="google_maps_embed_url" rows="3"
                                      placeholder="Paste iframe embed code dari Google Maps"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">{{ old('google_maps_embed_url', $settings->data['google_maps']['embed_url'] ?? '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Paste iframe embed code dari Google Maps</p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                                    Latitude
                                </label>
                                <input type="number" step="any" name="latitude" id="latitude" 
                                       value="{{ old('latitude', $settings->data['google_maps']['latitude'] ?? '') }}"
                                       placeholder="-6.200000"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>

                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                                    Longitude
                                </label>
                                <input type="number" step="any" name="longitude" id="longitude" 
                                       value="{{ old('longitude', $settings->data['google_maps']['longitude'] ?? '') }}"
                                       placeholder="106.816666"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Logo Perusahaan
                                </label>
                                <input type="file" name="logo" id="logo" accept="image/*"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                                <p class="mt-1 text-xs text-gray-500">JPG, PNG, SVG (Max 2MB)</p>
                            </div>

                            <div>
                                <label for="favicon" class="block text-sm font-medium text-gray-700 mb-2">
                                    Favicon
                                </label>
                                <input type="file" name="favicon" id="favicon" accept=".ico,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                                <p class="mt-1 text-xs text-gray-500">ICO, PNG (Max 512KB)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6">
                <a href="{{ route('admin.dashboard') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .tab-link {
        @apply inline-flex items-center px-1 py-4 border-b-2 text-sm font-medium transition-colors duration-200;
    }
    .tab-link.active {
        @apply text-indigo-600 border-indigo-600;
    }
</style>
@endpush

@push('scripts')
<script>
    // Tab Navigation
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-link');
        const sections = document.querySelectorAll('.tab-content');
        
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('active', 'text-indigo-600', 'border-indigo-600');
                    t.classList.add('text-gray-500', 'border-transparent');
                });
                
                // Add active class to clicked tab
                this.classList.add('active', 'text-indigo-600', 'border-indigo-600');
                this.classList.remove('text-gray-500', 'border-transparent');
                
                // Hide all sections
                sections.forEach(s => s.classList.add('hidden'));
                
                // Show corresponding section
                const targetId = this.getAttribute('href').substring(1) + '-section';
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    targetSection.classList.remove('hidden');
                }
            });
        });

        // Character counting for SEO fields
        const titleInput = document.getElementById('seo_default_title');
        const titleCount = document.getElementById('title-count');
        const descInput = document.getElementById('seo_default_description');
        const descCount = document.getElementById('description-count');

        function updateCount(input, counter) {
            if (input && counter) {
                counter.textContent = input.value.length;
                if (input.value.length > parseInt(input.getAttribute('maxlength'))) {
                    counter.classList.add('text-red-500');
                } else {
                    counter.classList.remove('text-red-500');
                }
            }
        }

        if (titleInput) {
            updateCount(titleInput, titleCount);
            titleInput.addEventListener('input', () => updateCount(titleInput, titleCount));
        }

        if (descInput) {
            updateCount(descInput, descCount);
            descInput.addEventListener('input', () => updateCount(descInput, descCount));
        }

        // Mobile tab select
        const mobileSelect = document.getElementById('tabs');
        if (mobileSelect) {
            mobileSelect.addEventListener('change', function() {
                const index = this.selectedIndex;
                if (tabs[index]) {
                    tabs[index].click();
                }
            });
        }
    });
</script>
@endpush
@endsection