@extends('layouts.learninguiux')

@section('title', 'Gamification - Admin')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .gam-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 32px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }
    .gam-stat-card {
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(248, 250, 252, 0.95);
        padding: 1.25rem;
    }
    .gam-section-title {
        font-size: 0.78rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #5f7699;
        font-weight: 700;
    }
    .gam-badge-bar {
        height: 6px;
        border-radius: 3px;
        background: rgba(226, 232, 240, 0.6);
    }
    .gam-badge-bar .bar {
        height: 100%;
        border-radius: 3px;
        transition: width 300ms ease;
    }
</style>
@endpush

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">

            {{-- Hero --}}
            <div class="mb-4 admin-feed-hero">
                <div class="row align-items-center g-0 p-4 p-lg-5">
                    <div class="col-12 col-lg-8 admin-feed-hero-copy">
                        <h1 class="fs-3 fw-semibold mb-2">Gamification</h1>
                        <p class="text-secondary mb-0">XP leaderboards, streak activity, and badge distribution across all learners.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('app.admin.gamification.export') }}" class="btn btn-theme btn-sm me-2"><i class="bi bi-download me-1"></i>Export CSV</a>
                        @if (auth()->user()?->isSiteAdmin())
                            <a href="{{ route('app.admin.gamification.settings') }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-sliders me-1"></i>Settings</a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="gam-stat-card">
                        <div class="small text-secondary">Total XP Awarded</div>
                        <div class="fs-3 fw-bold text-primary mt-1">{{ number_format($totalXpAwarded) }}</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="gam-stat-card">
                        <div class="small text-secondary">Active Streaks</div>
                        <div class="fs-3 fw-bold text-warning mt-1">{{ $activeStreaks }}</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="gam-stat-card">
                        <div class="small text-secondary">Badges Earned</div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ $badgesEarned }}</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="gam-stat-card">
                        <div class="small text-secondary">Avg XP per Learner</div>
                        <div class="fs-3 fw-bold text-info mt-1">{{ number_format($avgXp) }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Team Leaderboard --}}
                <div class="col-12 col-lg-6">
                    <div class="card gam-card">
                        <div class="card-header px-4 py-3 border-bottom">
                            <span class="gam-section-title">Team Leaderboard</span>
                        </div>
                        <div class="card-body p-0">
                            @if ($teamLeaderboard->isEmpty())
                                <div class="text-center py-4 text-secondary">No team data.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4" style="width: 50px;">#</th>
                                                <th>Team</th>
                                                <th class="text-center">Members</th>
                                                <th class="text-center">Avg XP</th>
                                                <th class="text-end pe-4">Total XP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($teamLeaderboard as $team)
                                                <tr>
                                                    <td class="ps-4 fw-semibold">{{ $team->rank }}</td>
                                                    <td>{{ $team->team }}</td>
                                                    <td class="text-center">{{ $team->member_count }}</td>
                                                    <td class="text-center">{{ number_format($team->avg_xp) }}</td>
                                                    <td class="text-end pe-4 fw-semibold">{{ number_format($team->total_xp) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Top Earners --}}
                <div class="col-12 col-lg-6">
                    <div class="card gam-card">
                        <div class="card-header px-4 py-3 border-bottom">
                            <span class="gam-section-title">Top Earners</span>
                        </div>
                        <div class="card-body p-0">
                            @if ($topEarners->isEmpty())
                                <div class="text-center py-4 text-secondary">No XP earned yet.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4" style="width: 50px;">#</th>
                                                <th>Learner</th>
                                                <th>Team</th>
                                                <th class="text-end pe-4">XP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topEarners as $i => $earner)
                                                <tr>
                                                    <td class="ps-4 fw-semibold">{{ $i + 1 }}</td>
                                                    <td>
                                                        <a href="{{ route('app.admin.users.show', $earner->id) }}" class="text-decoration-none">{{ $earner->name }}</a>
                                                    </td>
                                                    <td class="text-secondary small">{{ $earner->team ?? '—' }}</td>
                                                    <td class="text-end pe-4 fw-semibold">{{ number_format($earner->total_xp) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Badge Distribution --}}
            <div class="card gam-card mt-4 mb-4">
                <div class="card-header px-4 py-3 border-bottom">
                    <span class="gam-section-title">Badge Distribution</span>
                </div>
                <div class="card-body p-4">
                    @if ($badgeDistribution->isEmpty())
                        <div class="text-center py-4 text-secondary">No badges configured yet.</div>
                    @else
                        <div class="row g-3">
                            @foreach ($badgeDistribution as $badge)
                                @php
                                    $pct = $totalLearners > 0 ? round(($badge->users_count / $totalLearners) * 100) : 0;
                                @endphp
                                <div class="col-12 col-md-6 col-xl-4">
                                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light">
                                        <div class="avatar avatar-40 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0">
                                            <i class="bi {{ $badge->icon }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0" style="font-size: .85rem;">{{ $badge->name }}</h6>
                                                <span class="badge bg-primary-subtle text-primary" style="font-size: .7rem;">{{ $badge->users_count }} earned</span>
                                            </div>
                                            <div class="gam-badge-bar mt-1">
                                                <div class="bar bg-primary" style="width: {{ $pct }}%;"></div>
                                            </div>
                                            <div class="text-secondary mt-1" style="font-size: .7rem;">{{ $pct }}% of {{ $totalLearners }} learners</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
