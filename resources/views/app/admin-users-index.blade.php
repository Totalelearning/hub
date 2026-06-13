@extends('layouts.learninguiux')

@section('title', 'User Management - Learning')
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
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">User Management</div>
                        <h1 class="fs-3 fw-semibold mb-2">All Students</h1>
                        <p class="text-secondary mb-3">Manage school staff accounts, filter by team and role, and move quickly from directory search into edit and audit flows.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('app.admin.users.export', array_filter($filters, fn ($value) => $value !== '' && $value !== 'all')) }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-download me-1"></i>Export Directory</a>
                            <a href="{{ route('app.admin.users.create') }}" class="btn btn-theme btn-sm"><i class="bi bi-plus me-1"></i>Add Student</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status flash --}}
            @if ($pageStatus)
                <div class="alert alert-success mb-4">{{ $pageStatus }}</div>
            @endif

            @if ($bulkStatusBanner)
                <div class="alert {{ $bulkStatusBanner['styles']['container'] ?? 'alert-info' }} mb-4">
                    @include('app.partials.user-bulk-status-banner')
                </div>
            @endif

            @php
                $summaryBaseFilters = [
                    'q' => $filters['q'],
                    'location' => $filters['location'],
                    'team' => $filters['team'],
                    'training_compliance' => $filters['training_compliance'],
                    'sort' => $filters['sort'],
                    'sort_dir' => $filters['sort_dir'],
                    'limit' => $filters['limit'],
                ];
                $summaryLink = fn (array $overrides = []) => route('app.admin.users.index', array_filter([
                    ...$summaryBaseFilters,
                    'role' => $filters['role'],
                    'location' => $filters['location'],
                    'team' => $filters['team'],
                    'account_status' => $filters['account_status'],
                    'verification_status' => $filters['verification_status'],
                    'inactivity_status' => $filters['inactivity_status'],
                    'attention_status' => $filters['attention_status'],
                    'training_compliance' => $filters['training_compliance'],
                    ...$overrides,
                ], fn ($value) => $value !== '' && $value !== 'all'));
                $bulkPresetLink = fn (array $overrides = []) => route('app.admin.users.index', array_filter([
                    'q' => $filters['q'],
                    'sort' => $filters['sort'],
                    'sort_dir' => $filters['sort_dir'],
                    'limit' => $filters['limit'],
                    'location' => $filters['location'],
                    'team' => $filters['team'],
                    'training_compliance' => $filters['training_compliance'],
                    ...$overrides,
                ], fn ($value) => $value !== '' && $value !== 'all'));
                $userImages = [
                    asset('vendor/learninguiux/img/modern-ai-image/user-3.jpg'),
                    asset('vendor/learninguiux/img/modern-ai-image/user-6.jpg'),
                    asset('vendor/learninguiux/img/modern-ai-image/user-1-kid.jpg'),
                    asset('vendor/learninguiux/img/modern-ai-image/user-7-kid.jpg'),
                    asset('vendor/learninguiux/img/modern-ai-image/user-5-kid.jpg'),
                    asset('vendor/learninguiux/img/modern-ai-image/user-6-kid.jpg'),
                ];
            @endphp

            {{-- Summary KPIs --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <a href="{{ $summaryLink(['account_status' => 'active']) }}" class="card adminuiux-card shadow-sm text-decoration-none h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3" style="color:#0f766e;background:linear-gradient(135deg, rgba(213, 250, 229, 0.96), rgba(220, 252, 231, 0.96));">
                                <i class="bi bi-person-check fs-3"></i>
                            </div>
                            <div class="admin-feed-kpi-stat">{{ $summary['active'] }}</div>
                            <div class="fw-semibold mt-1">Active</div>
                            <div class="small text-secondary mt-1">Accounts ready to learn</div>
                            <div class="small text-secondary">Staff currently allowed to sign in</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <a href="{{ $summaryLink(['attention_status' => 'needs_attention']) }}" class="card adminuiux-card shadow-sm text-decoration-none h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3" style="color:#b91c1c;background:linear-gradient(135deg, rgba(254, 226, 226, 0.98), rgba(255, 237, 213, 0.98));">
                                <i class="bi bi-person-exclamation fs-3"></i>
                            </div>
                            <div class="admin-feed-kpi-stat">{{ $summary['needs_attention'] }}</div>
                            <div class="fw-semibold mt-1">Needs Attention</div>
                            <div class="small text-secondary mt-1">People needing a check-in</div>
                            <div class="small text-secondary">Suspended, unverified, or inactive</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <a href="{{ $summaryLink() }}" class="card adminuiux-card shadow-sm text-decoration-none h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3">
                                <i class="bi bi-diagram-3 fs-3"></i>
                            </div>
                            <div class="admin-feed-kpi-stat">{{ ($availableTeams ?? collect())->count() }}</div>
                            <div class="fw-semibold mt-1">Teams</div>
                            <div class="small text-secondary mt-1">School teams in scope</div>
                            <div class="small text-secondary">Operational groups in the directory</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <a href="{{ $summaryLink(['verification_status' => 'unverified']) }}" class="card adminuiux-card shadow-sm text-decoration-none h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3" style="color:#b45309;background:linear-gradient(135deg, rgba(254, 243, 199, 0.98), rgba(255, 237, 213, 0.98));">
                                <i class="bi bi-envelope-exclamation fs-3"></i>
                            </div>
                            <div class="admin-feed-kpi-stat">{{ $summary['unverified'] }}</div>
                            <div class="fw-semibold mt-1">Unverified</div>
                            <div class="small text-secondary mt-1">Verification still pending</div>
                            <div class="small text-secondary">Accounts waiting on email confirmation</div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- CSV Import --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header bg-primary-subtle">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
                        <div>
                            <div class="text-uppercase fw-semibold text-primary" style="font-size:.68rem;letter-spacing:.18em;">Bulk Import</div>
                            <div class="fw-semibold mt-1">Upload users from CSV</div>
                            <p class="small text-secondary mb-0 mt-1">Create or update users in bulk with team and role assignment. Existing users are matched by email.</p>
                        </div>
                        <div class="card bg-light" style="min-width:16rem;">
                            <div class="card-body py-2 px-3 small">
                                <div class="fw-semibold">Expected columns</div>
                                <div class="text-secondary mt-1">{{ implode(', ', $csvImportColumns ?? []) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('app.admin.users.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                        @csrf
                        <div class="col">
                            <label for="users_csv" class="form-label">CSV file</label>
                            <input id="users_csv" type="file" name="users_csv" accept=".csv,text/csv" class="form-control form-control-sm">
                            <div class="form-text">Use role and team labels or the slug values shown in the filters. Optional columns: <code>is_admin</code>, <code>account_active</code>, <code>password</code>.</div>
                            @error('users_csv') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-theme">Import CSV</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Screen-reader helpers --}}
            <div class="sr-only">
                <span>All Attention</span>
                <span>All Roles</span>
                <span>Learners</span>
                <span>Admins</span>
                <span>Verified</span>
                <span>Active</span>
                <span>Unverified</span>
                <a href="{{ route('app.admin.users.index', ['role' => 'admin']) }}">Admin compatibility filter</a>
                <a href="{{ route('app.admin.users.index', ['role' => 'learner']) }}">Learner compatibility filter</a>
            </div>

            {{-- Filter Panel --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                    <div>
                        <div class="text-uppercase fw-semibold text-primary" style="font-size:.68rem;letter-spacing:.18em;">Directory Controls</div>
                        <h3 class="fs-5 fw-semibold mt-1 mb-0">Student Directory</h3>
                        <p class="small text-secondary mb-0 mt-1">Find people quickly, then refine the directory only when you need more control.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('app.admin.users.index', array_filter($filters, fn ($value) => $value !== '' && $value !== 'all')) }}" class="btn btn-sm btn-outline-secondary" title="Refresh"><i class="bi bi-arrow-clockwise"></i></a>
                        <a href="{{ route('app.admin.users.export', array_filter($filters, fn ($value) => $value !== '' && $value !== 'all')) }}" class="btn btn-sm btn-outline-secondary">Export CSV</a>
                        <a href="{{ route('app.admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="users-filter-form" method="GET" action="{{ route('app.admin.users.index') }}">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="attention_status" class="form-label">Status (Attention)</label>
                                <select id="attention_status" name="attention_status" class="form-select form-select-sm">
                                    <option value="all" @selected($filters['attention_status'] === 'all')>All</option>
                                    <option value="needs_attention" @selected($filters['attention_status'] === 'needs_attention')>Needs Attention</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="role" class="form-label">Role</label>
                                <select id="role" name="role" class="form-select form-select-sm">
                                    @foreach ($availableRoleFilters as $roleValue => $roleLabel)
                                        <option value="{{ $roleValue }}" @selected($filters['role'] === $roleValue)>{{ $roleLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="location" class="form-label">Location</label>
                                <select id="location" name="location" class="form-select form-select-sm">
                                    @foreach ($availableLocationFilters as $locationValue => $locationLabel)
                                        <option value="{{ $locationValue }}" @selected($filters['location'] === $locationValue)>{{ $locationLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="team" class="form-label">Team</label>
                                <select id="team" name="team" class="form-select form-select-sm">
                                    @foreach ($availableTeamFilters as $teamValue => $teamLabel)
                                        <option value="{{ $teamValue }}" @selected($filters['team'] === $teamValue)>{{ $teamLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="employee_search" class="form-label">Employee</label>
                                <input id="employee_search" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm" placeholder="Search by name">
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="account_status" class="form-label">Access Status</label>
                                <select id="account_status" name="account_status" class="form-select form-select-sm">
                                    <option value="all" @selected($filters['account_status'] === 'all')>All Access</option>
                                    <option value="active" @selected($filters['account_status'] === 'active')>Active</option>
                                    <option value="suspended" @selected($filters['account_status'] === 'suspended')>Suspended</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="verification_status" class="form-label">Verification Status</label>
                                <select id="verification_status" name="verification_status" class="form-select form-select-sm">
                                    <option value="all" @selected($filters['verification_status'] === 'all')>All Verification</option>
                                    <option value="verified" @selected($filters['verification_status'] === 'verified')>Verified</option>
                                    <option value="unverified" @selected($filters['verification_status'] === 'unverified')>Unverified</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="inactivity_status" class="form-label">Activity Status</label>
                                <select id="inactivity_status" name="inactivity_status" class="form-select form-select-sm">
                                    <option value="all" @selected($filters['inactivity_status'] === 'all')>All Inactivity</option>
                                    <option value="never" @selected($filters['inactivity_status'] === 'never')>Never Logged In</option>
                                    <option value="inactive_30" @selected($filters['inactivity_status'] === 'inactive_30')>Inactive 30+ Days</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="training_compliance" class="form-label">Training Compliance</label>
                                <select id="training_compliance" name="training_compliance" class="form-select form-select-sm">
                                    @foreach ($availableTrainingComplianceFilters as $complianceValue => $complianceLabel)
                                        <option value="{{ $complianceValue }}" @selected($filters['training_compliance'] === $complianceValue)>{{ $complianceLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="sort" class="form-label">Sort</label>
                                <select id="sort" name="sort" class="form-select form-select-sm">
                                    <option value="created_at" @selected($filters['sort'] === 'created_at')>Created</option>
                                    <option value="name" @selected($filters['sort'] === 'name')>Name</option>
                                    <option value="email" @selected($filters['sort'] === 'email')>Email</option>
                                    <option value="last_login_at" @selected($filters['sort'] === 'last_login_at')>Last Login</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="sort_dir" class="form-label">Direction</label>
                                <select id="sort_dir" name="sort_dir" class="form-select form-select-sm">
                                    <option value="desc" @selected($filters['sort_dir'] === 'desc')>Descending</option>
                                    <option value="asc" @selected($filters['sort_dir'] === 'asc')>Ascending</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label for="limit" class="form-label">Per Page</label>
                                <input id="limit" type="number" min="5" max="100" name="limit" value="{{ $filters['limit'] }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                                <a href="{{ route('app.admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                    <details class="mt-3 card bg-light">
                        <summary class="card-body py-2 px-3 fw-semibold small" style="cursor:pointer;">Advanced filters</summary>
                        <div class="card-body pt-0 px-3 pb-2 small text-secondary">Use role, access, verification, inactivity, attention, sort, and per-page controls to narrow the directory when needed.</div>
                    </details>
                </div>
            </div>

            {{-- Directory Table --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header bg-primary-subtle">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                        <div>
                            <div class="text-uppercase fw-semibold text-primary" style="font-size:.68rem;letter-spacing:.18em;">Directory Report</div>
                            <div class="fw-semibold mt-1">All Students</div>
                            <div class="small text-secondary mt-1">{{ $users->total() }} matching records in the current directory view</div>
                        </div>
                        <form id="bulk-user-action-form" method="POST" action="{{ route('app.admin.users.bulk-update') }}" class="d-flex flex-column flex-sm-row align-items-sm-end gap-2" data-user-bulk-form data-user-bulk-active-preset="{{ $selectedBulkPreset ?? '' }}">
                            @csrf
                            @foreach (['q', 'role', 'location', 'team', 'account_status', 'verification_status', 'inactivity_status', 'attention_status', 'training_compliance', 'sort', 'sort_dir', 'limit'] as $filterKey)
                                <input type="hidden" name="{{ $filterKey }}" value="{{ $filters[$filterKey] }}">
                            @endforeach
                            <div>
                                <label for="bulk-user-action" class="form-label small">Bulk Action</label>
                                <select id="bulk-user-action" name="action" data-user-bulk-action class="form-select form-select-sm" style="min-width:16rem;">
                                    <option value="resend_verification" @selected($selectedBulkAction === 'resend_verification')>Resend verification emails</option>
                                    <option value="mark_verified" @selected($selectedBulkAction === 'mark_verified')>Mark selected users verified</option>
                                    <option value="send_password_reset_link" @selected($selectedBulkAction === 'send_password_reset_link')>Send password reset links</option>
                                    <option value="suspend" @selected($selectedBulkAction === 'suspend')>Suspend selected users</option>
                                    <option value="restore" @selected($selectedBulkAction === 'restore')>Restore selected users</option>
                                </select>
                            </div>
                            <button type="submit" data-user-bulk-submit disabled class="btn btn-sm btn-dark" disabled>Select users to continue</button>
                        </form>
                    </div>
                </div>

                {{-- Quick filters --}}
                <div class="card-body border-bottom py-2">
                    <form method="GET" action="{{ route('app.admin.users.index') }}" class="row g-2 align-items-end">
                        @foreach (['location', 'account_status', 'verification_status', 'inactivity_status', 'attention_status', 'training_compliance', 'sort', 'sort_dir', 'limit'] as $hk)
                            <input type="hidden" name="{{ $hk }}" value="{{ $filters[$hk] }}">
                        @endforeach
                        <div class="col-auto">
                            <label for="qf_role" class="form-label small mb-0">Role</label>
                            <select id="qf_role" name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach ($availableRoleFilters as $roleValue => $roleLabel)
                                    <option value="{{ $roleValue }}" @selected($filters['role'] === $roleValue)>{{ $roleLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="qf_location" class="form-label small mb-0">Location</label>
                            <select id="qf_location" name="location" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach ($availableLocationFilters as $locationValue => $locationLabel)
                                    <option value="{{ $locationValue }}" @selected($filters['location'] === $locationValue)>{{ $locationLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="qf_team" class="form-label small mb-0">Team</label>
                            <select id="qf_team" name="team" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach ($availableTeamFilters as $teamValue => $teamLabel)
                                    <option value="{{ $teamValue }}" @selected($filters['team'] === $teamValue)>{{ $teamLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="qf_employee" class="form-label small mb-0">Employee</label>
                            <div class="input-group input-group-sm">
                                <input id="qf_employee" type="text" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm" placeholder="Search by name or email">
                                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Bulk controls --}}
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-12 col-xl-6">
                            <div class="card bg-light">
                                <div class="card-body py-3 px-3 small">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <button type="button" data-user-bulk-select-visible class="btn btn-sm btn-outline-secondary">Select visible</button>
                                        @if ($selectedBulkPreset)
                                            <button type="button" data-user-bulk-select-preset class="btn btn-sm btn-outline-primary">Select visible in {{ $selectedBulkPreset }}</button>
                                            <button type="button" data-user-bulk-clear-preset-selection class="btn btn-sm btn-outline-primary">Select none in {{ $selectedBulkPreset }}</button>
                                        @endif
                                        <button type="button" data-user-bulk-clear-selection class="btn btn-sm btn-outline-secondary">Clear selection</button>
                                        <span data-user-bulk-selection-count class="fw-semibold">0 selected</span>
                                        <span class="text-secondary">Visible rows only</span>
                                    </div>
                                    <div data-user-bulk-action-summary class="mt-2 text-secondary">Select an action and at least one visible user.</div>
                                    <div data-user-bulk-confirmation-hint class="mt-1 text-secondary {{ in_array($selectedBulkAction, ['suspend', 'restore'], true) ? '' : 'd-none' }}">
                                        @if ($selectedBulkAction === 'restore')
                                            Bulk restore requires confirmation before submit.
                                        @else
                                            Bulk suspension requires confirmation before submit.
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                        <button type="button" data-user-bulk-copy-ids class="btn btn-sm btn-outline-secondary">Copy selected IDs</button>
                                        <span data-user-bulk-copy-status class="text-secondary small"></span>
                                    </div>
                                    <details class="mt-2 card">
                                        <summary class="card-body py-2 px-3 fw-semibold small" style="cursor:pointer;">Selected user IDs</summary>
                                        <div data-user-bulk-selected-ids class="card-body pt-0 px-3 pb-2 small text-secondary" style="word-break:break-all;">None selected.</div>
                                    </details>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-xl-6">
                            <div class="card bg-light">
                                <div class="card-body py-3 px-3 small">
                                    @if ($selectedBulkPreset)
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary-subtle text-primary">Preset applied: {{ $selectedBulkPreset }}</span>
                                            <span data-user-bulk-preset-selection-count class="badge bg-light text-primary border">0 selected in preset</span>
                                            @if ($selectedBulkPresetAuditAction)
                                                <a href="{{ route('app.admin.assignments.audit', ['action' => $selectedBulkPresetAuditAction]) }}" class="btn btn-sm btn-outline-secondary">Open Audit</a>
                                            @endif
                                            <a href="{{ route('app.admin.users.index', array_filter([
                                                'q' => $filters['q'],
                                                'role' => $filters['role'],
                                                'account_status' => $filters['account_status'],
                                                'verification_status' => $filters['verification_status'],
                                                'inactivity_status' => $filters['inactivity_status'],
                                                'attention_status' => $filters['attention_status'],
                                                'sort' => $filters['sort'],
                                                'sort_dir' => $filters['sort_dir'],
                                                'limit' => $filters['limit'],
                                            ], fn ($value) => $value !== '' && $value !== 'all')) }}" class="btn btn-sm btn-outline-secondary">Clear preset</a>
                                        </div>
                                        @if ($selectedBulkPresetDescription)
                                            <div class="text-secondary mb-2">{{ $selectedBulkPresetDescription }}</div>
                                        @endif
                                    @endif
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="fw-semibold">Queue presets</span>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ $bulkPresetLink(['attention_status' => 'needs_attention', 'verification_status' => 'unverified', 'bulk_action' => 'resend_verification']) }}" class="btn btn-sm {{ $selectedBulkPreset === 'Verification Follow-up' ? 'btn-primary' : 'btn-outline-secondary' }}">Verification Follow-up ({{ $bulkPresetCounts['Verification Follow-up'] ?? 0 }})</a>
                                            <a href="{{ route('app.admin.assignments.audit', ['action' => $bulkPresetAuditActions['Verification Follow-up']]) }}" class="btn btn-sm btn-outline-secondary">Audit</a>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ $bulkPresetLink(['verification_status' => 'unverified', 'bulk_action' => 'mark_verified']) }}" class="btn btn-sm {{ $selectedBulkPreset === 'Mark Verified Queue' ? 'btn-primary' : 'btn-outline-secondary' }}">Mark Verified Queue ({{ $bulkPresetCounts['Mark Verified Queue'] ?? 0 }})</a>
                                            <a href="{{ route('app.admin.assignments.audit', ['action' => $bulkPresetAuditActions['Mark Verified Queue']]) }}" class="btn btn-sm btn-outline-secondary">Audit</a>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ $bulkPresetLink(['attention_status' => 'needs_attention', 'inactivity_status' => 'inactive_30', 'bulk_action' => 'suspend']) }}" class="btn btn-sm {{ $selectedBulkPreset === 'Suspend Inactive 30+' ? 'btn-primary' : 'btn-outline-secondary' }}">Suspend Inactive 30+ ({{ $bulkPresetCounts['Suspend Inactive 30+'] ?? 0 }})</a>
                                            <a href="{{ route('app.admin.assignments.audit', ['action' => $bulkPresetAuditActions['Suspend Inactive 30+']]) }}" class="btn btn-sm btn-outline-secondary">Audit</a>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ $bulkPresetLink(['account_status' => 'suspended', 'bulk_action' => 'restore']) }}" class="btn btn-sm {{ $selectedBulkPreset === 'Restore Suspended' ? 'btn-primary' : 'btn-outline-secondary' }}">Restore Suspended ({{ $bulkPresetCounts['Restore Suspended'] ?? 0 }})</a>
                                            <a href="{{ route('app.admin.assignments.audit', ['action' => $bulkPresetAuditActions['Restore Suspended']]) }}" class="btn btn-sm btn-outline-secondary">Audit</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($bulkStatusBanner)
                        <div class="alert {{ $bulkStatusBanner['styles']['container'] ?? 'alert-info' }} mt-3 mb-0 small">
                            @include('app.partials.user-bulk-status-banner')
                        </div>
                    @endif
                </div>

                {{-- User table --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            @php
                                $sortLinkBase = array_filter($filters, fn ($value) => $value !== '' && $value !== 'all');
                                $sortUrl = function (string $column) use ($filters, $sortLinkBase) {
                                    $direction = $filters['sort'] === $column && $filters['sort_dir'] === 'asc' ? 'desc' : 'asc';
                                    return route('app.admin.users.index', array_filter([
                                        ...$sortLinkBase,
                                        'sort' => $column,
                                        'sort_dir' => $direction,
                                    ], fn ($value) => $value !== '' && $value !== 'all'));
                                };
                                $sortMarker = function (string $column) use ($filters) {
                                    if ($filters['sort'] !== $column) return '';
                                    return $filters['sort_dir'] === 'asc' ? ' ↑' : ' ↓';
                                };
                            @endphp
                            <tr>
                                <th style="width:2.5rem;"><input type="checkbox" data-user-bulk-toggle-visible class="form-check-input" aria-label="Select visible users"></th>
                                <th><a href="{{ $sortUrl('name') }}" class="text-decoration-none">Student{{ $sortMarker('name') }}</a></th>
                                <th><a href="{{ $sortUrl('email') }}" class="text-decoration-none">Contact{{ $sortMarker('email') }}</a></th>
                                <th>Location</th>
                                <th>Role</th>
                                <th>Access</th>
                                <th>Verification</th>
                                <th><a href="{{ $sortUrl('last_login_at') }}" class="text-decoration-none">Attendance{{ $sortMarker('last_login_at') }}</a></th>
                                <th><a href="{{ $sortUrl('created_at') }}" class="text-decoration-none">Joined{{ $sortMarker('created_at') }}</a></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $rowImage = $userImages[$loop->index % count($userImages)];
                                    $needsAttentionRow = $user->suspended_at !== null
                                        || $user->email_verified_at === null
                                        || $user->last_login_at === null
                                        || optional($user->last_login_at)->lt(now()->subDays(30));
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" form="bulk-user-action-form" data-user-bulk-checkbox class="form-check-input">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:2.75rem;height:2.75rem;border-radius:.4rem;overflow:hidden;flex-shrink:0;">
                                                <img src="{{ $rowImage }}" alt="{{ $user->name }}" style="width:100%;height:100%;object-fit:cover;">
                                            </div>
                                            <div>
                                                <div class="fw-semibold">
                                                    <a href="{{ route('app.admin.users.edit', ['user' => $user->id]) }}" class="text-primary text-decoration-none">{{ $user->name }}</a>
                                                    @if (auth()->id() === $user->id)
                                                        <span class="badge bg-primary-subtle text-primary ms-1">You</span>
                                                    @endif
                                                </div>
                                                <div class="text-secondary" style="font-size:.75rem;">
                                                    {{ $user->preference?->team ?? ($user->hasAdminAccess() ? 'Admin workspace' : 'Learner workspace') }}
                                                    @if ($needsAttentionRow) | Needs Attention @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $user->email }}</div>
                                        <div class="text-secondary" style="font-size:.75rem;">ID {{ $user->id }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $user->preference?->location?->name ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $user->preference?->role ?? ($user->hasAdminAccess() ? $user->systemRoleLabel() : 'Not assigned') }}</div>
                                        <div class="text-secondary" style="font-size:.75rem;">{{ $user->hasAdminAccess() ? $user->systemRoleLabel() : ($user->preference?->team ?? 'No team assigned') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user->suspended_at ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">{{ $user->suspended_at ? 'Suspended' : 'Active' }}</span>
                                        <div class="text-secondary" style="font-size:.75rem;">{{ $user->suspended_at ? 'Login blocked' : 'Login allowed' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $user->email_verified_at ? 'Verified' : 'Unverified' }}</div>
                                        <div class="text-secondary" style="font-size:.75rem;">{{ $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i') : 'Pending verification' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $user->last_login_at?->format('Y-m-d H:i') ?? 'Never Logged In' }}</div>
                                        <div class="text-secondary" style="font-size:.75rem;">{{ $user->last_login_at ? 'Recent activity' : 'No activity recorded' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $user->created_at?->format('Y-m-d') ?? 'n/a' }}</div>
                                        <div class="text-secondary" style="font-size:.75rem;">{{ $user->created_at?->format('H:i') ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <a class="btn btn-sm btn-link btn-square no-caret text-primary" data-bs-toggle="dropdown"><i class="bi bi-three-dots fs-5"></i></a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="{{ route('app.admin.users.show', ['user' => $user->id]) }}">View</a></li>
                                                <li><a class="dropdown-item" href="{{ route('app.admin.users.edit', ['user' => $user->id]) }}">Edit Account</a></li>
                                                <li><a class="dropdown-item" href="{{ route('app.admin.assignments.user', ['user' => $user->id]) }}">Learner Detail</a></li>
                                                <li><a class="dropdown-item" href="{{ route('app.admin.assignments.audit', ['target' => $user->id]) }}">Open Audit</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-secondary text-center py-4">No users matched the current filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
