@extends('layouts.learninguiux')

@section('title', ($pageTitle ?? 'Learning Path') . ' - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .path-form-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
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
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Learning Journey Builder</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">{{ __($pageTitle) }}</h1>
                        <p class="mt-3 text-base text-slate-600">Define ordered modules for a role-based learning path.</p>
                    </div>
                    <a href="{{ route('app.admin.paths.index') }}" class="admin-feed-action px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900">Back to Paths</a>
                </div>
            </div>

            <div class="py-2">
        <div class="w-full space-y-6">
            @if (session('status'))
                <div class="path-form-card border-green-200 bg-green-50/90 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ $formAction }}" class="path-form-card space-y-6 p-6">
                @csrf
                @if ($formMethod !== 'POST')
                    @method($formMethod)
                @endif

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $path->title) }}" class="w-full rounded border-gray-300 text-sm" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="4" class="w-full rounded border-gray-300 text-sm" required>{{ old('description', $path->description) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="w-full rounded border-gray-300 text-sm">
                            @foreach (['draft', 'published', 'archived'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $path->status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Target Roles</label>
                        <input type="text" name="target_roles" value="{{ old('target_roles', collect($path->target_roles)->join(', ')) }}" class="w-full rounded border-gray-300 text-sm" placeholder="Headteacher / Principal, Subject Lead">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Ordered Path Steps</label>
                        @php
                            $selectedModules = collect(old('module_ids', $path->steps->pluck('learning_module_id')->all()))->map(fn ($id) => (int) $id)->all();
                            $existingStepDelays = $path->steps->pluck('delay_days', 'learning_module_id')->map(fn ($value) => (int) $value)->all();
                            $submittedStepDelays = collect(old('step_delays', []))
                                ->mapWithKeys(fn ($value, $key) => [(int) $key => max(0, (int) $value)])
                                ->all();
                        @endphp
                        <div class="grid gap-2 rounded-3xl border border-slate-200 bg-slate-50/80 p-4">
                            @forelse ($availableModules as $module)
                                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white/95 px-3 py-2 text-sm text-slate-700 shadow-sm">
                                    <input type="checkbox" name="module_ids[]" value="{{ $module->id }}" class="mt-1 rounded border-gray-300 text-indigo-600" @checked(in_array($module->id, $selectedModules, true))>
                                    <span>
                                        <span class="block font-medium text-slate-900">{{ $module->title }}</span>
                                        <span class="text-xs uppercase tracking-wide text-slate-400">{{ $module->status }}</span>
                                        <span class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                                            <span>Delay after previous step (days)</span>
                                            <input
                                                type="number"
                                                name="step_delays[{{ $module->id }}]"
                                                min="0"
                                                max="3650"
                                                value="{{ $submittedStepDelays[$module->id] ?? $existingStepDelays[$module->id] ?? 0 }}"
                                                class="w-24 rounded border-gray-300 text-xs"
                                            >
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <div class="text-sm text-gray-500">No modules available.</div>
                            @endforelse
                        </div>
                        <div class="mt-1 text-xs text-gray-400">Selected modules are saved in the order they are checked in the form submission.</div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">{{ $submitLabel }}</button>
                </div>
            </form>
        </div>
    </div>
        </div>
    </main>
</div>
@endsection
