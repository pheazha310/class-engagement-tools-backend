<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Enterprise Admin') }} - {{ config('app.name', 'Laravel') }}</title>

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Vite Assets (Tailwind CSS) --}}
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-[#FFFFFF] min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">

    {{-- Session Status --}}
    @if (session('status'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-md px-4"
        >
            <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 shadow-lg" role="alert">
                <svg class="w-5 h-5 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span class="flex-1">{{ session('status') }}</span>
                <button @click="show = false" class="shrink-0 text-emerald-400 hover:text-emerald-600 transition-colors" aria-label="Dismiss">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Login Card --}}
    <div class="w-full max-w-[460px] bg-white rounded-2xl shadow-xl p-10 sm:p-10">
        {{-- Header --}}
        <div class="text-center mb-8">
            {{-- Shield Icon --}}
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-blue-600 mb-6 shadow-md shadow-blue-200">
                <svg class="w-7 h-7 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>

            {{-- Title --}}
            <h1 class="text-5xl font-bold text-gray-900 tracking-tight">
                Enterprise Admin
            </h1>
            <p class="mt-3 text-base text-gray-500 font-medium">
                Secure Institutional Gateway
            </p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl" role="alert">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 shrink-0 text-red-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-red-800 mb-1">
                            {{ __('Whoops! Something went wrong.') }}
                        </p>
                        <ul class="text-sm text-red-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-start gap-1.5">
                                    <span class="block w-1 h-1 rounded-full bg-red-400 mt-2 shrink-0"></span>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-6" role="form">
            @csrf

            {{-- Email Address --}}
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('Email Address') }}
                </label>
                <div class="relative">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="admin@example.com"
                        class="block w-full h-14 px-4 text-sm text-gray-900 bg-[#EFF6FF] border border-gray-200 rounded-xl placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('email') border-red-300 bg-red-50 focus:ring-red-500 focus:border-red-500 @enderror"
                    />
                </div>
                @error('email')
                    <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5" role="alert">
                        <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-semibold text-gray-700">
                        {{ __('Password') }}
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                            {{ __('Forgot Password?') }}
                        </a>
                    @endif
                </div>

                <div class="relative" x-data="{ show: false }">
                    <input
                        id="password"
                        :type="show ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        class="block w-full h-14 px-4 pr-12 text-sm text-gray-900 bg-[#EFF6FF] border border-gray-200 rounded-xl placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('password') border-red-300 bg-red-50 focus:ring-red-500 focus:border-red-500 @enderror"
                    />

                    {{-- Eye Toggle (Alpine.js) --}}
                    <button
                        type="button"
                        @click="show = !show"
                        :aria-label="show ? 'Hide password' : 'Show password'"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none focus:text-blue-600"
                        tabindex="-1"
                    >
                        {{-- Eye (visible when password hidden) --}}
                        <svg x-show="!show" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>

                        {{-- Eye Off (visible when password shown) --}}
                        <svg x-show="show" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                            <line x1="2" y1="2" x2="22" y2="22"/>
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5" role="alert">
                        <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center gap-3">
                <input
                    id="remember"
                    type="checkbox"
                    name="remember"
                    {{ old('remember') ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 transition-colors"
                />
                <label for="remember" class="text-sm font-medium text-gray-600 select-none cursor-pointer">
                    {{ __('Keep me signed in') }}
                </label>
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                class="group relative w-full h-14 inline-flex items-center justify-center gap-3 px-6 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 shadow-sm hover:shadow-lg hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300"
            >
                <span>{{ __('Sign In') }}</span>

                {{-- Right Arrow --}}
                <svg class="w-4 h-4 text-gray-500 group-hover:translate-x-0.5 transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </button>
        </form>

        {{-- Footer --}}
        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400 leading-relaxed">
                Access is limited to authorized administrators.<br>
                Your session is protected and encrypted.
            </p>
        </div>
    </div>
</body>
</html>
