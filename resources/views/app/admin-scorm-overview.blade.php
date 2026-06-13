@extends('layouts.learninguiux')

@section('title', 'SCORM Overview - Learning')
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
                    <div class="col-12 col-lg-8 admin-feed-hero-copy">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">SCORM Reports</div>
                        <h1 class="fs-3 fw-semibold mb-2">{{ __('SCORM Overview') }}</h1>
                        <p class="text-secondary mb-3">SCORM module completion, learner results, attempt history, and score tracking.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.admin.modules.index') }}" class="btn btn-outline-theme btn-sm">Manage Modules</a>
                            <a href="{{ route('app.admin.scorm.export') }}" class="btn btn-theme btn-sm"><i class="bi bi-download me-1"></i>Export CSV</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary card with donut + stats --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-lg-auto text-center">
                            <div style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:9rem;height:9rem;border-radius:50%;background:radial-gradient(closest-side,#fff 78%,transparent 79% 100%),conic-gradient(#38b86f calc({{ $summary['completion_rate'] ?? 0 }} * 1%),#e5e7eb 0);">
                                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:112px;height:112px;border-radius:50%;background:#fff;box-shadow:inset 0 2px 4px rgba(0,0,0,.06);">
                                    <div class="fs-3 fw-semibold">{{ $summary['completion_rate'] ?? 0 }}%</div>
                                    <div class="text-uppercase fw-semibold text-secondary" style="font-size:.68rem;letter-spacing:.18em;">Complete</div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row row-cols-2 row-cols-lg-6 g-3 text-center text-lg-start">
                                <div class="col">
                                    <div class="small text-secondary">Modules</div>
                                    <div class="fs-2 fw-semibold">{{ $summary['modules'] }}</div>
                                </div>
                                <div class="col">
                                    <div class="small text-secondary">Learners</div>
                                    <div class="fs-2 fw-semibold">{{ $summary['learners'] }}</div>
                                </div>
                                <div class="col">
                                    <div class="small text-secondary">Completed</div>
                                    <div class="fs-2 fw-semibold text-success">{{ $summary['completed'] }}</div>
                                </div>
                                <div class="col">
                                    <div class="small text-secondary">In Progress</div>
                                    <div class="fs-2 fw-semibold">{{ $summary['in_progress'] }}</div>
                                </div>
                                <div class="col">
                                    <div class="small text-secondary">Launches</div>
                                    <div class="fs-2 fw-semibold">{{ $summary['launches'] }}</div>
                                </div>
                                <div class="col">
                                    <div class="small text-secondary">Avg Session</div>
                                    <div class="fs-2 fw-semibold">{{ $summary['average_session_label'] }}</div>
                                    <div class="text-secondary" style="font-size:.75rem;">{{ $summary['attempts'] }} attempts</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts: 2x2 grid --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-6">
                    @include('app.partials.admin-report-donut', [
                        'eyebrow' => 'Learner Results',
                        'title' => 'SCORM completion breakdown',
                        'subtitle' => 'How the tracked SCORM learner population is progressing.',
                        'items' => [
                            ['label' => 'Completed', 'value' => $summary['completed'] ?? 0, 'color' => '#10b981', 'meta' => 'Learners with recorded completion'],
                            ['label' => 'In progress', 'value' => $summary['in_progress'] ?? 0, 'color' => '#3b82f6', 'meta' => 'Learners with partial SCORM progress'],
                            ['label' => 'Not started', 'value' => max(0, ($summary['learners'] ?? 0) - (($summary['completed'] ?? 0) + ($summary['in_progress'] ?? 0))), 'color' => '#94a3b8', 'meta' => 'Tracked learners without progress yet'],
                        ],
                        'centerValue' => $summary['completion_rate'] ?? 0,
                        'centerLabel' => 'Completion %',
                        'badge' => ($summary['learners'] ?? 0).' learners',
                    ])
                </div>
                <div class="col-12 col-xl-6">
                    @include('app.partials.admin-report-bars', [
                        'eyebrow' => 'Activity Volume',
                        'title' => 'Launches, attempts, and completions',
                        'subtitle' => 'See whether learners are opening modules but not finishing them.',
                        'items' => [
                            ['label' => 'Launches', 'value' => $summary['launches'] ?? 0, 'color' => '#0ea5e9', 'meta' => 'All recorded launch events'],
                            ['label' => 'Attempts', 'value' => $summary['attempts'] ?? 0, 'color' => '#8b5cf6', 'meta' => 'Runtime commit events'],
                            ['label' => 'Completions', 'value' => $summary['completed'] ?? 0, 'color' => '#10b981', 'meta' => 'Completed learner progress rows'],
                        ],
                        'badge' => $summary['average_session_label'] ?? 'n/a',
                    ])
                </div>
            </div>

            {{-- SCORM Modules table --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <div class="text-uppercase fw-semibold text-primary" style="letter-spacing:.2em;font-size:.7rem;">Modules</div>
                        <h6 class="fw-semibold mt-1 mb-0">SCORM Modules</h6>
                        <p class="small text-secondary mb-0">Published SCORM modules with completion and score summary.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Module</th>
                                    <th class="px-4 py-3">Learners</th>
                                    <th class="px-4 py-3">Completed</th>
                                    <th class="px-4 py-3">In Progress</th>
                                    <th class="px-4 py-3">Completion</th>
                                    <th class="px-4 py-3">Launches</th>
                                    <th class="px-4 py-3">Avg Score</th>
                                    <th class="px-4 py-3">Avg Session</th>
                                    <th class="px-4 py-3">Last Launch</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($moduleRows as $row)
                                    <tr>
                                        <td class="px-4 fw-medium">{{ $row['title'] }}</td>
                                        <td class="px-4">{{ $row['learner_count'] }}</td>
                                        <td class="px-4"><span class="badge bg-success-subtle text-success">{{ $row['completed_count'] }}</span></td>
                                        <td class="px-4"><span class="badge bg-primary-subtle text-primary">{{ $row['in_progress_count'] }}</span></td>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:6px;min-width:50px;">
                                                    <div class="progress-bar {{ $row['completion_rate'] >= 80 ? 'bg-success' : ($row['completion_rate'] >= 40 ? 'bg-primary' : 'bg-warning') }}" style="width:{{ min(100, $row['completion_rate']) }}%"></div>
                                                </div>
                                                <span class="small fw-semibold">{{ $row['completion_rate'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4">{{ $row['launch_count'] }}</td>
                                        <td class="px-4">{{ $row['average_score'] }}</td>
                                        <td class="px-4">{{ $row['average_session_label'] }}</td>
                                        <td class="px-4 small">{{ $row['last_launch_at'] ? $row['last_launch_at']->format('Y-m-d H:i') : '—' }}</td>
                                        <td class="px-4">
                                            <a href="{{ route('app.admin.modules.edit', ['module' => $row['id']]) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="px-4 py-4 text-secondary">No SCORM modules available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Activity: Launches / Attempts / Completions --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-4">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body p-0">
                            <div class="px-4 py-3 border-bottom">
                                <h6 class="fw-semibold mb-0">Recent Launches</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3 py-2">When</th>
                                            <th class="px-3 py-2">Module</th>
                                            <th class="px-3 py-2">Learner</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentLaunches as $launch)
                                            <tr>
                                                <td class="px-3">{{ $launch['when']?->format('M d, H:i') }}</td>
                                                <td class="px-3">{{ $launch['module_title'] }}</td>
                                                <td class="px-3">{{ $launch['learner_name'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-3 py-4 text-secondary">No SCORM launches recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body p-0">
                            <div class="px-4 py-3 border-bottom">
                                <h6 class="fw-semibold mb-0">Recent Attempts</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3 py-2">When</th>
                                            <th class="px-3 py-2">Module</th>
                                            <th class="px-3 py-2">Learner</th>
                                            <th class="px-3 py-2">Status</th>
                                            <th class="px-3 py-2">Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentAttempts as $attempt)
                                            <tr>
                                                <td class="px-3">{{ $attempt['when']?->format('M d, H:i') }}</td>
                                                <td class="px-3">{{ $attempt['module_title'] }}</td>
                                                <td class="px-3">{{ $attempt['learner_name'] }}</td>
                                                <td class="px-3"><span class="badge {{ $attempt['status'] === 'completed' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">{{ $attempt['status'] }}</span></td>
                                                <td class="px-3">{{ $attempt['score_raw'] ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="px-3 py-4 text-secondary">No SCORM attempts recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body p-0">
                            <div class="px-4 py-3 border-bottom">
                                <h6 class="fw-semibold mb-0">Recent Completions</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3 py-2">Completed</th>
                                            <th class="px-3 py-2">Learner</th>
                                            <th class="px-3 py-2">Module</th>
                                            <th class="px-3 py-2">Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentCompletions as $completion)
                                            <tr>
                                                <td class="px-3">{{ $completion['completed_at']?->format('M d, H:i') ?? '—' }}</td>
                                                <td class="px-3">{{ $completion['learner_name'] }}</td>
                                                <td class="px-3">{{ $completion['module_title'] }}</td>
                                                <td class="px-3">{{ $completion['score_raw'] ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="px-3 py-4 text-secondary">No SCORM completions recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Scores + Leaderboard --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-6">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body p-0">
                            <div class="px-4 py-3 border-bottom">
                                <h6 class="fw-semibold mb-0">Top Scores</h6>
                                <p class="small text-secondary mb-0">Highest SCORM scores across learners and modules.</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-3">Score</th>
                                            <th class="px-4 py-3">Learner</th>
                                            <th class="px-4 py-3">Module</th>
                                            <th class="px-4 py-3">When</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($topScores as $score)
                                            <tr>
                                                <td class="px-4 fw-semibold">{{ $score['score_raw'] }}</td>
                                                <td class="px-4">{{ $score['learner_name'] }}</td>
                                                <td class="px-4">{{ $score['module_title'] }}</td>
                                                <td class="px-4 small">{{ $score['when']?->format('M d, H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="px-4 py-4 text-secondary">No scored SCORM attempts recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-body p-0">
                            <div class="px-4 py-3 border-bottom">
                                <h6 class="fw-semibold mb-0">Learner Leaderboard</h6>
                                <p class="small text-secondary mb-0">Average and best SCORM scores by learner.</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-3">Learner</th>
                                            <th class="px-4 py-3">Attempts</th>
                                            <th class="px-4 py-3">Avg Score</th>
                                            <th class="px-4 py-3">Best Score</th>
                                            <th class="px-4 py-3">Avg Session</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($learnerLeaderboard as $learner)
                                            <tr>
                                                <td class="px-4 fw-medium">{{ $learner['learner_name'] }}</td>
                                                <td class="px-4">{{ $learner['attempt_count'] }}</td>
                                                <td class="px-4">{{ $learner['average_score'] }}</td>
                                                <td class="px-4">{{ $learner['best_score'] }}</td>
                                                <td class="px-4">{{ $learner['average_session_label'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="px-4 py-4 text-secondary">No scored SCORM learners recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Most Active Learners --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <h6 class="fw-semibold mb-0">Most Active Learners</h6>
                        <p class="small text-secondary mb-0">Learners with the most SCORM launches and runtime activity.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Learner</th>
                                    <th class="px-4 py-3">Launches</th>
                                    <th class="px-4 py-3">Attempts</th>
                                    <th class="px-4 py-3">Last Launch</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mostActiveLearners as $learner)
                                    <tr>
                                        <td class="px-4 fw-medium">{{ $learner['learner_name'] }}</td>
                                        <td class="px-4">{{ $learner['launch_count'] }}</td>
                                        <td class="px-4">{{ $learner['attempt_count'] }}</td>
                                        <td class="px-4 small">{{ $learner['last_launch_at']?->format('M d, H:i') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-4 text-secondary">No active SCORM learners recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
