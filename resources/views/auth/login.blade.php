<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Sign In') }} - {{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css'])

    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative Background Elements */
        .bg-decoration {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .bg-decoration::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
            animation: float 20s ease-in-out infinite;
        }

        .bg-decoration::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* Grid Pattern Overlay */
        .grid-pattern {
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(rgba(59, 130, 246, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
            z-index: 0;
        }

        .login-shell {
            position: relative;
            min-height: 100vh;
            padding: 24px;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 32px;
            box-shadow: 
                0 25px 50px rgba(15, 23, 42, 0.08),
                0 0 0 1px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            min-height: 700px;
        }

        /* Left Side - Visual/Branding */
        .login-brand {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 60px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .login-brand::before {
            content: '';
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            color: white;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 60px;
        }

        .brand-logo-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .brand-logo-text {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-title {
            font-size: 48px;
            font-weight: 900;
            letter-spacing: -1.5px;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .brand-description {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
            font-weight: 500;
            max-width: 400px;
        }

        .brand-footer {
            position: relative;
            z-index: 2;
        }

        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-size: 14px;
            font-weight: 600;
        }

        .brand-feature-icon {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Decorative Elements */
        .brand-decoration {
            position: absolute;
            top: 50%;
            right: -100px;
            transform: translateY(-50%);
            width: 300px;
            height: 300px;
            opacity: 0.1;
            pointer-events: none;
        }

        /* Right Side - Login Form */
        .login-form-side {
            padding: 60px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 36px;
            font-weight: 900;
            letter-spacing: -1px;
            color: #0f172a;
            line-height: 1.1;
            margin: 0 0 12px 0;
        }

        .login-header p {
            font-size: 15px;
            color: #64748b;
            font-weight: 500;
            line-height: 1.6;
            margin: 0;
        }

        /* Enhanced Input Styling */
        .login-input {
            width: 100%;
            height: 48px;
            padding: 0 16px 0 44px;
            font-size: 15px;
            line-height: 1.5;
            font-weight: 500;
            color: #1f2937;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            background: #fafbfc;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .login-input::placeholder {
            color: #9ca3af;
            font-weight: 400;
            font-size: 15px;
        }

        /* Hover State */
        .login-input:hover {
            background: #ffffff;
            border-color: #d1d5db;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.06);
        }

        /* Focus State - Premium */
        .login-input:focus {
            background: white;
            border-color: #3b82f6;
            box-shadow: 
                0 0 0 3px rgba(59, 130, 246, 0.1),
                0 4px 12px rgba(59, 130, 246, 0.1);
            outline: none;
            transform: translateY(-1px);
        }

        /* Autofill Styling */
        .login-input:-webkit-autofill,
        .login-input:-webkit-autofill:hover,
        .login-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #1f2937 !important;
            -webkit-box-shadow: 
                0 0 0 3px rgba(59, 130, 246, 0.1),
                0 0 0 1px rgba(59, 130, 246, 0.3),
                0 4px 12px rgba(59, 130, 246, 0.1),
                0 0 0 1000px white inset !important;
            caret-color: #3b82f6;
            transition: background-color 9999s ease-out 0s, box-shadow 0.3s ease;
        }

        /* Input Icon Container */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        /* Input Icon Styling */
        .input-icon {
            position: absolute;
            left: 14px;
            width: 20px;
            height: 20px;
            color: #9ca3af;
            transition: all 0.3s ease;
            flex-shrink: 0;
            z-index: 2;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Icon Focus State */
        .input-wrapper:focus-within .input-icon {
            color: #3b82f6;
            transform: scale(1.1);
        }

        /* Password Toggle Button */
        .password-toggle {
            position: absolute;
            right: 14px;
            width: 20px;
            height: 20px;
            color: #9ca3af;
            background: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
            padding: 0;
            margin: 0;
            flex-shrink: 0;
        }

        .password-toggle:hover {
            color: #6b7280;
            transform: scale(1.1);
        }

        .password-toggle:active {
            color: #3b82f6;
            transform: scale(0.95);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

        /* Label Styling */
        label {
            display: block;
            color: #1f2937;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: -0.3px;
            margin-bottom: 10px;
            text-transform: none;
        }

        /* Error State */
        .login-input.error {
            border-color: #f87171;
            background: #fef2f2;
        }

        .login-input.error:focus {
            border-color: #ef4444;
            box-shadow: 
                0 0 0 3px rgba(239, 68, 68, 0.1),
                0 4px 12px rgba(239, 68, 68, 0.1);
        }

        /* Error Text */
        .error-text {
            font-size: 12px;
            color: #dc2626;
            margin-top: 8px;
            font-weight: 600;
            letter-spacing: -0.1px;
            display: flex;
            align-items: center;
            gap: 6px;
            animation: slide-up 0.3s ease;
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Group */
        .form-group {
            margin-bottom: 24px;
            animation: fade-in 0.6s ease backwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Checkbox Styling */
        .checkbox-custom {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
            position: relative;
        }

        .checkbox-custom:hover {
            border-color: #9ca3af;
            background: #f9fafb;
        }

        .checkbox-custom:checked {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .checkbox-custom:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: 800;
            font-size: 12px;
            line-height: 1;
        }

        /* Button Styling */
        .login-button {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            font-weight: 700;
            letter-spacing: -0.4px;
            height: 48px;
            border: none;
            cursor: pointer;
            border-radius: 12px;
            width: 100%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-size: 15px;
        }

        .login-button::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.2) 0%,
                transparent 100%
            );
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-button:hover:not(:disabled)::before {
            opacity: 1;
        }

        .login-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.3);
        }

        .login-button:active:not(:disabled) {
            transform: translateY(0);
        }

        .login-button:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        /* Link Styling */
        .link-hover {
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none;
            color: #3b82f6;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: -0.2px;
        }

        .link-hover::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #3b82f6;
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .link-hover:hover::after {
            width: 100%;
        }

        .link-hover:hover {
            color: #2563eb;
        }

        /* Alert Messages */
        .error-alert {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 12px;
            border: 1px solid rgba(248, 113, 113, 0.3);
            background: #fef2f2;
            color: #7f1d1d;
            margin-bottom: 24px;
            animation: fade-in 0.4s ease;
        }

        .success-alert {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 12px;
            border: 1px solid rgba(134, 239, 172, 0.3);
            background: #f0fdf4;
            color: #15803d;
            margin-bottom: 24px;
            animation: fade-in 0.4s ease;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 480px;
            }

            .login-brand {
                display: none;
            }

            .login-form-side {
                padding: 40px 32px;
            }

            .login-header h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 640px) {
            .login-form-side {
                padding: 32px 24px;
            }

            .login-header h2 {
                font-size: 24px;
            }

            .login-button {
                height: 44px;
            }
        }

        /* Selection */
        .login-input::selection {
            background: rgba(59, 130, 246, 0.2);
            color: #1f2937;
        }

        /* Focus Visible */
        .login-input:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Disabled State */
        .login-input:disabled {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
            border-color: #e5e7eb;
        }

        /* Social Login Divider */
        .divider {
            position: relative;
            margin: 28px 0;
            height: 1px;
            background: #e5e7eb;
        }

        .divider-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 16px;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
        }

        /* Social Login Buttons */
        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .social-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .social-button:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .social-button svg {
            width: 18px;
            height: 18px;
        }

        /* Footer Links */
        .login-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }

        .login-footer p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        .login-footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 700;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="antialiased">
    <div class="bg-decoration"></div>
    <div class="grid-pattern"></div>
    
    <div class="login-shell">
        <div class="login-container">
            <!-- Left Side - Branding -->
            <div class="login-brand">
                <div class="brand-content">
                    <div class="brand-logo">
                        <div class="brand-logo-icon">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 7h16" />
                                <path d="M4 12h16" />
                                <path d="M4 17h16" />
                                <path d="M8 7v10" />
                            </svg>
                        </div>
                        <div class="brand-logo-text">EngageClass KH</div>
                    </div>
                    
                    <h1 class="brand-title">Smart learning,<br>better results</h1>
                    <p class="brand-description">
                        Access your dashboard, manage classrooms, and empower your teaching journey with our intelligent platform.
                    </p>
                </div>

                <div class="brand-footer">
                    <div class="brand-features">
                        <div class="brand-feature">
                            <div class="brand-feature-icon">
                                <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <span>Interactive classroom tools</span>
                        </div>
                        <div class="brand-feature">
                            <div class="brand-feature-icon">
                                <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <span>Real-time student engagement</span>
                        </div>
                        <div class="brand-feature">
                            <div class="brand-feature-icon">
                                <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <span>Analytics & insights dashboard</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="login-form-side">
                <div class="login-header">
                    <h2>Welcome back</h2>
                    <p>Sign in to access your dashboard and manage your classroom</p>
                </div>

                <!-- Status Messages -->
                @if (session('status'))
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition.opacity.duration.400ms
                        x-init="setTimeout(() => show = false, 5000)"
                        class="mb-6"
                    >
                        <div class="success-alert">
                            <svg class="h-5 w-5 shrink-0 text-green-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            <span class="text-sm font-medium flex-1">{{ session('status') }}</span>
                            <button @click="show = false" class="ml-2 flex-shrink-0 hover:opacity-70 transition">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="error-alert">
                        <svg class="h-5 w-5 shrink-0 text-red-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-bold">
                                {{ __('Whoops! Something went wrong.') }}
                            </p>
                            <ul class="mt-2.5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-xs font-medium">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-0">
                    @csrf

                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email">
                            Email Address
                        </label>
                        <div class="input-wrapper">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="name@company.com"
                                class="login-input @error('email') error @enderror"
                            />
                        </div>
                        @error('email')
                            <p class="error-text">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">
                            Password
                        </label>
                        <div class="input-wrapper" x-data="{ show: false }">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <input
                                id="password"
                                :type="show ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="login-input @error('password') error @enderror"
                            />
                            <button
                                type="button"
                                @click="show = !show"
                                :aria-label="show ? 'Hide password' : 'Show password'"
                                class="password-toggle"
                            >
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                    <line x1="2" y1="2" x2="22" y2="22" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="error-text">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between gap-3 pt-1 pb-2">
                        <div class="flex items-center gap-3">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                                class="checkbox-custom"
                            />
                            <label for="remember" class="select-none text-sm text-slate-700 font-medium m-0 mb-0">
                                Remember me
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="link-hover">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Sign In Button -->
                    <button
                        type="submit"
                        class="login-button group flex items-center justify-center gap-2 mt-2"
                    >
                        <span>Sign In</span>
                        <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12 5 19 12 12 19" />
                        </svg>
                    </button>

                    <!-- Divider -->
                    <div class="divider">
                        <span class="divider-text">or</span>
                    </div>

                    <!-- Social Login -->
                    <div class="social-login">
                        <button type="button" class="social-button">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            Google
                        </button>
                        <button type="button" class="social-button">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" fill="#333"/>
                            </svg>
                            GitHub
                        </button>
                    </div>

                    <!-- Footer -->
                    <div class="login-footer">
                        <p>
                            Don't have an account? <a href="#">Contact administrator</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
