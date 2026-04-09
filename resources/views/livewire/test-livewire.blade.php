<div class="space-y-4">
    <div class="rounded border border-gray-200 bg-white p-4">
        <h2 class="text-lg font-semibold text-gray-900">Livewire Test</h2>
        <p class="mt-2 text-sm text-gray-700">Current user: {{ auth()->user()->name }}</p>
    </div>

    <div class="rounded border border-gray-200 bg-white p-4">
        <p class="text-sm text-gray-700">Counter: <span class="font-semibold">{{ $count }}</span></p>
        <button
            type="button"
            wire:click="increment"
            class="mt-3 inline-flex items-center rounded bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
        >
            Increment
        </button>
    </div>
</div>

