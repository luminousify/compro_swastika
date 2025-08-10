@extends('admin.layouts.app')

@section('title', 'View Message')

@section('breadcrumbs')
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <a href="{{ route('admin.messages.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Messages</a>
</li>
<li class="flex items-center">
    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
    </svg>
    <span class="ml-4 text-sm font-medium text-gray-900">{{ $message->name }}</span>
</li>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Status Bar -->
        <div class="mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div class="flex items-center">
                            @if($message->handled)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Handled
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Unhandled
                                </span>
                            @endif
                            <span class="ml-4 text-sm text-gray-500">
                                Submitted: {{ $message->created_at->format('F j, Y \a\t g:i A') }}
                            </span>
                        </div>
                        <div class="mt-4 sm:mt-0 flex space-x-2">
                            @if(!$message->handled)
                                <form method="POST" action="{{ route('admin.messages.handle', $message) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="handled" value="1">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mark as Handled
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.messages.handle', $message) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="handled" value="0">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mark as Unhandled
                                    </button>
                                </form>
                            @endif
                            
                            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Contact Information -->
            <div class="lg:col-span-1">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Contact Information</h3>
                        
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $message->name }}</dd>
                            </div>
                            
                            @if($message->company)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Company</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $message->company }}</dd>
                                </div>
                            @endif
                            
                            @if($message->email)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="mailto:{{ $message->email }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $message->email }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            
                            @if($message->phone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="tel:{{ $message->phone }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $message->phone }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                        
                        <hr class="my-4">
                        
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Technical Information</h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $message->created_by_ip }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                                <dd class="mt-1 text-xs text-gray-900 break-all">{{ $message->user_agent }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Message -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Message</h3>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($message->message)) !!}
                        </div>
                    </div>
                </div>

                <!-- Internal Notes -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Internal Notes</h3>
                        
                        <form method="POST" action="{{ route('admin.messages.handle', $message) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="handled" value="{{ $message->handled ? '1' : '0' }}">
                            
                            <div>
                                <label for="note" class="sr-only">Internal notes</label>
                                <textarea name="note" id="note" rows="4"
                                          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                          placeholder="Add notes about this message or how it was handled...">{{ old('note', $message->note) }}</textarea>
                                @error('note')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                    Save Notes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection