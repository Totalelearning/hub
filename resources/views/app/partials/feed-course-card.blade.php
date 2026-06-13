@php
    $courseImages = ['1.jpg', '2.jpg', '3.jpg', '4.jpg'];
    $courseImage = $courseImages[($course->id ?? 0) % count($courseImages)];
    $topicLabel = $course->topic ? ucfirst(str_replace('_', ' ', $course->topic)) : 'General';
    $moduleCount = $course->modules_count ?? $course->modules->count();
    $durationLabel = $course->estimated_minutes ? $course->estimated_minutes . ' min' : null;
    $progressPercent = $course->course_progress_percent ?? 0;
    $completedModules = $course->course_completed_modules ?? 0;
    $enrolmentStatus = $course->enrolment_status ?? null;
    $isCompleted = $enrolmentStatus === 'completed' || $progressPercent === 100;
@endphp

<div class="col-12 col-md-6 col-xl-3 mb-4">
    <div class="card border-0 h-100 overflow-hidden{{ $isCompleted ? ' opacity-75' : '' }}" style="border-radius:16px; box-shadow: 0 8px 30px rgba(43, 82, 138, 0.10); background: #fff;">

        {{-- Title & topic --}}
        <div class="px-3 pt-3 pb-2">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <a href="{{ route('app.courses.show', $course) }}" class="text-decoration-none">
                    <h6 class="fw-bold text-primary mb-1" style="min-height:2.6em;">{{ \Illuminate\Support\Str::limit($course->title, 42) }}</h6>
                </a>
                @if ($isCompleted)
                    <span class="badge bg-success-subtle text-success flex-shrink-0">Completed</span>
                @endif
            </div>
            <p class="small text-secondary mb-0">{{ $topicLabel }}</p>
        </div>

        {{-- Course image --}}
        <div class="px-3">
            <figure class="coverimg rounded-3 mb-0" style="height:160px;">
                <img src="{{ asset('vendor/learninguiux/img/learning/' . $courseImage) }}" alt="{{ $course->title }}">
            </figure>
        </div>

        {{-- Info & action --}}
        <div class="card-body px-3 pb-3 pt-2 d-flex flex-column">
            <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="badge rounded-pill text-bg-primary">{{ $moduleCount }} module{{ $moduleCount === 1 ? '' : 's' }}</span>
                @if ($durationLabel)
                    <span class="badge rounded-pill text-bg-light border">{{ $durationLabel }}</span>
                @endif
            </div>

            @if ($progressPercent > 0)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="progress flex-grow-1" style="height:5px;">
                        <div class="progress-bar {{ $progressPercent === 100 ? 'bg-success' : '' }}" style="width:{{ $progressPercent }}%"></div>
                    </div>
                    <span class="small text-secondary" style="white-space:nowrap;">{{ $completedModules }}/{{ $moduleCount }}</span>
                </div>
            @endif

            @if ($course->description)
                <p class="small text-secondary mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $course->description }}</p>
            @endif

            <div class="d-flex align-items-center justify-content-between mt-auto pt-2">
                <div class="small text-secondary">
                    @foreach ($course->modules->take(2) as $m)
                        <span class="badge bg-light border text-dark">{{ \Illuminate\Support\Str::limit($m->title, 20) }}</span>
                    @endforeach
                    @if ($moduleCount > 2)
                        <span class="small text-secondary">+{{ $moduleCount - 2 }}</span>
                    @endif
                </div>
                <a href="{{ route('app.courses.show', $course) }}" class="btn btn-sm btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;" title="Start">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
