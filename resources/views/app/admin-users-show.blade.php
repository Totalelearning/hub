@extends('layouts.learninguiux')

@section('title', 'User Detail - Learning')
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
                    <div class="col-12 col-lg-7 admin-feed-hero-copy mb-3 mb-lg-0">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">User Evidence</div>
                        <h1 class="fs-3 fw-semibold mb-2">{{ $managedUser->name }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('app.admin.assignments') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('app.admin.users.index') }}">All Students</a></li>
                                <li class="breadcrumb-item active" aria-current="page">User Detail</li>
                            </ol>
                        </nav>
                        <p class="text-secondary mb-0">Review account state, verification, assignment context, and recent activity.</p>
                    </div>
                    <div class="col-12 col-lg-5 d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('app.admin.users.edit', ['user' => $managedUser->id]) }}" class="btn btn-theme btn-sm">Edit User</a>
                        <a href="{{ route('app.admin.assignments.user', ['user' => $managedUser->id]) }}" class="btn btn-outline-theme btn-sm">Learner Detail</a>
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id]) }}" class="btn btn-outline-theme btn-sm">Open Audit</a>
                        <a href="{{ route('app.admin.users.show.export', ['user' => $managedUser->id]) }}" class="btn btn-outline-theme btn-sm">Export CSV</a>
                        <a href="{{ route('app.admin.users.index') }}" class="btn btn-outline-theme btn-sm">Back to Users</a>
                    </div>
                </div>
            </div>

            {{-- Status flash --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            @php
                $needsAttention = $managedUser->suspended_at !== null
                    || $managedUser->email_verified_at === null
                    || $managedUser->last_login_at === null
                    || optional($managedUser->last_login_at)->lt(now()->subDays(30));
                $profileImage = $managedUser->hasAdminAccess()
                    ? asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg')
                    : asset('vendor/learninguiux/img/modern-ai-image/user-3.jpg');
            @endphp

            {{-- Profile + Signals --}}
            <div class="row g-4 mb-4">
                {{-- Profile card --}}
                <div class="col-12 col-lg-4">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="position-relative" style="height:10rem;overflow:hidden;border-radius:.75rem .75rem 0 0;background:linear-gradient(135deg,rgba(224,242,254,0.95),rgba(238,242,255,0.95));">
                            <img src="{{ $profileImage }}" alt="{{ $managedUser->name }}" style="width:100%;height:100%;object-fit:cover;">
                            <div class="position-absolute top-0 end-0 p-2">
                                @if ($managedUser->suspended_at)
                                    <span class="badge bg-warning-subtle text-warning-emphasis">Suspended</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @endif
                            </div>
                            @if ($needsAttention)
                                <div class="position-absolute bottom-0 start-0 p-2">
                                    <span class="badge bg-danger-subtle text-danger">Needs Attention</span>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <h4 class="fw-semibold mb-1">{{ $managedUser->name }}</h4>
                            <p class="text-secondary small mb-2">{{ $managedUser->email }}</p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="badge {{ $managedUser->email_verified_at ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $managedUser->email_verified_at ? 'Verified' : 'Unverified' }}</span>
                                <span class="badge bg-primary-subtle text-primary">{{ $managedUser->systemRoleLabel() }}</span>
                            </div>
                            <div class="small text-secondary"><i class="bi bi-calendar-event me-1"></i>Created {{ $managedUser->created_at?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                            <div class="small text-secondary mt-1"><i class="bi bi-person-badge me-1"></i>User ID {{ $managedUser->id }}</div>
                            <div class="small text-secondary mt-1"><i class="bi bi-shield-check me-1"></i>{{ $managedUser->email_verified_at ? 'Verified '.$managedUser->email_verified_at->format('Y-m-d H:i') : 'Pending verification' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Profile signals --}}
                <div class="col-12 col-lg-8">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-header bg-primary-subtle">
                            <div class="fw-semibold">Profile Signals</div>
                            <div class="small text-secondary mt-1">Login, verification, and assignment context at a glance.</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-4">
                                    <div class="card bg-light h-100">
                                        <div class="card-body p-3 small">
                                            <div class="text-uppercase fw-semibold text-secondary" style="font-size:.65rem;letter-spacing:.15em;">Last Login</div>
                                            <div class="fw-semibold mt-1">{{ $managedUser->last_login_at?->format('Y-m-d H:i') ?? 'Never' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="card bg-light h-100">
                                        <div class="card-body p-3 small">
                                            <div class="text-uppercase fw-semibold text-secondary" style="font-size:.65rem;letter-spacing:.15em;">Assignments</div>
                                            <div class="fw-semibold mt-1">{{ $assignmentSummary['required_total'] }}</div>
                                            <div class="text-secondary">{{ $assignmentSummary['overdue_total'] }} overdue | {{ $assignmentSummary['waived_total'] }} waived</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="card bg-light h-100">
                                        <div class="card-body p-3 small">
                                            <div class="text-uppercase fw-semibold text-secondary" style="font-size:.65rem;letter-spacing:.15em;">Pending Reminders</div>
                                            <div class="fw-semibold mt-1">{{ $assignmentSummary['pending_reminders_count'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body p-3 small">
                                            <div class="text-uppercase fw-semibold text-secondary mb-2" style="font-size:.65rem;letter-spacing:.15em;">Account Summary</div>
                                            <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-secondary">Updated</span><span>{{ $managedUser->updated_at?->format('Y-m-d H:i') ?? 'n/a' }}</span></div>
                                            <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-secondary">Access</span><span class="fw-semibold {{ $managedUser->suspended_at ? 'text-warning' : 'text-success' }}">{{ $managedUser->suspended_at ? 'Suspended' : 'Active' }}</span></div>
                                            <div class="d-flex justify-content-between py-1"><span class="text-secondary">System Role</span><span>{{ $managedUser->systemRoleLabel() }}</span></div>
                                            @if ($managedUser->managed_teams)
                                                <div class="d-flex justify-content-between py-1 border-top"><span class="text-secondary">Teams</span><span>{{ implode(', ', $managedUser->managed_teams) }}</span></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body p-3 small">
                                            <div class="text-uppercase fw-semibold text-secondary mb-2" style="font-size:.65rem;letter-spacing:.15em;">Quick Links</div>
                                            <a href="{{ route('app.admin.users.edit', ['user' => $managedUser->id]) }}" class="d-block btn btn-sm btn-outline-primary mb-1">Edit account settings</a>
                                            <a href="{{ route('app.admin.assignments.user', ['user' => $managedUser->id]) }}" class="d-block btn btn-sm btn-outline-secondary mb-1">Learner compliance detail</a>
                                            <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id]) }}" class="d-block btn btn-sm btn-outline-secondary">Full audit history</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI cards --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-journal-check fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $assignmentSummary['required_total'] }}</div>
                            <div class="fw-semibold mt-1">Required</div>
                            <div class="small text-secondary mt-auto pt-2">Assignments</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3" style="color:#b91c1c;background:linear-gradient(135deg,rgba(254,226,226,0.98),rgba(255,237,213,0.98));"><i class="bi bi-exclamation-triangle fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $assignmentSummary['overdue_total'] }}</div>
                            <div class="fw-semibold mt-1">Overdue</div>
                            <div class="small text-secondary mt-auto pt-2">Past due date</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-shield-slash fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $assignmentSummary['waived_total'] }}</div>
                            <div class="fw-semibold mt-1">Waived</div>
                            <div class="small text-secondary mt-auto pt-2">Exemptions</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3" style="color:#b45309;background:linear-gradient(135deg,rgba(254,243,199,0.98),rgba(255,237,213,0.98));"><i class="bi bi-bell fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $assignmentSummary['pending_reminders_count'] }}</div>
                            <div class="fw-semibold mt-1">Reminders</div>
                            <div class="small text-secondary mt-auto pt-2">Pending queue</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header bg-primary-subtle">
                    <div class="fw-semibold">Quick Actions</div>
                    <div class="small text-secondary mt-1">Manage account access, verification, and password for this user.</div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if (auth()->user()?->isSiteAdmin())
                        <form method="POST" action="{{ route('app.admin.users.toggle-admin-access', ['user' => $managedUser->id]) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $managedUser->is_admin ? 'Remove Admin Access' : 'Grant Admin Access' }}</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('app.admin.users.toggle-account-access', ['user' => $managedUser->id]) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $managedUser->suspended_at ? 'btn-outline-success' : 'btn-outline-warning' }}">{{ $managedUser->suspended_at ? 'Restore Account' : 'Suspend Account' }}</button>
                        </form>
                        <form method="POST" action="{{ route('app.admin.users.toggle-email-verification', ['user' => $managedUser->id]) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $managedUser->email_verified_at ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $managedUser->email_verified_at ? 'Clear Verification' : 'Mark Email Verified' }}</button>
                        </form>
                        <form method="POST" action="{{ route('app.admin.users.send-password-reset-link', ['user' => $managedUser->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Send Password Reset Link</button>
                        </form>
                        @if (! $managedUser->email_verified_at)
                            <form method="POST" action="{{ route('app.admin.users.send-email-verification-link', ['user' => $managedUser->id]) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">Resend Verification Email</button>
                            </form>
                        @endif
                        @if (auth()->id() !== $managedUser->id)
                            <form method="POST" action="{{ route('app.admin.users.destroy', ['user' => $managedUser->id]) }}" onsubmit="return confirm('Are you sure you want to permanently delete this user? This cannot be undone.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete User</button>
                            </form>
                        @endif
                    </div>
                    @if (auth()->id() === $managedUser->id)
                        <div class="form-text mt-2">Self-lockout protections apply on these actions.</div>
                    @endif
                </div>
            </div>

            {{-- Audit shortcuts --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold">Audit Shortcuts</div>
                        <div class="small text-secondary mt-1">Jump straight into the most relevant audit view for this account.</div>
                    </div>
                    <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id]) }}" class="btn btn-sm btn-outline-secondary">All User Audit</a>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id, 'action' => 'user_updated']) }}" class="btn btn-sm btn-outline-secondary">User Updates</a>
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id, 'action' => 'user_password_reset']) }}" class="btn btn-sm btn-outline-primary">Password Resets</a>
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id, 'action' => 'user_password_reset_link_sent']) }}" class="btn btn-sm btn-outline-primary">Reset Links</a>
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id, 'action' => 'user_suspended']) }}" class="btn btn-sm btn-outline-warning">Suspensions</a>
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id, 'action' => 'user_restored']) }}" class="btn btn-sm btn-outline-success">Restores</a>
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id, 'action' => 'user_verification_marked']) }}" class="btn btn-sm btn-outline-success">Verification Marks</a>
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id, 'action' => 'user_verification_link_sent']) }}" class="btn btn-sm btn-outline-success">Verification Links</a>
                        <a href="{{ route('app.admin.assignments.audit', ['target' => $managedUser->id, 'action' => 'user_verification_cleared']) }}" class="btn btn-sm btn-outline-danger">Verification Clears</a>
                    </div>
                </div>
            </div>

            {{-- Recent activity tables --}}
            <div class="row g-4 mb-4">
                {{-- Learning events --}}
                <div class="col-12 col-lg-6">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-header bg-primary-subtle d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold">Recent Learning Events</div>
                                <div class="small text-secondary mt-1">Latest learner activity for modules, paths, and preferences.</div>
                            </div>
                            <a href="{{ route('app.admin.assignments.user.events', ['user' => $managedUser->id]) }}" class="btn btn-sm btn-outline-secondary">Open Events</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 small">
                                <tbody>
                                    @forelse ($recentLearningEvents as $event)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ $event->event_type }}</div>
                                                <div class="text-secondary">
                                                    {{ $event->entity_type }}
                                                    @if ($event->entity_type === 'learning_module')
                                                        | {{ $eventModuleTitles[(int) $event->entity_id] ?? ('#'.$event->entity_id) }}
                                                    @else
                                                        | #{{ $event->entity_id }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-end text-secondary" style="white-space:nowrap;">{{ $event->created_at?->format('Y-m-d H:i') ?? 'n/a' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-secondary text-center py-4">No learning events recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Reminders --}}
                <div class="col-12 col-lg-6">
                    <div class="card adminuiux-card shadow-sm h-100">
                        <div class="card-header bg-primary-subtle">
                            <div class="fw-semibold">Recent Reminder Activity</div>
                            <div class="small text-secondary mt-1">Most recent reminder queue items and send state.</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 small">
                                <tbody>
                                    @forelse ($recentReminders as $reminder)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ str($reminder->reminder_type)->replace('_', ' ')->title() }}</div>
                                                <div class="text-secondary">{{ $reminder->module?->title ?? 'Module removed' }}</div>
                                                <div class="text-secondary">
                                                    Status: {{ str($reminder->status)->replace('_', ' ')->title() }}
                                                    @if ($reminder->due_on) | Due {{ $reminder->due_on->format('Y-m-d') }} @endif
                                                </div>
                                            </td>
                                            <td class="text-end text-secondary" style="white-space:nowrap;">{{ $reminder->created_at?->format('Y-m-d H:i') ?? 'n/a' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-secondary text-center py-4">No reminder activity recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Audit activity --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header bg-primary-subtle">
                    <div class="fw-semibold">Recent User Audit Activity</div>
                    <div class="small text-secondary mt-1">Latest user-management and account-scoped audit events.</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <tbody>
                            @forelse ($recentAuditEvents as $event)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ str($event->action)->replace('_', ' ')->title() }}</div>
                                        <div class="text-secondary">
                                            @if ($event->actor)
                                                {{ $event->actor->name }} ({{ $event->actor->email }})
                                            @else
                                                System
                                            @endif
                                        </div>
                                        @if (($event->meta['reason'] ?? null) !== null)
                                            <div class="text-secondary">{{ $event->meta['reason'] }}</div>
                                        @elseif (($event->meta['changed_keys'] ?? null) !== null)
                                            <div class="text-secondary">Changed: {{ implode(', ', $event->meta['changed_keys']) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end text-secondary" style="white-space:nowrap;">{{ $event->created_at?->format('Y-m-d H:i') ?? 'n/a' }}</td>
                                </tr>
                            @empty
                                <tr><td class="text-secondary text-center py-4">No audit activity recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
