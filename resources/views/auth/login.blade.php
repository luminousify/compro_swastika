<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DSP Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-xl shadow-xl p-8 border border-gray-100">
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
            <p class="text-gray-600">Sign in to your DSP Admin account</p>
        </div>

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

        <form method="POST" action="{{ route('login') }}" class="space-y-6" novalidate id="loginForm" data-skip-form-enhancement="true">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label required">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus
                       autocomplete="email"
                       class="form-input @error('email') error @enderror"
                       placeholder="Enter your email address"
                       aria-describedby="@error('email') email-error @enderror"
                       @error('email') aria-invalid="true" @enderror>
                @error('email')
                    <div id="email-error" class="form-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="form-label required">Password</label>
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-500 transition-colors">
                        Forgot password?
                    </a>
                </div>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required
                       autocomplete="current-password"
                       class="form-input @error('password') error @enderror"
                       placeholder="Enter your password"
                       aria-describedby="@error('password') password-error @enderror"
                       @error('password') aria-invalid="true" @enderror>
                @error('password')
                    <div id="password-error" class="form-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" 
                           name="remember" 
                           class="form-checkbox" 
                           {{ old('remember') ? 'checked' : '' }}>
                    <span class="ml-3 text-sm text-gray-700 select-none">Keep me signed in</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-full text-lg py-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Sign In
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-500">
                Having trouble? Contact your system administrator for assistance.
            </p>
        </div>
    </div>

    <script>
        // Simple loading state without AJAX interference
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitButton = e.target.querySelector('button[type="submit"]');
            
            // Add loading state to button
            submitButton.disabled = true;
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m100 50l20-20a28.4 28.4 0 1 1 40 40l-20 20a28.4 28.4 0 1 1-40-40z"></path></svg>Signing In...';
            
            // Let the form submit normally - don't prevent default
            // If there's a 419 error, Laravel will redirect back to login
            // If successful, Laravel will redirect to dashboard
        });

        // Fix Enter key behavior for password field
        document.getElementById('password').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>