<div class="{{ $class ?? 'rounded border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-900' }}">
    <div class="font-semibold">{{ $failure['label'] }}</div>
    <div class="mt-1 text-xs text-amber-800">count {{ $failure['count'] }}; providers {{ implode(', ', $failure['providers']) ?: 'n/a' }}</div>
    @if (!empty($failure['sources']))
        <div class="mt-1 text-xs text-amber-800">sources {{ implode(', ', $failure['sources']) }}</div>
    @endif
    <div class="mt-1 text-xs text-amber-700">{{ $failure['message'] }}</div>
</div>
