@php
    $statusLabel = ucfirst(str_replace('_', ' ', $status));
    $isScormModule = ($module->source_type ?? null) === 'scorm';
    $nextStepHint = $status === 'completed'
        ? 'Completion is recorded. You can move on to the next learner action from this module page.'
        : ($status === 'in_progress'
            ? 'Progress is active. Continue this module to keep your learner evidence current.'
            : 'No learner progress is recorded yet. Start or launch this module to create activity.');
@endphp

<div class="card learner-module-panel mt-4">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <span class="learner-module-section-title d-inline-block mb-2">Live Progress Panel</span>
                <h3 class="mb-1">Your progress evidence</h3>
                <p class="text-secondary mb-0">This panel reflects the live learner progress record for this module.</p>
            </div>
            <div class="rounded-4 border bg-light px-4 py-3 align-self-start">
                <div class="small text-secondary">Current state</div>
                <div class="fw-semibold mt-1">{{ $statusLabel }}</div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-5">
                <div class="rounded-4 border bg-light p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small text-uppercase">{{ $statusLabel }}</span>
                        <span class="fw-semibold">{{ $percentComplete }}%</span>
                    </div>
                    <div class="progress mb-3" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentComplete }}" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ $percentComplete }}%"></div>
                    </div>
                    <div class="small text-secondary">{{ $nextStepHint }}</div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @if ($isScormModule)
                            <a href="{{ route('app.modules.scorm.launch', ['module' => $module->id]) }}" class="btn btn-theme btn-sm">
                                {{ $status === 'completed' ? 'Review SCORM' : ($status === 'in_progress' ? 'Continue SCORM' : 'Launch SCORM') }}
                            </a>
                        @elseif ($status !== 'completed')
                            <button type="button" wire:click="markCompleted" class="btn btn-outline-theme btn-sm">
                                Mark Completed
                            </button>
                        @endif
                        @if ($requiresAcknowledgement && ! $isAcknowledged)
                            <button type="button" wire:click="acknowledge" class="btn btn-outline-theme btn-sm">
                                Acknowledge Module
                            </button>
                        @endif
                        <a href="{{ route('app.reminders', ['module_id' => $module->id]) }}" class="btn btn-outline-theme btn-sm">
                            Module reminders
                        </a>
                        <a href="{{ route('app.feed') }}" class="btn btn-outline-theme btn-sm">
                            Dashboard
                        </a>
                        @if (app()->environment(['local', 'testing']))
                            <button type="button" wire:click="incrementForTesting" class="btn btn-theme btn-sm">
                                +10% (Test)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="small text-secondary">Started</div>
                            <div class="fw-semibold mt-1">{{ $startedAt ? \Illuminate\Support\Carbon::parse($startedAt)->format('M d, Y H:i') : '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="small text-secondary">Last activity</div>
                            <div class="fw-semibold mt-1">{{ $lastActivityAt ? \Illuminate\Support\Carbon::parse($lastActivityAt)->format('M d, Y H:i') : '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="small text-secondary">Completed</div>
                            <div class="fw-semibold mt-1">{{ $completedAt ? \Illuminate\Support\Carbon::parse($completedAt)->format('M d, Y H:i') : '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="small text-secondary">Acknowledgement</div>
                            <div class="fw-semibold mt-1">
                                @if (! $requiresAcknowledgement)
                                    Not required
                                @elseif ($isAcknowledged)
                                    {{ $acknowledgedAt ? \Illuminate\Support\Carbon::parse($acknowledgedAt)->format('M d, Y H:i') : 'Recorded' }}
                                @else
                                    Still required
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="small text-secondary">Last recorded position</div>
                            <div class="small fw-semibold mt-1 text-break">{{ $lastPosition ? json_encode($lastPosition) : 'No position data recorded yet.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
