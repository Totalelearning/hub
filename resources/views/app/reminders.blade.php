@extends('layouts.learninguiux')

@section('title', 'Reminders - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .learner-reminder-panel,
    .learner-reminder-summary {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(43, 82, 138, 0.1);
        background: rgba(255, 255, 255, 0.97);
    }

    .learner-reminder-title {
        font-size: 0.85rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f7699;
    }

    .learner-reminder-row + .learner-reminder-row {
        border-top: 1px solid rgba(120, 145, 185, 0.18);
    }

    .learner-reminder-summary {
        min-height: 132px;
    }
</style>
@endpush

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar', ['active' => 'reminders'])

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4">
            <div class="card learner-reminder-panel mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="learner-reminder-title d-inline-block mb-2">Reminders</span>
                            <h3 class="mb-1">Stay ahead of due soon, overdue, and inactivity nudges.</h3>
                            <p class="text-secondary mb-0">Live reminder notifications and reinforcement follow-ups keep you moving back into the right course at the right time.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">Back to dashboard</a>
                            <form method="POST" action="{{ route('app.reminders.read-all') }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-theme btn-sm">Mark all read</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-2">
                    <div class="card learner-reminder-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Total</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $summary['total'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-2">
                    <div class="card learner-reminder-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Unread</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $summary['unread'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-2">
                    <div class="card learner-reminder-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Read</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $summary['read'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-2">
                    <div class="card learner-reminder-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Overdue</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $summary['overdue'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-2">
                    <div class="card learner-reminder-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Due in 7 days</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $summary['due_soon'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-2">
                    <div class="card learner-reminder-summary">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Reinforcement due</div>
                            <div class="fs-3 fw-semibold mt-1">{{ $reinforcementSummary['due'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="card learner-reminder-panel mb-4 border-success-subtle bg-success-subtle">
                    <div class="card-body px-4 py-3 text-success-emphasis">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            <div class="card learner-reminder-panel mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <span class="learner-reminder-title d-inline-block mb-2">Filters</span>
                            <h3 class="mb-1">Refine your reminder list</h3>
                            <p class="text-secondary mb-0">Filter by read state or reminder type, then jump back into the relevant learning item.</p>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('app.reminders') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="status" class="form-label small text-secondary">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All</option>
                                <option value="unread" @selected(($filters['status'] ?? 'all') === 'unread')>Unread</option>
                                <option value="read" @selected(($filters['status'] ?? 'all') === 'read')>Read</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label small text-secondary">Type</label>
                            <select id="type" name="type" class="form-select">
                                <option value="all" @selected(($filters['type'] ?? 'all') === 'all')>All</option>
                                @foreach ($availableTypes as $reminderType)
                                    <option value="{{ $reminderType }}" @selected(($filters['type'] ?? 'all') === $reminderType)>{{ str_replace('_', ' ', $reminderType) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (($filters['module_id'] ?? 0) > 0)
                            <input type="hidden" name="module_id" value="{{ $filters['module_id'] }}">
                        @endif
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-theme">Apply filters</button>
                            <a href="{{ route('app.reminders') }}" class="btn btn-outline-theme">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            @if ($latestCompletionSummary)
                <div class="card learner-reminder-panel mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <span class="learner-reminder-title d-inline-block mb-2">Latest Completion</span>
                                <h3 class="mb-1">{{ $latestCompletionSummary['module_title'] }}</h3>
                                <p class="text-secondary mb-0">
                                    Completed {{ optional($latestCompletionSummary['completed_at'])->diffForHumans() ?? 'recently' }}
                                    with {{ $latestCompletionSummary['percent_complete'] }}% progress and status
                                    {{ ucfirst(str_replace('_', ' ', (string) $latestCompletionSummary['status'])) }}.
                                </p>
                            </div>
                            @if ($latestCompletionSummary['module'])
                                <div class="d-flex align-items-start">
                                    <a href="{{ route('app.modules.show', ['module' => $latestCompletionSummary['module']->id]) }}" class="btn btn-outline-theme">
                                        Review completed module
                                    </a>
                                </div>
                            @endif
                        </div>
                        @if (($completionNextActions ?? collect())->isNotEmpty())
                            <div class="rounded-4 border bg-light p-3 mt-4">
                                <div class="small text-secondary">Next actions after completion</div>
                                <div class="row g-3 mt-1">
                                    @foreach ($completionNextActions as $action)
                                        <div class="col-lg-4">
                                            <div class="rounded-4 border bg-white p-3 h-100">
                                                <div class="small text-secondary">{{ $action['label'] }}</div>
                                                <div class="fw-semibold mt-2">{{ $action['title'] }}</div>
                                                <div class="small text-secondary mt-2">{{ $action['summary'] }}</div>
                                                <a href="{{ $action['href'] }}" class="btn btn-outline-theme btn-sm mt-3">{{ $action['cta'] }}</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card learner-reminder-panel mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <span class="learner-reminder-title d-inline-block mb-2">Reinforcement</span>
                            <h3 class="mb-1">Ongoing reinforcement + proof</h3>
                            <p class="text-secondary mb-0">These follow-ups keep key learning alive after completion and record evidence that knowledge is still current.</p>
                        </div>
                    </div>

                    @forelse ($reinforcementTouchpoints as $touchpoint)
                        <div class="learner-reminder-row py-3">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        @if ($touchpoint->module)
                                            <a href="{{ route('app.modules.show', ['module' => $touchpoint->module->id]) }}" class="fw-semibold text-decoration-none">
                                                {{ $touchpoint->module->title }}
                                            </a>
                                        @else
                                            <span class="fw-semibold">{{ $touchpoint->title }}</span>
                                        @endif
                                        <span class="badge rounded-pill text-bg-light border">{{ $touchpoint->interval_days }}-day follow-up</span>
                                        <span class="badge rounded-pill {{ $touchpoint->computed_status === 'completed' ? 'text-bg-success' : ($touchpoint->computed_status === 'due' ? 'text-bg-warning' : 'text-bg-light border') }}">
                                            {{ ucfirst($touchpoint->computed_status) }}
                                        </span>
                                    </div>
                                    <div class="small text-secondary">
                                        Due {{ optional($touchpoint->due_on)->format('Y-m-d') ?? 'n/a' }}
                                        @if (!empty($touchpoint->metadata['source_type']))
                                            | {{ strtoupper((string) $touchpoint->metadata['source_type']) }}
                                        @endif
                                    </div>
                                    <div class="small text-secondary mt-1">{{ $touchpoint->prompt }}</div>
                                    @if ($touchpoint->proof_summary)
                                        <div class="small text-success-emphasis mt-1">{{ $touchpoint->proof_summary }}</div>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    @if ($touchpoint->module)
                                        <a href="{{ route('app.modules.show', ['module' => $touchpoint->module->id]) }}" class="btn btn-outline-theme btn-sm">Open module</a>
                                    @endif
                                    @if ($touchpoint->computed_status !== 'completed')
                                        @if ($touchpoint->reinforcement_question_set_id)
                                            <a href="{{ route('app.reinforcement.show', ['touchpoint' => $touchpoint->id]) }}" class="btn btn-theme btn-sm">Answer questions</a>
                                        @else
                                            <form method="POST" action="{{ route('app.reinforcement.complete', ['touchpoint' => $touchpoint->id]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-theme btn-sm">Record proof</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-4 border bg-light p-4 text-secondary">
                            No reinforcement follow-ups are queued yet. They will appear automatically after completed modules reach their next check-in window.
                        </div>
                    @endforelse
                </div>
            </div>

            @if (($courseReinforcementNotifications ?? collect())->isNotEmpty())
            <div class="card learner-reminder-panel mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <span class="learner-reminder-title d-inline-block mb-2">Knowledge Checks</span>
                            <h3 class="mb-1">Course reinforcement quizzes</h3>
                            <p class="text-secondary mb-0">Complete these knowledge checks to confirm your understanding of course material.</p>
                        </div>
                    </div>

                    @foreach ($courseReinforcementNotifications as $crNotification)
                        <div class="learner-reminder-row py-3">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <span class="fw-semibold">{{ $crNotification->data['course_title'] ?? 'Course' }}</span>
                                        <span class="badge rounded-pill {{ $crNotification->read_at ? 'text-bg-light border' : 'text-bg-primary' }}">
                                            {{ $crNotification->read_at ? 'Read' : 'Unread' }}
                                        </span>
                                        <span class="badge rounded-pill text-bg-info border">Knowledge check</span>
                                    </div>
                                    @if (!empty($crNotification->data['message']))
                                        <div class="small text-secondary mt-1">{{ $crNotification->data['message'] }}</div>
                                    @endif
                                    <div class="small text-secondary mt-1">Sent {{ $crNotification->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    @if (!empty($crNotification->data['action_url']))
                                        <a href="{{ $crNotification->data['action_url'] }}" class="btn btn-theme btn-sm">Take quiz</a>
                                    @endif
                                    @if (! $crNotification->read_at)
                                        <form method="POST" action="{{ route('app.reminders.read', ['notification' => $crNotification->id]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-theme btn-sm">Mark read</button>
                                        </form>
                                    @else
                                        <div class="small text-secondary align-self-center">
                                            Read {{ $crNotification->read_at->format('M d, Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="card learner-reminder-panel">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <span class="learner-reminder-title d-inline-block mb-2">Notifications</span>
                            <h3 class="mb-1">Live reminder activity</h3>
                            <p class="text-secondary mb-0">Each entry links back to the module it references so you can act on it quickly.</p>
                        </div>
                    </div>

                    @forelse ($notifications as $notification)
                        @php
                            $dueOn = !empty($notification->data['due_on']) ? \Illuminate\Support\Carbon::parse($notification->data['due_on']) : null;
                            $isOverdue = $dueOn?->copy()->startOfDay()->lt(now()->startOfDay()) ?? false;
                            $isDueSoon = ! $isOverdue && $dueOn?->copy()->startOfDay()->betweenIncluded(now()->startOfDay(), now()->startOfDay()->copy()->addDays(7));
                        @endphp
                        <div class="learner-reminder-row py-3">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <a href="{{ route('app.modules.show', ['module' => $notification->data['module_id']]) }}" class="fw-semibold text-decoration-none">
                                            {{ $notification->data['module_title'] ?? 'Module' }}
                                        </a>
                                        <span class="badge rounded-pill {{ $notification->read_at ? 'text-bg-light border' : 'text-bg-primary' }}">
                                            {{ $notification->read_at ? 'Read' : 'Unread' }}
                                        </span>
                                        <span class="badge rounded-pill text-bg-light border">
                                            {{ str_replace('_', ' ', $notification->data['reminder_type'] ?? 'reminder') }}
                                        </span>
                                        @if ($isOverdue)
                                            <span class="badge rounded-pill text-bg-danger">Overdue</span>
                                        @elseif ($isDueSoon)
                                            <span class="badge rounded-pill text-bg-warning">Due soon</span>
                                        @endif
                                    </div>
                                    <div class="small text-secondary">
                                        Due {{ $notification->data['due_on'] ?? 'n/a' }}
                                        @if (!empty($notification->data['compliance_area']))
                                            | {{ $notification->data['compliance_area'] }}
                                        @endif
                                    </div>
                                    @if ($isOverdue || $isDueSoon)
                                        <div class="small mt-1 {{ $isOverdue ? 'text-danger-emphasis' : 'text-warning-emphasis' }}">
                                            {{ $isOverdue ? 'This reminder is tied to overdue learning.' : 'This reminder is tied to learning due soon.' }}
                                        </div>
                                    @endif
                                    @if (!empty($notification->data['message']))
                                        <div class="small text-secondary mt-1">{{ $notification->data['message'] }}</div>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    <a href="{{ route('app.modules.show', ['module' => $notification->data['module_id']]) }}" class="btn btn-outline-theme btn-sm">Open module</a>
                                    <a href="{{ route('app.reminders', ['module_id' => $notification->data['module_id']]) }}" class="btn btn-outline-theme btn-sm">Module reminders</a>
                                    @if (! $notification->read_at)
                                        <form method="POST" action="{{ route('app.reminders.read', ['notification' => $notification->id]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-theme btn-sm">Mark read</button>
                                        </form>
                                    @else
                                        <div class="small text-secondary align-self-center">
                                            Read {{ $notification->read_at->format('M d, Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-4 border bg-light p-4 text-secondary">
                            No reminders yet. As assignments move due soon, overdue, or inactive, they will appear here automatically.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
