<x-app-layout>
    @push('styles')
        <style>
            .assignment-detail-card {
                border-radius: 1.75rem;
                border: 1px solid rgba(226, 232, 240, 0.9);
                background: rgba(255, 255, 255, 0.96);
                box-shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
            }

            .assignment-detail-band {
                border-radius: 1.5rem;
                background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
            }
        </style>
    @endpush

    <x-slot name="header">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="mb-2">
                    <span class="inline-flex rounded-full border border-sky-300 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700">
                        Assignment Breakdown
                    </span>
                </div>
                <h2 class="font-semibold text-2xl text-slate-900 leading-tight">
                    {{ __('Compliance Area Detail') }}
                </h2>
                <p class="mt-1 text-sm text-slate-600">Compliance area: {{ $complianceArea }}</p>
            </div>
            <a href="{{ route('app.admin.assignments') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50">
                Back to Admin Assignments
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full">
            <div class="assignment-detail-card overflow-hidden">
                <div class="assignment-detail-band border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Required Modules in this Compliance Area</h3>
                    <p class="mt-1 text-sm text-gray-500">Assigned users and urgency by module.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Module</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Refresh Days</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Assigned Users</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Overdue</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Due Soon</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Waived</th>
                                <th class="px-5 py-3 text-left font-medium text-gray-500">Users</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($moduleRows as $row)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $row['title'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['refresh_interval_days'] ?? 'n/a' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['assigned_user_count'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['overdue_count'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['due_soon_count'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $row['waived_count'] }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @if ($row['assigned_users']->isEmpty())
                                            None
                                        @else
                                            {{ $row['assigned_users']->map(fn ($user) => $user['name'].' ['.$user['role'].', '.$user['urgency'].']')->join(', ') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-4 text-gray-500">No modules found for this compliance area.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
