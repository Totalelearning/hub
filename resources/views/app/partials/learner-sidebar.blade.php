@php
    $active = $active ?? 'dashboard';
@endphp

<div class="adminuiux-sidebar shadow-sm">
    <div class="adminuiux-sidebar-inner">
        <ul class="nav flex-column menu-active-line">
            <li class="nav-item">
                <a href="{{ route('app.feed') }}" class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-grid-1x2"></i>
                    <span class="menu-name">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('app.feed.required') }}" class="nav-link {{ $active === 'required' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-journal-check"></i>
                    <span class="menu-name">Required</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('app.paths') }}" class="nav-link {{ $active === 'paths' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-signpost-split"></i>
                    <span class="menu-name">Paths</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('app.feed.saved') }}" class="nav-link {{ $active === 'saved' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-bookmark-heart"></i>
                    <span class="menu-name">Saved</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('app.feed.recommended') }}" class="nav-link {{ $active === 'recommended' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-stars"></i>
                    <span class="menu-name">Recommended</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('app.leaderboard') }}" class="nav-link {{ $active === 'leaderboard' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-trophy"></i>
                    <span class="menu-name">Leaderboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('app.badges') }}" class="nav-link {{ $active === 'badges' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-award"></i>
                    <span class="menu-name">Badges</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('app.reminders') }}" class="nav-link {{ $active === 'reminders' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-bell"></i>
                    <span class="menu-name">Reminders</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('app.preferences') }}" class="nav-link {{ $active === 'preferences' ? 'active' : '' }}">
                    <i class="menu-icon bi bi-sliders"></i>
                    <span class="menu-name">Preferences</span>
                </a>
            </li>
        </ul>
    </div>
</div>
