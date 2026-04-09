@php
    $barItems = collect($items ?? [])
        ->map(function ($item) {
            return [
                'label' => $item['label'] ?? 'Item',
                'value' => max(0, (int) ($item['value'] ?? 0)),
                'color' => $item['color'] ?? '#0ea5e9',
                'meta' => $item['meta'] ?? null,
            ];
        })
        ->values();

    $barMax = max(1, (int) $barItems->max('value'));
@endphp

<div class="{{ $cardClass ?? 'card adminuiux-card shadow-sm p-4' }}">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div>
            <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.24em;font-size:.72rem;">{{ $eyebrow ?? 'Visual Report' }}</div>
            <h3 class="fs-5 fw-semibold mb-1">{{ $title }}</h3>
            @if (!empty($subtitle))
                <p class="text-secondary small mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if (!empty($badge))
            <span class="badge bg-light text-secondary border" style="letter-spacing:.18em;font-size:.68rem;">{{ $badge }}</span>
        @endif
    </div>

    <div class="mt-4 d-flex flex-column gap-3">
        @forelse ($barItems as $item)
            @php($width = (int) round(($item['value'] / $barMax) * 100))
            <div>
                <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                    <div class="text-truncate">
                        <div class="fw-semibold small">{{ $item['label'] }}</div>
                        @if (!empty($item['meta']))
                            <div class="text-secondary" style="font-size:.75rem;">{{ $item['meta'] }}</div>
                        @endif
                    </div>
                    <div class="fw-semibold small flex-shrink-0">{{ $item['value'] }}</div>
                </div>
                <div class="progress" style="height:10px;">
                    <div class="progress-bar" style="width:{{ $width }}%;background-color:{{ $item['color'] }};border-radius:50rem;"></div>
                </div>
            </div>
        @empty
            <div class="border border-dashed rounded-3 bg-light text-secondary text-center small py-4 px-3">
                No report data yet.
            </div>
        @endforelse
    </div>
</div>
