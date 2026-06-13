@php
    $active = $active ?? 'dashboard';
@endphp

<div class="adminuiux-sidebar shadow-sm">
    <div class="adminuiux-sidebar-inner">
        <ul class="nav flex-column menu-active-line">
            <li class="nav-item">
                <a href="{{ route('app.parent.dashboard') }}" class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-grid-1x2"></i>
                    <span class="menu-name">Dashboard</span>
                </a>
            </li>
        </ul>
    </div>
</div>
