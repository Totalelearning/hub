@extends('layouts.learninguiux')

@section('title', 'Roles & Teams - Learning')
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
                        <nav aria-label="breadcrumb" class="mb-2">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="{{ route('app.admin.assignments') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Roles & Teams</li>
                            </ol>
                        </nav>
                        <h1 class="fs-3 fw-semibold mb-2">Roles & Teams</h1>
                        <p class="text-secondary mb-0">Manage the roles and teams available across the platform. Changes here update filters, user forms, compliance rules, and content targeting.</p>
                    </div>
                    <div class="col-12 col-lg-5 d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('app.admin.users.index') }}" class="btn btn-outline-theme btn-sm">Back to Users</a>
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
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-person-badge fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $roles->count() }}</div>
                            <div class="fw-semibold mt-1">Roles</div>
                            <div class="small text-secondary mt-auto pt-2">Available roles</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-people fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ $teams->count() }}</div>
                            <div class="fw-semibold mt-1">Teams</div>
                            <div class="small text-secondary mt-auto pt-2">Available teams</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-person-check fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ \App\Models\UserPreference::whereNotNull('role')->where('role', '!=', '')->count() }}</div>
                            <div class="fw-semibold mt-1">Users with Role</div>
                            <div class="small text-secondary mt-auto pt-2">Assigned</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card adminuiux-card shadow-sm h-100 admin-feed-kpi">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="admin-feed-kpi-icon mb-3"><i class="bi bi-person-lines-fill fs-3"></i></div>
                            <div class="admin-feed-kpi-stat">{{ \App\Models\UserPreference::whereNotNull('team')->where('team', '!=', '')->count() }}</div>
                            <div class="fw-semibold mt-1">Users with Team</div>
                            <div class="small text-secondary mt-auto pt-2">Assigned</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Roles --}}
                <div class="col-12 col-lg-6">
                    <div class="card adminuiux-card shadow-sm mb-4">
                        <div class="card-header bg-primary-subtle d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">Roles</div>
                                <div class="small text-secondary">Staff roles used for filters, compliance, and content targeting.</div>
                            </div>
                        </div>

                        {{-- Add role form --}}
                        <div class="card-body border-bottom py-3">
                            <form method="POST" action="{{ route('app.admin.roles-teams.roles.store') }}" class="row g-2 align-items-end">
                                @csrf
                                <div class="col">
                                    <label for="new_role_name" class="form-label small mb-0">New Role</label>
                                    <input type="text" name="name" id="new_role_name" class="form-control form-control-sm" placeholder="e.g. Head of Year" required>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-theme"><i class="bi bi-plus-lg me-1"></i>Add</button>
                                </div>
                            </form>
                        </div>

                        {{-- Roles list --}}
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small fw-semibold">#</th>
                                        <th class="small fw-semibold">Name</th>
                                        <th class="small fw-semibold">Slug</th>
                                        <th class="small fw-semibold text-center">Users</th>
                                        <th class="small fw-semibold text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($roles as $role)
                                        @php $roleUserCount = \App\Models\UserPreference::where('role', $role->name)->count(); @endphp
                                        <tr>
                                            <td class="small text-secondary">{{ $role->sort_order }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('app.admin.roles-teams.roles.update', $role) }}" class="d-flex gap-2 align-items-center" id="role-form-{{ $role->id }}">
                                                    @csrf @method('PATCH')
                                                    <input type="text" name="name" value="{{ $role->name }}" class="form-control form-control-sm" style="min-width:160px;" required>
                                                </form>
                                            </td>
                                            <td class="small text-secondary">{{ $role->slug }}</td>
                                            <td class="text-center">
                                                @if ($roleUserCount > 0)
                                                    <span class="badge bg-primary-subtle text-primary">{{ $roleUserCount }}</span>
                                                @else
                                                    <span class="text-secondary small">0</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
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

                {{-- Teams --}}
                <div class="col-12 col-lg-6">
                    <div class="card adminuiux-card shadow-sm mb-4">
                        <div class="card-header bg-primary-subtle d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">Teams</div>
                                <div class="small text-secondary">School teams used for reporting and compliance grouping.</div>
                            </div>
                        </div>

                        {{-- Add team form --}}
                        <div class="card-body border-bottom py-3">
                            <form method="POST" action="{{ route('app.admin.roles-teams.teams.store') }}" class="row g-2 align-items-end">
                                @csrf
                                <div class="col">
                                    <label for="new_team_name" class="form-label small mb-0">New Team</label>
                                    <input type="text" name="name" id="new_team_name" class="form-control form-control-sm" placeholder="e.g. EYFS Team" required>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-theme"><i class="bi bi-plus-lg me-1"></i>Add</button>
                                </div>
                            </form>
                        </div>

                        {{-- Teams list --}}
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small fw-semibold">#</th>
                                        <th class="small fw-semibold">Name</th>
                                        <th class="small fw-semibold">Slug</th>
                                        <th class="small fw-semibold text-center">Users</th>
                                        <th class="small fw-semibold text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($teams as $team)
                                        @php $teamUserCount = \App\Models\UserPreference::where('team', $team->name)->count(); @endphp
                                        <tr>
                                            <td class="small text-secondary">{{ $team->sort_order }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('app.admin.roles-teams.teams.update', $team) }}" class="d-flex gap-2 align-items-center" id="team-form-{{ $team->id }}">
                                                    @csrf @method('PATCH')
                                                    <input type="text" name="name" value="{{ $team->name }}" class="form-control form-control-sm" style="min-width:160px;" required>
                                                </form>
                                            </td>
                                            <td class="small text-secondary">{{ $team->slug }}</td>
                                            <td class="text-center">
                                                @if ($teamUserCount > 0)
                                                    <span class="badge bg-primary-subtle text-primary">{{ $teamUserCount }}</span>
                                                @else
                                                    <span class="text-secondary small">0</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
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
    </main>
</div>
@endsection
