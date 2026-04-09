@extends('layouts.learninguiux')

@section('title', 'Reminder Settings - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">

            {{-- Hero --}}
            <div class="mb-4 admin-feed-hero">
                <div class="row align-items-center g-0 p-4 p-lg-5">
                    <div class="col-12 col-lg-8 admin-feed-hero-copy mb-3 mb-lg-0">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Automation Tuning</div>
                        <h1 class="fs-3 fw-semibold mb-2">Reminder Settings</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('app.admin.assignments') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Reminder Settings</li>
                            </ol>
                        </nav>
                        <p class="text-secondary mb-0">Tune nudge trigger windows and cooldowns for reminder automation.</p>
                    </div>
                    <div class="col-12 col-lg-4 d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('app.admin.assignments') }}" class="btn btn-outline-theme btn-sm">Back to Dashboard</a>
                    </div>
                </div>
            </div>

            {{-- Status flash --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            {{-- KPI cards --}}
            <div class="row g-4 mb-4">
                @foreach ($settings as $key => $value)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                            <div class="card-body d-flex flex-column align-items-center text-center p-4">
                                <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-bell fs-3"></i></div>
                                <div class="admin-feed-kpi-stat">{{ old("settings.$key", $value) }}</div>
                                <div class="fw-semibold mt-1">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                                <div class="small text-secondary mt-auto pt-2">Default {{ $defaults[$key] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Settings form --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header bg-primary-subtle">
                    <div class="fw-semibold">Reminder Controls</div>
                    <div class="small text-secondary mt-1">Adjust the windows and cooldowns that shape reminder automation across required learning.</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('app.admin.reminder-settings.update') }}">
                        @csrf
                        @foreach ($settings as $key => $value)
                            <div class="row g-2 align-items-center mb-3">
                                <div class="col-12 col-md-4">
                                    <label for="settings_{{ $key }}" class="form-label mb-0 text-capitalize">{{ str_replace('_', ' ', $key) }}</label>
                                </div>
                                <div class="col-12 col-md-4">
                                    <input id="settings_{{ $key }}" type="number" min="0" max="365" name="settings[{{ $key }}]" value="{{ old("settings.$key", $value) }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <span class="form-text">Default: {{ $defaults[$key] ?? 0 }}</span>
                                </div>
                                @error("settings.$key")
                                    <div class="col-12 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-end gap-2 pt-3">
                            <button type="submit" formnovalidate formaction="{{ route('app.admin.reminder-settings.reset') }}" class="btn btn-outline-secondary btn-sm">Reset to Defaults</button>
                            <button type="submit" class="btn btn-theme">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
