<div class="mt-2 flex items-center gap-2">
    <span data-health-scope-badge class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $isFiltered ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }}">
        scope: {{ $isFiltered ? 'filtered' : 'global' }}
    </span>
    <span data-health-filter-count class="text-xs text-gray-500">{{ $filterCount }} active filter{{ $filterCount === 1 ? '' : 's' }}</span>
</div>
<p class="mt-1 text-xs text-gray-500">Provider filters probe rows. Trigger filters severity-transition audit entries.</p>
<p class="mt-1 text-xs text-gray-500" data-health-provider-filter-label-wrapper>Viewing {{ $providerLabel }}.</p>
<p class="mt-1 text-xs text-gray-500" data-health-active-filter-summary>
    <span data-health-active-filter-summary-label>Filters: provider={{ $providerLabel }}, trigger={{ $triggerLabel }}</span>
    <span class="mx-1 text-gray-300">|</span>
    <a href="{{ $apiUrl }}" data-health-summary-open-url class="font-medium text-gray-600 underline decoration-gray-300 underline-offset-2 hover:text-gray-900">
        API
    </a>
    <span class="mx-1 text-gray-300">|</span>
    <a href="{{ $auditUrl }}" data-health-summary-open-audit class="font-medium text-gray-600 underline decoration-gray-300 underline-offset-2 hover:text-gray-900">
        Audit
    </a>
</p>
<p data-health-provider-mismatch class="mt-1 text-xs text-amber-700 {{ $providerMismatchMessage ? '' : 'hidden' }}">
    {{ $providerMismatchMessage }}
</p>
<p class="mt-2 text-xs text-gray-500" {!! $latencyDataAttribute ?? '' !!}>
    {!! $latencySummary !!}
</p>
