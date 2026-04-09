<div class="space-y-4">
    @if (session('status'))
        <div class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded border border-gray-200 bg-white p-4">
        <h2 class="text-lg font-semibold text-gray-900">Assignment Rules</h2>
        <p class="mt-1 text-sm text-gray-600">Manage role to compliance-area inheritance used for required learning assignment.</p>
    </div>

    <div class="rounded border border-gray-200 bg-white p-4 space-y-4">
        @foreach ($rules as $index => $rule)
            <div class="grid gap-3 rounded border border-gray-200 p-3 md:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
                    <input type="text" wire:model="rules.{{ $index }}.role" class="w-full rounded border-gray-300 text-sm" placeholder="e.g. manager">
                    @error("rules.$index.role") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Compliance Area</label>
                    <input type="text" wire:model="rules.{{ $index }}.compliance_area" class="w-full rounded border-gray-300 text-sm" placeholder="e.g. data-privacy">
                    @error("rules.$index.compliance_area") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <button type="button" wire:click="removeRule({{ $index }})" class="rounded border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                        Remove
                    </button>
                </div>
            </div>
        @endforeach

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" wire:click="addRule" class="inline-flex items-center rounded border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Add Rule
            </button>
            <button type="button" wire:click="save" class="inline-flex items-center rounded bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Save Rules
            </button>
            <span class="text-sm text-gray-500">Last saved: {{ $lastSavedAt ?? 'never' }}</span>
        </div>
    </div>
</div>
