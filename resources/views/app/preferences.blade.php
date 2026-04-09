@extends('layouts.learninguiux')

@section('title', 'Preferences - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .learner-preferences-panel,
    .learner-preferences-summary {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }

    .learner-preferences-title {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }

    .learner-preferences-summary {
        min-height: 132px;
    }
</style>
@endpush

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'preferences'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
            <section class="card learner-preferences-panel mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="learner-preferences-title d-inline-block mb-2">Preferences</span>
                            <h3 class="mb-1">Personalise your learning experience</h3>
                            <p class="text-secondary mb-0">Tune topics, role, goal, and difficulty so your dashboard, reminders, saved modules, and paths stay aligned to what matters next.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">Back to dashboard</a>
                            <a href="{{ route('app.reminders') }}" class="btn btn-theme btn-sm">Open reminders</a>
                        </div>
                    </div>
                </div>
            </section>

            <livewire:user-preferences-page />
        </div>
    </main>
</div>
@endsection
