@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-start text-base font-semibold text-sky-800 shadow-sm focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full rounded-2xl border border-transparent px-4 py-3 text-start text-base font-medium text-slate-600 hover:border-slate-200 hover:bg-white hover:text-slate-900 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
