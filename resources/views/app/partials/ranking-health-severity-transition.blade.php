<div class="{{ $class ?? 'rounded border border-gray-200 bg-white px-4 py-3' }}">
    <div class="flex items-center justify-between gap-3">
        <div class="text-sm font-semibold text-gray-900">
            {{ $transition['before_label'] ?? ($transition['before_level'] ?? 'unknown') }}
            <span class="text-gray-400">-></span>
            {{ $transition['after_label'] ?? ($transition['after_level'] ?? 'unknown') }}
        </div>
        <div class="text-xs text-gray-500">
            {{ !empty($transition['created_at']) ? \Illuminate\Support\Carbon::parse($transition['created_at'])->format('Y-m-d H:i') : 'n/a' }}
        </div>
    </div>
    <div class="mt-1 text-xs text-gray-500">
        trigger {{ $transition['trigger'] ?? 'n/a' }} by {{ $transition['actor_name'] ?? 'system' }}
    </div>
    @if (!empty($transition['after_reason']))
        <div class="mt-1 text-xs text-gray-500">{{ $transition['after_reason'] }}</div>
    @endif
</div>
