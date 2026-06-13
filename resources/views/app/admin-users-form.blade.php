@extends('layouts.learninguiux')

@section('title', ($pageTitle ?? 'User') . ' - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .gam-stat-card { border-radius: 16px; border: 1px solid rgba(226, 232, 240, 0.9); background: rgba(248, 250, 252, 0.95); padding: 1.25rem; }
    .form-card { border: 0; border-radius: 20px; box-shadow: 0 12px 32px rgba(43, 82, 138, 0.1); background: rgba(255, 255, 255, 0.97); }
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
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">User Management</div>
                        <h1 class="fs-3 fw-semibold mb-2">{{ $pageTitle }}</h1>
                        <p class="text-secondary mb-3">{{ $pageDescription }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($managedUser->exists)
                                <a href="{{ route('app.admin.users.show', ['user' => $managedUser->id]) }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-person me-1"></i>View User</a>
                                <a href="{{ route('app.admin.assignments.user', ['user' => $managedUser->id]) }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-list-check me-1"></i>Learner Detail</a>
                            @endif
                            <a href="{{ route('app.admin.users.index') }}" class="btn btn-outline-theme btn-sm"><i class="bi bi-arrow-left me-1"></i>All Users</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status flash --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            {{-- KPI cards (edit only) --}}
            @if ($managedUser->exists)
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="gam-stat-card">
                        <div class="small text-secondary">Created</div>
                        <div class="fs-5 fw-bold mt-1">{{ $managedUser->created_at?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="gam-stat-card">
                        <div class="small text-secondary">Email Verified</div>
                        <div class="fs-5 fw-bold mt-1 {{ $managedUser->email_verified_at ? 'text-success' : 'text-warning' }}">{{ $managedUser->email_verified_at ? $managedUser->email_verified_at->format('d M Y') : 'Not verified' }}</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="gam-stat-card">
                        <div class="small text-secondary">Status</div>
                        <div class="fs-5 fw-bold mt-1 {{ $managedUser->suspended_at ? 'text-danger' : 'text-success' }}">{{ $managedUser->suspended_at ? 'Suspended' : 'Active' }}</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="gam-stat-card">
                        <div class="small text-secondary">System Role</div>
                        <div class="fs-5 fw-bold mt-1 text-primary">{{ $managedUser->systemRoleLabel() }}</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Edit form --}}
            <div class="card form-card mb-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $formAction }}">
                        @csrf
                        @if ($formMethod !== 'POST')
                            @method($formMethod)
                        @endif

                        <div class="row g-3">
                            {{-- Name --}}
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label">Name</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $managedUser->name) }}" class="form-control form-control-sm" required>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $managedUser->email) }}" class="form-control form-control-sm" required>
                                <div class="form-text">Changing the email clears the current email verification timestamp.</div>
                                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Role --}}
                            <div class="col-12 col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <select id="role" name="role" class="form-select form-select-sm">
                                    <option value="">Select a role</option>
                                    @foreach (($availableRoleOptions ?? []) as $value => $label)
                                        <option value="{{ $value }}" @selected(old('role', array_search($managedUser->preference?->role, $availableRoleOptions ?? [], true)) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Choose the staff role used for directory filters, reporting, and team views.</div>
                                @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Location --}}
                            <div class="col-12 col-md-6">
                                <label for="location_id" class="form-label">Location</label>
                                @can('manage-teams')
                                    <select id="location_id" name="location_id" class="form-select form-select-sm">
                                        <option value="">Select a location</option>
                                        @foreach (($availableLocationOptions ?? []) as $id => $label)
                                            <option value="{{ $id }}" @selected(old('location_id', $managedUser->preference?->location_id) == $id)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Assign the user to a school location for multi-site filtering and comparison.</div>
                                @else
                                    <input type="text" class="form-control form-control-sm" value="{{ $managedUser->preference?->location?->name ?? 'Not assigned' }}" disabled>
                                    <div class="form-text">Location assignment can only be changed by a Site Administrator or SLT Manager.</div>
                                @endcan
                                @error('location_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Team --}}
                            <div class="col-12 col-md-6">
                                <label for="team" class="form-label">Team</label>
                                @can('manage-teams')
                                    <select id="team" name="team" class="form-select form-select-sm">
                                        <option value="">Select a team</option>
                                        @foreach (($availableTeamOptions ?? []) as $value => $label)
                                            <option value="{{ $value }}" @selected(old('team', array_search($managedUser->preference?->team, $availableTeamOptions ?? [], true)) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Assign the user to the school team that should drive reporting and compliance grouping.</div>
                                @else
                                    <input type="text" class="form-control form-control-sm" value="{{ $managedUser->preference?->team ?? 'Not assigned' }}" disabled>
                                    <div class="form-text">Team assignment can only be changed by a Site Administrator or SLT Manager.</div>
                                @endcan
                                @error('team') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- System Role (site admin only) --}}
                            @if (auth()->user()?->isSiteAdmin())
                            <div class="col-12 col-md-6" x-data="{ role: '{{ old('system_role', $managedUser->system_role ?? 'learner') }}' }">
                                <label for="system_role" class="form-label">System Role</label>
                                <select id="system_role" name="system_role" class="form-select form-select-sm" x-model="role">
                                    <option value="parent">Parent</option>
                                    <option value="learner">Learner</option>
                                    <option value="manager">Manager</option>
                                    <option value="slt_manager">SLT Manager</option>
                                    <option value="trustee">Trustee</option>
                                    <option value="site_admin">Site Administrator</option>
                                </select>
                                <div class="form-text">Controls what admin features this user can access. Managers see only their assigned teams.</div>
                                @error('system_role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                {{-- Managed Teams (visible for manager / slt_manager) --}}
                                <template x-if="role === 'manager' || role === 'slt_manager'">
                                    <div class="mt-3">
                                        <label class="form-label">Managed Teams</label>
                                        <div class="card bg-light">
                                            <div class="card-body py-2 px-3">
                                                @foreach (($availableTeamOptions ?? []) as $slug => $teamName)
                                                    <div class="form-check">
                                                        <input type="checkbox" name="managed_teams[]" value="{{ $teamName }}" class="form-check-input" id="mt_{{ $slug }}"
                                                            @checked(in_array($teamName, old('managed_teams', $managedUser->managed_teams ?? []), true))>
                                                        <label class="form-check-label" for="mt_{{ $slug }}">{{ $teamName }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="form-text">Select the teams this manager can view and manage.</div>
                                        @error('managed_teams') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </template>

                                {{-- Managed Locations (visible for manager / slt_manager) --}}
                                <template x-if="role === 'manager' || role === 'slt_manager'">
                                    <div class="mt-3">
                                        <label class="form-label">Managed Locations</label>
                                        <div class="card bg-light">
                                            <div class="card-body py-2 px-3">
                                                @foreach (($availableLocationOptions ?? []) as $id => $locationName)
                                                    <div class="form-check">
                                                        <input type="checkbox" name="managed_locations[]" value="{{ $locationName }}" class="form-check-input" id="ml_{{ $id }}"
                                                            @checked(in_array($locationName, old('managed_locations', $managedUser->managed_locations ?? []), true))>
                                                        <label class="form-check-label" for="ml_{{ $id }}">{{ $locationName }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="form-text">Select the locations this manager can view and manage.</div>
                                        @error('managed_locations') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </template>
                            </div>
                            @else
                                @if ($managedUser->exists && $managedUser->hasAdminAccess())
                                <div class="col-12 col-md-6">
                                    <label class="form-label">System Role</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $managedUser->systemRoleLabel() }}" disabled>
                                    <div class="form-text">System role can only be changed by a Site Administrator.</div>
                                </div>
                                @endif
                            @endif

                            {{-- Account active checkbox --}}
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="account_active" value="1" class="form-check-input" id="account_active" @checked(old('account_active', ! $managedUser->suspended_at))>
                                    <label class="form-check-label" for="account_active">Account active</label>
                                </div>
                                <div class="form-text">
                                    @if ($managedUser->exists)
                                        Uncheck to suspend login access while preserving assignments, progress, and audit history.
                                    @else
                                        New accounts are active by default.
                                    @endif
                                </div>
                            </div>

                            {{-- Magic link (create only) --}}
                            @unless ($managedUser->exists)
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body py-3 px-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="send_magic_link" value="1" class="form-check-input" id="send_magic_link"
                                                    @checked(old('send_magic_link'))
                                                    onchange="document.getElementById('password-fields').style.display = this.checked ? 'none' : ''">
                                                <label class="form-check-label fw-semibold" for="send_magic_link">
                                                    <i class="bi bi-envelope-check me-1"></i> Send magic link instead of setting a password
                                                </label>
                                            </div>
                                            <div class="form-text mt-1">The user will receive an email with a one-click login link (valid for 48 hours). No password needed.</div>
                                        </div>
                                    </div>
                                </div>
                            @endunless

                            {{-- Password fields --}}
                            <div class="col-12" id="password-fields" @unless($managedUser->exists) style="{{ old('send_magic_link') ? 'display:none' : '' }}" @endunless>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label">{{ $managedUser->exists ? 'New Password' : 'Password' }}</label>
                                        <input id="password" type="password" name="password" class="form-control form-control-sm" autocomplete="new-password">
                                        <div class="form-text">
                                            @if ($managedUser->exists)
                                                Leave blank to keep the current password.
                                            @else
                                                Set the initial password for this account.
                                            @endif
                                        </div>
                                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="password_confirmation" class="form-label">{{ $managedUser->exists ? 'Confirm New Password' : 'Confirm Password' }}</label>
                                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-sm" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="col-12 d-flex justify-content-end pt-2">
                                <button type="submit" class="btn btn-theme">{{ $submitLabel }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection
