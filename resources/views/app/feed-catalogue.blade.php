@extends('layouts.learninguiux')

@section('title', $catalogueTitle.' - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .learner-panel-card {
        border: 0;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
    }

    .learner-catalogue-summary {
        min-height: 132px;
    }

    .learner-section-title {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }
 </style>
@endpush

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => $activeLearnerPage ?? 'dashboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
            <div class="card learner-panel-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="learner-section-title d-inline-block mb-2">{{ $catalogueSectionLabel ?? 'Catalogue' }}</span>
                            <h3 class="mb-1">{{ $catalogueTitle }}</h3>
                            <p class="text-secondary mb-0">{{ $catalogueSubtitle }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge text-bg-light border rounded-pill">{{ $catalogueCountLabel }}</span>
                            <a href="{{ $cataloguePrimaryCtaHref ?? route('app.feed') }}" class="btn btn-theme btn-sm">{{ $cataloguePrimaryCtaLabel ?? 'Back to dashboard' }}</a>
                            <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>

            @if (($catalogueSummaryCards ?? collect())->isNotEmpty())
                <div class="row g-3 mb-4">
                    @foreach ($catalogueSummaryCards as $summaryCard)
                        <div class="col-md-4">
                            <div class="card learner-panel-card learner-catalogue-summary">
                                <div class="card-body p-3">
                                    <div class="small text-secondary">{{ $summaryCard['label'] }}</div>
                                    <div class="fs-3 fw-semibold mt-1">{{ $summaryCard['value'] }}</div>
                                    <div class="small text-secondary mt-2">{{ $summaryCard['summary'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (($assignedCourses ?? collect())->isNotEmpty())
                <div class="card learner-panel-card mb-4">
                    <div class="card-body p-4">
                        <span class="learner-section-title d-inline-block mb-2">{{ $courseSectionLabel ?? 'Your Courses' }}</span>
                        <h5 class="mb-1">{{ $courseSectionTitle ?? 'Assigned courses' }}</h5>
                        <p class="text-secondary mb-3">{{ $courseSectionSubtitle ?? 'Courses assigned to you based on your role.' }}</p>
                        <div class="row">
                            @foreach ($assignedCourses as $course)
                                @include('app.partials.feed-course-card', ['course' => $course])
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if (($catalogueModules ?? collect())->isNotEmpty())
                <div class="card learner-panel-card mb-4">
                    <div class="card-body p-4">
                        <span class="learner-section-title d-inline-block mb-2">Modules</span>
                        <h5 class="mb-3">Individual modules</h5>
                        <div class="row">
                            @foreach ($catalogueModules as $module)
                                @include('app.partials.feed-module-card', [
                                    'module' => $module,
                                    'savedModuleIds' => $savedModuleIds,
                                    'cardContext' => $activeLearnerPage ?? 'dashboard',
                                ])
                            @endforeach
                        </div>
                    </div>
                </div>
            @elseif ($catalogueEmptyMessage ?? null)
                <div class="card learner-panel-card">
                    <div class="card-body p-4 text-secondary">
                        {{ $catalogueEmptyMessage }}
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
