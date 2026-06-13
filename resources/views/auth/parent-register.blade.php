@extends('layouts.auth')

@section('title', 'Parent Registration - Learning')
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
                    <p class="company-tagline">Parent portal</p>
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
                    src="{{ asset('vendor/learninguiux/img/learning/3.jpg') }}"
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
                                            <span class="badge rounded-pill text-bg-primary mb-2">Parent Account</span>
                                            <h2 class="mb-1">Create your account</h2>
                                            <p class="text-secondary mb-0">Sign up to access training assigned by your child's school</p>
                                        </div>

                                        <form method="POST" action="{{ route('parent.register') }}">
                                            @csrf

                                            <div class="form-floating mb-3">
                                                <input
                                                    type="text"
                                                    class="form-control login-template-field @error('name') is-invalid @enderror"
                                                    id="name"
                                                    name="name"
                                                    placeholder="Full name"
                                                    value="{{ old('name') }}"
                                                    required
                                                    autofocus
                                                    autocomplete="name"
                                                >
                                                <label for="name">Full Name</label>
                                            </div>
                                            @error('name')
                                                <div class="mb-3 text-sm text-danger">{{ $message }}</div>
                                            @enderror

                                            <div class="form-floating mb-3">
                                                <input
                                                    type="email"
                                                    class="form-control login-template-field @error('email') is-invalid @enderror"
                                                    id="email"
                                                    name="email"
                                                    placeholder="Email address"
                                                    value="{{ old('email') }}"
                                                    required
                                                    autocomplete="username"
                                                >
                                                <label for="email">Email Address</label>
                                            </div>
                                            @error('email')
                                                <div class="mb-3 text-sm text-danger">{{ $message }}</div>
                                            @enderror

                                            <div class="form-floating mb-3">
                                                <input
                                                    type="password"
                                                    class="form-control login-template-field @error('password') is-invalid @enderror"
                                                    id="password"
                                                    name="password"
                                                    placeholder="Password"
                                                    required
                                                    autocomplete="new-password"
                                                >
                                                <label for="password">Password</label>
                                            </div>
                                            @error('password')
                                                <div class="mb-3 text-sm text-danger">{{ $message }}</div>
                                            @enderror

                                            <div class="form-floating mb-4">
                                                <input
                                                    type="password"
                                                    class="form-control login-template-field"
                                                    id="password_confirmation"
                                                    name="password_confirmation"
                                                    placeholder="Confirm password"
                                                    required
                                                    autocomplete="new-password"
                                                >
                                                <label for="password_confirmation">Confirm Password</label>
                                            </div>

                                            <button type="submit" class="btn btn-lg btn-theme w-100 mb-3">Create Account</button>

                                            <p class="text-center text-secondary small mb-0">
                                                Already have an account? <a href="{{ route('login') }}">Sign in</a>
                                            </p>
                                        </form>
                                    </div>
                                </div>
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
