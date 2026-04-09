<?php

namespace App\Http\Controllers;

use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLearningEventsReportController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('admin-access');

        $filters = $this->normalizeFilters($request);
        $events = $this->queryEvents($filters);
        $moduleTitles = $this->moduleTitleMap($events);

        return view('app.admin-learning-events-report', [
            'events' => $events,
            'moduleTitles' => $moduleTitles,
            'summary' => $this->summary($events),
            'byType' => $events->groupBy('event_type')->map->count()->sortDesc(),
            'filters' => $filters,
            'eventTypes' => LearningEvent::query()->distinct()->orderBy('event_type')->pluck('event_type'),
            'entityTypes' => LearningEvent::query()->distinct()->orderBy('entity_type')->pluck('entity_type'),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('admin-access');

        $filters = $this->normalizeFilters($request);
        $events = $this->queryEvents($filters);
        $moduleTitles = $this->moduleTitleMap($events);
        $filename = 'learning-events-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($events, $moduleTitles): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['event_id', 'when', 'user_id', 'user_name', 'user_email', 'event_type', 'entity_type', 'entity_id', 'entity_label', 'metadata']);

            foreach ($events as $event) {
                $entityLabel = $event->entity_type === 'learning_module'
                    ? ($moduleTitles[(int) $event->entity_id] ?? 'unknown module')
                    : '';

                fputcsv($handle, [
                    $event->id,
                    $event->created_at?->toDateTimeString(),
                    $event->user_id,
                    $event->user?->name,
                    $event->user?->email,
                    $event->event_type,
                    $event->entity_type,
                    $event->entity_id,
                    $entityLabel,
                    json_encode($event->metadata ?? []),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function normalizeFilters(Request $request): array
    {
        $eventType = trim((string) $request->query('event_type', ''));
        $entityType = trim((string) $request->query('entity_type', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        $userId = trim((string) $request->query('user_id', ''));

        return [
            'event_type' => $eventType !== '' ? $eventType : null,
            'entity_type' => $entityType !== '' ? $entityType : null,
            'from' => $from !== '' ? $from : null,
            'to' => $to !== '' ? $to : null,
            'user_id' => ctype_digit($userId) ? (int) $userId : null,
        ];
    }

    private function queryEvents(array $filters): Collection
    {
        return LearningEvent::query()
            ->with('user')
            ->when($filters['event_type'], fn ($query, $value) => $query->where('event_type', $value))
            ->when($filters['entity_type'], fn ($query, $value) => $query->where('entity_type', $value))
            ->when($filters['user_id'], fn ($query, $value) => $query->where('user_id', $value))
            ->when($filters['from'], fn ($query, $value) => $query->where('created_at', '>=', $value.' 00:00:00'))
            ->when($filters['to'], fn ($query, $value) => $query->where('created_at', '<=', $value.' 23:59:59'))
            ->latest()
            ->limit(300)
            ->get();
    }

    private function moduleTitleMap(Collection $events): array
    {
        $moduleIds = $events
            ->where('entity_type', 'learning_module')
            ->pluck('entity_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($moduleIds->isEmpty()) {
            return [];
        }

        return LearningModule::query()
            ->whereIn('id', $moduleIds)
            ->pluck('title', 'id')
            ->toArray();
    }

    private function summary(Collection $events): array
    {
        return [
            'total' => $events->count(),
            'unique_users' => $events->pluck('user_id')->filter()->unique()->count(),
            'unique_modules' => $events->where('entity_type', 'learning_module')->pluck('entity_id')->unique()->count(),
        ];
    }
}
