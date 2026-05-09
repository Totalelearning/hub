@php
    $g = $gamification ?? [];
    $totalXp = $g['total_xp'] ?? 0;
    $level = $g['level'] ?? 1;
    $levelName = $g['level_name'] ?? 'Newcomer';
    $progressPercent = $g['progress_percent'] ?? 0;
    $xpInLevel = $g['xp_in_level'] ?? 0;
    $xpForLevel = $g['xp_for_level'] ?? 1;
    $streak = $g['streak'] ?? ['current' => 0, 'longest' => 0, 'is_active_today' => false];
    $badges = $g['badges'] ?? collect();
    $badgeCount = $g['badge_count'] ?? 0;
    $rank = $g['rank'] ?? '—';
    $teamRank = $g['team_rank'] ?? null;
    $recentXp = $g['recent_xp'] ?? 0;
@endphp

<div class="row g-3 mb-4">
    {{-- XP & Level --}}
    <div class="col-6 col-lg-3">
        <div class="card adminuiux-card shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="position-relative d-inline-block mb-3">
                    <svg width="70" height="70" viewBox="0 0 70 70" class="d-block mx-auto">
                        <circle cx="35" cy="35" r="30" fill="none" stroke="#e9ecef" stroke-width="5" />
                        <circle cx="35" cy="35" r="30" fill="none" stroke="#6366f1" stroke-width="5"
                            stroke-dasharray="{{ 2 * 3.14159 * 30 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 30 * (1 - $progressPercent / 100) }}"
                            stroke-linecap="round"
                            transform="rotate(-90 35 35)" />
                        <text x="35" y="40" text-anchor="middle" class="fw-bold" style="font-size: 18px; fill: #6366f1;">{{ $level }}</text>
                    </svg>
                </div>
                <h5 class="mb-0">{{ number_format($totalXp) }} <span class="text-secondary fw-normal" style="font-size: .75rem;">XP</span></h5>
                <p class="fw-medium mb-1 text-primary" style="font-size: .85rem;">{{ $levelName }}</p>
                <p class="text-secondary small mb-0">{{ $xpInLevel }} / {{ $xpForLevel }} to next level</p>
            </div>
        </div>
    </div>

    {{-- Streak --}}
    <div class="col-6 col-lg-3">
        <div class="card adminuiux-card shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="avatar avatar-60 rounded-circle {{ $streak['is_active_today'] ? 'bg-warning-subtle text-warning' : 'bg-light text-secondary' }} h3 mb-3 mx-auto">
                    <i class="bi bi-fire"></i>
                </div>
                <h3 class="mb-0">{{ $streak['current'] }}</h3>
                <p class="fw-medium mb-1">Day Streak</p>
                <p class="text-secondary small mb-2">
                    @if ($streak['is_active_today'])
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Active today</span>
                    @else
                        <span class="text-muted">Not active today</span>
                    @endif
                </p>
                <span class="badge bg-warning-subtle text-warning">Best: {{ $streak['longest'] }} days</span>
            </div>
        </div>
    </div>

    {{-- Badges --}}
    <div class="col-6 col-lg-3">
        <div class="card adminuiux-card shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="avatar avatar-60 rounded-circle bg-success-subtle text-success h3 mb-3 mx-auto">
                    <i class="bi bi-award"></i>
                </div>
                <h3 class="mb-0">{{ $badgeCount }}</h3>
                <p class="fw-medium mb-1">Badges Earned</p>
                @if ($badges->isNotEmpty())
                    <div class="d-flex justify-content-center gap-1 mt-2 flex-wrap">
                        @foreach ($badges->take(5) as $badge)
                            <span class="badge bg-light text-dark border" title="{{ $badge->name }}">
                                <i class="bi {{ $badge->icon }}"></i>
                            </span>
                        @endforeach
                        @if ($badgeCount > 5)
                            <span class="badge bg-light text-secondary border">+{{ $badgeCount - 5 }}</span>
                        @endif
                    </div>
                @else
                    <p class="text-secondary small mb-0">Complete courses to earn badges</p>
                @endif
                <a href="{{ route('app.badges') }}" class="btn btn-sm btn-outline-success mt-2">View All</a>
            </div>
        </div>
    </div>

    {{-- Rank --}}
    <div class="col-6 col-lg-3">
        <div class="card adminuiux-card shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="avatar avatar-60 rounded-circle bg-info-subtle text-info h3 mb-3 mx-auto">
                    <i class="bi bi-trophy"></i>
                </div>
                <h3 class="mb-0">#{{ $rank }}</h3>
                <p class="fw-medium mb-1">Your Rank</p>
                @if ($teamRank)
                    <p class="text-secondary small mb-2">Team rank: #{{ $teamRank }}</p>
                @else
                    <p class="text-secondary small mb-2">{{ $recentXp }} XP this week</p>
                @endif
                <a href="{{ route('app.leaderboard') }}" class="btn btn-sm btn-outline-info mt-1">Leaderboard</a>
            </div>
        </div>
    </div>
</div>
