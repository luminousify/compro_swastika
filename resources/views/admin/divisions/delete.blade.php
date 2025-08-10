@extends('admin.layouts.app')

@section('title', 'Delete Division')

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
    <span class="ml-4 text-sm font-medium text-gray-900">Delete {{ $division->name }}</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                @if($hasContent)
                    <!-- Warning for division with content -->
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Delete Division with Content
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    You are about to delete the division "<strong>{{ $division->name }}</strong>".
                                </p>
                                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800">
                                                This division contains:
                                            </h3>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @if($productCount > 0)
                                                        <li>{{ $productCount }} {{ Str::plural('Product', $productCount) }}</li>
                                                    @endif
                                                    @if($technologyCount > 0)
                                                        <li>{{ $technologyCount }} {{ Str::plural('Technology', $technologyCount) }}</li>
                                                    @endif
                                                    @if($machineCount > 0)
                                                        <li>{{ $machineCount }} {{ Str::plural('Machine', $machineCount) }}</li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <p class="mt-3 text-sm text-red-600 font-semibold">
                                                Are you sure you want to delete this division and all its content?
                                            </p>
                                            <p class="mt-1 text-xs text-gray-600">
                                                This action cannot be undone. All related content will be permanently deleted.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Simple confirmation for empty division -->
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Delete Division
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete the division "<strong>{{ $division->name }}</strong>"? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <form method="POST" action="{{ route('admin.divisions.destroy', $division) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        @if($hasContent)
                            <input type="hidden" name="confirm" value="true">
                        @endif
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $hasContent ? 'Delete Division and All Content' : 'Delete Division' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.divisions.index') }}" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection