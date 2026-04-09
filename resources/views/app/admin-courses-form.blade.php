@extends('layouts.learninguiux')

@section('title', ($course ? 'Edit' : 'Create') . ' Course - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

@push('styles')
<style>
    .course-module-item {
        cursor: pointer;
        transition: background-color 150ms ease;
    }
    .course-module-item:hover {
        background-color: rgba(248, 250, 252, 0.88);
    }
    .course-module-item.selected {
        background-color: rgba(219, 234, 254, 0.6);
        border-color: rgba(59, 130, 246, 0.4) !important;
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
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('app.admin.courses.index') }}">Courses</a></li>
                                <li class="breadcrumb-item active">{{ $course ? 'Edit' : 'Create' }}</li>
                            </ol>
                        </nav>
                        <h1 class="fs-3 fw-semibold mb-2">{{ $course ? 'Edit Course' : 'Create Course' }}</h1>
                        <p class="text-secondary mb-0">A course groups 2&ndash;5 short modules into a single ~15 minute learning experience.</p>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Hidden form that actually submits --}}
            <form id="course-form" method="POST" action="{{ $course ? route('app.admin.courses.update', $course) : route('app.admin.courses.store') }}" style="display:none;">
                @csrf
                @if ($course)
                    @method('PATCH')
                @endif
                <input type="hidden" name="title" id="hid-title">
                <input type="hidden" name="description" id="hid-description">
                <input type="hidden" name="topic" id="hid-topic">
                <input type="hidden" name="estimated_minutes" id="hid-estimated_minutes">
                <input type="hidden" name="reinforcement_delay_days" id="hid-reinforcement_delay_days">
                <input type="hidden" name="status" id="hid-status">
                <div id="hid-roles"></div>
                <div id="hid-modules"></div>
            </form>

            <div class="row g-4">
                {{-- Left: course details --}}
                <div class="col-lg-5">
                    <div class="card adminuiux-card shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">Course Details</h5>

                            <div class="mb-3">
                                <label for="title" class="form-label fw-medium small">Title</label>
                                <input type="text" id="inp-title" class="form-control" value="{{ old('title', $course?->title) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-medium small">Description</label>
                                <textarea id="inp-description" class="form-control" rows="3">{{ old('description', $course?->description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="topic" class="form-label fw-medium small">Topic</label>
                                <select id="inp-topic" class="form-select">
                                    <option value="">— Select topic —</option>
                                    @foreach ($topicOptions as $topicOpt)
                                        <option value="{{ $topicOpt }}" {{ old('topic', $course?->topic) === $topicOpt ? 'selected' : '' }}>{{ ucfirst($topicOpt) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium small">Target Roles</label>
                                <p class="text-secondary small mb-2">Users with a matching role will be auto-assigned this course.</p>
                                @php $selectedRoles = old('target_roles', $course?->target_roles ?? []); @endphp
                                <div class="d-flex flex-wrap gap-2">
                                    <label class="d-inline-flex align-items-center gap-1 border rounded px-3 py-2 role-check-label">
                                        <input type="checkbox" class="form-check-input mt-0 role-checkbox" value="all"
                                            {{ in_array('all', $selectedRoles) ? 'checked' : '' }}
                                            onchange="window.courseToggleAllRoles(this)"> All
                                    </label>
                                    @foreach ($roleOptions as $roleOpt)
                                        <label class="d-inline-flex align-items-center gap-1 border rounded px-3 py-2 role-check-label">
                                            <input type="checkbox" class="form-check-input mt-0 role-checkbox" value="{{ $roleOpt }}"
                                                {{ in_array($roleOpt, $selectedRoles) ? 'checked' : '' }}
                                                onchange="window.courseRoleChanged()"> {{ $roleOpt }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="estimated_minutes" class="form-label fw-medium small">Duration (minutes)</label>
                                    <input type="number" id="inp-estimated_minutes" class="form-control" value="{{ old('estimated_minutes', $course?->estimated_minutes) }}" min="1" max="999" placeholder="e.g. 15">
                                </div>
                                <div class="col-md-4">
                                    <label for="reinforcement_delay_days" class="form-label fw-medium small">Reinforcement Delay (days)</label>
                                    <input type="number" id="inp-reinforcement_delay_days" class="form-control" value="{{ old('reinforcement_delay_days', $course?->reinforcement_delay_days ?? 30) }}" min="1" max="365" placeholder="e.g. 30">
                                    <div class="form-text">Days after completion before sending the knowledge check email.</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="status" class="form-label fw-medium small">Status</label>
                                    <select id="inp-status" class="form-select" required>
                                        @foreach (['draft', 'published', 'archived'] as $s)
                                            <option value="{{ $s }}" {{ old('status', $course?->status ?? 'draft') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($course && isset($moduleQuestionReadiness) && $moduleQuestionReadiness->isNotEmpty())
                        <div class="card adminuiux-card shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-semibold mb-1">Reinforcement Readiness</h5>
                                <p class="text-secondary small mb-3">Question sets for the knowledge check sent {{ $course->reinforcement_delay_days ?? 30 }} days after course completion.</p>

                                @php
                                    $approvedCount = $moduleQuestionReadiness->where('status', 'approved')->count();
                                    $totalModules = $moduleQuestionReadiness->count();
                                    $allReady = $approvedCount === $totalModules;
                                @endphp

                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="progress flex-grow-1" style="height:8px;">
                                        <div class="progress-bar {{ $allReady ? 'bg-success' : 'bg-warning' }}" style="width:{{ $totalModules > 0 ? round(($approvedCount / $totalModules) * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="badge {{ $allReady ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $approvedCount }}/{{ $totalModules }}</span>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    @foreach ($moduleQuestionReadiness as $item)
                                        <div class="d-flex align-items-center gap-2 border rounded-3 p-2 px-3">
                                            @if ($item['status'] === 'approved')
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @elseif ($item['status'] === 'in_review')
                                                <i class="bi bi-clock-fill text-warning"></i>
                                            @elseif ($item['status'] === 'draft')
                                                <i class="bi bi-pencil-fill text-info"></i>
                                            @else
                                                <i class="bi bi-x-circle text-danger"></i>
                                            @endif
                                            <div class="flex-grow-1">
                                                <div class="small fw-medium">{{ $item['module']->title }}</div>
                                                <div class="small text-secondary">
                                                    @if ($item['status'] === 'none')
                                                        No questions yet
                                                    @else
                                                        {{ ucfirst($item['status']) }} &middot; {{ $item['question_count'] }} questions
                                                    @endif
                                                </div>
                                            </div>
                                            <a href="{{ route('app.admin.modules.edit', $item['module']) }}#reinforcement-questions" class="btn btn-outline-secondary btn-sm">
                                                {{ $item['status'] === 'none' ? 'Create' : 'Edit' }}
                                            </a>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($approvedCount === 0)
                                    <div class="alert alert-danger small mt-3 mb-0">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        No approved question sets. Knowledge checks will not be sent for this course until at least one module has approved questions.
                                    </div>
                                @elseif (! $allReady)
                                    <div class="alert alert-warning small mt-3 mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Some modules are missing approved questions. The knowledge check will only include questions from modules that have approved sets.
                                    </div>
                                @else
                                    <div class="alert alert-success small mt-3 mb-0">
                                        <i class="bi bi-check-circle me-1"></i>
                                        All modules have approved questions. Knowledge checks are ready to send.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-theme" onclick="window.submitCourseForm()">{{ $course ? 'Update Course' : 'Create Course' }}</button>
                        <a href="{{ route('app.admin.courses.index') }}" class="btn btn-outline-theme">Cancel</a>
                    </div>
                </div>

                {{-- Right: module picker (no form wrapper) --}}
                <div class="col-lg-7">
                    <div class="card adminuiux-card shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-semibold mb-0">Modules</h5>
                                <span class="badge bg-primary-subtle text-primary" id="selected-count">{{ count($selectedModuleIds) }} selected</span>
                            </div>
                            <p class="small text-secondary mb-3">Select the modules that make up this course. Each module should be 2&ndash;3 minutes.</p>

                            <div class="d-flex gap-2 mb-3">
                                <input type="text" class="form-control form-control-sm" id="module-search" placeholder="Search modules...">
                                <select class="form-select form-select-sm" id="module-topic-filter" style="max-width:180px;" onchange="window.courseFilterApply()">
                                    <option value="">All topics</option>
                                    @foreach ($modules->pluck('topic')->filter()->map(fn($t) => strtolower($t))->unique()->sort() as $topic)
                                        <option value="{{ $topic }}">{{ ucfirst($topic) }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-theme btn-sm text-nowrap" onclick="window.courseFilterApply()">Apply</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm text-nowrap" onclick="window.courseFilterClear()">Clear</button>
                            </div>

                            <div style="max-height: 500px; overflow-y: auto;">
                                @foreach ($modules as $module)
                                    <label class="d-flex align-items-center gap-3 rounded-3 border p-3 mb-2 course-module-item {{ in_array($module->id, $selectedModuleIds) ? 'selected' : '' }}" data-title="{{ strtolower($module->title) }}" data-topic="{{ strtolower($module->topic ?? '') }}">
                                        <input type="checkbox" value="{{ $module->id }}" class="form-check-input mt-0 module-checkbox"
                                            {{ in_array($module->id, $selectedModuleIds) ? 'checked' : '' }}
                                            onchange="this.closest('.course-module-item').classList.toggle('selected',this.checked);window.courseUpdateCount()">
                                        <div class="flex-grow-1">
                                            <div class="fw-medium">{{ $module->title }}</div>
                                            <div class="small text-secondary">
                                                {{ $module->topic ? ucfirst($module->topic) : 'General' }}
                                                @if ($module->source_type)
                                                    <span class="badge bg-light border text-secondary ms-1">{{ strtoupper($module->source_type) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
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
window.courseFilterApply = function () {
    var search = document.getElementById('module-search');
    var topicFilter = document.getElementById('module-topic-filter');
    var items = document.querySelectorAll('.course-module-item');
    var q = search ? search.value.toLowerCase() : '';
    var topic = topicFilter ? topicFilter.value : '';
    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var matchesSearch = !q || item.getAttribute('data-title').indexOf(q) !== -1;
        var matchesTopic = !topic || item.getAttribute('data-topic') === topic;
        item.style.display = (matchesSearch && matchesTopic) ? '' : 'none';
    }
};

window.courseFilterClear = function () {
    var search = document.getElementById('module-search');
    var topicFilter = document.getElementById('module-topic-filter');
    if (search) search.value = '';
    if (topicFilter) topicFilter.value = '';
    window.courseFilterApply();
};

window.courseUpdateCount = function () {
    var countBadge = document.getElementById('selected-count');
    var checked = document.querySelectorAll('.module-checkbox:checked').length;
    if (countBadge) countBadge.textContent = checked + ' selected';
};

window.courseToggleAllRoles = function (allCheckbox) {
    var boxes = document.querySelectorAll('.role-checkbox');
    for (var i = 0; i < boxes.length; i++) {
        boxes[i].checked = allCheckbox.checked;
    }
};

window.courseRoleChanged = function () {
    var allBox = document.querySelector('.role-checkbox[value="all"]');
    var others = document.querySelectorAll('.role-checkbox:not([value="all"])');
    var allChecked = true;
    for (var i = 0; i < others.length; i++) {
        if (!others[i].checked) { allChecked = false; break; }
    }
    if (allBox) allBox.checked = allChecked;
};

window.submitCourseForm = function () {
    document.getElementById('hid-title').value = document.getElementById('inp-title').value;
    document.getElementById('hid-description').value = document.getElementById('inp-description').value;
    document.getElementById('hid-topic').value = document.getElementById('inp-topic').value;
    document.getElementById('hid-estimated_minutes').value = document.getElementById('inp-estimated_minutes').value;
    document.getElementById('hid-reinforcement_delay_days').value = document.getElementById('inp-reinforcement_delay_days').value;
    document.getElementById('hid-status').value = document.getElementById('inp-status').value;

    var rolesContainer = document.getElementById('hid-roles');
    rolesContainer.innerHTML = '';
    var checkedRoles = document.querySelectorAll('.role-checkbox:checked');
    for (var i = 0; i < checkedRoles.length; i++) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'target_roles[]';
        inp.value = checkedRoles[i].value;
        rolesContainer.appendChild(inp);
    }

    var container = document.getElementById('hid-modules');
    container.innerHTML = '';
    var checked = document.querySelectorAll('.module-checkbox:checked');
    for (var i = 0; i < checked.length; i++) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'modules[]';
        inp.value = checked[i].value;
        container.appendChild(inp);
    }

    document.getElementById('course-form').submit();
};
</script>
@endpush
