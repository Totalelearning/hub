<?php

namespace App\Http\Controllers;

use App\Models\AiProviderUsage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAiUsageController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('admin-access');

        $validated = $this->validatedFilters($request);
        $filters = [
            'provider' => $validated['provider'] ?? '',
            'capability' => $validated['capability'] ?? '',
            'request_id' => $validated['request_id'] ?? '',
            'success' => array_key_exists('success', $validated) ? $validated['success'] : '',
            'from' => $validated['from'] ?? '',
            'to' => $validated['to'] ?? '',
            'limit' => (int) ($validated['limit'] ?? 20),
        ];
        $limit = $filters['limit'];
        $baseQuery = $this->filteredQuery($validated);
        $summary = [
            'total' => (clone $baseQuery)->count(),
            'successes' => (clone $baseQuery)->where('success', true)->count(),
            'failures' => (clone $baseQuery)->where('success', false)->count(),
            'providers' => (clone $baseQuery)
                ->selectRaw('provider, COUNT(*) as aggregate_count')
                ->groupBy('provider')
                ->orderByDesc('aggregate_count')
                ->orderBy('provider')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'label' => (string) $row->provider,
                    'count' => (int) $row->aggregate_count,
                ])
                ->all(),
            'capabilities' => (clone $baseQuery)
                ->selectRaw('capability, COUNT(*) as aggregate_count')
                ->groupBy('capability')
                ->orderByDesc('aggregate_count')
                ->orderBy('capability')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'label' => (string) $row->capability,
                    'count' => (int) $row->aggregate_count,
                ])
                ->all(),
        ];
        $rows = $baseQuery
            ->orderByDesc('created_at')
            ->paginate($limit)
            ->appends($request->query());
        $activeFilters = array_filter([
            'provider' => $filters['provider'],
            'capability' => $filters['capability'],
            'request_id' => $filters['request_id'],
            'success' => match ($filters['success']) {
                '1' => 'success',
                '0' => 'failure',
                default => '',
            },
            'from' => $filters['from'],
            'to' => $filters['to'],
        ], fn ($value) => $value !== '');

        return view('app.admin-ai-usages', [
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
            'activeFilters' => $activeFilters,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('admin-access');

        $validated = $this->validatedFilters($request);
        $rows = $this->filteredQuery($validated)
            ->orderByDesc('created_at')
            ->limit(1000)
            ->get();

        $filename = 'ai-usage-records-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['when', 'provider', 'capability', 'status', 'latency_ms', 'request_id', 'model', 'error_type', 'message']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->created_at?->format('Y-m-d H:i:s'),
                    $row->provider,
                    $row->capability,
                    $row->success ? 'success' : 'failure',
                    $row->latency_ms,
                    $row->request_id,
                    $row->model,
                    $row->error_type,
                    $row->metadata['message'] ?? $row->error_message,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'provider' => ['nullable', 'string', 'max:255'],
            'capability' => ['nullable', 'string', 'max:255'],
            'request_id' => ['nullable', 'string', 'max:255'],
            'success' => ['nullable', 'in:0,1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
    }

    private function filteredQuery(array $validated)
    {
        return AiProviderUsage::query()
            ->when(! empty($validated['provider']), fn ($query) => $query->where('provider', $validated['provider']))
            ->when(! empty($validated['capability']), fn ($query) => $query->where('capability', $validated['capability']))
            ->when(! empty($validated['request_id']), fn ($query) => $query->where('request_id', $validated['request_id']))
            ->when(array_key_exists('success', $validated), fn ($query) => $query->where('success', $validated['success'] === '1'))
            ->when(! empty($validated['from']), fn ($query) => $query->where('created_at', '>=', $validated['from']))
            ->when(! empty($validated['to']), fn ($query) => $query->where('created_at', '<=', $validated['to']));
    }
}
