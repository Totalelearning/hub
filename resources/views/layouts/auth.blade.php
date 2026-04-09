<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Learning')</title>
    <link rel="icon" type="image/png" href="{{ asset('vendor/learninguiux/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/learninguiux/css/app.css') }}">

    <style>
        :root {
            --adminuiux-content-font: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --adminuiux-title-font: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --adminuiux-content-font-weight: 400;
            --adminuiux-title-font-weight: 600;
        }
    </style>

    @stack('styles')
</head>
<body class="@yield('body_class', $bodyClass ?? '')" {!! trim($__env->yieldContent('body_attributes', $bodyAttributes ?? '')) !!}>
    @yield('content')

    @stack('scripts')
</body>
</html>
