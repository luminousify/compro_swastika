@extends('admin.layouts.app')

@section('title', 'Edit Milestone')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <a href="{{ route('admin.milestones.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Milestones</a>
</li>
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">Edit {{ $milestone->year }}</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Milestone Information</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Update the milestone details.
                    </p>
                    <p class="mt-3 text-sm text-gray-600">
                        Use the rich text editor to format your milestone description with headings, lists, and emphasis.
                    </p>
                </div>
            </div>
            
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form method="POST" action="{{ route('admin.milestones.update', $milestone) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Year -->
                            <div>
                                <label for="year" class="block text-sm font-medium text-gray-700">
                                    Year <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="year" id="year" value="{{ old('year', $milestone->year) }}"
                                       min="1900" max="2100"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('year') border-red-500 @enderror"
                                       required>
                                @error('year')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Enter a year between 1900 and 2100</p>
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <label for="text" class="block text-sm font-medium text-gray-700">
                                    Description <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <textarea name="text" id="text" rows="8"
                                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('text') border-red-500 @enderror"
                                              required>{{ old('text', $milestone->text) }}</textarea>
                                </div>
                                @error('text')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">You can use HTML tags for formatting</p>
                            </div>
                            
                            <!-- Rich Text Editor Toolbar (Basic) -->
                            <div class="border-t pt-4">
                                <p class="text-sm text-gray-600 mb-2">Formatting tips:</p>
                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                                    <div><code>&lt;h2&gt;Heading&lt;/h2&gt;</code> - Section heading</div>
                                    <div><code>&lt;strong&gt;Bold&lt;/strong&gt;</code> - Bold text</div>
                                    <div><code>&lt;em&gt;Italic&lt;/em&gt;</code> - Italic text</div>
                                    <div><code>&lt;ul&gt;&lt;li&gt;Item&lt;/li&gt;&lt;/ul&gt;</code> - Bullet list</div>
                                    <div><code>&lt;ol&gt;&lt;li&gt;Item&lt;/li&gt;&lt;/ol&gt;</code> - Numbered list</div>
                                    <div><code>&lt;p&gt;Paragraph&lt;/p&gt;</code> - Paragraph</div>
                                </div>
                            </div>
                            
                            <!-- Preview -->
                            <div class="border-t pt-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Current Display:</p>
                                <div class="prose prose-sm max-w-none bg-gray-50 rounded-md p-4">
                                    {!! $milestone->text !!}
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('admin.milestones.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Update Milestone
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection