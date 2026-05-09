@extends('layouts.learninguiux')

@section('title', 'Gamification Settings - Admin')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .settings-card {
        border-radius: 1.75rem;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
    }
    .gam-section-title {
        font-size: 0.78rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #5f7699;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">

            <div class="mb-4 admin-feed-hero">
                <div class="row align-items-center g-0 p-4 p-lg-5">
                    <div class="col-12 col-lg-8">
                        <h1 class="fs-3 fw-semibold mb-2">Gamification Settings</h1>
                        <p class="text-secondary mb-0">Configure XP values for each qualifying action. Changes apply to future awards only.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('app.admin.gamification') }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card settings-card mb-4">
                <div class="card-header px-4 py-3 border-bottom">
                    <span class="gam-section-title">XP Awards</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('app.admin.gamification.settings.update') }}">
                        @csrf

                        @php
                            $labels = [
                                'course_completed' => ['Course Completed', 'XP awarded when a learner completes a course.'],
                                'reinforcement_completed' => ['Knowledge Check Completed', 'XP for completing a reinforcement attempt (pass or fail).'],
                                'perfect_score' => ['Perfect Score Bonus', 'Additional XP for scoring 100% on a knowledge check.'],
                                'streak_3_day' => ['3-Day Streak', 'Bonus XP at 3 consecutive days.'],
                                'streak_5_day' => ['5-Day Streak', 'Bonus XP at 5 consecutive days.'],
                                'streak_7_day' => ['7-Day Streak (1 Week)', 'Bonus XP at 7 consecutive days.'],
                                'streak_14_day' => ['14-Day Streak (2 Weeks)', 'Bonus XP at 14 consecutive days.'],
                                'streak_30_day' => ['30-Day Streak (1 Month)', 'Bonus XP at 30 consecutive days.'],
                            ];
                        @endphp

                        <div class="row g-4">
                            @foreach ($settings as $key => $value)
                                @php
                                    $label = $labels[$key][0] ?? ucwords(str_replace('_', ' ', $key));
                                    $help = $labels[$key][1] ?? '';
                                    $default = $defaults[$key] ?? 0;
                                    $isModified = (int)$value !== (int)$default;
                                @endphp
                                <div class="col-12 col-md-6">
                                    <label for="settings_{{ $key }}" class="form-label fw-semibold">
                                        {{ $label }}
                                        @if ($isModified)
                                            <span class="badge bg-warning-subtle text-warning ms-1" style="font-size: .65rem;">Modified</span>
                                        @endif
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               id="settings_{{ $key }}"
                                               name="settings[{{ $key }}]"
                                               value="{{ $value }}"
                                               class="form-control"
                                               min="0"
                                               max="1000">
                                        <span class="input-group-text">XP</span>
                                    </div>
                                    @if ($help)
                                        <div class="form-text">{{ $help }} <span class="text-secondary">(Default: {{ $default }})</span></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Save Settings</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('app.admin.gamification.settings.reset') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Reset all gamification settings to defaults?')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to Defaults
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
