@php
    $adminLinks = [
        ['label' => 'Dashboard', 'route' => route('app.admin.assignments'), 'active' => request()->routeIs('app.admin.assignments'), 'icon' => 'bi bi-grid-1x2'],
        ['label' => 'Courses', 'route' => route('app.admin.modules.index'), 'active' => request()->routeIs('app.admin.modules.*') || request()->routeIs('app.admin.courses.*'), 'icon' => 'bi bi-journal-check'],
        ['label' => 'SCORM', 'route' => route('app.admin.scorm.index'), 'active' => request()->routeIs('app.admin.scorm.*'), 'icon' => 'bi bi-arrow-repeat'],
        ['label' => 'Analytics', 'route' => route('app.admin.course-analytics'), 'active' => request()->routeIs('app.admin.course-analytics*'), 'icon' => 'bi bi-bar-chart-line'],
        ['label' => 'Compliance', 'route' => route('app.admin.compliance'), 'active' => request()->routeIs('app.admin.compliance*'), 'icon' => 'bi bi-shield-check'],
        ['label' => 'Users', 'route' => route('app.admin.users.index'), 'active' => request()->routeIs('app.admin.users.*'), 'icon' => 'bi bi-people'],
        ['label' => 'Paths', 'route' => route('app.admin.paths.index'), 'active' => request()->routeIs('app.admin.paths.*'), 'icon' => 'bi bi-signpost-split'],
        ['label' => 'Roles & Teams', 'route' => route('app.admin.roles-teams.index'), 'active' => request()->routeIs('app.admin.roles-teams.*'), 'icon' => 'bi bi-diagram-3'],
        ['label' => 'Settings', 'route' => route('app.admin.reminder-settings.edit'), 'active' => request()->routeIs('app.admin.reminder-settings.*') || request()->routeIs('app.admin.scoring.*') || request()->routeIs('app.admin.ranking.*') || request()->routeIs('app.assignment-rules'), 'icon' => 'bi bi-sliders'],
    ];
@endphp

<div class="adminuiux-sidebar shadow-sm">
    <div class="adminuiux-sidebar-inner">
        <ul class="nav flex-column menu-active-line">
            @foreach ($adminLinks as $link)
                <li class="nav-item">
                    <a href="{{ $link['route'] }}" class="nav-link {{ $link['active'] ? 'active' : '' }}">
                        <i class="menu-icon {{ $link['icon'] }}"></i>
                        <span class="menu-name">{{ $link['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
