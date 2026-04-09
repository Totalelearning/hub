<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-full border border-sky-700 bg-sky-700 px-5 py-2.5 font-semibold text-xs uppercase tracking-[0.2em] text-white shadow-sm transition ease-in-out duration-150 hover:bg-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
