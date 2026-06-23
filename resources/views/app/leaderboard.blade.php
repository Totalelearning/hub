@extends('layouts.learninguiux')

@section('title', 'Leaderboard - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .leaderboard-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }

    .leaderboard-section-title {
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }

    .leaderboard-row-highlight {
        background: rgba(99, 102, 241, 0.08) !important;
    }

    .rank-badge-1 { background: linear-gradient(135deg, #ffd700, #ffb800); color: #fff; }
    .rank-badge-2 { background: linear-gradient(135deg, #c0c0c0, #a0a0a0); color: #fff; }
    .rank-badge-3 { background: linear-gradient(135deg, #cd7f32, #b8690f); color: #fff; }
</style>
@endpush

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'leaderboard'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" x-data="{ tab: 'individual' }">

            {{-- Header --}}
            <div class="card leaderboard-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="leaderboard-section-title d-inline-block mb-2">Leaderboard</span>
                            <h3 class="mb-1">See how you compare</h3>
                            <p class="text-secondary mb-0">Earn XP by completing courses and knowledge checks. Climb the ranks!</p>
                        </div>
                        <div class="text-end">
                            <div class="d-flex align-items-center gap-3 justify-content-end">
                                <div class="text-center">
                                    <h4 class="mb-0 text-primary">{{ number_format($userSummary['total_xp'] ?? 0) }}</h4>
                                    <small class="text-secondary">Your XP</small>
                                </div>
                                <div class="text-center">
                                    <h4 class="mb-0 text-warning"><i class="bi bi-fire"></i> {{ $userSummary['streak']['current'] ?? 0 }}</h4>
                                    <small class="text-secondary">Streak</small>
                                </div>
                                <div class="text-center">
                                    <h4 class="mb-0 text-info">#{{ $userSummary['rank'] ?? '—' }}</h4>
                                    <small class="text-secondary">Rank</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab navigation --}}
            <div class="d-flex gap-2 mb-3">
                <button class="btn btn-sm" :class="tab === 'individual' ? 'btn-primary' : 'btn-outline-secondary'" @click="tab = 'individual'">
                    <i class="bi bi-person"></i> Individual
                </button>
                <button class="btn btn-sm" :class="tab === 'teams' ? 'btn-primary' : 'btn-outline-secondary'" @click="tab = 'teams'">
                    <i class="bi bi-people"></i> Teams
                </button>
            </div>

            {{-- Individual leaderboard --}}
            <div x-show="tab === 'individual'" x-cloak
                 x-data="{
                    perPage: 10,
                    currentPage: 1,
                    total: {{ $individuals->count() }},
                    get totalPages() { return Math.ceil(this.total / this.perPage) },
                    get from() { return (this.currentPage - 1) * this.perPage },
                    get to() { return Math.min(this.currentPage * this.perPage, this.total) },
                    isVisible(index) { return index >= this.from && index < this.to },
                    goTo(page) { this.currentPage = Math.max(1, Math.min(page, this.totalPages)) }
                 }">
                <div class="card leaderboard-card">
                    <div class="card-header px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="leaderboard-section-title">Individual Rankings</span>
                        <form method="GET" class="d-inline-flex gap-2">
                            <select name="team" class="form-select form-select-sm" style="min-width: 200px;" onchange="this.form.submit()">
                                <option value="">All Teams</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team }}" {{ $teamFilter === $team ? 'selected' : '' }}>{{ $team }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        @if ($individuals->isEmpty())
                            <div class="text-center py-5 text-secondary">
                                <i class="bi bi-trophy h1 d-block mb-3"></i>
                                <p>No XP earned yet. Complete courses to appear on the leaderboard!</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4" style="width: 60px;">Rank</th>
                                            <th>Learner</th>
                                            <th>Team</th>
                                            <th class="text-center">Level</th>
                                            <th class="text-center">Streak</th>
                                            <th class="text-center">Badges</th>
                                            <th class="text-end pe-4">XP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($individuals as $index => $entry)
                                            <tr x-show="isVisible({{ $index }})"
                                                class="{{ $entry->id === $currentUserId ? 'leaderboard-row-highlight fw-semibold' : '' }}">
                                                <td class="ps-4">
                                                    @if ($entry->rank <= 3)
                                                        <span class="badge rounded-pill rank-badge-{{ $entry->rank }}">{{ $entry->rank }}</span>
                                                    @else
                                                        <span class="text-secondary">{{ $entry->rank }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $entry->name }}
                                                    @if ($entry->id === $currentUserId)
                                                        <span class="badge bg-primary-subtle text-primary ms-1">You</span>
                                                    @endif
                                                </td>
                                                <td><span class="text-secondary small">{{ $entry->team ?? '—' }}</span></td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary-subtle text-primary">Lv {{ $entry->level }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if (($entry->current_streak ?? 0) > 0)
                                                        <span class="text-warning"><i class="bi bi-fire"></i> {{ $entry->current_streak }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $entry->badge_count }}</td>
                                                <td class="text-end pe-4 fw-semibold">{{ number_format($entry->total_xp) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <template x-if="totalPages > 1">
                                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                                    <small class="text-secondary">Showing <span x-text="from + 1"></span>–<span x-text="to"></span> of <span x-text="total"></span></small>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item" :class="currentPage === 1 && 'disabled'">
                                                <a class="page-link" href="#" @click.prevent="goTo(currentPage - 1)"><i class="bi bi-chevron-left"></i></a>
                                            </li>
                                            <template x-for="p in totalPages" :key="p">
                                                <li class="page-item" :class="p === currentPage && 'active'">
                                                    <a class="page-link" href="#" x-text="p" @click.prevent="goTo(p)"></a>
                                                </li>
                                            </template>
                                            <li class="page-item" :class="currentPage === totalPages && 'disabled'">
                                                <a class="page-link" href="#" @click.prevent="goTo(currentPage + 1)"><i class="bi bi-chevron-right"></i></a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </template>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Team leaderboard --}}
            <div x-show="tab === 'teams'" x-cloak
                 x-data="{
                    perPage: 10,
                    currentPage: 1,
                    total: {{ $teamLeaderboard->count() }},
                    get totalPages() { return Math.ceil(this.total / this.perPage) },
                    get from() { return (this.currentPage - 1) * this.perPage },
                    get to() { return Math.min(this.currentPage * this.perPage, this.total) },
                    isVisible(index) { return index >= this.from && index < this.to },
                    goTo(page) { this.currentPage = Math.max(1, Math.min(page, this.totalPages)) }
                 }">
                <div class="card leaderboard-card">
                    <div class="card-header px-4 py-3 border-bottom">
                        <span class="leaderboard-section-title">Team Rankings</span>
                    </div>
                    <div class="card-body p-0">
                        @if ($teamLeaderboard->isEmpty())
                            <div class="text-center py-5 text-secondary">
                                <i class="bi bi-people h1 d-block mb-3"></i>
                                <p>No team data yet.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4" style="width: 60px;">Rank</th>
                                            <th>Team</th>
                                            <th class="text-center">Members</th>
                                            <th class="text-center">Avg XP</th>
                                            <th class="text-end pe-4">Total XP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($teamLeaderboard as $index => $team)
                                            <tr x-show="isVisible({{ $index }})">
                                                <td class="ps-4">
                                                    @if ($team->rank <= 3)
                                                        <span class="badge rounded-pill rank-badge-{{ $team->rank }}">{{ $team->rank }}</span>
                                                    @else
                                                        <span class="text-secondary">{{ $team->rank }}</span>
                                                    @endif
                                                </td>
                                                <td class="fw-semibold">{{ $team->team }}</td>
                                                <td class="text-center">{{ $team->member_count }}</td>
                                                <td class="text-center">{{ number_format($team->avg_xp) }}</td>
                                                <td class="text-end pe-4 fw-semibold">{{ number_format($team->total_xp) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <template x-if="totalPages > 1">
                                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                                    <small class="text-secondary">Showing <span x-text="from + 1"></span>–<span x-text="to"></span> of <span x-text="total"></span></small>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item" :class="currentPage === 1 && 'disabled'">
                                                <a class="page-link" href="#" @click.prevent="goTo(currentPage - 1)"><i class="bi bi-chevron-left"></i></a>
                                            </li>
                                            <template x-for="p in totalPages" :key="p">
                                                <li class="page-item" :class="p === currentPage && 'active'">
                                                    <a class="page-link" href="#" x-text="p" @click.prevent="goTo(p)"></a>
                                                </li>
                                            </template>
                                            <li class="page-item" :class="currentPage === totalPages && 'disabled'">
                                                <a class="page-link" href="#" @click.prevent="goTo(currentPage + 1)"><i class="bi bi-chevron-right"></i></a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </template>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
