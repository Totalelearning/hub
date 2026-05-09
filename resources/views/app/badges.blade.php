@extends('layouts.learninguiux')

@section('title', 'Badges - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .badges-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }

    .badges-section-title {
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }

    .badge-tile {
        border: 2px solid #e9ecef;
        border-radius: 20px;
        padding: 1.5rem 1rem;
        text-align: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        background: #fff;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .badge-tile.earned {
        border-color: rgba(99, 102, 241, 0.3);
        box-shadow: 0 4px 16px rgba(99, 102, 241, 0.1);
    }

    .badge-tile:not(.earned) {
        opacity: 0.5;
        filter: grayscale(0.6);
    }

    .badge-tile .badge-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .badge-tile.earned .badge-icon {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(168, 85, 247, 0.15));
        color: #6366f1;
    }

    .badge-tile:not(.earned) .badge-icon {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .category-label {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: capitalize;
        color: #334155;
    }
</style>
@endpush

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'badges'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4">

            {{-- Header --}}
            <div class="card badges-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="badges-section-title d-inline-block mb-2">Badges</span>
                            <h3 class="mb-1">Your Achievements</h3>
                            <p class="text-secondary mb-0">Earn badges by completing courses, maintaining streaks, and reaching milestones.</p>
                        </div>
                        <div class="text-end">
                            <div class="d-inline-flex align-items-center gap-2 bg-light rounded-pill px-3 py-2">
                                <i class="bi bi-award text-primary"></i>
                                <span class="fw-semibold">{{ $earnedCount }} / {{ $totalCount }}</span>
                                <span class="text-secondary small">earned</span>
                            </div>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    @if ($totalCount > 0)
                        <div class="mt-3">
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: {{ round(($earnedCount / $totalCount) * 100) }}%"
                                     aria-valuenow="{{ $earnedCount }}" aria-valuemin="0" aria-valuemax="{{ $totalCount }}">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @php
                $categoryLabels = [
                    'achievement' => 'Achievements',
                    'mastery' => 'Mastery',
                    'streak' => 'Streaks',
                    'topic' => 'Topic Mastery',
                ];
                $categoryIcons = [
                    'achievement' => 'bi-trophy',
                    'mastery' => 'bi-bullseye',
                    'streak' => 'bi-fire',
                    'topic' => 'bi-bookmark-star',
                ];
            @endphp

            @foreach ($categories as $category => $badges)
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi {{ $categoryIcons[$category] ?? 'bi-award' }} text-primary"></i>
                        <span class="category-label">{{ $categoryLabels[$category] ?? ucfirst($category) }}</span>
                        <span class="badge bg-light text-secondary border">{{ $badges->where('is_earned', true)->count() }} / {{ $badges->count() }}</span>
                    </div>
                    <div class="row g-3">
                        @foreach ($badges as $badge)
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="badge-tile {{ $badge->is_earned ? 'earned' : '' }}">
                                    <div class="badge-icon">
                                        <i class="bi {{ $badge->icon }}"></i>
                                    </div>
                                    <h6 class="mb-1" style="font-size: 0.85rem;">{{ $badge->name }}</h6>
                                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">{{ $badge->description }}</p>
                                    @if ($badge->is_earned && $badge->earned_at_date)
                                        <span class="badge bg-success-subtle text-success mt-2" style="font-size: 0.65rem;">
                                            <i class="bi bi-check-circle"></i> {{ \Illuminate\Support\Carbon::parse($badge->earned_at_date)->format('M j, Y') }}
                                        </span>
                                    @elseif ($badge->xp_reward > 0)
                                        <span class="badge bg-primary-subtle text-primary mt-2" style="font-size: 0.65rem;">
                                            +{{ $badge->xp_reward }} XP
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </main>
</div>
@endsection
