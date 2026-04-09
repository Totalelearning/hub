<?php

namespace App\Http\Controllers;

use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\LearningAsset;
use App\Services\AssignmentService;
use App\Services\FeedRankingService;
use App\Services\LearningPathService;
use App\Services\ProgressService;
use App\Support\ScormRuntimeMetrics;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AppScormController extends Controller
{
    public function launch(LearningModule $module): View
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(app(FeedRankingService::class)->isVisibleToUser($user, $module), 404);

        $asset = $this->scormAsset($module);
        $progress = $user->moduleProgress()
            ->where('learning_module_id', $module->id)
            ->first();
        $latestRuntime = LearningEvent::query()
            ->where('user_id', $user->id)
            ->where('event_type', 'scorm_runtime_committed')
            ->where('entity_type', 'learning_module')
            ->where('entity_id', $module->id)
            ->latest('id')
            ->first();

        $paths = app(LearningPathService::class);
        $assignments = app(AssignmentService::class);
        $pathCollection = $paths->visiblePathsForUser($user)
            ->map(function ($path) use ($paths, $user) {
                $path->setAttribute('step_states', $paths->stepStates($user, $path));
                $path->setAttribute(
                    'next_step',
                    collect($path->step_states)->first(fn (array $state) => ! $state['is_completed'] && $state['is_unlocked'])
                );

                return $path;
            });
        $activePath = $pathCollection->first(fn ($path) => $path->next_step) ?? $pathCollection->first();

        $visibleModules = LearningModule::query()
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->filter(fn (LearningModule $candidate) => app(FeedRankingService::class)->isVisibleToUser($user, $candidate))
            ->values();

        $progressByModuleId = $user->moduleProgress()
            ->whereIn('learning_module_id', $visibleModules->pluck('id'))
            ->get()
            ->keyBy('learning_module_id');

        $requiredModules = $visibleModules
            ->map(function (LearningModule $candidate) use ($assignments, $progressByModuleId, $user) {
                $assignment = $assignments->forUser($user, $candidate, $progressByModuleId->get($candidate->id));
                $candidate->setAttribute('assignment', $assignment);
                $candidate->setAttribute('user_progress_status', $progressByModuleId->get($candidate->id)?->status ?? 'not_started');

                return $candidate;
            })
            ->filter(fn (LearningModule $candidate) => (bool) ($candidate->assignment['is_required'] ?? false))
            ->values();

        $recommendedModules = $visibleModules
            ->reject(fn (LearningModule $candidate) => (bool) ($candidate->assignment['is_required'] ?? false))
            ->values();

        $completionNextActions = collect();
        if (($progress?->status ?? null) === 'completed') {
            $nextPathModule = $activePath->next_step['module'] ?? null;
            if ($nextPathModule && (int) $nextPathModule->id !== (int) $module->id) {
                $completionNextActions->push([
                    'label' => 'Next path step',
                    'title' => $nextPathModule->title,
                    'summary' => 'Continue into the next unlocked step from your current learning path.',
                    'href' => route('app.modules.show', ['module' => $nextPathModule->id]),
                    'cta' => 'Open next step',
                ]);
            }

            $nextRequiredModule = $requiredModules->first(fn (LearningModule $candidate) => (bool) ($candidate->assignment['is_incomplete_required'] ?? false) && (int) $candidate->id !== (int) $module->id);
            if ($nextRequiredModule) {
                $completionNextActions->push([
                    'label' => 'Required next',
                    'title' => $nextRequiredModule->title,
                    'summary' => 'Move on to the next required module in your learner record.',
                    'href' => route('app.modules.show', ['module' => $nextRequiredModule->id]),
                    'cta' => 'Open required module',
                ]);
            }

            $nextRecommendedModule = $recommendedModules->first(fn (LearningModule $candidate) => ($candidate->user_progress_status ?? 'not_started') !== 'completed' && (int) $candidate->id !== (int) $module->id);
            if ($nextRecommendedModule) {
                $completionNextActions->push([
                    'label' => 'Recommended next',
                    'title' => $nextRecommendedModule->title,
                    'summary' => 'Keep the momentum going with another visible module matched to your profile.',
                    'href' => route('app.modules.show', ['module' => $nextRecommendedModule->id]),
                    'cta' => 'Open recommendation',
                ]);
            }

            $completionNextActions->push([
                'label' => 'Dashboard',
                'title' => 'Return to learner dashboard',
                'summary' => 'Review your latest completion proof, reminders, and priority actions.',
                'href' => route('app.feed'),
                'cta' => 'Back to dashboard',
            ]);
        }

        $completionNextActions = $completionNextActions
            ->unique(fn (array $action) => $action['href'])
            ->take(3)
            ->values();

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'scorm_launched',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'asset_id' => $asset->id,
                'launch_path' => $asset->launch_path,
            ],
        ]);

        return view('app.module-scorm-launch', [
            'module' => $module,
            'asset' => $asset,
            'progress' => $progress,
            'latestRuntime' => $latestRuntime,
            'completionNextActions' => $completionNextActions,
            'launchUrl' => route('app.modules.scorm.asset', [
                'module' => $module->id,
                'path' => $asset->launch_path,
            ]),
        ]);
    }

    public function asset(Request $request, LearningModule $module, string $path = ''): Response|BinaryFileResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(app(FeedRankingService::class)->isVisibleToUser($user, $module), 404);

        $asset = $this->scormAsset($module);
        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
        abort_if($normalizedPath === '' || str_contains($normalizedPath, '..'), 404);

        $relativePath = rtrim((string) $asset->extracted_path, '/').'/'.$normalizedPath;
        abort_unless(Storage::disk($asset->extracted_disk ?: 'local')->exists($relativePath), 404);

        $absolutePath = Storage::disk($asset->extracted_disk ?: 'local')->path($relativePath);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mimeType = Storage::disk($asset->extracted_disk ?: 'local')->mimeType($relativePath) ?: 'application/octet-stream';

        if (in_array($extension, ['html', 'htm'], true)) {
            $content = file_get_contents($absolutePath) ?: '';
            $content = $this->injectRuntimeBridge($request, $module, $content);

            return response($content, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return response()->file($absolutePath, ['Content-Type' => $mimeType]);
    }

    public function runtime(Request $request, LearningModule $module, ProgressService $progress): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(app(FeedRankingService::class)->isVisibleToUser($user, $module), 404);

        $validated = $request->validate([
            'completion_status' => ['nullable', 'string', 'max:50'],
            'lesson_status' => ['nullable', 'string', 'max:50'],
            'progress_measure' => ['nullable', 'numeric'],
            'score_raw' => ['nullable', 'numeric'],
            'lesson_location' => ['nullable', 'string', 'max:500'],
            'suspend_data' => ['nullable', 'string'],
            'session_time' => ['nullable', 'string', 'max:50'],
            'exit' => ['nullable', 'string', 'max:50'],
            'raw' => ['nullable', 'array'],
        ]);

        $existing = $progress->getProgress($user, $module);
        $percent = $this->derivePercentComplete($validated, (int) ($existing?->percent_complete ?? 0));

        $saved = $progress->updateProgress($user, $module, $percent, [
            'scorm' => [
                'completion_status' => $validated['completion_status'] ?? null,
                'lesson_status' => $validated['lesson_status'] ?? null,
                'progress_measure' => isset($validated['progress_measure']) ? (float) $validated['progress_measure'] : null,
                'score_raw' => isset($validated['score_raw']) ? (float) $validated['score_raw'] : null,
                'lesson_location' => $validated['lesson_location'] ?? null,
                'suspend_data' => $validated['suspend_data'] ?? null,
                'session_time' => $validated['session_time'] ?? null,
                'exit' => $validated['exit'] ?? null,
            ],
            'raw' => $validated['raw'] ?? [],
        ]);

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'percent_complete' => $saved->percent_complete,
                'status' => $saved->status,
                'completion_status' => $validated['completion_status'] ?? null,
                'lesson_status' => $validated['lesson_status'] ?? null,
                'score_raw' => isset($validated['score_raw']) ? (float) $validated['score_raw'] : null,
                'session_time' => $validated['session_time'] ?? null,
                'session_seconds' => ScormRuntimeMetrics::parseSessionSeconds($validated['session_time'] ?? null),
                'lesson_location' => $validated['lesson_location'] ?? null,
            ],
        ]);

        return response()->json([
            'data' => [
                'status' => $saved->status,
                'percent_complete' => $saved->percent_complete,
            ],
        ]);
    }

    private function scormAsset(LearningModule $module): LearningAsset
    {
        return $module->latestScormAsset() ?? abort(404);
    }

    private function derivePercentComplete(array $payload, int $existingPercent): int
    {
        $completionStatus = strtolower((string) ($payload['completion_status'] ?? ''));
        $lessonStatus = strtolower((string) ($payload['lesson_status'] ?? ''));

        if (in_array($completionStatus, ['completed', 'passed'], true) || in_array($lessonStatus, ['completed', 'passed'], true)) {
            return 100;
        }

        if (isset($payload['progress_measure'])) {
            $measure = (float) $payload['progress_measure'];
            $percent = $measure <= 1 ? (int) round($measure * 100) : (int) round($measure);

            return max($existingPercent, max(0, min(100, $percent)));
        }

        if (($payload['lesson_location'] ?? null) !== null || ($payload['suspend_data'] ?? null) !== null) {
            return max($existingPercent, 10);
        }

        return $existingPercent;
    }

    private function injectRuntimeBridge(Request $request, LearningModule $module, string $content): string
    {
        $progress = auth()->user()?->moduleProgress()
            ->where('learning_module_id', $module->id)
            ->first();
        $resumeState = $progress?->last_position['scorm'] ?? [];

        $endpoint = route('app.modules.scorm.runtime', ['module' => $module->id]);
        $csrf = csrf_token();
        $state = json_encode($resumeState, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $bridge = <<<HTML
<script>
(() => {
  const endpoint = {$this->json($endpoint)};
  const csrf = {$this->json($csrf)};
  const state = Object.assign({
    completion_status: null,
    lesson_status: null,
    progress_measure: null,
    score_raw: null,
    lesson_location: null,
    suspend_data: null,
    session_time: null,
    exit: null,
  }, {$state});

  const mapGet = (key) => {
    const values = {
      'cmi.core.lesson_status': state.lesson_status ?? '',
      'cmi.core.score.raw': state.score_raw ?? '',
      'cmi.core.lesson_location': state.lesson_location ?? '',
      'cmi.suspend_data': state.suspend_data ?? '',
      'cmi.completion_status': state.completion_status ?? '',
      'cmi.progress_measure': state.progress_measure ?? '',
      'cmi.location': state.lesson_location ?? '',
      'cmi.session_time': state.session_time ?? '',
      'cmi.exit': state.exit ?? '',
    };
    return values[key] ?? '';
  };

  const mapSet = (key, value) => {
    const mappings = {
      'cmi.core.lesson_status': 'lesson_status',
      'cmi.core.score.raw': 'score_raw',
      'cmi.core.lesson_location': 'lesson_location',
      'cmi.suspend_data': 'suspend_data',
      'cmi.completion_status': 'completion_status',
      'cmi.progress_measure': 'progress_measure',
      'cmi.location': 'lesson_location',
      'cmi.session_time': 'session_time',
      'cmi.exit': 'exit',
    };
    if (mappings[key]) state[mappings[key]] = value;
    return 'true';
  };

  const commit = () => fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf,
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      completion_status: state.completion_status,
      lesson_status: state.lesson_status,
      progress_measure: state.progress_measure,
      score_raw: state.score_raw,
      lesson_location: state.lesson_location,
      suspend_data: state.suspend_data,
      session_time: state.session_time,
      exit: state.exit,
      raw: state,
    }),
  }).catch(() => null);

  window.API = {
    LMSInitialize: () => 'true',
    LMSFinish: () => { commit(); return 'true'; },
    LMSGetValue: (key) => mapGet(key),
    LMSSetValue: (key, value) => mapSet(key, value),
    LMSCommit: () => { commit(); return 'true'; },
    LMSGetLastError: () => '0',
    LMSGetErrorString: () => 'No error',
    LMSGetDiagnostic: () => '',
  };

  window.API_1484_11 = {
    Initialize: () => 'true',
    Terminate: () => { commit(); return 'true'; },
    GetValue: (key) => mapGet(key),
    SetValue: (key, value) => mapSet(key, value),
    Commit: () => { commit(); return 'true'; },
    GetLastError: () => '0',
    GetErrorString: () => 'No error',
    GetDiagnostic: () => '',
  };
})();
</script>
HTML;

        if (stripos($content, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $bridge.'</head>', $content, 1) ?: $bridge.$content;
        }

        return $bridge.$content;
    }

    private function json(string $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '""';
    }
}
