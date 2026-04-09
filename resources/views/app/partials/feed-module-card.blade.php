@php
    $isPrimaryDemoCourse = \App\Support\ScormDemoScenario::isPrimaryDemoCourse($module);
    $cardContext = $cardContext ?? 'dashboard';
    $state = $module->user_progress_status ?? 'not_started';
    $progressPercent = (int) ($module->user_progress_percent ?? 0);
    $assignment = $module->assignment ?? [];
    $renewal = $module->renewal ?? [];
    $isDue = (bool) ($assignment['is_overdue'] ?? false);
    $isDueSoon = (bool) ($assignment['is_due_soon'] ?? false);
    $isCompleted = $state === 'completed' && ! $isDue && ! $isDueSoon;
    $isSaved = in_array((int) $module->id, $savedModuleIds ?? [], true);

    $stateLabel = $isDue
        ? 'Refresh Due'
        : ($isDueSoon
            ? 'Refresh Soon'
            : ($state === 'completed'
                ? 'Completed'
                : ($state === 'in_progress' ? 'In Progress' : 'New')));
    $stateBadgeClass = $isDue
        ? 'text-bg-danger'
        : ($isDueSoon
            ? 'text-bg-warning'
            : ($state === 'completed'
                ? 'text-bg-success'
                : ($state === 'in_progress' ? 'text-bg-warning' : 'text-bg-primary')));

    $ctaLabel = match ($cardContext) {
        'required' => $isDue || $isDueSoon ? 'Review now' : ($state === 'in_progress' ? 'Resume' : 'Start'),
        'recommended' => $state === 'in_progress' ? 'Resume' : ($state === 'completed' ? 'Review' : 'Explore'),
        'saved' => $state === 'in_progress' ? 'Resume' : ($state === 'completed' ? 'Review' : 'Open'),
        default => ($state === 'completed' || $isDue || $isDueSoon) ? 'Review' : ($state === 'in_progress' ? 'Continue' : 'Start'),
    };

    // Cycle through available course images
    $courseImages = ['1.jpg', '2.jpg', '3.jpg', '4.jpg'];
    $courseImage = $courseImages[($module->id ?? 0) % count($courseImages)];

    $topicLabel = $module->topic ? ucfirst(str_replace('_', ' ', $module->topic)) : ($module->compliance_area ?: 'General');
@endphp

<div class="col-12 col-md-6 col-xl-3 mb-4">
    <div class="card border-0 h-100 overflow-hidden {{ $isCompleted ? 'opacity-75' : '' }}" style="border-radius:16px; box-shadow: 0 8px 30px rgba(43, 82, 138, 0.10); background: #fff;">

        {{-- Title & topic --}}
        <div class="px-3 pt-3 pb-2">
            <a href="{{ route('app.modules.show', ['module' => $module->id]) }}" class="text-decoration-none">
                <h6 class="fw-bold text-primary mb-1" style="min-height:2.6em;">{{ \Illuminate\Support\Str::limit($module->title, 42) }}</h6>
            </a>
            <p class="small text-secondary mb-0">{{ $topicLabel }}</p>
        </div>

        {{-- Course image --}}
        <div class="px-3">
            <a href="{{ route('app.modules.show', ['module' => $module->id]) }}">
                <figure class="coverimg rounded-3 mb-0" style="height:160px;">
                    <img src="{{ asset('vendor/learninguiux/img/learning/' . $courseImage) }}" alt="{{ $module->title }}">
                </figure>
            </a>
        </div>

        {{-- Status & progress --}}
        <div class="card-body px-3 pb-3 pt-2 d-flex flex-column">
            {{-- Badges --}}
            <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="badge rounded-pill {{ $stateBadgeClass }}">
                    @if ($state === 'completed') <i class="bi bi-check-circle me-1"></i> @endif
                    {{ $stateLabel }}
                </span>
                @if ($assignment['is_required'] ?? $module->is_required)
                    <span class="badge rounded-pill text-bg-danger">Required</span>
                @endif
                @if ($module->source_type === 'scorm')
                    <span class="badge rounded-pill text-bg-light border">SCORM</span>
                @endif
            </div>

            {{-- Progress bar (in progress only) --}}
            @if ($state === 'in_progress')
                <div class="mb-2">
                    <div class="d-flex justify-content-between small text-secondary mb-1">
                        <span>Progress</span>
                        <span>{{ $progressPercent }}%</span>
                    </div>
                    <div class="progress" role="progressbar" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100" style="height:5px;">
                        <div class="progress-bar bg-primary" style="width:{{ $progressPercent }}%"></div>
                    </div>
                </div>
            @endif

            {{-- Overdue/due-soon alert --}}
            @if ($isDue || $isDueSoon)
                <div class="small {{ $isDue ? 'text-danger' : 'text-warning' }} mb-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    @if (!empty($renewal['due_at']))
                        Due {{ $renewal['due_at']->format('M d, Y') }}
                    @else
                        {{ $isDue ? 'Overdue' : 'Due soon' }}
                    @endif
                </div>
            @endif

            {{-- Bottom action row --}}
            <div class="d-flex align-items-center justify-content-between mt-auto pt-2">
                <div class="d-flex gap-2">
                    @if ($isSaved)
                        <form action="{{ route('app.feed.unsave', ['module' => $module->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Unsave">
                                <i class="bi bi-heart-fill fs-5"></i>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('app.feed.save', ['module' => $module->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link text-secondary p-0" title="Save">
                                <i class="bi bi-heart fs-5"></i>
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('app.modules.show', ['module' => $module->id]) }}#details" class="btn btn-sm btn-link text-secondary p-0" title="Details">
                        <i class="bi bi-info-circle fs-5"></i>
                    </a>
                </div>
                <a href="{{ route('app.modules.show', ['module' => $module->id]) }}" class="btn btn-sm {{ $isDue ? 'btn-danger' : 'btn-primary' }} rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;" title="{{ $ctaLabel }}">
                    <i class="bi {{ $state === 'in_progress' ? 'bi-play-fill' : ($state === 'completed' ? 'bi-arrow-repeat' : 'bi-arrow-right') }}"></i>
                </a>
            </div>
        </div>
    </div>
</div>
