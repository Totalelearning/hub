<?php

namespace App\Services;

use App\Models\LearningModule;
use App\Models\LearningEvent;
use App\Models\ModuleProgress;
use App\Models\User;
use Illuminate\Support\Carbon;

class FeedScoringService
{
    private array $recentTopicCache = [];
    private array $recentModuleEngagementCache = [];
    private array $pathPriorityCache = [];

    public function __construct(
        private readonly AssignmentService $assignments,
        private readonly FeedScoringSettingsService $settings,
        private readonly LearningPathService $paths,
    ) {
    }

    /**
     * Deterministic scoring rules:
     * +75 required and not completed, +50 topic match,
     * +40 role match for role-targeted modules,
     * +35 compliance-area match for required compliance modules,
     * +20 difficulty match (unless user chooses "any"),
     * +10 goal affinity based on keyword overlap between user goal and module text,
     * +90 when a required completed module is due for refresh,
     * +25 when a required completed module is due soon,
     * +30 when module is the next unlocked step in a visible learning path,
     * +15 when module is not completed,
     * +12/9/6 when re-engaged with same module within <=3/<=7/<=14 days,
     * +5 when recently active on same topic.
     */
    public function score(User $user, LearningModule $module, ?ModuleProgress $progress): int
    {
        return $this->scoreWithBreakdown($user, $module, $progress)['score'];
    }

    public function scoreWithBreakdown(User $user, LearningModule $module, ?ModuleProgress $progress): array
    {
        $preference = $user->preference;
        $topics = collect($preference?->topics ?? [])->filter()->map(fn ($v) => strtolower((string) $v));
        $userRole = strtolower(trim((string) ($preference?->role ?? '')));
        $moduleTopic = strtolower((string) ($module->topic ?? ''));
        $userDifficulty = strtolower((string) ($preference?->difficulty ?? 'any'));
        $moduleDifficulty = strtolower((string) ($module->difficulty ?? ''));
        $userGoal = strtolower(trim((string) ($preference?->goal ?? '')));
        $assignment = $this->assignments->forUser($user, $module, $progress);
        $renewal = $assignment['renewal'];
        $roleTargeting = $assignment['role_targeting'];
        $complianceTargeting = $assignment['compliance_targeting'];
        $prerequisites = $assignment['prerequisites'];
        $acknowledgement = $assignment['acknowledgement'];

        $breakdown = [
            'required_module' => 0,
            'renewal_due' => 0,
            'renewal_due_soon' => 0,
            'role_match' => 0,
            'compliance_match' => 0,
            'topic_match' => 0,
            'difficulty_match' => 0,
            'goal_affinity' => 0,
            'path_next_step' => 0,
            'not_completed' => 0,
            'recent_module_reengagement' => 0,
            'recent_topic_activity' => 0,
            'prerequisites_unlocked' => 0,
            'acknowledgement_required' => 0,
        ];
        $explanations = [
            'path_next_step_paths' => [],
            'recent_module_reengagement_days' => null,
        ];

        if ($assignment['is_incomplete_required']) {
            $breakdown['required_module'] = $this->weight('required_module', 75);
        }

        if ($renewal['is_due']) {
            $breakdown['renewal_due'] = $this->weight('renewal_due', 90);
        } elseif ($renewal['is_due_soon']) {
            $breakdown['renewal_due_soon'] = $this->weight('renewal_due_soon', 25);
        }

        if ($roleTargeting['is_targeted'] && $roleTargeting['matches']) {
            $breakdown['role_match'] = $this->weight('role_match', 40);
        }

        if ($complianceTargeting['is_targeted'] && $complianceTargeting['matches']) {
            $breakdown['compliance_match'] = $this->weight('compliance_match', 35);
        }

        if ($moduleTopic !== '' && $topics->contains($moduleTopic)) {
            $breakdown['topic_match'] = $this->weight('topic_match', 50);
        }

        if ($userDifficulty !== '' && $userDifficulty !== 'any' && $moduleDifficulty !== '' && $userDifficulty === $moduleDifficulty) {
            $breakdown['difficulty_match'] = $this->weight('difficulty_match', 20);
        }

        $breakdown['goal_affinity'] = $this->goalAffinityScore($userGoal, $module);
        $breakdown['path_next_step'] = $this->pathPriorityScore($user, (int) $module->id);

        if (($progress?->status ?? 'not_started') !== 'completed') {
            $breakdown['not_completed'] = $this->weight('not_completed', 15);
            $breakdown['recent_module_reengagement'] = $this->recentModuleReengagementScore($user, (int) $module->id);
            if ($breakdown['recent_module_reengagement'] > 0) {
                $explanations['recent_module_reengagement_days'] = $this->recentlyEngagedModuleAges($user)[(int) $module->id] ?? null;
            }
        }

        if ($breakdown['path_next_step'] > 0) {
            $pathDetails = $this->pathPriorityScores($user)[(int) $module->id] ?? null;
            $explanations['path_next_step_paths'] = is_array($pathDetails) ? ($pathDetails['paths'] ?? []) : [];
        }

        $recentTopics = $this->recentTopics($user);
        if ($moduleTopic !== '' && in_array($moduleTopic, $recentTopics, true)) {
            $breakdown['recent_topic_activity'] = $this->weight('recent_topic_activity', 5);
        }

        if ($prerequisites['has_prerequisites'] && $prerequisites['is_unlocked']) {
            $breakdown['prerequisites_unlocked'] = $this->weight('prerequisites_unlocked', 10);
        }

        if ($acknowledgement['is_required'] && ! $acknowledgement['is_acknowledged']) {
            $breakdown['acknowledgement_required'] = $this->weight('acknowledgement_required', 20);
        }

        return [
            'score' => array_sum($breakdown),
            'breakdown' => $breakdown,
            'renewal' => $renewal,
            'role_targeting' => $roleTargeting,
            'compliance_targeting' => $complianceTargeting,
            'prerequisites' => $prerequisites,
            'acknowledgement' => $acknowledgement,
            'assignment' => $assignment,
            'explanations' => $explanations,
            'highlights' => $this->buildHighlights($breakdown, $explanations),
        ];
    }

    private function recentModuleReengagementScore(User $user, int $moduleId): int
    {
        $engagementAges = $this->recentlyEngagedModuleAges($user);
        if (! array_key_exists($moduleId, $engagementAges)) {
            return 0;
        }

        $days = $engagementAges[$moduleId];
        $baseWeight = $this->weight('recent_module_reengagement', 12);
        $fullDays = max(0, $this->weight('recent_module_reengagement_full_days', 3));
        $midDays = max($fullDays, $this->weight('recent_module_reengagement_mid_days', 7));
        $windowDays = max($midDays, $this->weight('recent_module_reengagement_window_days', 14));

        if ($days <= $fullDays) {
            return $baseWeight;
        }

        if ($days <= $midDays) {
            return (int) round($baseWeight * 0.75);
        }

        if ($days <= $windowDays) {
            return (int) round($baseWeight * 0.5);
        }

        return 0;
    }

    private function recentlyEngagedModuleAges(User $user): array
    {
        if (array_key_exists($user->id, $this->recentModuleEngagementCache)) {
            return $this->recentModuleEngagementCache[$user->id];
        }

        $windowDays = max(1, $this->weight('recent_module_reengagement_window_days', 14));

        $ages = LearningEvent::query()
            ->selectRaw('entity_id, MAX(created_at) as latest_event_at')
            ->where('user_id', $user->id)
            ->where('entity_type', 'learning_module')
            ->whereIn('event_type', ['module_viewed', 'module_saved', 'module_progress_updated'])
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->groupBy('entity_id')
            ->get()
            ->mapWithKeys(function ($row): array {
                $moduleId = (int) $row->entity_id;
                if ($moduleId <= 0) {
                    return [];
                }

                $latest = $row->latest_event_at ? Carbon::parse((string) $row->latest_event_at) : null;
                if (! $latest) {
                    return [];
                }

                $days = $latest->startOfDay()->diffInDays(now()->startOfDay(), false);
                if ($days < 0) {
                    $days = 0;
                }

                return [$moduleId => $days];
            })
            ->all();

        $this->recentModuleEngagementCache[$user->id] = $ages;

        return $ages;
    }

    public function isVisibleToUser(User $user, LearningModule $module): bool
    {
        return $this->assignments->isVisibleToUser($user, $module);
    }

    public function renewalStatus(LearningModule $module, ?ModuleProgress $progress): array
    {
        if (! $module->is_required || ! $module->refresh_interval_days || ! $progress?->completed_at) {
            return [
                'is_due' => false,
                'is_due_soon' => false,
                'days_until_due' => null,
                'due_at' => null,
            ];
        }

        $dueAt = $progress->completed_at->copy()->addDays((int) $module->refresh_interval_days);
        $daysUntilDue = (int) Carbon::now()->startOfDay()->diffInDays($dueAt->copy()->startOfDay(), false);

        return [
            'is_due' => $daysUntilDue <= 0,
            'is_due_soon' => $daysUntilDue > 0 && $daysUntilDue <= 7,
            'days_until_due' => $daysUntilDue,
            'due_at' => $dueAt,
        ];
    }

    private function recentTopics(User $user): array
    {
        if (array_key_exists($user->id, $this->recentTopicCache)) {
            return $this->recentTopicCache[$user->id];
        }

        $windowDays = max(1, $this->weight('recent_topic_activity_window_days', 7));

        $topics = ModuleProgress::query()
            ->join('learning_modules', 'learning_modules.id', '=', 'module_progress.learning_module_id')
            ->where('module_progress.user_id', $user->id)
            ->whereNotNull('module_progress.last_activity_at')
            ->where('module_progress.last_activity_at', '>=', now()->subDays($windowDays))
            ->whereNotNull('learning_modules.topic')
            ->pluck('learning_modules.topic')
            ->map(fn ($topic) => strtolower((string) $topic))
            ->unique()
            ->values()
            ->all();

        $eventTopics = LearningEvent::query()
            ->join('learning_modules', function ($join): void {
                $join->on('learning_modules.id', '=', 'learning_events.entity_id')
                    ->where('learning_events.entity_type', '=', 'learning_module');
            })
            ->where('learning_events.user_id', $user->id)
            ->whereIn('learning_events.event_type', ['module_viewed', 'module_saved'])
            ->where('learning_events.created_at', '>=', now()->subDays($windowDays))
            ->whereNotNull('learning_modules.topic')
            ->pluck('learning_modules.topic')
            ->map(fn ($topic) => strtolower((string) $topic))
            ->unique()
            ->values()
            ->all();

        $topics = collect(array_merge($topics, $eventTopics))
            ->unique()
            ->values()
            ->all();

        $this->recentTopicCache[$user->id] = $topics;

        return $topics;
    }

    private function goalAffinityScore(string $userGoal, LearningModule $module): int
    {
        $goalKeywords = $this->goalKeywords($userGoal);
        if ($goalKeywords === []) {
            return 0;
        }

        $moduleText = strtolower(implode(' ', array_filter([
            $module->title,
            $module->description,
            $module->topic,
            $module->compliance_area,
        ])));

        $matchCount = collect($goalKeywords)
            ->filter(fn (string $keyword) => str_contains($moduleText, $keyword))
            ->count();

        $perKeyword = $this->weight('goal_affinity_per_keyword', 5);
        $maxGoalScore = max(0, $this->weight('goal_affinity_max', 10));

        return min($maxGoalScore, $matchCount * $perKeyword);
    }

    private function goalKeywords(string $goal): array
    {
        if ($goal === '') {
            return [];
        }

        $stopwords = [
            'the', 'and', 'for', 'with', 'from', 'that', 'this', 'your', 'into',
            'about', 'have', 'will', 'would', 'should', 'could', 'than', 'then',
            'been', 'being', 'into', 'over', 'under', 'after', 'before', 'through',
        ];

        return collect(preg_split('/[^a-z0-9]+/i', $goal) ?: [])
            ->map(fn ($token) => strtolower(trim((string) $token)))
            ->filter(fn ($token) => strlen($token) >= 4 && ! in_array($token, $stopwords, true))
            ->unique()
            ->values()
            ->all();
    }

    private function weight(string $key, int $default): int
    {
        return $this->settings->weight($key, $default);
    }

    private function buildHighlights(array $breakdown, array $explanations): array
    {
        $positive = collect($breakdown)
            ->filter(fn ($score) => (int) $score > 0)
            ->sortByDesc(fn ($score) => (int) $score);

        return $positive
            ->map(function (int $score, string $key) use ($explanations): ?array {
                $label = match ($key) {
                    'required_module' => 'Required assignment',
                    'renewal_due' => 'Refresh due now',
                    'renewal_due_soon' => 'Refresh due soon',
                    'role_match' => 'Matches your role',
                    'compliance_match' => 'Compliance area match',
                    'topic_match' => 'Topic preference match',
                    'difficulty_match' => 'Difficulty preference match',
                    'goal_affinity' => 'Goal alignment',
                    'path_next_step' => ! empty($explanations['path_next_step_paths'])
                        ? 'Next step in path: ' . (string) $explanations['path_next_step_paths'][0]
                        : 'Next step in learning path',
                    'not_completed' => 'Not completed yet',
                    'recent_module_reengagement' => ($explanations['recent_module_reengagement_days'] ?? null) !== null
                        ? 'You engaged ' . $explanations['recent_module_reengagement_days'] . ' day(s) ago'
                        : 'Recently engaged with this module',
                    'recent_topic_activity' => 'Recent activity in this topic',
                    'prerequisites_unlocked' => 'Prerequisites completed',
                    'acknowledgement_required' => 'Acknowledgement pending',
                    default => null,
                };

                if ($label === null) {
                    return null;
                }

                return [
                    'key' => $key,
                    'score' => $score,
                    'label' => $label,
                ];
            })
            ->filter()
            ->take(4)
            ->values()
            ->all();
    }

    private function pathPriorityScore(User $user, int $moduleId): int
    {
        $scores = $this->pathPriorityScores($user);

        return (int) (($scores[$moduleId]['score'] ?? 0));
    }

    private function pathPriorityScores(User $user): array
    {
        if (array_key_exists($user->id, $this->pathPriorityCache)) {
            return $this->pathPriorityCache[$user->id];
        }

        $nextStepWeight = $this->weight('path_next_step', 30);
        $scores = [];

        foreach ($this->paths->visiblePathsForUser($user) as $path) {
            $nextStep = $this->paths->stepStates($user, $path)
                ->first(fn (array $state) => ! $state['is_completed'] && $state['is_unlocked']);

            if (! $nextStep || ! isset($nextStep['module'])) {
                continue;
            }

            $moduleId = (int) $nextStep['module']->id;
            if ($moduleId <= 0) {
                continue;
            }

            if (! isset($scores[$moduleId])) {
                $scores[$moduleId] = [
                    'score' => 0,
                    'paths' => [],
                ];
            }

            $scores[$moduleId]['score'] = max((int) $scores[$moduleId]['score'], $nextStepWeight);
            $scores[$moduleId]['paths'][] = (string) $path->title;
            $scores[$moduleId]['paths'] = array_values(array_unique($scores[$moduleId]['paths']));
        }

        $this->pathPriorityCache[$user->id] = $scores;

        return $scores;
    }
}
