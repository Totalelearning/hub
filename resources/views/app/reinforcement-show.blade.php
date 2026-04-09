@extends('layouts.learninguiux')

@section('title', 'Reinforcement Check - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@section('content')
@include('app.partials.learner-header')

<div class="adminuiux-wrap">
    @include('app.partials.learner-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">
            <div class="mb-4 overflow-hidden rounded-[2rem] border border-white/70 bg-[linear-gradient(135deg,rgba(236,249,255,0.98),rgba(247,245,255,0.98))] shadow-[0_18px_48px_rgba(43,82,138,0.12)]">
                <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between lg:p-5">
                    <div class="rounded-[1.75rem] bg-white/70 p-4 backdrop-blur">
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">Ongoing reinforcement + proof</div>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-slate-900">{{ $touchpoint->module?->title ?? $touchpoint->title }}</h1>
                        <p class="mt-3 text-base text-slate-600">{{ $touchpoint->prompt }}</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-white/80 p-4 shadow-sm">
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Follow-up</div>
                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $touchpoint->interval_days }}-day knowledge check</div>
                        <div class="mt-2 text-sm text-slate-600">Due {{ $touchpoint->due_on?->format('d M Y') ?? 'n/a' }}</div>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-3xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900">
                    {{ session('status') }}
                </div>
            @endif

            @if ($touchpoint->status === 'needs_retry')
                <div class="mb-4 rounded-[1.75rem] border border-rose-200 bg-rose-50/85 p-5 shadow-[0_12px_36px_rgba(43,82,138,0.08)]">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">Retry needed</div>
                            <h2 class="mt-2 text-xl font-semibold text-slate-900">Some answers were incorrect, so extra learning has been assigned.</h2>
                            <p class="mt-2 text-sm text-slate-700">
                                {{ $incorrectCount }} answer{{ $incorrectCount === 1 ? '' : 's' }} missed the reviewed standard.
                                Complete the follow-up learning below, then return and try this reinforcement check again.
                            </p>
                            @if ($touchpoint->proof_summary)
                                <div class="mt-3 rounded-2xl border border-rose-200 bg-white/80 px-4 py-3 text-sm text-slate-700">
                                    {{ $touchpoint->proof_summary }}
                                </div>
                            @endif
                        </div>
                        <div class="rounded-[1.5rem] border border-rose-200 bg-white/90 px-4 py-3 text-sm text-slate-700">
                            <div class="text-xs uppercase tracking-[0.16em] text-slate-500">Current state</div>
                            <div class="mt-2 font-semibold text-slate-900">Needs retry</div>
                            <div class="mt-1">{{ $remediationModules->count() }} remediation module{{ $remediationModules->count() === 1 ? '' : 's' }} assigned</div>
                        </div>
                    </div>
                    @if ($remediationModules->isNotEmpty())
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach ($remediationModules as $remediationModule)
                                <div class="rounded-2xl border border-rose-200 bg-white/90 px-4 py-4">
                                    <div class="text-xs uppercase tracking-[0.16em] text-rose-700">Assigned follow-up</div>
                                    <div class="mt-2 font-semibold text-slate-900">{{ $remediationModule->title }}</div>
                                    <div class="mt-1 text-sm text-slate-600">{{ strtoupper((string) ($remediationModule->source_type ?: 'manual')) }} module</div>
                                    <div class="mt-3">
                                        <a href="{{ route('app.modules.show', ['module' => $remediationModule->id]) }}" class="rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-500">
                                            Open follow-up module
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1.1fr)_minmax(280px,0.9fr)]">
                <div class="space-y-4">
                    @if ($questionSet && $questionSet->questions->isNotEmpty())
                        <form method="POST" action="{{ route('app.reinforcement.submit', ['touchpoint' => $touchpoint->id]) }}" class="space-y-4">
                            @csrf
                            @foreach ($questionSet->questions as $index => $question)
                                <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-5 shadow-[0_12px_36px_rgba(43,82,138,0.08)]">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-500">Question {{ $index + 1 }}</div>
                                    <h2 class="mt-3 text-lg font-semibold text-slate-900">{{ $question->question_text }}</h2>
                                    <div class="mt-4 grid gap-3">
                                        @foreach (($question->options ?? []) as $optionKey => $optionText)
                                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm text-slate-700 hover:border-sky-200 hover:bg-sky-50/60">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $optionKey }}" class="mt-1 border-gray-300 text-indigo-600" @checked(old("answers.$question->id", $existingResponses->get($question->id)?->selected_answer) === $optionKey)>
                                                <span>
                                                    <span class="block font-semibold text-slate-900">{{ $optionKey }}</span>
                                                    <span class="mt-1 block">{{ $optionText }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error("answers.$question->id")
                                        <div class="mt-2 text-sm text-rose-700">{{ $message }}</div>
                                    @enderror
                                    @if ($question->explanation)
                                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm text-slate-600">
                                            <div class="font-semibold text-slate-900">Reviewer note</div>
                                            <div class="mt-1">{{ $question->explanation }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <a href="{{ route('app.reminders') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50">
                                    Back to reminders
                                </a>
                                <button type="submit" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
                                    Submit answers
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-6 text-sm text-slate-600 shadow-[0_12px_36px_rgba(43,82,138,0.08)]">
                            No approved reinforcement questions are linked to this touchpoint yet.
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-5 shadow-[0_12px_36px_rgba(43,82,138,0.08)]">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">What happens next</div>
                        <div class="mt-3 space-y-3 text-sm text-slate-700">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">Correct answers record reinforcement proof and keep your competence evidence current.</div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">Incorrect answers can assign extra learning automatically when a remediation module has been mapped by the admin reviewer.</div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">Your latest follow-up evidence also appears in admin reporting for audits and compliance reviews.</div>
                        </div>
                    </div>

                    @if ($questionSet)
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-5 shadow-[0_12px_36px_rgba(43,82,138,0.08)]">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Question set readiness</div>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                                    <div class="text-xs uppercase tracking-[0.16em] text-slate-500">Questions</div>
                                    <div class="mt-1 font-semibold text-slate-900">{{ $questionSet->questions->count() }}</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                                    <div class="text-xs uppercase tracking-[0.16em] text-slate-500">Without remediation</div>
                                    <div class="mt-1 font-semibold text-slate-900">{{ $questionsMissingRemediation }}</div>
                                </div>
                            </div>
                            <div class="mt-3 text-sm text-slate-600">
                                Questions without remediation can still record a failed reinforcement attempt, but they will not assign extra learning automatically.
                            </div>
                        </div>
                    @endif

                    @if ($touchpoint->proof_summary)
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-5 shadow-[0_12px_36px_rgba(43,82,138,0.08)]">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Latest proof note</div>
                            <div class="mt-3 text-sm text-slate-700">{{ $touchpoint->proof_summary }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
