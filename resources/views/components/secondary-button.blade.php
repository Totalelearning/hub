<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-full border border-slate-300 bg-white px-5 py-2.5 font-semibold text-xs uppercase tracking-[0.2em] text-slate-700 shadow-sm transition ease-in-out duration-150 hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
