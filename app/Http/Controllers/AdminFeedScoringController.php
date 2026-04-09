<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAuditEvent;
use App\Services\FeedScoringSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminFeedScoringController extends Controller
{
    public function edit(FeedScoringSettingsService $settings): View
    {
        Gate::authorize('admin-access');

        $weights = $settings->allWeights();
        $defaults = $settings->defaults();
        $meta = $this->fieldMetadata();
        $groups = [
            'Core Priorities' => ['required_module', 'renewal_due', 'renewal_due_soon', 'path_next_step'],
            'Personalization Fit' => ['role_match', 'compliance_match', 'topic_match', 'difficulty_match', 'goal_affinity_per_keyword', 'goal_affinity_max'],
            'Activity Signals' => ['not_completed', 'recent_module_reengagement', 'recent_topic_activity', 'prerequisites_unlocked', 'acknowledgement_required'],
            'Activity Windows (Days)' => ['recent_module_reengagement_full_days', 'recent_module_reengagement_mid_days', 'recent_module_reengagement_window_days', 'recent_topic_activity_window_days'],
        ];

        $grouped = [];
        foreach ($groups as $groupName => $keys) {
            $rows = collect($keys)
                ->filter(fn (string $key) => array_key_exists($key, $weights))
                ->map(function (string $key) use ($weights, $defaults, $meta): array {
                    return [
                        'key' => $key,
                        'value' => (int) ($weights[$key] ?? 0),
                        'default' => (int) ($defaults[$key] ?? 0),
                        'label' => $meta[$key]['label'] ?? str_replace('_', ' ', $key),
                        'help' => $meta[$key]['help'] ?? null,
                    ];
                })
                ->values()
                ->all();

            if ($rows !== []) {
                $grouped[] = [
                    'name' => $groupName,
                    'rows' => $rows,
                ];
            }
        }

        $covered = collect($grouped)->flatMap(fn (array $group) => collect($group['rows'])->pluck('key'))->all();
        $remaining = collect($weights)
            ->keys()
            ->reject(fn (string $key) => in_array($key, $covered, true))
            ->values();
        if ($remaining->isNotEmpty()) {
            $grouped[] = [
                'name' => 'Additional Settings',
                'rows' => $remaining->map(fn (string $key): array => [
                    'key' => $key,
                    'value' => (int) ($weights[$key] ?? 0),
                    'default' => (int) ($defaults[$key] ?? 0),
                    'label' => $meta[$key]['label'] ?? str_replace('_', ' ', $key),
                    'help' => $meta[$key]['help'] ?? null,
                ])->all(),
            ];
        }

        return view('app.admin-feed-scoring', [
            'weights' => $weights,
            'defaults' => $defaults,
            'groups' => $grouped,
            'presets' => $settings->presets(),
            'currentPreset' => $settings->detectCurrentPreset($weights),
        ]);
    }

    public function update(Request $request, FeedScoringSettingsService $settings): RedirectResponse
    {
        Gate::authorize('admin-access');

        $defaults = $settings->defaults();
        $rules = ['weights' => ['required', 'array']];
        foreach (array_keys($defaults) as $key) {
            $rules["weights.{$key}"] = ['required', 'integer', 'min:0', 'max:500'];
        }

        $validated = $request->validate($rules);
        $windowErrors = $this->validateRecencyWindows($validated['weights']);
        if ($windowErrors->isNotEmpty()) {
            return redirect()
                ->route('app.admin.scoring.edit')
                ->withErrors($windowErrors)
                ->withInput();
        }

        $before = $settings->allWeights();
        $settings->update($validated['weights']);
        $this->recordAuditEvent('feed_scoring_settings_updated', [
            'changed_keys' => collect($validated['weights'])
                ->filter(fn ($value, $key) => (int) $value !== (int) ($before[$key] ?? 0))
                ->keys()
                ->values()
                ->all(),
            'values' => collect($validated['weights'])->map(fn ($value) => (int) $value)->all(),
        ]);

        return redirect()
            ->route('app.admin.scoring.edit')
            ->with('status', 'Feed scoring weights saved.');
    }

    public function reset(FeedScoringSettingsService $settings): RedirectResponse
    {
        Gate::authorize('admin-access');

        $before = $settings->allWeights();
        $settings->resetToDefaults();
        $this->recordAuditEvent('feed_scoring_settings_reset', [
            'previous_values' => $before,
        ]);

        return redirect()
            ->route('app.admin.scoring.edit')
            ->with('status', 'Feed scoring weights reset to defaults.');
    }

    public function applyPreset(Request $request, FeedScoringSettingsService $settings): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'preset' => ['required', 'string', 'max:100'],
        ]);

        $before = $settings->allWeights();
        $preset = $settings->applyPreset((string) $validated['preset']);

        if (! $preset) {
            return redirect()
                ->route('app.admin.scoring.edit')
                ->withErrors(['preset' => 'Unknown preset selected.']);
        }

        $after = $settings->allWeights();
        $this->recordAuditEvent('feed_scoring_preset_applied', [
            'preset' => $preset['key'],
            'preset_label' => $preset['label'],
            'changed_keys' => collect($after)
                ->filter(fn ($value, $key) => (int) $value !== (int) ($before[$key] ?? 0))
                ->keys()
                ->values()
                ->all(),
            'values' => $after,
        ]);

        return redirect()
            ->route('app.admin.scoring.edit')
            ->with('status', 'Feed scoring preset applied: '.$preset['label'].'.');
    }

    private function recordAuditEvent(string $action, array $meta = []): void
    {
        if (! Schema::hasTable('assignment_audit_events')) {
            return;
        }

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => auth()->id(),
            'target_user_id' => null,
            'learning_module_id' => null,
            'entity_type' => 'feed_scoring_settings',
            'entity_id' => null,
            'action' => $action,
            'meta' => $meta,
        ]);
    }

    private function fieldMetadata(): array
    {
        return [
            'required_module' => ['label' => 'Required Module Priority', 'help' => 'Boost for required modules not yet completed.'],
            'renewal_due' => ['label' => 'Renewal Due Priority', 'help' => 'Boost when a required completed module is due now.'],
            'renewal_due_soon' => ['label' => 'Renewal Due Soon Priority', 'help' => 'Boost when a required completed module is due soon.'],
            'path_next_step' => ['label' => 'Path Next Step Priority', 'help' => 'Boost when module is next unlocked step in a visible learning path.'],
            'role_match' => ['label' => 'Role Match', 'help' => 'Boost when module target role matches learner role.'],
            'compliance_match' => ['label' => 'Compliance Match', 'help' => 'Boost for required compliance modules matching role scope.'],
            'topic_match' => ['label' => 'Topic Match', 'help' => 'Boost when module topic is in learner topic preferences.'],
            'difficulty_match' => ['label' => 'Difficulty Match', 'help' => 'Boost when module difficulty matches learner preference.'],
            'goal_affinity_per_keyword' => ['label' => 'Goal Affinity Per Keyword', 'help' => 'Points per goal keyword found in module text.'],
            'goal_affinity_max' => ['label' => 'Goal Affinity Max', 'help' => 'Upper cap for goal affinity score.'],
            'not_completed' => ['label' => 'Not Completed', 'help' => 'Baseline boost for modules not completed by learner.'],
            'recent_module_reengagement' => ['label' => 'Recent Module Re-engagement', 'help' => 'Boost when learner recently engaged this same module.'],
            'recent_topic_activity' => ['label' => 'Recent Topic Activity', 'help' => 'Boost when learner recently engaged modules in the same topic.'],
            'prerequisites_unlocked' => ['label' => 'Prerequisites Unlocked', 'help' => 'Boost when module prerequisites are satisfied.'],
            'acknowledgement_required' => ['label' => 'Acknowledgement Required', 'help' => 'Boost when required acknowledgement is still pending.'],
            'recent_module_reengagement_full_days' => ['label' => 'Re-engagement Full Window (days)', 'help' => 'Days for full re-engagement score.'],
            'recent_module_reengagement_mid_days' => ['label' => 'Re-engagement Mid Window (days)', 'help' => 'Days for mid re-engagement score tier.'],
            'recent_module_reengagement_window_days' => ['label' => 'Re-engagement Max Window (days)', 'help' => 'Maximum age in days for re-engagement scoring.'],
            'recent_topic_activity_window_days' => ['label' => 'Topic Activity Window (days)', 'help' => 'Maximum age in days for recent topic activity scoring.'],
        ];
    }

    private function validateRecencyWindows(array $weights): MessageBag
    {
        $errors = new MessageBag();

        $full = (int) ($weights['recent_module_reengagement_full_days'] ?? 0);
        $mid = (int) ($weights['recent_module_reengagement_mid_days'] ?? 0);
        $window = (int) ($weights['recent_module_reengagement_window_days'] ?? 0);

        if ($full > $mid) {
            $errors->add('weights.recent_module_reengagement_mid_days', 'Re-engagement mid window must be greater than or equal to full window.');
        }

        if ($mid > $window) {
            $errors->add('weights.recent_module_reengagement_window_days', 'Re-engagement max window must be greater than or equal to mid window.');
        }

        return $errors;
    }
}
