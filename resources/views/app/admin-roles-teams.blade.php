@extends('layouts.learninguiux')

@section('title', 'Roles & Teams - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .analytics-card { border: 0; border-radius: 20px; box-shadow: 0 12px 32px rgba(43, 82, 138, 0.1); background: rgba(255, 255, 255, 0.97); }
    .analytics-section-title { font-size: 0.78rem; letter-spacing: 0.14em; text-transform: uppercase; color: #5f7699; font-weight: 700; }
    .section-toggle { cursor: pointer; user-select: none; display: flex; align-items: center; justify-content: space-between; }
    .section-toggle .chevron { transition: transform 200ms ease; font-size: 0.85rem; color: #94a3b8; }
    .section-toggle .chevron.collapsed { transform: rotate(-90deg); }
    .section-collapse { transition: max-height 300ms ease, opacity 200ms ease; overflow: hidden; }
    .section-collapse.open { max-height: 5000px; opacity: 1; }
    .section-collapse.closed { max-height: 0; opacity: 0; }
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
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Platform Settings</div>
                        <h1 class="fs-3 fw-semibold mb-2">Roles{{ auth()->user()->hasUnrestrictedView() ? ', Teams & Locations' : ' & Teams' }}</h1>
                        <p class="text-secondary mb-0">Manage the roles{{ auth()->user()->hasUnrestrictedView() ? ', teams, and school locations' : ' and teams' }} available across the platform. Changes here update filters, user forms, and content targeting.</p>
                    </div>
                </div>
            </div>

            {{-- Status flash --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- KPI cards --}}
            @php
                $usersWithRole = \App\Models\UserPreference::whereNotNull('role')->where('role', '!=', '')->count();
                $usersWithTeam = \App\Models\UserPreference::whereNotNull('team')->where('team', '!=', '')->count();
                $usersWithLocation = \App\Models\UserPreference::whereNotNull('location_id')->count();
            @endphp
            <div class="row g-3 mb-4">
                <div class="{{ auth()->user()->hasUnrestrictedView() ? 'col-6 col-xl-4' : 'col-6' }}">
                    <div class="card analytics-card h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-person-badge fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $roles->count() }}</div>
                            <div class="fw-semibold mt-1">Roles</div>
                            <div class="small text-secondary mt-auto pt-2">{{ $usersWithRole }} users assigned</div>
                        </div>
                    </div>
                </div>
                <div class="{{ auth()->user()->hasUnrestrictedView() ? 'col-6 col-xl-4' : 'col-6' }}">
                    <div class="card analytics-card h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-people fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $teams->count() }}</div>
                            <div class="fw-semibold mt-1">Teams</div>
                            <div class="small text-secondary mt-auto pt-2">{{ $usersWithTeam }} users assigned</div>
                        </div>
                    </div>
                </div>
                @if (auth()->user()->hasUnrestrictedView())
                <div class="col-6 col-xl-4">
                    <div class="card analytics-card h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-building fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $locations->count() }}</div>
                            <div class="fw-semibold mt-1">Locations</div>
                            <div class="small text-secondary mt-auto pt-2">{{ $usersWithLocation }} users assigned</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="row g-4">
                {{-- Roles --}}
                <div class="col-12" x-data="{ open: false }">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div>
                                    <div class="analytics-section-title mb-0">Roles <span class="badge bg-primary-subtle text-primary ms-2" style="font-size:.7rem;">{{ $roles->count() }}</span></div>
                                    <p class="small text-secondary mb-0 mt-1">Staff roles used for filters, compliance, and content targeting.</p>
                                </div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">
                                <div class="border rounded-3 p-3 mb-3 bg-light">
                                    <form method="POST" action="{{ route('app.admin.roles-teams.roles.store') }}" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col">
                                            <label for="new_role_name" class="form-label small mb-0">Add New Role</label>
                                            <input type="text" name="name" id="new_role_name" class="form-control form-control-sm" placeholder="e.g. Head of Year" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-theme"><i class="bi bi-plus-lg me-1"></i>Add</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-2">#</th>
                                                <th class="px-3 py-2">Name</th>
                                                <th class="px-3 py-2">Slug</th>
                                                <th class="px-3 py-2 text-center">Users</th>
                                                <th class="px-3 py-2 text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($roles as $role)
                                                @php $roleUserCount = \App\Models\UserPreference::where('role', $role->name)->count(); @endphp
                                                <tr>
                                                    <td class="px-3 small text-secondary">{{ $role->sort_order }}</td>
                                                    <td class="px-3">
                                                        <form method="POST" action="{{ route('app.admin.roles-teams.roles.update', $role) }}" class="d-flex gap-2 align-items-center" id="role-form-{{ $role->id }}">
                                                            @csrf @method('PATCH')
                                                            <input type="text" name="name" value="{{ $role->name }}" class="form-control form-control-sm" style="min-width:160px;" required>
                                                        </form>
                                                    </td>
                                                    <td class="px-3 small text-secondary">{{ $role->slug }}</td>
                                                    <td class="px-3 text-center">
                                                        @if ($roleUserCount > 0)
                                                            <span class="badge bg-primary-subtle text-primary">{{ $roleUserCount }}</span>
                                                        @else
                                                            <span class="text-secondary small">0</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 text-end">
                                                        <div class="d-flex gap-1 justify-content-end">
                                                            <button type="submit" form="role-form-{{ $role->id }}" class="btn btn-sm btn-outline-primary" title="Save"><i class="bi bi-check-lg"></i></button>
                                                            <form method="POST" action="{{ route('app.admin.roles-teams.roles.destroy', $role) }}" onsubmit="return confirm('Delete role &quot;{{ $role->name }}&quot;?{{ $roleUserCount > 0 ? ' This will unset the role for ' . $roleUserCount . ' user(s).' : '' }}')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-secondary py-4">No roles defined yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Teams --}}
                <div class="col-12" x-data="{ open: false }">
                    <div class="card analytics-card">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div>
                                    <div class="analytics-section-title mb-0">Teams <span class="badge bg-primary-subtle text-primary ms-2" style="font-size:.7rem;">{{ $teams->count() }}</span></div>
                                    <p class="small text-secondary mb-0 mt-1">School teams used for reporting and compliance grouping.</p>
                                </div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">
                                <div class="border rounded-3 p-3 mb-3 bg-light">
                                    <form method="POST" action="{{ route('app.admin.roles-teams.teams.store') }}" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col">
                                            <label for="new_team_name" class="form-label small mb-0">Add New Team</label>
                                            <input type="text" name="name" id="new_team_name" class="form-control form-control-sm" placeholder="e.g. EYFS Team" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-theme"><i class="bi bi-plus-lg me-1"></i>Add</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-2">#</th>
                                                <th class="px-3 py-2">Name</th>
                                                <th class="px-3 py-2">Slug</th>
                                                <th class="px-3 py-2 text-center">Users</th>
                                                <th class="px-3 py-2 text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($teams as $team)
                                                @php $teamUserCount = \App\Models\UserPreference::where('team', $team->name)->count(); @endphp
                                                <tr>
                                                    <td class="px-3 small text-secondary">{{ $team->sort_order }}</td>
                                                    <td class="px-3">
                                                        <form method="POST" action="{{ route('app.admin.roles-teams.teams.update', $team) }}" class="d-flex gap-2 align-items-center" id="team-form-{{ $team->id }}">
                                                            @csrf @method('PATCH')
                                                            <input type="text" name="name" value="{{ $team->name }}" class="form-control form-control-sm" style="min-width:160px;" required>
                                                        </form>
                                                    </td>
                                                    <td class="px-3 small text-secondary">{{ $team->slug }}</td>
                                                    <td class="px-3 text-center">
                                                        @if ($teamUserCount > 0)
                                                            <span class="badge bg-primary-subtle text-primary">{{ $teamUserCount }}</span>
                                                        @else
                                                            <span class="text-secondary small">0</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 text-end">
                                                        <div class="d-flex gap-1 justify-content-end">
                                                            <button type="submit" form="team-form-{{ $team->id }}" class="btn btn-sm btn-outline-primary" title="Save"><i class="bi bi-check-lg"></i></button>
                                                            <form method="POST" action="{{ route('app.admin.roles-teams.teams.destroy', $team) }}" onsubmit="return confirm('Delete team &quot;{{ $team->name }}&quot;?{{ $teamUserCount > 0 ? ' This will unset the team for ' . $teamUserCount . ' user(s).' : '' }}')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-secondary py-4">No teams defined yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Locations (trustee + site_admin only) --}}
                @if (auth()->user()->hasUnrestrictedView())
                <div class="col-12" x-data="{ open: false }">
                    <div class="card analytics-card mb-4">
                        <div class="card-body p-4">
                            <div class="section-toggle" @click="open = !open">
                                <div>
                                    <div class="analytics-section-title mb-0">Locations <span class="badge bg-primary-subtle text-primary ms-2" style="font-size:.7rem;">{{ $locations->count() }}</span></div>
                                    <p class="small text-secondary mb-0 mt-1">School sites and trust locations. Each user can be assigned to one location for cross-school reporting.</p>
                                </div>
                                <i class="bi bi-chevron-down chevron" :class="{ 'collapsed': !open }"></i>
                            </div>
                            <div class="section-collapse mt-3" :class="open ? 'open' : 'closed'">
                                <div class="border rounded-3 p-3 mb-3 bg-light">
                                    <form method="POST" action="{{ route('app.admin.roles-teams.locations.store') }}" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col">
                                            <label for="new_location_name" class="form-label small mb-0">Add New Location</label>
                                            <input type="text" name="name" id="new_location_name" class="form-control form-control-sm" placeholder="e.g. Oakwood Primary Academy" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-theme"><i class="bi bi-plus-lg me-1"></i>Add</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-3 py-2">#</th>
                                                <th class="px-3 py-2">Name</th>
                                                <th class="px-3 py-2">Slug</th>
                                                <th class="px-3 py-2 text-center">Users</th>
                                                <th class="px-3 py-2 text-center">Active</th>
                                                <th class="px-3 py-2 text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($locations as $location)
                                                @php $locationUserCount = \App\Models\UserPreference::where('location_id', $location->id)->count(); @endphp
                                                <tr>
                                                    <td class="px-3 small text-secondary">{{ $location->sort_order }}</td>
                                                    <td class="px-3">
                                                        <form method="POST" action="{{ route('app.admin.roles-teams.locations.update', $location) }}" class="d-flex gap-2 align-items-center" id="location-form-{{ $location->id }}">
                                                            @csrf @method('PATCH')
                                                            <input type="text" name="name" value="{{ $location->name }}" class="form-control form-control-sm" style="min-width:220px;" required>
                                                        </form>
                                                    </td>
                                                    <td class="px-3 small text-secondary">{{ $location->slug }}</td>
                                                    <td class="px-3 text-center">
                                                        @if ($locationUserCount > 0)
                                                            <span class="badge bg-primary-subtle text-primary">{{ $locationUserCount }}</span>
                                                        @else
                                                            <span class="text-secondary small">0</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 text-center">
                                                        @if ($location->is_active)
                                                            <span class="badge bg-success-subtle text-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 text-end">
                                                        <div class="d-flex gap-1 justify-content-end">
                                                            <button type="submit" form="location-form-{{ $location->id }}" class="btn btn-sm btn-outline-primary" title="Save"><i class="bi bi-check-lg"></i></button>
                                                            <form method="POST" action="{{ route('app.admin.roles-teams.locations.destroy', $location) }}" onsubmit="return confirm('Delete location &quot;{{ $location->name }}&quot;?{{ $locationUserCount > 0 ? ' This will unset the location for ' . $locationUserCount . ' user(s).' : '' }}')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center text-secondary py-4">No locations defined yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </main>
</div>
@endsection
