@extends('layouts.learninguiux')

@section('title', 'Feed Scoring Weights - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .settings-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .settings-band {
            border-radius: 1.5rem;
            background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
        }
    </style>
@endpush

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
            <div class="mb-4 admin-feed-hero">
                <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between lg:p-5">
                    <div class="admin-feed-hero-copy">
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Personalization Tuning</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">Feed Scoring Weights</h1>
                        <p class="mt-3 text-base text-slate-600">Tune deterministic personalization weights without code changes.</p>
                    </div>
                    <a href="{{ route('app.admin.assignments') }}" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Back to Admin Assignments</a>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6">
            @if (session('status'))
                <div class="settings-card border-green-200 bg-green-50/90 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="settings-card border-indigo-200 bg-indigo-50/90 px-4 py-3 text-sm text-indigo-800">
                <strong>Current Profile:</strong>
                {{ $currentPreset['label'] ?? 'Custom' }}
            </div>

            <section class="grid gap-4 md:grid-cols-3">
                <div class="settings-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4"><i class="bi bi-sliders fs-3"></i></div>
                        <div class="fs-4 fw-semibold text-slate-900">{{ $currentPreset['label'] ?? 'Custom' }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Current Profile</div>
                        <div class="mt-auto pt-3 small text-secondary">Ranking mode</div>
                    </div>
                </div>
                <div class="settings-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#0369a1;background:linear-gradient(135deg, rgba(224, 242, 254, 0.96), rgba(219, 234, 254, 0.96));"><i class="bi bi-collection fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ count($presets) }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Preset Profiles</div>
                        <div class="mt-auto pt-3 small text-secondary">Quick shifts</div>
                    </div>
                </div>
                <div class="settings-card admin-feed-kpi p-5">
                    <div class="d-flex h-100 flex-column text-center">
                        <div class="admin-feed-kpi-icon mx-auto mb-4" style="color:#4f46e5;background:linear-gradient(135deg, rgba(224, 231, 255, 0.96), rgba(238, 242, 255, 0.96));"><i class="bi bi-calculator fs-3"></i></div>
                        <div class="fs-3 fw-semibold text-slate-900">{{ collect($groups)->sum(fn ($group) => count($group['rows'])) }}</div>
                        <div class="mt-2 text-base fw-semibold text-slate-900">Manual Weights</div>
                        <div class="mt-auto pt-3 small text-secondary">Deterministic inputs</div>
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('app.admin.scoring.preset') }}" class="settings-card overflow-hidden">
                @csrf
                <div class="settings-band border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Preset Profiles</h3>
                    <p class="mt-1 text-sm text-slate-600">Apply a ranking profile to quickly shift how the learner feed prioritises modules.</p>
                </div>
                <div class="flex flex-col gap-3 p-6 md:flex-row md:items-end md:justify-between">
                    <div class="flex-1">
                        <label for="preset" class="text-sm font-medium text-gray-700">Apply Preset</label>
                        <select id="preset" name="preset" class="mt-1 w-full rounded border-gray-300 text-sm">
                            @foreach ($presets as $preset)
                                <option value="{{ $preset['key'] }}">{{ $preset['label'] }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">One-click tuning profile for deterministic ranking behavior.</p>
                        @error('preset')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 hover:bg-white hover:text-slate-900">
                        Apply Preset
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('app.admin.scoring.update') }}" id="feed-scoring-update-form" class="settings-card overflow-hidden">
                @csrf
                <div class="settings-band border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Manual Weights</h3>
                    <p class="mt-1 text-sm text-slate-600">Fine-tune deterministic scoring inputs for urgency, preferences, role fit, and learning signals.</p>
                </div>

                <div class="space-y-4 p-6">
                    @foreach ($groups as $group)
                        <div class="rounded-[1.35rem] border border-gray-200 bg-slate-50/70 p-4">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $group['name'] }}</h3>
                            <div class="mt-3 space-y-4">
                                @foreach ($group['rows'] as $row)
                                    <div class="grid gap-2 md:grid-cols-3 md:items-center">
                                        <label for="weights_{{ $row['key'] }}" class="text-sm font-medium text-gray-700">
                                            {{ $row['label'] }}
                                        </label>
                                        <input
                                            id="weights_{{ $row['key'] }}"
                                            type="number"
                                            min="0"
                                            max="500"
                                            name="weights[{{ $row['key'] }}]"
                                            value="{{ old("weights.{$row['key']}", $row['value']) }}"
                                            class="rounded border-gray-300 text-sm"
                                            required
                                        >
                                        <div class="text-xs text-gray-500">
                                            Default: {{ $row['default'] }}
                                            @if (!empty($row['help']))
                                                <div>{{ $row['help'] }}</div>
                                            @endif
                                        </div>
                                        @error("weights.{$row['key']}")
                                            <div class="md:col-span-3 text-sm text-red-600">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="submit" form="feed-scoring-reset-form" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 hover:bg-white hover:text-slate-900">
                        Reset to Defaults
                    </button>
                    <button type="submit" class="admin-feed-action bg-sky-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-white hover:bg-sky-700">
                        Save Weights
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('app.admin.scoring.reset') }}" id="feed-scoring-reset-form" class="hidden">
                @csrf
            </form>
        </div>
    </div>
        </div>
    </main>
</div>
@endsection
