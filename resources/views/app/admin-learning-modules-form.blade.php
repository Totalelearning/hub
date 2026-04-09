@extends('layouts.learninguiux')

@section('title', ($pageTitle ?? 'Module') . ' - Learning')
@section('body_class', 'main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup')
@section('body_attributes', 'data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard" data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent"')

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
                        <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.3em;font-size:.72rem;">Module Publishing</div>
                        <h1 class="fs-3 fw-semibold mb-2">{{ __($pageTitle) }}</h1>
                        <p class="text-secondary mb-0">Manage module metadata, targeting, SCORM package readiness, and publication status.</p>
                        @if (!empty($formIntro))
                            <p class="fw-medium text-primary small mt-2 mb-0">{{ $formIntro }}</p>
                        @endif
                        @if ($module->exists)
                            <div class="mt-2"><span class="badge bg-primary-subtle text-primary">Reinforcement Questions</span></div>
                        @endif
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="#field-title" class="btn btn-sm btn-outline-secondary rounded-pill">Basics</a>
                            <a href="#field-target-roles" class="btn btn-sm btn-outline-secondary rounded-pill">Audience</a>
                            <a href="#field-availability-window" class="btn btn-sm btn-outline-secondary rounded-pill">Availability</a>
                            <a href="#scorm-package" class="btn btn-sm btn-outline-secondary rounded-pill">SCORM Package</a>
                            @if ($module->exists)
                                <a href="#reinforcement-questions" class="btn btn-sm btn-outline-secondary rounded-pill">Reinforcement</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 d-flex justify-content-lg-end">
                        <a href="{{ route('app.admin.modules.index') }}" class="btn btn-outline-theme">Back to Modules</a>
                    </div>
                </div>
            </div>

            {{-- Status flash --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            @php
                $reviewQuestions = $reinforcementQuestionSet?->questions ?? collect();
                $questionsMissingRemediation = $reviewQuestions->filter(fn ($question) => $question->remediation_learning_module_id === null)->count();
                $questionsWithExplanation = $reviewQuestions->filter(fn ($question) => filled($question->explanation))->count();
                $reviewReadiness = $questionsMissingRemediation === 0 ? 'Ready for approval' : 'Review remediation mappings';
            @endphp

            {{-- Module form --}}
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                        @csrf
                        @if ($formMethod !== 'POST')
                            @method($formMethod)
                        @endif

                        @if (\App\Support\ScormDemoScenario::isPrimaryDemoCourse($module))
                            <div class="alert alert-info mb-4">
                                <span class="badge bg-primary-subtle text-primary me-2">Primary Demo Course</span>
                                This is the canonical seeded SCORM course for the client walkthrough.
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label id="field-title" class="form-label">Title</label>
                                <input type="text" name="title" value="{{ old('title', $module->title) }}" class="form-control form-control-sm" required>
                                @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label id="field-description" class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control form-control-sm">{{ old('description', $module->description) }}</textarea>
                                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Cover Image</label>
                                @if ($module->cover_image)
                                    <div class="d-flex align-items-start gap-3 mb-2">
                                        <div style="width:160px;height:100px;border-radius:.5rem;overflow:hidden;flex-shrink:0;" class="border">
                                            <img src="{{ Storage::disk('public')->url($module->cover_image) }}" alt="Cover" style="width:100%;height:100%;object-fit:cover;">
                                        </div>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="small text-secondary">Current cover image</span>
                                            <label class="form-check d-flex align-items-center gap-2 mb-0">
                                                <input type="checkbox" name="remove_cover_image" value="1" class="form-check-input">
                                                <span class="small text-danger">Remove image</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm">
                                <div class="form-text">JPG, PNG or WebP. Max 2 MB.</div>
                                @error('cover_image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Topic</label>
                                <select name="topic" class="form-select form-select-sm">
                                    <option value="">— Select topic —</option>
                                    @foreach ($topicOptions as $topicOpt)
                                        <option value="{{ $topicOpt }}" {{ old('topic', $module->topic) === $topicOpt ? 'selected' : '' }}>{{ ucfirst($topicOpt) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label id="field-status" class="form-label">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    @foreach (['draft', 'published', 'archived'] as $status)
                                        <option value="{{ $status }}" @selected(old('status', $module->status) === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label id="field-review-status" class="form-label">Review Status</label>
                                <select name="review_status" class="form-select form-select-sm">
                                    @foreach (['draft', 'in_review', 'approved'] as $reviewStatus)
                                        <option value="{{ $reviewStatus }}" @selected(old('review_status', $module->review_status) === $reviewStatus)>{{ ucfirst(str_replace('_', ' ', $reviewStatus)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Content Owner</label>
                                <select name="owner_user_id" class="form-select form-select-sm">
                                    <option value="">Unassigned</option>
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}" @selected((string) old('owner_user_id', $module->owner_user_id) === (string) $owner->id)>{{ $owner->name }} ({{ $owner->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Refresh Interval Days</label>
                                <input type="number" name="refresh_interval_days" value="{{ old('refresh_interval_days', $module->refresh_interval_days) }}" class="form-control form-control-sm" min="1">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Reinforcement Follow-up Days</label>
                                <input type="text" name="reinforcement_intervals_days" value="{{ old('reinforcement_intervals_days', collect($module->reinforcement_intervals_days ?? [7, 30])->join(', ')) }}" class="form-control form-control-sm" placeholder="7, 30">
                                <div class="form-text">Comma-separated days after completion. Example: 3, 14, 30.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label id="field-availability-window" class="form-label">Available From</label>
                                <input type="datetime-local" name="available_from" value="{{ old('available_from', $module->available_from?->format('Y-m-d\\TH:i')) }}" class="form-control form-control-sm">
                                @error('available_from') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Available Until</label>
                                <input type="datetime-local" name="available_until" value="{{ old('available_until', $module->available_until?->format('Y-m-d\\TH:i')) }}" class="form-control form-control-sm">
                                @error('available_until') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Source Type</label>
                                <select name="source_type" class="form-select form-select-sm">
                                    @foreach (['manual', 'pdf', 'scorm'] as $sourceType)
                                        <option value="{{ $sourceType }}" @selected(old('source_type', $module->source_type) === $sourceType)>{{ strtoupper($sourceType) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Source URI</label>
                                <input type="text" name="source_uri" value="{{ old('source_uri', $module->source_uri) }}" class="form-control form-control-sm">
                            </div>

                            <div class="col-12">
                                <a id="field-prerequisites" class="form-label mb-2 d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="collapse" href="#prerequisites-collapse" role="button" aria-expanded="false" aria-controls="prerequisites-collapse">
                                    Prerequisites <i class="bi bi-chevron-down small"></i>
                                </a>
                                @php
                                    $selectedPrerequisites = collect(old('prerequisite_ids', $module->prerequisites->pluck('id')->all()))
                                        ->map(fn ($id) => (int) $id)
                                        ->all();
                                @endphp
                                <div class="collapse" id="prerequisites-collapse">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row g-2">
                                                @forelse ($availablePrerequisites as $prerequisite)
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-check card card-body py-2 px-3">
                                                            <label class="form-check-label d-flex align-items-start gap-2">
                                                                <input type="checkbox" name="prerequisite_ids[]" value="{{ $prerequisite->id }}" class="form-check-input mt-1" @checked(in_array($prerequisite->id, $selectedPrerequisites, true))>
                                                                <span>
                                                                    <span class="fw-medium d-block">{{ $prerequisite->title }}</span>
                                                                    <span class="text-uppercase text-secondary" style="font-size:.7rem;">{{ $prerequisite->status }}</span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-secondary small">No other modules available yet. Create modules first, then return here to add prerequisites.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('prerequisite_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('prerequisite_ids.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Content Text</label>
                                <textarea name="content_text" rows="8" class="form-control form-control-sm">{{ old('content_text', $module->content_text) }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_required" value="1" class="form-check-input" id="is_required" @checked(old('is_required', $module->is_required))>
                                    <label class="form-check-label" for="is_required">Required module</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="requires_acknowledgement" value="1" class="form-check-input" id="requires_ack" @checked(old('requires_acknowledgement', $module->requires_acknowledgement))>
                                    <label class="form-check-label" for="requires_ack">Requires learner acknowledgement</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-theme">{{ $submitLabel }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- SCORM Package section --}}
            @if ($module->exists)
                <div id="scorm-package" class="card adminuiux-card shadow-sm mb-4">
                    @php
                        $scormPackageReady = (bool) ($latestScormAsset && $latestScormAsset->status === 'processed' && filled($latestScormAsset->launch_path));
                        $scormCanLaunchForLearners = $module->source_type === 'scorm' && $scormPackageReady;
                        $latestFailedScormAsset = collect($scormAssetHistory ?? [])->firstWhere('status', 'failed');
                    @endphp
                    <div class="card-header d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <h3 class="fs-5 fw-semibold mb-1">SCORM Package</h3>
                            <p class="small text-secondary mb-0">Upload a SCORM .zip package, extract its manifest, and bind the launch file to this module.</p>
                        </div>
                        @if ($scormCanLaunchForLearners)
                            <a href="{{ route('app.modules.scorm.launch', ['module' => $module->id]) }}" class="btn btn-sm btn-outline-primary">Launch Prototype</a>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info small mb-3">Use this panel to confirm the package is processed, the launch path is detected, and the learner start button will be available before you publish.</div>

                        {{-- 3 status cards --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-lg-4">
                                <div class="card h-100 {{ $scormPackageReady ? 'border-success border-opacity-25 bg-success-subtle' : 'border-secondary border-opacity-25 bg-light' }}">
                                    <div class="card-body">
                                        <div class="text-uppercase fw-semibold" style="font-size:.68rem;letter-spacing:.18em;">Package Readiness</div>
                                        <div class="fw-semibold mt-2">{{ $scormPackageReady ? 'Ready to launch' : 'Not ready yet' }}</div>
                                        <div class="small mt-1">{{ $scormPackageReady ? 'Manifest parsed and launch path detected.' : 'Learners will not see a start button until the package is processed.' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="card h-100 {{ $module->source_type === 'scorm' ? 'border-primary border-opacity-25 bg-primary-subtle' : 'border-secondary border-opacity-25 bg-light' }}">
                                    <div class="card-body">
                                        <div class="text-uppercase fw-semibold" style="font-size:.68rem;letter-spacing:.18em;">Module Source Type</div>
                                        <div class="fw-semibold mt-2">{{ strtoupper($module->source_type ?: 'manual') }}</div>
                                        <div class="small mt-1">{{ $module->source_type === 'scorm' ? 'Module is configured as a SCORM learner experience.' : 'Change the module source type to SCORM if this should launch in the player.' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="card h-100 {{ $scormCanLaunchForLearners ? 'border-primary border-opacity-25 bg-primary-subtle' : 'bg-light' }}">
                                    <div class="card-body">
                                        <div class="text-uppercase fw-semibold" style="font-size:.68rem;letter-spacing:.18em;">Learner Launch</div>
                                        <div class="fw-semibold mt-2">{{ $scormCanLaunchForLearners ? 'Enabled' : 'Unavailable' }}</div>
                                        <div class="small mt-1">{{ $scormCanLaunchForLearners ? 'The learner module page should now show the launch button.' : 'The learner module page will stay on details only until both source type and package are ready.' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Launch checklist --}}
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="fw-semibold small">SCORM launch checklist</div>
                                <div class="row g-2 mt-2">
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge rounded-pill {{ $module->source_type === 'scorm' ? 'bg-success' : 'bg-secondary' }}">1</span>
                                            <div><div class="fw-medium small">Module source type is SCORM</div><div class="small text-secondary">{{ $module->source_type === 'scorm' ? 'Complete.' : 'Currently set to '.($module->source_type ?: 'manual').'.' }}</div></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge rounded-pill {{ $latestScormAsset ? 'bg-success' : 'bg-secondary' }}">2</span>
                                            <div><div class="fw-medium small">A package upload exists</div><div class="small text-secondary">{{ $latestScormAsset ? $latestScormAsset->original_filename : 'No current package detected.' }}</div></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge rounded-pill {{ $latestScormAsset && $latestScormAsset->status === 'processed' ? 'bg-success' : 'bg-secondary' }}">3</span>
                                            <div><div class="fw-medium small">Package processed successfully</div><div class="small text-secondary">{{ $latestScormAsset ? 'Current status: '.$latestScormAsset->status : 'Waiting for upload.' }}</div></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge rounded-pill {{ $scormPackageReady ? 'bg-success' : 'bg-secondary' }}">4</span>
                                            <div><div class="fw-medium small">Launch path found</div><div class="small text-secondary">{{ $latestScormAsset?->launch_path ?: 'No launch path detected yet.' }}</div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Upload status flash --}}
                        @if (session('scormUploadStatus'))
                            @php($scormUploadStatus = session('scormUploadStatus'))
                            <div class="alert {{ ($scormUploadStatus['state'] ?? null) === 'failed' ? 'alert-danger' : 'alert-success' }} small mb-3">
                                <div class="fw-semibold">{{ $scormUploadStatus['title'] ?? 'SCORM package status updated.' }}</div>
                                @if (!empty($scormUploadStatus['message'])) <div class="mt-1">{{ $scormUploadStatus['message'] }}</div> @endif
                            </div>
                        @endif

                        @if (! session('scormUploadStatus') && ! $scormPackageReady)
                            <div class="alert alert-secondary small mb-3">
                                <div class="fw-semibold">Learner start button is not available yet.</div>
                                <div class="mt-1">
                                    @if ($latestFailedScormAsset)
                                        The latest failed upload was {{ $latestFailedScormAsset['original_filename'] }}. Check the failure message below and re-upload after fixing the package.
                                    @elseif (! $latestScormAsset)
                                        Upload a SCORM .zip file first. When processing succeeds, this panel will show a current package and learner launch will turn on.
                                    @elseif ($latestScormAsset->status !== 'processed')
                                        The current package status is {{ $latestScormAsset->status }}. Learners only get a launch button once the package is processed.
                                    @else
                                        A package exists, but the launch path is still missing. This usually means the package manifest could not provide a launchable entry file.
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Upload form --}}
                        <form method="POST" action="{{ route('app.admin.modules.scorm.upload', ['module' => $module->id]) }}" enctype="multipart/form-data" class="row g-3 align-items-end mb-3" data-scorm-upload-form data-scorm-upload-max-bytes="{{ 51200 * 1024 }}">
                            @csrf
                            <div class="col">
                                <label class="form-label">SCORM package (.zip)</label>
                                <label class="d-block border border-2 border-dashed border-primary rounded p-4 bg-primary-subtle text-center" style="cursor:pointer;" data-scorm-upload-dropzone>
                                    <input type="file" name="scorm_package" accept=".zip,application/zip" class="d-none" required data-scorm-upload-input>
                                    <span class="d-block fw-semibold small">Drop a SCORM package here or click to browse</span>
                                    <span class="d-block small text-primary mt-1">Accepted format: .zip containing imsmanifest.xml | max size 50 MB</span>
                                    <span class="d-block small text-secondary mt-2" data-scorm-upload-filename>No file selected.</span>
                                    <span class="d-none small text-danger mt-1" data-scorm-upload-error>Choose a valid SCORM .zip file under 50 MB.</span>
                                    <span class="d-none small text-warning mt-1" data-scorm-upload-progress>Upload in progress. Do not leave this page.</span>
                                </label>
                                @error('scorm_package') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-theme" data-scorm-upload-submit>Upload SCORM</button>
                            </div>
                        </form>

                        {{-- Current package --}}
                        @if ($latestScormAsset)
                            <div class="card {{ $scormPackageReady ? 'border-success border-opacity-25 bg-success-subtle' : 'bg-light' }} mb-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                        <div>
                                            <div class="fw-medium small">Current package</div>
                                            <div class="text-uppercase small {{ $scormPackageReady ? 'text-success' : 'text-secondary' }}" style="font-size:.7rem;letter-spacing:.16em;">{{ $scormPackageReady ? 'Ready for learner launch' : 'Needs attention before learner launch' }}</div>
                                        </div>
                                        @if ($scormCanLaunchForLearners)
                                            <a href="{{ route('app.modules.show', ['module' => $module->id]) }}" class="btn btn-sm btn-outline-success">Open learner page</a>
                                        @endif
                                    </div>
                                    <div class="row g-2 small">
                                        <div class="col-12 col-md-6"><strong>File:</strong> {{ $latestScormAsset->original_filename }}</div>
                                        <div class="col-12 col-md-6"><strong>Status:</strong> {{ $latestScormAsset->status }}</div>
                                        <div class="col-12 col-md-6"><strong>Launch path:</strong> {{ $latestScormAsset->launch_path ?? 'n/a' }}</div>
                                        <div class="col-12 col-md-6"><strong>Source URI:</strong> {{ $module->source_uri ?? 'n/a' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Package history --}}
                        @if ($scormAssetHistory->isNotEmpty())
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <div class="fw-medium small">Recent SCORM Package Uploads</div>
                                    <div class="small text-secondary">Review package history, launch paths, and activate a previous processed package if needed.</div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 small">
                                        <thead class="table-light">
                                            <tr><th>Uploaded</th><th>File</th><th>Status</th><th>Size</th><th>Launch Path</th><th>Failure</th><th>Action</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($scormAssetHistory as $assetRow)
                                                <tr>
                                                    <td class="text-nowrap">{{ $assetRow['uploaded_at']?->format('Y-m-d H:i') ?? 'n/a' }}</td>
                                                    <td>{{ $assetRow['original_filename'] }}</td>
                                                    <td><span class="badge {{ $assetRow['status'] === 'processed' ? 'bg-success-subtle text-success' : ($assetRow['status'] === 'failed' ? 'bg-danger-subtle text-danger' : 'bg-light text-dark border') }}">{{ $assetRow['status'] }}</span></td>
                                                    <td class="text-nowrap">{{ $assetRow['size_label'] }}</td>
                                                    <td>{{ $assetRow['launch_path'] ?? 'n/a' }}</td>
                                                    <td>{{ $assetRow['error_message'] ?? 'n/a' }}</td>
                                                    <td class="text-nowrap">
                                                        @if ($assetRow['is_current'])
                                                            <span class="badge bg-primary-subtle text-primary">Current package</span>
                                                        @elseif ($assetRow['status'] === 'processed')
                                                            <form method="POST" action="{{ route('app.admin.modules.scorm.activate', ['module' => $module->id, 'asset' => $assetRow['id']]) }}">
                                                                @csrf @method('PATCH')
                                                                <button type="submit" class="btn btn-sm btn-outline-secondary">Use as current package</button>
                                                            </form>
                                                        @else
                                                            <span class="text-secondary small">Unavailable</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- SCORM runtime summary --}}
                        @if ($scormSummary)
                            <div class="alert alert-info small mb-3">
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-primary-subtle text-primary">Demo Scenario: Client Walkthrough</span>
                                    @if (\App\Support\ScormDemoScenario::isPrimaryDemoCourse($module))
                                        <span class="badge bg-primary-subtle text-primary">Primary Demo Course</span>
                                    @endif
                                </div>
                                <div class="fw-medium">SCORM Runtime Summary</div>
                                <div class="row g-2 mt-1">
                                    <div class="col-12 col-md-6"><strong>Package status:</strong> {{ $scormSummary['package_status'] }}</div>
                                    <div class="col-12 col-md-6"><strong>Launch path:</strong> {{ $scormSummary['launch_path'] ?? 'n/a' }}</div>
                                    <div class="col-12 col-md-6"><strong>Learners with progress:</strong> {{ $scormSummary['learner_count'] }}</div>
                                    <div class="col-12 col-md-6"><strong>Completed:</strong> {{ $scormSummary['completed_count'] }}</div>
                                    <div class="col-12 col-md-6"><strong>In progress:</strong> {{ $scormSummary['in_progress_count'] }}</div>
                                    <div class="col-12 col-md-6"><strong>Average score:</strong> {{ $scormSummary['average_score'] }}</div>
                                    <div class="col-12 col-md-6"><strong>Logged session time:</strong> {{ $scormSummary['total_session_label'] }}</div>
                                    <div class="col-12 col-md-6"><strong>Last runtime:</strong> {{ $scormSummary['last_runtime_at'] ? \Illuminate\Support\Carbon::parse($scormSummary['last_runtime_at'])->format('Y-m-d H:i') : 'none' }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Recent SCORM Attempts --}}
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="fw-medium small">Recent SCORM Attempts</div>
                                <div class="small text-secondary">Latest runtime commits recorded for this module.</div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 small">
                                    <thead class="table-light">
                                        <tr><th>When</th><th>Learner</th><th>Status</th><th>Score</th><th>Session</th><th>Progress</th><th>Location</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentScormAttempts as $attempt)
                                            <tr>
                                                <td>{{ $attempt['when']?->format('Y-m-d H:i') }}</td>
                                                <td>{{ $attempt['learner_name'] }}@if ($attempt['learner_email'])<div class="text-secondary" style="font-size:.75rem;">{{ $attempt['learner_email'] }}</div>@endif</td>
                                                <td><span class="badge {{ $attempt['status'] === 'completed' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}">{{ $attempt['status'] }}</span></td>
                                                <td>{{ $attempt['score_raw'] ?? 'n/a' }}</td>
                                                <td>{{ $attempt['session_label'] }}</td>
                                                <td>{{ $attempt['percent_complete'] ?? 'n/a' }}</td>
                                                <td>{{ $attempt['lesson_location'] ?? 'n/a' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-secondary">No SCORM attempts recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Reinforcement Questions --}}
            @if ($module->exists)
                <div id="reinforcement-questions" class="card adminuiux-card shadow-sm mb-4">
                    <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                        <div>
                            <h3 class="fs-5 fw-semibold mb-1">Reinforcement Questions</h3>
                            <p class="small text-secondary mb-0">Hybrid flow: create an AI draft from the module content, then review, edit, and approve it before it is used for learner follow-up.</p>
                        </div>
                        <form method="POST" action="{{ route('app.admin.modules.reinforcement-questions.draft', ['module' => $module->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">Draft from module content</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <div class="d-flex flex-column gap-3">
                                    {{-- Question set state --}}
                                    <div class="card {{ $reinforcementQuestionSet ? 'border-primary border-opacity-25 bg-primary-subtle' : 'bg-light' }}">
                                        <div class="card-body">
                                            <div class="text-uppercase fw-semibold text-secondary" style="font-size:.68rem;letter-spacing:.18em;">Question set state</div>
                                            <div class="fw-semibold mt-2">{{ $reinforcementQuestionSet ? ucfirst(str_replace('_', ' ', $reinforcementQuestionSet->status)) : 'No draft yet' }}</div>
                                            <p class="small text-secondary mt-2 mb-0">
                                                @if ($reinforcementQuestionSet)
                                                    {{ $reinforcementQuestionSet->summary ?: 'Draft questions are ready for admin review.' }}
                                                @else
                                                    Generate a first draft from the module title, description, content text, and SCORM manifest metadata.
                                                @endif
                                            </p>
                                            @if ($reinforcementQuestionSet)
                                                <div class="row g-2 mt-2">
                                                    <div class="col-6"><div class="card"><div class="card-body py-2 px-3"><div class="text-uppercase text-primary" style="font-size:.65rem;letter-spacing:.16em;">Generation mode</div><div class="fw-semibold small mt-1">{{ ucfirst(str_replace('_', ' ', $reinforcementQuestionSet->generation_mode)) }}</div></div></div></div>
                                                    <div class="col-6"><div class="card"><div class="card-body py-2 px-3"><div class="text-uppercase text-primary" style="font-size:.65rem;letter-spacing:.16em;">Questions</div><div class="fw-semibold small mt-1">{{ $reinforcementQuestionSet->questions->count() }}</div></div></div></div>
                                                    <div class="col-6"><div class="card"><div class="card-body py-2 px-3"><div class="text-uppercase text-primary" style="font-size:.65rem;letter-spacing:.16em;">Generated</div><div class="fw-semibold small mt-1">{{ $reinforcementQuestionSet->generated_at?->format('Y-m-d H:i') ?? 'n/a' }}</div></div></div></div>
                                                    <div class="col-6"><div class="card"><div class="card-body py-2 px-3"><div class="text-uppercase text-primary" style="font-size:.65rem;letter-spacing:.16em;">Reviewed by</div><div class="fw-semibold small mt-1">{{ $reinforcementQuestionSet->reviewer?->name ?? 'Pending review' }}</div></div></div></div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Review readiness --}}
                                    @if ($reinforcementQuestionSet)
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="text-uppercase fw-semibold text-secondary" style="font-size:.68rem;letter-spacing:.18em;">Review readiness</div>
                                                <div class="fw-semibold mt-2">{{ $reviewReadiness }}</div>
                                                <p class="small text-secondary mt-2 mb-0">Use this quick check before approving the AI draft for live learner follow-up.</p>
                                                <div class="row g-2 mt-2">
                                                    <div class="col-4"><div class="card bg-light"><div class="card-body py-2 px-3"><div class="text-uppercase text-secondary" style="font-size:.65rem;">Questions</div><div class="fw-semibold small mt-1">{{ $reviewQuestions->count() }}</div></div></div></div>
                                                    <div class="col-4"><div class="card {{ $questionsMissingRemediation === 0 ? 'bg-success-subtle border-success border-opacity-25' : 'bg-light border-secondary border-opacity-25' }}"><div class="card-body py-2 px-3"><div class="text-uppercase {{ $questionsMissingRemediation === 0 ? 'text-success' : 'text-secondary' }}" style="font-size:.65rem;">Without remediation</div><div class="fw-semibold small mt-1">{{ $questionsMissingRemediation }}</div></div></div></div>
                                                    <div class="col-4"><div class="card bg-light"><div class="card-body py-2 px-3"><div class="text-uppercase text-secondary" style="font-size:.65rem;">With coaching note</div><div class="fw-semibold small mt-1">{{ $questionsWithExplanation }}</div></div></div></div>
                                                </div>
                                                <div class="small text-secondary mt-2">Questions without remediation can still fail a learner, but they will not assign extra follow-up learning automatically.</div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- How this works --}}
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="text-uppercase fw-semibold text-secondary" style="font-size:.68rem;letter-spacing:.18em;">How this works</div>
                                            <div class="d-flex flex-column gap-2 mt-2 small">
                                                <div class="card"><div class="card-body py-2 px-3">1. Draft questions are generated from module metadata and extracted SCORM/module text.</div></div>
                                                <div class="card"><div class="card-body py-2 px-3">2. An admin edits the wording, answers, and remediation module targets.</div></div>
                                                <div class="card"><div class="card-body py-2 px-3">3. Only approved question sets should be used for learner reinforcement follow-up.</div></div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($reinforcementQuestionSet?->draft_source_excerpt)
                                        <div class="card border-primary border-opacity-25 bg-primary-subtle">
                                            <div class="card-body">
                                                <div class="text-uppercase fw-semibold text-primary" style="font-size:.68rem;letter-spacing:.18em;">Draft source excerpt</div>
                                                <p class="small mt-2 mb-0">{{ $reinforcementQuestionSet->draft_source_excerpt }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                @if ($reinforcementQuestionSet)
                                    <form method="POST" action="{{ route('app.admin.modules.reinforcement-questions.update', ['module' => $module->id]) }}">
                                        @csrf @method('PATCH')

                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <label class="form-label">Question set title</label>
                                                <input type="text" name="set_title" value="{{ old('set_title', $reinforcementQuestionSet->title) }}" class="form-control form-control-sm" required>
                                                <label class="form-label mt-3">Reviewer summary</label>
                                                <textarea name="set_summary" rows="3" class="form-control form-control-sm">{{ old('set_summary', $reinforcementQuestionSet->summary) }}</textarea>
                                            </div>
                                        </div>

                                        @foreach ($reinforcementQuestionSet->questions as $index => $question)
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="text-uppercase fw-semibold text-primary" style="font-size:.68rem;letter-spacing:.18em;">Question {{ $index + 1 }}</div>
                                                    <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $question->id }}">
                                                    <label class="form-label mt-2">Question text</label>
                                                    <textarea name="questions[{{ $index }}][question_text]" rows="3" class="form-control form-control-sm" required>{{ old("questions.$index.question_text", $question->question_text) }}</textarea>

                                                    <div class="row g-2 mt-2">
                                                        @foreach (['A', 'B', 'C', 'D'] as $optionKey)
                                                            <div class="col-6">
                                                                <label class="form-label">Option {{ $optionKey }}</label>
                                                                <input type="text" name="questions[{{ $index }}][option_{{ strtolower($optionKey) }}]" value="{{ old("questions.$index.option_".strtolower($optionKey), $question->options[$optionKey] ?? '') }}" class="form-control form-control-sm" required>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="row g-2 mt-2">
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label">Correct answer</label>
                                                            <select name="questions[{{ $index }}][correct_answer]" class="form-select form-select-sm">
                                                                @foreach (['A', 'B', 'C', 'D'] as $optionKey)
                                                                    <option value="{{ $optionKey }}" @selected(old("questions.$index.correct_answer", $question->correct_answer) === $optionKey)>{{ $optionKey }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label">Remediation module if incorrect</label>
                                                            <select name="questions[{{ $index }}][remediation_learning_module_id]" class="form-select form-select-sm">
                                                                <option value="">No remediation mapping yet</option>
                                                                @foreach ($remediationModules as $remediationModule)
                                                                    <option value="{{ $remediationModule->id }}" @selected((string) old("questions.$index.remediation_learning_module_id", $question->remediation_learning_module_id) === (string) $remediationModule->id)>{{ $remediationModule->title }} ({{ $remediationModule->status }})</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="form-text">If the learner answers this question incorrectly, this follow-up module will be assigned automatically.</div>
                                                        </div>
                                                    </div>

                                                    <label class="form-label mt-3">Reviewer explanation / coaching note</label>
                                                    <textarea name="questions[{{ $index }}][explanation]" rows="2" class="form-control form-control-sm">{{ old("questions.$index.explanation", $question->explanation) }}</textarea>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-outline-secondary">Save review draft</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('app.admin.modules.reinforcement-questions.approve', ['module' => $module->id]) }}" class="mt-3">
                                        @csrf @method('PATCH')
                                        <div class="card {{ $reinforcementQuestionSet->status === 'approved' ? 'border-success border-opacity-25 bg-success-subtle' : 'border-secondary border-opacity-25 bg-light' }}">
                                            <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                                                <div>
                                                    <div class="text-uppercase fw-semibold {{ $reinforcementQuestionSet->status === 'approved' ? 'text-success' : 'text-secondary' }}" style="font-size:.68rem;letter-spacing:.18em;">Approval state</div>
                                                    <div class="fw-semibold mt-1">{{ $reinforcementQuestionSet->status === 'approved' ? 'Approved for learner reinforcement' : 'Admin approval still required' }}</div>
                                                    <p class="small mt-1 mb-0">Approve only when the wording, correct answers, and remediation mappings are ready for live learner follow-up.</p>
                                                </div>
                                                <button type="submit" class="btn btn-theme" {{ $reinforcementQuestionSet->status === 'approved' ? 'disabled' : '' }}>
                                                    {{ $reinforcementQuestionSet->status === 'approved' ? 'Already approved' : 'Approve question set' }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @else
                                    <div class="card border-dashed bg-light text-center py-5">
                                        <div class="card-body">
                                            <div class="fw-semibold">No reinforcement draft yet</div>
                                            <p class="small text-secondary mt-2 mb-0">Create a draft first, then review the questions here before approving them for learner use.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Revision History --}}
            @if (!empty($revisions) && $revisions->isNotEmpty())
                <div class="card adminuiux-card shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="fs-5 fw-semibold mb-1">Revision History</h3>
                        <p class="small text-secondary mb-0">Recent module snapshots for traceability.</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Revision</th><th>Change</th><th>Status</th><th>Actor</th><th>When</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($revisions as $revision)
                                    <tr>
                                        <td class="fw-medium">r{{ $revision->revision_number }}</td>
                                        <td>{{ str_replace('_', ' ', $revision->change_type) }}</td>
                                        <td>{{ $revision->status }}</td>
                                        <td>{{ $revision->user?->name ?? 'system' }}</td>
                                        <td>{{ $revision->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </main>
</div>
@endsection
