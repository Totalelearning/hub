@extends('layouts.learninguiux')

@section('title', 'Courses - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
    <style>
        .modules-index-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }

        .modules-index-band {
            border-radius: 1.5rem;
            background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
        }

        .modules-index-action-card {
            border-radius: 1.4rem;
            border: 1px solid rgba(191, 219, 254, 0.9);
            background: rgba(255, 255, 255, 0.92);
        }

        .modules-summary-card {
            padding: 1rem 1.1rem;
        }

        .modules-summary-label {
            font-size: 0.82rem;
            font-weight: 600;
        }

        .modules-toolbar-card {
            border-radius: 0.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(248, 250, 252, 0.94);
            padding: 1rem;
        }

        .modules-directory-table thead th {
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            color: rgb(37 99 235);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: none;
            white-space: nowrap;
        }

        .modules-directory-table tbody tr {
            transition: background-color 150ms ease;
        }

        .modules-directory-table tbody tr:hover {
            background: rgba(248, 250, 252, 0.88);
        }

        .modules-directory-table tbody td {
            padding-top: 1.15rem;
            padding-bottom: 1.15rem;
            vertical-align: middle;
        }

        .modules-directory-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.4rem 0.8rem;
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1;
        }

        .modules-directory-panel {
            border-radius: 0.75rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(248, 250, 252, 0.9);
            padding: 0.8rem 0.95rem;
        }

        .modules-directory-menu {
            border-radius: 0.5rem;
            color: #2563eb;
        }
    </style>
@endpush

@section('content')
@include('app.partials.admin-header')

<div class="adminuiux-wrap">
    @include('app.partials.admin-sidebar')

    <main class="adminuiux-content has-sidebar" onclick="contentClick()">
        <div class="container mt-4" id="main-content">

            {{-- Hero --}}
            <div class="mb-4 admin-feed-hero">
                <div class="row align-items-center g-0 p-4 p-lg-5">
                    <div class="col-12 col-lg-8 admin-feed-hero-copy mb-3 mb-lg-0">
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Course Publishing</div>
                        <h1 class="fs-3 fw-semibold mb-2">{{ __('Courses') }}</h1>
                        <p class="text-secondary mb-0">Manage courses and their modules, publication state, targeting, learner visibility, and SCORM readiness.</p>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('app.admin.courses.create') }}" class="btn btn-outline-theme">Create Course</a>
                        <a href="{{ route('app.admin.modules.create') }}" class="btn btn-theme">Create Module</a>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- 4 KPI cards --}}
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3">
                                <i class="bi bi-journal-richtext fs-3"></i>
                            </div>
                            <div class="fs-2 fw-semibold">{{ $moduleOverviewSummary['total'] }}</div>
                            <div class="fw-medium mb-1">Modules</div>
                            <p class="small text-secondary mb-0">Catalogue items currently tracked</p>
                            <div class="mt-auto pt-3">
                                <span class="badge bg-primary-subtle text-primary">Inventory</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3" style="color:#0f766e;background:linear-gradient(135deg,rgba(213,250,229,.96),rgba(220,252,231,.96));">
                                <i class="bi bi-check2-circle fs-3"></i>
                            </div>
                            <div class="fs-2 fw-semibold">{{ $moduleOverviewSummary['publish_ready'] }}</div>
                            <div class="fw-medium mb-1">Publish Ready</div>
                            <p class="small text-secondary mb-0">Modules ready to go live safely</p>
                            <div class="mt-auto pt-3">
                                <span class="badge bg-success-subtle text-success">Readiness</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3" style="color:#b45309;background:linear-gradient(135deg,rgba(254,243,199,.98),rgba(255,237,213,.98));">
                                <i class="bi bi-exclamation-octagon fs-3"></i>
                            </div>
                            <div class="fs-2 fw-semibold">{{ $moduleOverviewSummary['publish_blocked'] }}</div>
                            <div class="fw-medium mb-1">Publish Blocked</div>
                            <p class="small text-secondary mb-0">Modules needing review before launch</p>
                            <div class="mt-auto pt-3">
                                <span class="badge bg-warning-subtle text-warning">Action</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="admin-feed-kpi h-100">
                        <div class="d-flex h-100 flex-column text-center p-4">
                            <div class="admin-feed-kpi-icon mx-auto mb-3" style="color:#be123c;background:linear-gradient(135deg,rgba(255,228,230,.98),rgba(254,226,226,.98));">
                                <i class="bi bi-broadcast-pin fs-3"></i>
                            </div>
                            <div class="fs-2 fw-semibold">{{ $moduleOverviewSummary['live_without_audience'] }}</div>
                            <div class="fw-medium mb-1">Live, No Audience</div>
                            <p class="small text-secondary mb-0">Published with no learners matched</p>
                            <div class="mt-auto pt-3">
                                <span class="badge bg-danger-subtle text-danger">Visibility</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Topics --}}
            <div class="card adminuiux-card shadow-sm mb-4" x-data="topicDeleter()">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold mb-0">Topics</h6>
                        <span class="badge bg-primary-subtle text-primary">{{ $topics->count() }} total</span>
                    </div>
                    @if (session('topic_error'))
                        <div class="alert alert-warning alert-sm py-2 small mb-3">{{ session('topic_error') }}</div>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach ($topics as $topic)
                            <span class="badge rounded-pill bg-light border text-dark d-inline-flex align-items-center gap-1 py-2 px-3" id="topic-badge-{{ $topic->id }}">
                                {{ ucfirst($topic->name) }}
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-1" style="font-size:.7rem;line-height:1;" title="Remove"
                                    @click="confirmDelete({{ $topic->id }}, '{{ addslashes($topic->name) }}', '{{ route('app.admin.topics.check', $topic) }}', '{{ route('app.admin.topics.destroy', $topic) }}')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </span>
                        @endforeach
                    </div>
                    <form action="{{ route('app.admin.topics.store') }}" method="POST" class="d-flex gap-2" style="max-width:400px;">
                        @csrf
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="New topic name..." required>
                        <button type="submit" class="btn btn-theme btn-sm text-nowrap">Add Topic</button>
                    </form>
                </div>

                {{-- Confirmation modal --}}
                <div class="modal fade" id="topicDeleteModal" tabindex="-1" aria-hidden="true" x-ref="modal">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h6 class="modal-title fw-semibold">Delete Topic</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <template x-if="checking">
                                    <div class="text-center py-2 text-secondary">
                                        <div class="spinner-border spinner-border-sm me-1" role="status"></div> Checking&hellip;
                                    </div>
                                </template>
                                <template x-if="!checking && courseCount > 0">
                                    <div>
                                        <div class="alert alert-warning py-2 small mb-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            <strong x-text="'Warning:'"></strong>
                                            This topic is assigned to <strong x-text="courseCount"></strong> <span x-text="courseCount === 1 ? 'course' : 'courses'"></span>. Those courses will keep their topic value but it will no longer appear in the topic list.
                                        </div>
                                        <p class="small text-secondary mb-0">Are you sure you want to delete <strong x-text="topicName"></strong>?</p>
                                    </div>
                                </template>
                                <template x-if="!checking && courseCount === 0">
                                    <p class="small text-secondary mb-0">Delete topic <strong x-text="topicName"></strong>? This cannot be undone.</p>
                                </template>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-sm btn-danger" @click="doDelete()" :disabled="checking || deleting">
                                    <template x-if="deleting"><span class="spinner-border spinner-border-sm" role="status"></span></template>
                                    <template x-if="!deleting">Delete</template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden form for module bulk status --}}
            <form id="bulk-module-status-form" method="POST" action="{{ route('app.admin.modules.bulk-transition') }}" style="display:none;">
                @csrf
            </form>

            {{-- Tabs: Courses / Modules --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs px-4 pt-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-semibold" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses-pane" type="button" role="tab">Courses <span class="badge bg-primary-subtle text-primary ms-1">{{ $courses->count() }}</span></button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" id="modules-tab" data-bs-toggle="tab" data-bs-target="#modules-pane" type="button" role="tab">Modules <span class="badge bg-primary-subtle text-primary ms-1">{{ $modules->count() }}</span></button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- Courses pane --}}
                        <div class="tab-pane fade show active" id="courses-pane" role="tabpanel">
                            <div class="px-4 py-3 border-bottom d-flex flex-wrap align-items-center gap-3">
                                <form id="bulk-course-status-form" method="POST" action="{{ route('app.admin.courses.bulk-transition') }}" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm" style="width:auto;">
                                        <option value="draft">Set Draft</option>
                                        <option value="published">Set Published</option>
                                        <option value="archived">Set Archived</option>
                                    </select>
                                    <button type="submit" class="btn btn-theme btn-sm text-nowrap">Apply To Selected</button>
                                </form>
                            </div>
                            <div class="table-responsive">
                                <table class="modules-directory-table table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-3">Select</th>
                                            <th class="px-4 py-3">Title</th>
                                            <th class="px-4 py-3">Modules</th>
                                            <th class="px-4 py-3">Target Roles</th>
                                            <th class="px-4 py-3">Assigned Users</th>
                                            <th class="px-4 py-3">Duration</th>
                                            <th class="px-4 py-3">Questions</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3">Owner</th>
                                            <th class="px-4 py-3">Updated</th>
                                            <th class="px-4 py-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($courses as $course)
                                            @php
                                                $roles = collect($course->target_roles)->filter(fn($r) => $r !== 'all')->values();
                                                $hasAll = collect($course->target_roles)->contains('all');
                                                $statusClass = match($course->status) {
                                                    'published' => 'bg-success-subtle text-success',
                                                    'archived' => 'bg-secondary-subtle text-secondary',
                                                    default => 'bg-warning-subtle text-warning',
                                                };
                                            @endphp
                                            <tr>
                                                <td class="px-4">
                                                    <input type="checkbox" name="course_ids[]" value="{{ $course->id }}" form="bulk-course-status-form" class="form-check-input">
                                                </td>
                                                <td class="px-4">
                                                    <a href="{{ route('app.admin.courses.edit', $course) }}" class="fw-semibold text-decoration-none">{{ $course->title }}</a>
                                                    @if ($course->topic)
                                                        <div class="small text-secondary">{{ ucfirst($course->topic) }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4">
                                                    <span class="badge bg-primary-subtle text-primary">{{ $course->modules_count }}</span>
                                                </td>
                                                <td class="px-4">
                                                    <div style="max-width:14rem;">
                                                        @if ($hasAll)
                                                            <span class="badge bg-info-subtle text-info">All roles</span>
                                                        @elseif ($roles->isNotEmpty())
                                                            @foreach ($roles->take(3) as $role)
                                                                <span class="badge bg-light border text-dark">{{ $role }}</span>
                                                            @endforeach
                                                            @if ($roles->count() > 3)
                                                                <span class="small text-secondary">+{{ $roles->count() - 3 }} more</span>
                                                            @endif
                                                        @else
                                                            <span class="text-secondary">&mdash;</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4">
                                                    <span class="fw-medium">{{ $course->assigned_users_count }}</span>
                                                </td>
                                                <td class="px-4">
                                                    @if ($course->estimated_minutes)
                                                        {{ $course->estimated_minutes }} min
                                                    @else
                                                        <span class="text-secondary">&mdash;</span>
                                                    @endif
                                                </td>
                                                <td class="px-4">
                                                    @php
                                                        $qApproved = $course->question_readiness_approved ?? 0;
                                                        $qTotal = $course->question_readiness_total ?? 0;
                                                        $qReady = $qTotal > 0 && $qApproved === $qTotal;
                                                    @endphp
                                                    @if ($qTotal === 0)
                                                        <span class="text-secondary">&mdash;</span>
                                                    @else
                                                        <span class="badge {{ $qReady ? 'bg-success-subtle text-success' : ($qApproved > 0 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }}">{{ $qApproved }}/{{ $qTotal }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4">
                                                    <span class="badge {{ $statusClass }}">{{ ucfirst($course->status) }}</span>
                                                </td>
                                                <td class="px-4 small text-secondary">{{ $course->owner?->name ?? '—' }}</td>
                                                <td class="px-4 small text-secondary">{{ $course->updated_at->format('M d, Y') }}</td>
                                                <td class="px-4">
                                                    <div class="dropdown d-inline-block">
                                                        <a class="btn btn-sm btn-link btn-square no-caret modules-directory-menu" data-bs-toggle="dropdown"><i class="bi bi-three-dots fs-5"></i></a>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="{{ route('app.admin.courses.edit', $course) }}">Edit course</a></li>
                                                            <li><a class="dropdown-item" href="{{ route('app.admin.scores.course', $course) }}">View scores</a></li>
                                                            <li>
                                                                <form action="{{ route('app.admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Delete this course?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">Delete course</button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center py-5 text-secondary">
                                                    <i class="bi bi-collection fs-1 d-block mb-2"></i>
                                                    No courses yet. <a href="{{ route('app.admin.courses.create') }}">Create your first course</a>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Modules pane --}}
                        <div class="tab-pane fade" id="modules-pane" role="tabpanel">
                            <div class="px-4 py-3 border-bottom d-flex flex-wrap align-items-center gap-3">
                                <select name="status" form="bulk-module-status-form" class="form-select form-select-sm" style="width:auto;">
                                    <option value="draft">Set Draft</option>
                                    <option value="published">Set Published</option>
                                    <option value="archived">Set Archived</option>
                                </select>
                                <button type="submit" form="bulk-module-status-form" class="btn btn-theme btn-sm text-nowrap">Apply To Selected</button>
                            </div>
                            <div class="table-responsive">
                                <table class="modules-directory-table table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-3">Select</th>
                                            <th class="px-4 py-3">Name</th>
                                            <th class="px-4 py-3">Course Detail</th>
                                            <th class="px-4 py-3">Publishing</th>
                                            <th class="px-4 py-3">Audience</th>
                                            <th class="px-4 py-3">Package</th>
                                            <th class="px-4 py-3">Reinforcement</th>
                                            <th class="px-4 py-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($modules as $module)
                                            @php
                                                $scorm = $scormSummaries[$module->id] ?? null;
                                                $moduleState = $moduleOperationalStates[$module->id] ?? ['readiness' => ['can_publish' => false, 'blockers' => []], 'visibility' => ['timing_label' => 'not live', 'audience_label' => 'all roles', 'window_label' => 'always on'], 'impact' => ['headline' => 'Learner visibility pending', 'counts' => ['visible_now' => 0], 'signals' => []]];
                                                $moduleReadyToPublish = $moduleState['readiness']['can_publish'];
                                                $targetRoles = collect($module->target_roles)->filter()->values();
                                                $reinforcementDays = collect($module->reinforcement_intervals_days ?? [7, 30])->filter()->values();
                                                $courseDetailLabel = $module->compliance_area ?: ($module->topic ?: 'General');
                                                $courseDetailMeta = collect([
                                                    ucfirst($module->difficulty),
                                                    $module->is_required ? 'Required' : 'Optional',
                                                    $module->source_type === 'scorm' ? 'SCORM' : 'Manual',
                                                ])->join(' | ');
                                                $publishingMeta = str_replace('_', ' ', $module->review_status ?: 'draft');
                                                $audienceMeta = $targetRoles->join(', ') ?: 'all learners';
                                                $packageLabel = $scorm ? $scorm['package_status'] : 'n/a';
                                                $packageMeta = $scorm
                                                    ? ($scorm['launch_path'] ?: 'launch pending')
                                                    : 'no package';
                                                $reinforcementLabel = $reinforcementDays->join(', ') . ' day' . ($reinforcementDays->count() === 1 ? '' : 's');
                                            @endphp
                                            <tr>
                                                <td class="px-4">
                                                    <input type="checkbox" name="module_ids[]" value="{{ $module->id }}" form="bulk-module-status-form" class="form-check-input">
                                                </td>
                                                <td class="px-4">
                                                    <div class="fw-semibold" style="max-width:18rem;">{{ $module->title }}</div>
                                                    <div class="small text-secondary">{{ $courseDetailMeta }}</div>
                                                </td>
                                                <td class="px-4">
                                                    <div class="fw-medium" style="min-width:10rem;">{{ $courseDetailLabel }}</div>
                                                </td>
                                                <td class="px-4">
                                                    <div style="min-width:11rem;">
                                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                                            <span class="badge {{ $moduleReadyToPublish ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                                                {{ $moduleReadyToPublish ? 'publish ready' : 'publish blocked' }}
                                                            </span>
                                                            @if ($moduleReadyToPublish && (($moduleState['impact']['counts']['visible_now'] ?? 0) === 0))
                                                                <span class="badge bg-danger-subtle text-danger">no live audience</span>
                                                            @endif
                                                        </div>
                                                        <div class="small text-secondary">{{ $module->status }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-4">
                                                    <div style="min-width:10rem;">
                                                        <div class="fw-medium">{{ $moduleState['impact']['counts']['visible_now'] }} visible now</div>
                                                        @if (! empty($moduleState['impact']['primary_blocker']))
                                                            <div class="small text-danger">{{ str($moduleState['impact']['primary_blocker']['label'])->after('Blocked by ') }}</div>
                                                        @else
                                                            <div class="small text-secondary">{{ $moduleState['visibility']['timing_label'] }}</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4">
                                                    <div style="min-width:9rem;">
                                                        <div class="fw-medium">{{ $packageLabel }}</div>
                                                        <div class="small text-secondary">{{ $packageMeta }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-4">
                                                    <div style="min-width:8rem;">
                                                        <div class="fw-medium">{{ $reinforcementLabel }}</div>
                                                        @if ($module->requires_acknowledgement)
                                                            <span class="badge bg-info-subtle text-info small">ack required</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4">
                                                    <div class="dropdown d-inline-block">
                                                        <a class="btn btn-sm btn-link btn-square no-caret modules-directory-menu" data-bs-toggle="dropdown"><i class="bi bi-three-dots fs-5"></i></a>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="{{ route('app.admin.modules.edit', ['module' => $module->id]) }}">Edit module</a></li>
                                                            <li><a class="dropdown-item" href="{{ route('app.admin.modules.edit', ['module' => $module->id]) }}#scorm-package">Upload SCORM package</a></li>
                                                            @if (! $moduleReadyToPublish && ! empty($moduleState['readiness']['blockers']))
                                                                <li><a class="dropdown-item" href="{{ route('app.admin.modules.edit', ['module' => $module->id]) }}{{ $moduleState['readiness']['preferred_fix_href'] ?? '#field-status' }}">Fix publishing</a></li>
                                                            @endif
                                                            @if (($moduleState['impact']['counts']['visible_now'] ?? 0) === 0 || ! empty($moduleState['impact']['primary_blocker']))
                                                                <li><a class="dropdown-item" href="{{ route('app.admin.modules.edit', ['module' => $module->id]) }}{{ $moduleState['impact']['preferred_fix_href'] ?? '#field-target-roles' }}">Fix visibility</a></li>
                                                            @endif
                                                            @if ($scorm && $scorm['has_package'])
                                                                <li><a class="dropdown-item" href="{{ route('app.modules.scorm.launch', ['module' => $module->id]) }}">Launch</a></li>
                                                            @endif
                                                            @if ($module->status !== 'published')
                                                                <li>
                                                                    <form method="POST" action="{{ route('app.admin.modules.transition', ['module' => $module->id]) }}">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="published">
                                                                        <button type="submit" @disabled(! $moduleReadyToPublish) class="dropdown-item" title="{{ ! $moduleReadyToPublish ? 'Blocked until review and launch readiness issues are resolved' : ((($moduleState['impact']['counts']['visible_now'] ?? 0) === 0) ? 'Publish module: ready, but no learners will see it yet' : 'Publish module') }}">Publish</button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                            @if ($module->status !== 'archived')
                                                                <li>
                                                                    <form method="POST" action="{{ route('app.admin.modules.transition', ['module' => $module->id]) }}">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="archived">
                                                                        <button type="submit" class="dropdown-item">Archive</button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="px-4 py-4 text-secondary">No modules found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
function topicDeleter() {
    return {
        checking: false,
        deleting: false,
        courseCount: 0,
        topicName: '',
        _checkUrl: '',
        _deleteUrl: '',
        _topicId: null,
        _modal: null,

        async confirmDelete(id, name, checkUrl, deleteUrl) {
            this.topicName = name;
            this._checkUrl = checkUrl;
            this._deleteUrl = deleteUrl;
            this._topicId = id;
            this.courseCount = 0;
            this.checking = true;

            if (!this._modal) {
                this._modal = new bootstrap.Modal(this.$refs.modal);
            }
            this._modal.show();

            try {
                const res = await fetch(checkUrl, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                this.courseCount = json.course_count;
            } catch (e) {
                console.error('Topic check failed:', e);
                this.courseCount = 0;
            } finally {
                this.checking = false;
            }
        },

        async doDelete() {
            this.deleting = true;
            try {
                const res = await fetch(this._deleteUrl, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                if (res.ok) {
                    this._modal.hide();
                    const badge = document.getElementById('topic-badge-' + this._topicId);
                    if (badge) badge.remove();

                    // Update the total count badge
                    const countBadge = badge?.closest('.card-body')?.querySelector('.badge.bg-primary-subtle');
                    if (countBadge) {
                        const remaining = document.querySelectorAll('[id^="topic-badge-"]').length;
                        countBadge.textContent = remaining + ' total';
                    }
                }
            } catch (e) {
                console.error('Topic delete failed:', e);
            } finally {
                this.deleting = false;
            }
        }
    };
}
</script>
@endpush
