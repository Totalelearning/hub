<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('vendor/learninguiux/img/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300..800&family=SUSE:wght@100..800&display=swap" rel="stylesheet">
    <style>
        :root {
            --adminuiux-content-font: "Open Sans", sans-serif;
            --adminuiux-content-font-weight: 400;
            --adminuiux-title-font: "SUSE", sans-serif;
            --adminuiux-title-font-weight: 600;
        }
        .admin-refined-header .navbar {
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
        }
        .admin-refined-header .sidebar-toggler {
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: 0 12px 28px -24px rgba(43, 82, 138, 0.35);
        }
        .admin-refined-header .navbar-brand {
            gap: 0.9rem;
            padding: 0.45rem 0.7rem;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: 0 16px 34px -28px rgba(43, 82, 138, 0.32);
        }
        .admin-refined-header .navbar-brand .company-tagline {
            color: #6b85ab;
        }
        .admin-refined-header .btn-outline-theme.btn-sm {
            min-height: 40px;
            border-radius: 999px;
            border-color: rgba(174, 191, 216, 0.82);
            background: rgba(255, 255, 255, 0.88);
        }
        .admin-refined-header .btn-link-header,
        .admin-refined-header .btn-link.btn-square {
            background: rgba(255, 255, 255, 0.82);
            box-shadow: 0 12px 28px -24px rgba(43, 82, 138, 0.28);
        }
        .adminuiux-content.has-sidebar #main-content {
            max-width: 1520px;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('vendor/learninguiux/css/app.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup" data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent">
    @include('app.partials.admin-header')

    <div class="adminuiux-wrap">
        @include('app.partials.admin-sidebar')

        <main class="adminuiux-content has-sidebar" onclick="contentClick()">
            <div class="container mt-4" id="main-content">
                @isset($header)
                    <div class="mb-4">
                        {{ $header }}
                    </div>
                @endisset

                {{ $slot }}
            </div>
        </main>
    </div>

    <script src="{{ asset('vendor/learninguiux/js/app.js') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
