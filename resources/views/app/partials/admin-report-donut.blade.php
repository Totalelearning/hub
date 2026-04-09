@php
    $chartItems = collect($items ?? [])
        ->map(function ($item) {
            return [
                'label' => $item['label'] ?? 'Item',
                'value' => max(0, (int) ($item['value'] ?? 0)),
                'color' => $item['color'] ?? '#0ea5e9',
                'meta' => $item['meta'] ?? null,
            ];
        })
        ->filter(fn (array $item) => $item['value'] > 0)
        ->values();

    $chartTotal = (int) ($total ?? $chartItems->sum('value'));
    $chartCenterValue = $centerValue ?? $chartTotal;
    $chartCenterLabel = $centerLabel ?? 'Total';
    $chartGradient = 'conic-gradient(#d7e3f4 0 100%)';
    $centerImage = $centerImage ?? null;
    $levelLabel = $levelLabel ?? null;
    $legendColumns = (int) ($legendColumns ?? 2);

    if ($chartItems->isNotEmpty() && $chartTotal > 0) {
        $position = 0;
        $segments = [];

        foreach ($chartItems as $item) {
            $slice = ($item['value'] / $chartTotal) * 100;
            $next = $position + $slice;
            $segments[] = "{$item['color']} {$position}% {$next}%";
            $position = $next;
        }

        $chartGradient = 'conic-gradient('.implode(', ', $segments).')';
    }
@endphp

<div class="{{ $cardClass ?? 'card adminuiux-card shadow-sm p-4' }}">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div>
            @if (!empty($eyebrow))
                <div class="text-uppercase fw-semibold text-primary mb-2" style="letter-spacing:.24em;font-size:.72rem;">{{ $eyebrow }}</div>
            @endif
            <h3 class="fs-4 fw-semibold mb-1">{{ $title }}</h3>
            @if (!empty($subtitle))
                <p class="text-secondary small mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if (!empty($levelLabel))
            <div class="d-inline-flex align-items-center gap-2 fs-4 fw-medium text-secondary">
                <span class="bi bi-award text-warning" style="font-size:1.4rem;"></span>
                <span>{{ $levelLabel }}</span>
            </div>
        @elseif (!empty($badge))
            <span class="badge bg-light text-secondary border" style="letter-spacing:.18em;font-size:.68rem;">{{ $badge }}</span>
        @endif
    </div>

    <div class="mt-4">
        <div class="d-flex justify-content-center" style="height:280px;">
            <div style="position:relative;display:flex;align-items:center;justify-content:center;width:250px;height:250px;border-radius:50%;background:{{ $chartGradient }};">
                <div style="display:flex;align-items:center;justify-content:center;width:222px;height:222px;border-radius:50%;background:#fff;">
                    @if (!empty($centerImage))
                        <div style="width:160px;height:160px;border-radius:50%;overflow:hidden;background:#f1f5f9;" class="shadow-sm">
                            <img src="{{ $centerImage }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    @else
                        <div style="width:160px;height:160px;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(0,0,0,.06);">
                            <div class="fw-semibold" style="font-size:2.2rem;">{{ $chartCenterValue }}</div>
                            <div class="text-uppercase fw-semibold text-secondary" style="font-size:.68rem;letter-spacing:.18em;margin-top:.25rem;">{{ $chartCenterLabel }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row mt-4 {{ $legendColumns === 1 ? '' : 'row-cols-1 row-cols-md-2' }} g-3">
            @forelse ($chartItems as $item)
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <span style="display:inline-block;width:14px;height:14px;border-radius:50%;flex-shrink:0;background-color:{{ $item['color'] }};"></span>
                            <span class="fw-medium text-truncate">{{ $item['label'] }}</span>
                        </div>
                        <span class="fw-medium text-success flex-shrink-0">
                            @if ($chartTotal > 0)
                                {{ (int) round(($item['value'] / $chartTotal) * 100) }}%
                            @else
                                {{ $item['value'] }}
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="border border-dashed rounded-3 bg-light text-secondary text-center small py-4 px-3">
                        No report data yet.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
