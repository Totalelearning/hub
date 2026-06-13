@extends('layouts.auth')

@section('title', 'Login - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .login-template-shell {
            position: relative;
            min-height: calc(100vh - 86px);
            overflow: hidden;
        }

        .login-template-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(236, 249, 255, 0.92), rgba(247, 245, 255, 0.9));
        }

        .login-template-image {
            position: absolute;
            inset: 0 0 0 auto;
            width: 48%;
            overflow: hidden;
            z-index: 0;
        }

        .login-template-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-template-card {
            position: relative;
            z-index: 1;
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 20px 56px rgba(43, 82, 138, 0.14);
        }

        .login-template-field {
            border-radius: 1rem !important;
            border-color: rgba(203, 213, 225, 0.95) !important;
            min-height: 3.55rem;
            box-shadow: none !important;
        }

        .login-template-password-toggle {
            position: absolute;
            right: 0.8rem;
            top: 0.9rem;
            border: 0;
            background: transparent;
            color: #2563eb;
        }

        @media (max-width: 1199.98px) {
            .login-template-image {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
<header class="adminuiux-header">
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('vendor/learninguiux/img/nfc-logo.png') }}" alt="TotaleLearning Hub" style="height:36px;width:auto;">
                <div>
                    <span class="h4">Totale<span class="fw-bold">Learning</span> <span>Hub</span></span>
                    <p class="company-tagline">Learner workspace</p>
                </div>
            </a>
        </div>
    </nav>
</header>

<main class="flex-shrink-0 pt-0 z-index-1">
    <div class="container">
        <div class="login-template-shell">
            <div class="login-template-image">
                <img
                    src="{{ asset('vendor/learninguiux/img/learning/1.jpg') }}"
                    alt="Learning background"
                    loading="lazy"
                    decoding="async"
                >
            </div>

            <div class="row justify-content-center minheight-dynamic position-relative" style="--mih-dynamic: calc(100vh - 120px); z-index: 1;">
                <div class="col-12 col-md-8 col-xl-6">
                    <div class="h-100 py-4 px-md-3">
                        <div class="row h-100 align-items-center justify-content-center mt-md-3">
                            <div class="col-12 col-sm-9 col-md-11 col-xl-11 col-xxl-10">
                                <div class="login-template-card card shadow-sm mb-2">
                                    <div class="card-body p-4 p-md-5">
                                        <div class="text-center mb-4">
                                            <h2 class="mb-1">Student Login</h2>
                                            <p class="text-secondary mb-0">Enter your credentials to log in</p>
                                        </div>

                                        <x-auth-session-status class="mb-3 text-center text-sm text-emerald-700" :status="session('status')" />

                                        <form id="login-form" method="POST" action="{{ route('login') }}">
                                            @csrf
                                            <input type="hidden" name="payload_encoding" id="payload_encoding" value="">

                                            <div class="form-floating mb-3">
                                                <input
                                                    type="email"
                                                    class="form-control login-template-field @error('email') is-invalid @enderror"
                                                    id="email"
                                                    name="email"
                                                    placeholder="Enter email address"
                                                    value="{{ old('email') }}"
                                                    required
                                                    autofocus
                                                    autocomplete="username"
                                                >
                                                <label for="email">Email Address</label>
                                            </div>
                                            @error('email')
                                                <div class="mb-3 text-sm text-danger">{{ $message }}</div>
                                            @enderror

                                            <div class="position-relative">
                                                <div class="form-floating mb-3">
                                                    <input
                                                        type="password"
                                                        class="form-control login-template-field @error('password') is-invalid @enderror"
                                                        id="password"
                                                        name="password"
                                                        placeholder="Enter your password"
                                                        required
                                                        autocomplete="current-password"
                                                    >
                                                    <label for="password">Password</label>
                                                </div>
                                                <button type="button" class="login-template-password-toggle" data-login-password-toggle aria-label="Show password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            @error('password')
                                                <div class="mb-3 text-sm text-danger">{{ $message }}</div>
                                            @enderror

                                            <div class="row align-items-center mb-3">
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                                        <label class="form-check-label" for="remember_me">Remember me</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-4">
                                                <div class="col">
                                                    <button type="submit" class="btn btn-lg btn-theme w-100">Login</button>
                                                </div>
                                                <div class="col">
                                                    @if (Route::has('register'))
                                                        <a href="{{ route('register') }}" class="btn btn-lg btn-link w-100">Signup <i class="bi bi-chevron-right"></i></a>
                                                    @endif
                                                </div>
                                            </div>

                                        </form>

                                        <div class="row align-items-center my-4">
                                            <div class="col"><hr></div>
                                            <div class="col-auto">
                                                <p class="text-secondary mb-0">or sign in with a magic link</p>
                                            </div>
                                            <div class="col"><hr></div>
                                        </div>

                                        <form method="POST" action="{{ route('magic-link.send') }}">
                                            @csrf
                                            <div class="input-group">
                                                <input
                                                    type="email"
                                                    class="form-control login-template-field"
                                                    name="email"
                                                    placeholder="Enter your email"
                                                    value="{{ old('email') }}"
                                                    required
                                                >
                                                <button type="submit" class="btn btn-outline-theme">
                                                    <i class="bi bi-envelope me-1"></i> Send Link
                                                </button>
                                            </div>
                                            <div class="form-text text-center mt-2">We'll email you a one-click login link</div>
                                        </form>

                                        <div class="text-center mt-4 pt-3 border-top">
                                            <p class="text-secondary small mb-1">Are you a parent?</p>
                                            <a href="{{ route('parent.register') }}" class="btn btn-outline-theme btn-sm">
                                                <i class="bi bi-people me-1"></i> Parent Registration
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @if (Route::has('password.request'))
                                    <p class="mb-4">
                                        <a href="{{ route('password.request') }}" class="small">Forget Password?</a>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="adminuiux-footer mt-auto">
    <div class="container-fluid text-center">
        <span class="small">Copyright @2026, TotaleLearning Hub</span>
    </div>
</footer>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('login-form');
        if (!form) {
            return;
        }

        const emailInput = form.querySelector('input[name="email"]');
        const passwordInput = form.querySelector('input[name="password"]');
        const encodingInput = form.querySelector('#payload_encoding');
        const passwordToggle = document.querySelector('[data-login-password-toggle]');

        const toBase64 = (value) => {
            const bytes = new TextEncoder().encode(value);
            let binary = '';
            bytes.forEach((byte) => {
                binary += String.fromCharCode(byte);
            });
            return btoa(binary);
        };

        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                passwordToggle.innerHTML = `<i class="bi bi-eye${isPassword ? '-slash' : ''}"></i>`;
            });
        }

        form.addEventListener('submit', (event) => {
            const emailValue = emailInput ? emailInput.value.trim() : '';
            const passwordValue = passwordInput ? passwordInput.value : '';

            if (emailInput) {
                emailInput.value = emailValue;
            }

            if (emailValue === '' || passwordValue.trim() === '') {
                event.preventDefault();

                if (emailInput && emailValue === '') {
                    emailInput.focus();
                } else if (passwordInput) {
                    passwordInput.focus();
                }

                return;
            }

            if (emailInput) {
                emailInput.value = toBase64(emailValue);
            }

            if (passwordInput) {
                passwordInput.value = toBase64(passwordValue);
            }

            if (encodingInput) {
                encodingInput.value = 'base64';
            }
        });
    })();
</script>
@endpush
