<?php

namespace App\Http\Controllers;

use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Notifications\AssignmentReminderNotification;
use App\Notifications\CourseReinforcementNotification;
use App\Services\ReinforcementService;
use App\Services\LearningPathService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppReminderController extends Controller
{
    public function index(LearningPathService $paths, ReinforcementService $reinforcement): View
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $status = strtolower(trim((string) request()->query('status', 'all')));
        $type = strtolower(trim((string) request()->query('type', 'all')));
        $moduleId = (int) request()->query('module_id', 0);
        if (! in_array($status, ['all', 'read', 'unread'], true)) {
            $status = 'all';
        }

        $query = $user->notifications()
            ->where('type', AssignmentReminderNotification::class)
            ->when($status === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->when($status === 'unread', fn ($q) => $q->whereNull('read_at'));

        $notifications = $query
            ->latest()
            ->get()
            ->when($moduleId > 0, fn ($rows) => $rows->filter(
                fn ($notification) => (int) ($notification->data['module_id'] ?? 0) === $moduleId
            )->values())
            ->when($type !== '' && $type !== 'all', fn ($rows) => $rows->filter(
                fn ($notification) => strtolower((string) ($notification->data['reminder_type'] ?? '')) === $type
            )->values());

        // Course reinforcement notifications (knowledge checks)
        $courseReinforcementNotifications = $user->notifications()
            ->where('type', CourseReinforcementNotification::class)
            ->when($status === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->when($status === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->latest()
            ->get();

        $today = now()->startOfDay();
        $allNotifications = $notifications->concat($courseReinforcementNotifications);
        $summary = [
            'total' => $allNotifications->count(),
            'unread' => $allNotifications->whereNull('read_at')->count(),
            'read' => $allNotifications->whereNotNull('read_at')->count(),
            'overdue' => $notifications->filter(function ($notification) use ($today) {
                $dueOn = $notification->data['due_on'] ?? null;
                if (! is_string($dueOn) || $dueOn === '') {
                    return false;
                }

                return Carbon::parse($dueOn)->startOfDay()->lt($today);
            })->count(),
            'due_soon' => $notifications->filter(function ($notification) use ($today) {
                $dueOn = $notification->data['due_on'] ?? null;
                if (! is_string($dueOn) || $dueOn === '') {
                    return false;
                }

                $date = Carbon::parse($dueOn)->startOfDay();

                return $date->betweenIncluded($today, (clone $today)->addDays(7));
            })->count(),
        ];
        $reinforcementTouchpoints = $reinforcement->syncForUser($user);
        $reinforcementSummary = [
            'total' => $reinforcementTouchpoints->count(),
            'due' => $reinforcementTouchpoints->where('computed_status', 'due')->count(),
            'pending' => $reinforcementTouchpoints->where('computed_status', 'pending')->count(),
            'completed' => $reinforcementTouchpoints->where('computed_status', 'completed')->count(),
        ];

        $latestCompletedProgress = ModuleProgress::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->first();

        $latestCompletionSummary = null;
        $completionNextActions = collect();
        if ($latestCompletedProgress) {
            $module = LearningModule::query()->find($latestCompletedProgress->learning_module_id);
            $runtimeEvent = LearningEvent::query()
                ->where('user_id', $user->id)
                ->where('event_type', 'scorm_runtime_committed')
                ->where('entity_type', 'learning_module')
                ->where('entity_id', $latestCompletedProgress->learning_module_id)
                ->latest('id')
                ->first();

            $latestCompletionSummary = [
                'module' => $module,
                'module_title' => $module?->title ?? ('Module #'.$latestCompletedProgress->learning_module_id),
                'completed_at' => $latestCompletedProgress->completed_at,
                'percent_complete' => (int) $latestCompletedProgress->percent_complete,
                'status' => $runtimeEvent?->metadata['status'] ?? ($runtimeEvent?->metadata['lesson_status'] ?? $latestCompletedProgress->status),
            ];

            $activePath = $paths->visiblePathsForUser($user)
                ->map(function ($path) use ($paths, $user) {
                    $path->setAttribute('step_states', $paths->stepStates($user, $path));
                    $path->setAttribute(
                        'next_step',
                        collect($path->step_states)->first(fn (array $state) => ! $state['is_completed'] && $state['is_unlocked'])
                    );

                    return $path;
                })
                ->first(fn ($path) => $path->next_step);

            if ($activePath?->next_step['module'] ?? null) {
                $completionNextActions->push([
                    'label' => 'Next path step',
                    'title' => $activePath->next_step['module']->title,
                    'summary' => 'Move straight into the next unlocked step from your current learning path.',
                    'href' => route('app.modules.show', ['module' => $activePath->next_step['module']->id]),
                    'cta' => 'Open next step',
                ]);
            }

            $focusReminder = $notifications->firstWhere('read_at', null) ?? $notifications->first();
            if ((int) ($focusReminder->data['module_id'] ?? 0) > 0) {
                $completionNextActions->push([
                    'label' => 'Reminder follow-up',
                    'title' => (string) ($focusReminder->data['module_title'] ?? 'Reminder module'),
                    'summary' => 'Review the most recent reminder-linked module from your learner queue.',
                    'href' => route('app.modules.show', ['module' => (int) $focusReminder->data['module_id']]),
                    'cta' => 'Open reminder module',
                ]);
            }

            $completionNextActions->push([
                'label' => 'Dashboard',
                'title' => 'Return to your learner dashboard',
                'summary' => 'Check your priority rail, saved modules, and current momentum.',
                'href' => route('app.feed'),
                'cta' => 'Back to dashboard',
            ]);
        }

        $completionNextActions = $completionNextActions
            ->unique(fn (array $action) => $action['href'])
            ->take(3)
            ->values();

        return view('app.reminders', [
            'notifications' => $notifications,
            'courseReinforcementNotifications' => $courseReinforcementNotifications,
            'summary' => $summary,
            'reinforcementTouchpoints' => $reinforcementTouchpoints->take(6),
            'reinforcementSummary' => $reinforcementSummary,
            'latestCompletionSummary' => $latestCompletionSummary,
            'completionNextActions' => $completionNextActions,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'module_id' => $moduleId,
            ],
            'availableTypes' => $user->notifications()
                ->where('type', AssignmentReminderNotification::class)
                ->get()
                ->pluck('data.reminder_type')
                ->filter()
                ->map(fn ($value) => strtolower((string) $value))
                ->unique()
                ->sort()
                ->values(),
        ]);
    }

    public function markRead(DatabaseNotification $notification): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 403);
        abort_unless($notification->notifiable_id === $user->id, 403);

        $notification->markAsRead();

        return redirect()
            ->route('app.reminders')
            ->with('status', 'Reminder marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $user->unreadNotifications()
            ->whereIn('type', [
                AssignmentReminderNotification::class,
                CourseReinforcementNotification::class,
            ])
            ->update(['read_at' => now()]);

        return redirect()
            ->route('app.reminders')
            ->with('status', 'All reminders marked as read.');
    }
}
