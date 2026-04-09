<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Learning AdminUIUX')</title>
    <link rel="icon" type="image/png" href="{{ asset('vendor/learninguiux/img/favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300..800&family=SUSE:wght@100..800&display=swap" rel="stylesheet">
    <style>
        :root {
            --adminuiux-content-font: "Open Sans", sans-serif;
            --adminuiux-content-font-weight: 400;
            --adminuiux-title-font: "SUSE", sans-serif;
            --adminuiux-title-font-weight: 600;
            --admin-feed-card-radius: 1.75rem;
            --admin-feed-card-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
            --admin-feed-card-border: 1px solid rgba(226, 232, 240, 0.92);
            --admin-feed-band-radius: 1.4rem;
            --admin-feed-band-bg: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
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

        .admin-refined-header .navbar-brand .h4 {
            letter-spacing: -0.02em;
        }

        .admin-refined-header .navbar-brand .company-tagline {
            color: #6b85ab;
        }

        .admin-refined-header .btn-outline-theme.btn-sm {
            min-height: 40px;
            border-radius: 999px;
            border-color: rgba(174, 191, 216, 0.82);
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 12px 28px -24px rgba(43, 82, 138, 0.24);
        }

        .admin-refined-header .btn-link-header,
        .admin-refined-header .btn-link.btn-square {
            background: rgba(255, 255, 255, 0.82);
            box-shadow: 0 12px 28px -24px rgba(43, 82, 138, 0.28);
        }

        .adminuiux-content.has-sidebar #main-content {
            max-width: 1520px;
        }

        .adminuiux-content.has-sidebar #main-content > .mb-4:first-child {
            overflow: hidden;
            border-radius: var(--admin-feed-card-radius) !important;
            border: 1px solid rgba(255, 255, 255, 0.8);
            background: linear-gradient(135deg, rgba(236, 249, 255, 0.98), rgba(247, 245, 255, 0.98));
            box-shadow: var(--admin-feed-card-shadow);
        }

        .adminuiux-content.has-sidebar #main-content > .mb-4:first-child > div:first-child {
            gap: 1rem;
            padding: 1.35rem 1.45rem;
        }

        .adminuiux-content.has-sidebar #main-content > .mb-4:first-child > div:first-child > div:first-child {
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(10px);
            padding: 1.1rem 1.2rem;
        }

        .adminuiux-content.has-sidebar #main-content > .mb-4:first-child h1,
        .adminuiux-content.has-sidebar #main-content > .mb-4:first-child h2 {
            letter-spacing: -0.03em;
        }

        .adminuiux-content.has-sidebar #main-content > .mb-4:first-child .rounded-full,
        .adminuiux-content.has-sidebar #main-content > .mb-4:first-child .rounded-\[0\.55rem\],
        .adminuiux-content.has-sidebar #main-content > .mb-4:first-child .rounded-\[0\.35rem\] {
            border-radius: 0.45rem !important;
        }

        .adminuiux-content.has-sidebar .admin-unified-card,
        .adminuiux-content.has-sidebar .assignment-report-summary-card,
        .adminuiux-content.has-sidebar .compliance-report-card,
        .adminuiux-content.has-sidebar .event-report-card,
        .adminuiux-content.has-sidebar .scorm-report-card,
        .adminuiux-content.has-sidebar .user-form-card,
        .adminuiux-content.has-sidebar .users-index-card {
            border-radius: var(--admin-feed-card-radius);
            border: var(--admin-feed-card-border);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--admin-feed-card-shadow);
        }

        .adminuiux-content.has-sidebar .assignment-report-action,
        .adminuiux-content.has-sidebar .compliance-report-chip,
        .adminuiux-content.has-sidebar .compliance-report-filter-pill,
        .adminuiux-content.has-sidebar .scorm-action-card,
        .adminuiux-content.has-sidebar .users-control-card,
        .adminuiux-content.has-sidebar .users-filter-chip,
        .adminuiux-content.has-sidebar .users-filter-input,
        .adminuiux-content.has-sidebar .users-filter-select {
            border-radius: 1rem !important;
        }

        .adminuiux-content.has-sidebar .compliance-report-band,
        .adminuiux-content.has-sidebar .event-report-band,
        .adminuiux-content.has-sidebar .scorm-report-band,
        .adminuiux-content.has-sidebar .users-index-band {
            border-radius: var(--admin-feed-band-radius);
            background: var(--admin-feed-band-bg);
        }

        .adminuiux-content.has-sidebar [class*="rounded-[1.9rem]"],
        .adminuiux-content.has-sidebar [class*="rounded-[1.75rem]"],
        .adminuiux-content.has-sidebar [class*="rounded-[1.5rem]"],
        .adminuiux-content.has-sidebar [class*="rounded-[1.4rem]"],
        .adminuiux-content.has-sidebar [class*="rounded-[1.35rem]"] {
            border-radius: 1.35rem !important;
        }

        .adminuiux-content.has-sidebar .rounded-full.bg-sky-600,
        .adminuiux-content.has-sidebar .rounded-full.bg-indigo-600,
        .adminuiux-content.has-sidebar .rounded-full.border,
        .adminuiux-content.has-sidebar .rounded-\[0\.6rem\],
        .adminuiux-content.has-sidebar .rounded-\[0\.55rem\],
        .adminuiux-content.has-sidebar .rounded-\[0\.35rem\] {
            border-radius: 1rem !important;
        }

        .adminuiux-content.has-sidebar .text-xs.font-semibold.uppercase.tracking-\[0\.26em\],
        .adminuiux-content.has-sidebar .text-xs.font-semibold.uppercase.tracking-\[0\.3em\] {
            letter-spacing: 0.18em !important;
        }

        .adminuiux-content.has-sidebar .admin-feed-soft-card {
            border-radius: var(--admin-feed-card-radius);
            border: var(--admin-feed-card-border);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--admin-feed-card-shadow);
        }

        .adminuiux-content.has-sidebar .admin-feed-soft-band {
            border-radius: var(--admin-feed-band-radius);
            background: var(--admin-feed-band-bg);
        }

        .adminuiux-content.has-sidebar .admin-feed-hero {
            overflow: hidden;
            border-radius: 1.9rem;
            border: 1px solid rgba(255, 255, 255, 0.8);
            background: linear-gradient(135deg, rgba(236, 249, 255, 0.98), rgba(247, 245, 255, 0.98));
            box-shadow: var(--admin-feed-card-shadow);
        }

        .adminuiux-content.has-sidebar .admin-feed-hero-copy {
            border-radius: 1.55rem;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(10px);
            padding: 1.1rem 1.2rem;
        }

        .adminuiux-content.has-sidebar .admin-feed-action {
            border-radius: 0.8rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(248, 250, 252, 0.98);
            box-shadow: 0 10px 24px rgba(43, 82, 138, 0.08);
        }

        .adminuiux-content.has-sidebar .admin-feed-kpi {
            overflow: hidden;
            border-radius: 28px;
            border: 0;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
            min-height: 10.5rem;
        }

        .adminuiux-content.has-sidebar .admin-feed-kpi-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4rem;
            height: 4rem;
            border-radius: 1.25rem;
            color: #2454db;
            background: linear-gradient(135deg, rgba(213, 226, 255, 0.96), rgba(227, 236, 255, 0.96));
            box-shadow: none;
        }

        .adminuiux-content.has-sidebar .admin-feed-kpi-stat {
            border-radius: 18px;
            border: 0;
            background: rgba(246, 249, 255, 0.95);
            padding: 0.85rem 1rem;
        }

        .adminuiux-content.has-sidebar .admin-feed-summary-card {
            border-radius: 1.9rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 20px 56px rgba(43, 82, 138, 0.1);
        }

        .adminuiux-content.has-sidebar .overflow-x-auto table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .adminuiux-content.has-sidebar .overflow-x-auto thead tr,
        .adminuiux-content.has-sidebar table thead tr {
            background: rgba(248, 250, 252, 0.92);
        }

        .adminuiux-content.has-sidebar .overflow-x-auto thead th,
        .adminuiux-content.has-sidebar table thead th {
            border-bottom: 1px solid rgba(226, 232, 240, 0.92);
            color: #8b9ab3 !important;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: none;
            white-space: nowrap;
        }

        .adminuiux-content.has-sidebar .overflow-x-auto tbody td,
        .adminuiux-content.has-sidebar table tbody td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            vertical-align: middle;
        }

        .adminuiux-content.has-sidebar .overflow-x-auto tbody tr:hover,
        .adminuiux-content.has-sidebar table tbody tr:hover {
            background: rgba(248, 250, 252, 0.82);
        }

        .adminuiux-content.has-sidebar input[type="text"],
        .adminuiux-content.has-sidebar input[type="email"],
        .adminuiux-content.has-sidebar input[type="number"],
        .adminuiux-content.has-sidebar input[type="date"],
        .adminuiux-content.has-sidebar input[type="password"],
        .adminuiux-content.has-sidebar select,
        .adminuiux-content.has-sidebar textarea {
            border-radius: 1rem !important;
            border-color: rgba(203, 213, 225, 0.92) !important;
            background: #fff;
            box-shadow: none !important;
            min-height: 2.9rem;
        }

        .adminuiux-content.has-sidebar textarea {
            min-height: 7rem;
        }

        .adminuiux-content.has-sidebar button:not(.btn):not(.sidebar-toggler),
        .adminuiux-content.has-sidebar a[class*="rounded"][class*="border"] {
            box-shadow: 0 10px 24px rgba(43, 82, 138, 0.08);
        }

        .adminuiux-content.has-sidebar .border-b.border-slate-200.px-5.py-4,
        .adminuiux-content.has-sidebar .border-b.border-slate-200.px-6.py-4,
        .adminuiux-content.has-sidebar .border-b.border-gray-200.px-5.py-4,
        .adminuiux-content.has-sidebar .border-b.border-gray-200.px-6.py-4 {
            background: var(--admin-feed-band-bg);
        }

        .adminuiux-content.has-sidebar .text-lg.font-semibold.text-slate-900,
        .adminuiux-content.has-sidebar .text-lg.font-semibold.text-gray-900 {
            letter-spacing: -0.02em;
        }

        .adminuiux-content.has-sidebar .rounded.border.border-gray-300,
        .adminuiux-content.has-sidebar .rounded-md.border-gray-300,
        .adminuiux-content.has-sidebar .rounded.border-gray-300 {
            border-radius: 1rem !important;
        }

        .adminuiux-content.has-sidebar .admin-workflow-grid {
            display: grid;
            gap: 1rem;
        }

        @media (min-width: 1280px) {
            .adminuiux-content.has-sidebar .admin-workflow-grid {
                grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
            }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('vendor/learninguiux/css/app.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="@yield('body_class', $bodyClass ?? '')" {!! trim($__env->yieldContent('body_attributes', $bodyAttributes ?? '')) !!}>
    @yield('content')

    <script src="{{ asset('vendor/learninguiux/js/app.js') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
