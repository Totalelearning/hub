@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-2 text-sm font-semibold leading-5 text-sky-800 shadow-sm focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-full border border-transparent px-3 py-2 text-sm font-medium leading-5 text-slate-600 hover:border-slate-200 hover:bg-white hover:text-slate-900 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
