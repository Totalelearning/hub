@if ($bulkStatusBanner['preset'])
    <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-full border px-3 py-1 font-semibold {{ $bulkStatusBanner['styles']['badge'] }}">
            {{ $bulkStatusBanner['preset'] }}
        </span>
        <span>{{ $bulkStatusBanner['message'] }}</span>
    </div>
@else
    {{ $bulkStatusBanner['message'] }}
@endif
