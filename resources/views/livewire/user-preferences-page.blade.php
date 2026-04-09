<div>
    @if (session('status'))
        <div class="alert alert-success rounded-4 mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="row g-4">
        {{-- Left column: form --}}
        <div class="col-lg-8">
            {{-- Topics --}}
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-1">Topics</h5>
                    <p class="small text-secondary mb-3">Choose the themes you want to see more often in recommended and saved learning.</p>

                    @if (count($topicOptions))
                        <div class="row g-2">
                            @foreach ($topicOptions as $option)
                                <div class="col-6 col-md-4">
                                    <label class="d-flex align-items-center gap-2 rounded-3 border bg-light px-3 py-2 small fw-medium" style="cursor:pointer;">
                                        <input type="checkbox" value="{{ $option }}" wire:model="topics" class="form-check-input mt-0">
                                        {{ ucfirst($option) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="small text-secondary mb-0">No module topics available yet.</p>
                    @endif
                    @error('topics.*') <p class="small text-danger mt-2 mb-0">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Difficulty, Role & Goal --}}
            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Details</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="difficulty" class="form-label fw-medium small">Difficulty</label>
                            <select id="difficulty" wire:model="difficulty" class="form-select rounded-3">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="any">Any</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label fw-medium small">Role</label>
                            <input id="role" type="text" wire:model="role" class="form-control rounded-3" placeholder="e.g. specialist, new-starter">
                        </div>
                        <div class="col-12">
                            <label for="goal" class="form-label fw-medium small">Goal</label>
                            <input id="goal" type="text" wire:model="goal" class="form-control rounded-3" placeholder="e.g. complete mandatory learning, improve safeguarding knowledge">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column: info + save --}}
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, rgba(225,239,255,0.95), rgba(232,246,255,0.95));">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-primary mb-3">What changes</h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li class="rounded-3 bg-white border p-3 small text-secondary">Dashboard spotlight and saved learning become more relevant to your chosen topics.</li>
                        <li class="rounded-3 bg-white border p-3 small text-secondary">Difficulty tunes the pace and type of recommendations you see first.</li>
                        <li class="rounded-3 bg-white border p-3 small text-secondary">Role and goal give more context to learning paths, reminders, and module priorities.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <p class="small text-secondary mb-1">Last saved</p>
                    <p class="fw-semibold mb-3">{{ $lastSavedAt ?? 'Never' }}</p>
                    <button type="button" wire:click="save" class="btn btn-theme w-100">
                        Save Preferences
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
