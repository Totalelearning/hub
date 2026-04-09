@php
    $isAdminShell = request()->routeIs('app.admin.*') || request()->routeIs('app.assignment-rules');
    $workspaceLinks = [
        ['label' => 'Dashboard', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard'), 'icon' => 'dashboard'],
        ['label' => 'Preferences', 'route' => route('app.preferences'), 'active' => request()->routeIs('app.preferences'), 'icon' => 'sliders'],
        ['label' => 'Reminders', 'route' => route('app.reminders'), 'active' => request()->routeIs('app.reminders*'), 'icon' => 'bell'],
        ['label' => 'Learning Paths', 'route' => route('app.paths'), 'active' => request()->routeIs('app.paths'), 'icon' => 'path'],
    ];

    if (auth()->check() && auth()->user()->can('admin-access')) {
        $workspaceLinks = array_merge($workspaceLinks, [
            ['label' => 'Admin Users', 'route' => route('app.admin.users.index'), 'active' => request()->routeIs('app.admin.users.*'), 'icon' => 'users'],
            ['label' => 'Admin Modules', 'route' => route('app.admin.modules.index'), 'active' => request()->routeIs('app.admin.modules.*'), 'icon' => 'modules'],
            ['label' => 'SCORM Overview', 'route' => route('app.admin.scorm.index'), 'active' => request()->routeIs('app.admin.scorm.*'), 'icon' => 'scorm'],
            ['label' => 'Admin Paths', 'route' => route('app.admin.paths.index'), 'active' => request()->routeIs('app.admin.paths.*'), 'icon' => 'branch'],
            ['label' => 'Admin Assignments', 'route' => route('app.admin.assignments'), 'active' => request()->routeIs('app.admin.assignments'), 'icon' => 'clipboard'],
            ['label' => 'Compliance Report', 'route' => route('app.admin.compliance'), 'active' => request()->routeIs('app.admin.compliance'), 'icon' => 'shield'],
            ['label' => 'Assignment Rules', 'route' => route('app.assignment-rules'), 'active' => request()->routeIs('app.assignment-rules'), 'icon' => 'rules'],
        ]);
    }

    $workspaceLinks[] = ['label' => 'Livewire Test', 'route' => route('app.test-livewire'), 'active' => request()->routeIs('app.test-livewire'), 'icon' => 'spark'];
    $activeWorkspaceLink = collect($workspaceLinks)->firstWhere('active', true) ?? $workspaceLinks[0];
    $adminShellLinks = [
        ['label' => 'Dashboard', 'route' => route('app.admin.assignments'), 'active' => request()->routeIs('app.admin.assignments'), 'icon' => 'clipboard'],
        ['label' => 'Modules', 'route' => route('app.admin.modules.index'), 'active' => request()->routeIs('app.admin.modules.*'), 'icon' => 'modules'],
        ['label' => 'SCORM', 'route' => route('app.admin.scorm.index'), 'active' => request()->routeIs('app.admin.scorm.*'), 'icon' => 'scorm'],
        ['label' => 'Compliance', 'route' => route('app.admin.compliance'), 'active' => request()->routeIs('app.admin.compliance*'), 'icon' => 'shield'],
        ['label' => 'Users', 'route' => route('app.admin.users.index'), 'active' => request()->routeIs('app.admin.users.*'), 'icon' => 'users'],
        ['label' => 'Paths', 'route' => route('app.admin.paths.index'), 'active' => request()->routeIs('app.admin.paths.*'), 'icon' => 'branch'],
        ['label' => 'Settings', 'route' => route('app.admin.reminder-settings.edit'), 'active' => request()->routeIs('app.admin.reminder-settings.*') || request()->routeIs('app.admin.scoring.*') || request()->routeIs('app.admin.ranking.*') || request()->routeIs('app.assignment-rules'), 'icon' => 'sliders'],
    ];
    $shellLinks = $isAdminShell ? $adminShellLinks : $workspaceLinks;
    $activeShellLink = collect($shellLinks)->firstWhere('active', true) ?? $shellLinks[0];

    $iconSvg = function (string $icon): string {
        return match ($icon) {
            'dashboard' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M3 10.75A1.75 1.75 0 0 1 4.75 9h2.5A1.75 1.75 0 0 1 9 10.75v4.5A1.75 1.75 0 0 1 7.25 17h-2.5A1.75 1.75 0 0 1 3 15.25v-4.5Zm8-6A1.75 1.75 0 0 1 12.75 3h2.5A1.75 1.75 0 0 1 17 4.75v10.5A1.75 1.75 0 0 1 15.25 17h-2.5A1.75 1.75 0 0 1 11 15.25V4.75Zm-4 3A1.75 1.75 0 0 1 8.75 6h2.5A1.75 1.75 0 0 1 13 7.75v7.5A1.75 1.75 0 0 1 11.25 17h-2.5A1.75 1.75 0 0 1 7 15.25v-7.5Z"/></svg>
            SVG,
            'sliders' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M4.75 3a.75.75 0 0 1 .75.75V6h9V3.75a.75.75 0 0 1 1.5 0v6.5a.75.75 0 0 1-1.5 0V7.5h-9v8.75a.75.75 0 0 1-1.5 0V3.75A.75.75 0 0 1 4.75 3Zm3 7a1.75 1.75 0 1 0 0 3.5 1.75 1.75 0 0 0 0-3.5Zm4.5-5a1.75 1.75 0 1 0 0 3.5 1.75 1.75 0 0 0 0-3.5Z"/></svg>
            SVG,
            'bell' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a4 4 0 0 0-4 4v1.382c0 .537-.214 1.053-.595 1.434L4.31 9.91A1.5 1.5 0 0 0 5.37 12.5h9.26a1.5 1.5 0 0 0 1.06-2.56l-1.095-1.094A2.03 2.03 0 0 1 14 7.382V6a4 4 0 0 0-4-4Z" /><path d="M8 14a2 2 0 1 0 4 0H8Z" /></svg>
            SVG,
            'path' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M4.75 3a2.75 2.75 0 1 0 0 5.5h2.19a2.75 2.75 0 1 1 0 5.5H6.5a.75.75 0 0 0 0 1.5h.44a4.25 4.25 0 1 0 0-8.5H4.75a1.25 1.25 0 1 1 0-2.5h8.75a.75.75 0 0 0 0-1.5H4.75Z"/></svg>
            SVG,
            'users' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM5.5 8a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5ZM14.5 8a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5ZM2 14.25A2.25 2.25 0 0 1 4.25 12h11.5A2.25 2.25 0 0 1 18 14.25V15a.75.75 0 0 1-.75.75H2.75A.75.75 0 0 1 2 15v-.75Z"/></svg>
            SVG,
            'modules' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3.75A.75.75 0 0 1 3.75 3h12.5a.75.75 0 0 1 0 1.5H4.5v11h11.75a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 16.25V3.75Z" /><path d="M8 6.75A.75.75 0 0 1 8.75 6h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 8 6.75Zm0 3.5a.75.75 0 0 1 .75-.75h6.5a.75.75 0 0 1 0 1.5h-6.5a.75.75 0 0 1-.75-.75Zm0 3.5a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 0 1.5h-3.5a.75.75 0 0 1-.75-.75Z" /></svg>
            SVG,
            'scorm' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.5a.75.75 0 0 0-1.5 0v1.02a6.5 6.5 0 1 0 6.23 6.23h1.02a.75.75 0 0 0 0-1.5h-1.72a.75.75 0 0 0-.75.75 5 5 0 1 1-5.03-5V5a.75.75 0 0 0 1.5 0V2.5Z" /><path d="M12.5 2.75a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0V4.56l-3.97 3.97a.75.75 0 1 1-1.06-1.06l3.97-3.97h-1.19a.75.75 0 0 1-.75-.75Z" /></svg>
            SVG,
            'branch' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M5.75 3a2.75 2.75 0 1 0 2.45 4h4.3a1.25 1.25 0 0 1 1.25 1.25v1.17a2.75 2.75 0 1 0 1.5 0V8.25A2.75 2.75 0 0 0 12.5 5.5H8.2A2.75 2.75 0 0 0 5.75 3Zm0 9.5a2.75 2.75 0 1 0 2.45 4h4.3a2.75 2.75 0 0 0 2.75-2.75v-.17a2.75 2.75 0 1 0-1.5 0v.17A1.25 1.25 0 0 1 12.5 15H8.2a2.75 2.75 0 0 0-2.45-2.5Z"/></svg>
            SVG,
            'clipboard' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M7.5 2a.75.75 0 0 0 0 1.5h5A.75.75 0 0 0 12.5 2h-5Z" /><path fill-rule="evenodd" d="M4.25 3A2.25 2.25 0 0 0 2 5.25v9.5A2.25 2.25 0 0 0 4.25 17h11.5A2.25 2.25 0 0 0 18 14.75v-9.5A2.25 2.25 0 0 0 15.75 3H14.5a.75.75 0 0 1-.75.75h-7.5A.75.75 0 0 1 5.5 3H4.25Zm2.5 4.25a.75.75 0 0 1 .75-.75h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1-.75-.75Zm0 3.5A.75.75 0 0 1 7.5 10h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1-.75-.75Zm0 3.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 0 1.5h-3a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" /></svg>
            SVG,
            'shield' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1.75a.75.75 0 0 1 .356.09l5.5 3A.75.75 0 0 1 16.25 5.5v4.69c0 3.332-2.244 6.249-5.455 7.096a.75.75 0 0 1-.39 0C7.194 16.44 4.95 13.523 4.95 10.19V5.5a.75.75 0 0 1 .394-.66l5.5-3A.75.75 0 0 1 10 1.75Zm2.03 6.22a.75.75 0 1 0-1.06-1.06L9 8.88 8.03 7.91a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.06 0l2.5-2.5Z" clip-rule="evenodd" /></svg>
            SVG,
            'rules' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 3A2.25 2.25 0 0 0 2 5.25v9.5A2.25 2.25 0 0 0 4.25 17h11.5A2.25 2.25 0 0 0 18 14.75v-9.5A2.25 2.25 0 0 0 15.75 3H4.25Zm2.22 3.47a.75.75 0 0 1 1.06 0L10 8.94l2.47-2.47a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
            SVG,
            'spark' => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2.5a.75.75 0 0 1 .696.472l1.164 2.912 2.91 1.163a.75.75 0 0 1 0 1.392l-2.91 1.164-1.164 2.91a.75.75 0 0 1-1.392 0L8.14 9.603 5.23 8.439a.75.75 0 0 1 0-1.392l2.91-1.163 1.164-2.912A.75.75 0 0 1 10 2.5Z"/><path d="M4.5 12.5a.75.75 0 0 1 .696.472l.332.83.83.332a.75.75 0 0 1 0 1.392l-.83.332-.332.83a.75.75 0 0 1-1.392 0l-.332-.83-.83-.332a.75.75 0 0 1 0-1.392l.83-.332.332-.83A.75.75 0 0 1 4.5 12.5Z"/></svg>
            SVG,
            default => <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a.75.75 0 0 1 .75.75v5.44l3.22 1.86a.75.75 0 1 1-.75 1.3l-3.59-2.08a.75.75 0 0 1-.37-.65V3.75A.75.75 0 0 1 10 3Z" /><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm0-1.5A6.5 6.5 0 1 0 10 3.5a6.5 6.5 0 0 0 0 13Z" clip-rule="evenodd" /></svg>
            SVG,
        };
    };
@endphp

<nav x-data="{ open: false }">
    <div class="border-b border-white/70 bg-white/80 backdrop-blur-xl lg:hidden">
        <div class="flex items-center justify-between gap-3 px-4 py-4 sm:px-6">
            <button @click="open = true" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-100 bg-white text-sky-600 shadow-sm transition hover:border-sky-200 hover:text-sky-700 focus:outline-none" aria-label="Open workspace menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 5.75A.75.75 0 0 1 3.75 5h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 5.75Zm0 4.25a.75.75 0 0 1 .75-.75h9.5a.75.75 0 0 1 0 1.5h-9.5A.75.75 0 0 1 3 10Zm0 4.25a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 14.25Z" clip-rule="evenodd" />
                </svg>
            </button>

            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-[#7957ff] via-[#b24ff2] to-[#6fd0ff] shadow-[0_18px_35px_-24px_rgba(59,130,246,0.8)]">
                    <x-application-logo class="block h-8 w-auto fill-current text-white" />
                </span>
                <span class="min-w-0">
                    <span class="font-display block truncate text-2xl font-semibold text-slate-900">TotaleLearning Hub</span>
                    <span class="block truncate text-sm text-slate-500">{{ $isAdminShell ? 'Admin workspace' : $activeShellLink['label'] }}</span>
                </span>
            </a>

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 shadow-sm transition hover:border-sky-200 hover:text-slate-900 focus:outline-none">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>

    <div x-show="open" x-cloak class="relative z-50 lg:hidden" style="display: none;">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="open = false"></div>

        <aside x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="-translate-x-full opacity-0"
            class="fixed inset-y-0 left-0 flex w-80 flex-col border-r border-white/70 bg-[linear-gradient(180deg,_rgba(242,251,255,0.98)_0%,_rgba(232,241,255,0.98)_58%,_rgba(238,235,255,0.98)_100%)] px-5 pb-5 pt-6 shadow-2xl">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-[#7957ff] via-[#b24ff2] to-[#6fd0ff] shadow-[0_18px_35px_-24px_rgba(59,130,246,0.8)]">
                        <x-application-logo class="block h-8 w-auto fill-current text-white" />
                    </span>
                    <span class="min-w-0">
                        <span class="font-display block truncate text-2xl font-semibold text-slate-900">TotaleLearning Hub</span>
                        <span class="block truncate text-sm text-slate-500">AdminUIUX workspace</span>
                    </span>
                </a>

                <button @click="open = false" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-sky-200 hover:text-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 1 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="mt-8 grid grid-cols-3 gap-3">
                @foreach ($shellLinks as $link)
                    <a href="{{ $link['route'] }}" class="group flex min-h-[6.25rem] flex-col items-center justify-center gap-2 rounded-[1.75rem] border px-3 py-4 text-center shadow-sm transition {{ $link['active'] ? 'border-sky-200 bg-[#1158f1] text-white shadow-[0_24px_40px_-28px_rgba(17,88,241,0.95)]' : 'border-white/80 bg-white/80 text-slate-500 hover:border-sky-100 hover:bg-white hover:text-slate-700' }}">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $link['active'] ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-sky-50 group-hover:text-sky-600' }}">
                            {!! $iconSvg($link['icon']) !!}
                        </span>
                        <span class="text-xs font-semibold leading-tight">{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="mt-auto rounded-[1.75rem] border border-white/80 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-sm font-semibold text-slate-700">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                        <div class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition hover:border-sky-200 hover:text-slate-900">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition hover:border-sky-200 hover:text-slate-900">Log Out</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>

    <aside class="fixed inset-y-4 left-4 z-40 hidden {{ $isAdminShell ? 'w-20' : 'w-24' }} flex-col overflow-hidden rounded-[2rem] border border-white/80 {{ $isAdminShell ? 'bg-white/96' : 'bg-[linear-gradient(180deg,_rgba(244,251,255,0.96)_0%,_rgba(227,240,255,0.9)_56%,_rgba(233,229,255,0.9)_100%)]' }} p-4 shadow-[0_30px_70px_-44px_rgba(15,23,42,0.55)] backdrop-blur lg:flex">
        <div class="flex flex-col items-center gap-5">
            <a href="{{ $isAdminShell ? route('app.admin.assignments') : route('dashboard') }}" class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-[1.6rem] bg-gradient-to-br from-[#7957ff] via-[#b24ff2] to-[#6fd0ff] shadow-[0_18px_35px_-24px_rgba(59,130,246,0.8)]">
                <x-application-logo class="block h-8 w-auto fill-current text-white" />
            </a>

            @unless($isAdminShell)
                <button type="button" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/80 bg-white/80 text-sky-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 5.75A.75.75 0 0 1 3.75 5h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 5.75Zm0 4.25a.75.75 0 0 1 .75-.75h9.5a.75.75 0 0 1 0 1.5h-9.5A.75.75 0 0 1 3 10Zm0 4.25a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 14.25Z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endunless
        </div>

        <div class="mt-6 flex-1 overflow-y-auto pr-1">
            <div class="flex flex-col items-center gap-3">
                @foreach ($shellLinks as $link)
                    <a href="{{ $link['route'] }}"
                        title="{{ $link['label'] }}"
                        class="group flex {{ $isAdminShell ? 'h-14 w-14 rounded-[1.35rem]' : 'h-16 w-16 rounded-[1.55rem]' }} items-center justify-center border text-center shadow-sm transition {{ $link['active'] ? 'border-sky-200 bg-[#1158f1] text-white shadow-[0_22px_40px_-28px_rgba(17,88,241,1)]' : 'border-white/90 bg-white/85 text-slate-400 hover:-translate-y-0.5 hover:border-sky-100 hover:bg-white hover:text-sky-600' }}">
                        {!! $iconSvg($link['icon']) !!}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex flex-col items-center gap-3">
            <a href="{{ route('profile.edit') }}" title="Profile" class="flex h-14 w-14 items-center justify-center rounded-[1.45rem] border border-white/90 bg-white/85 text-slate-500 shadow-sm transition hover:border-sky-100 hover:bg-white hover:text-sky-600">
                <span class="text-sm font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" title="Log Out" class="flex h-14 w-14 items-center justify-center rounded-[1.45rem] border border-white/90 bg-white/85 text-slate-400 shadow-sm transition hover:border-sky-100 hover:bg-white hover:text-sky-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3.25 4A1.75 1.75 0 0 1 5 2.25h5a.75.75 0 0 1 0 1.5H5A.25.25 0 0 0 4.75 4v12A.25.25 0 0 0 5 16.25h5a.75.75 0 0 1 0 1.5H5A1.75 1.75 0 0 1 3.25 16V4Zm8.22 2.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 1 1-1.06-1.06l1.97-1.97H8a.75.75 0 0 1 0-1.5h5.44l-1.97-1.97a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>
</nav>
