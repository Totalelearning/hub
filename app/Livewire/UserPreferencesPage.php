<?php

namespace App\Livewire;

use App\Models\LearningEvent;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class UserPreferencesPage extends Component
{
    public array $topics = [];
    public ?string $role = null;
    public ?string $goal = null;
    public string $difficulty = 'any';
    public ?string $lastSavedAt = null;
    public array $topicOptions = [];

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $this->topicOptions = \App\Models\LearningModule::query()
            ->whereNotNull('topic')
            ->pluck('topic')
            ->filter()
            ->map(fn ($topic) => strtolower((string) $topic))
            ->unique()
            ->values()
            ->all();

        $preference = $user->preference;
        if ($preference) {
            Gate::authorize('view', $preference);
            $this->topics = $preference->topics ?? [];
            $this->role = $preference->role;
            $this->goal = $preference->goal;
            $this->difficulty = (string) ($preference->difficulty ?: 'any');
            $this->lastSavedAt = optional($preference->updated_at)->toDateTimeString();
        }
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $validated = $this->validate([
            'topics' => ['array'],
            'topics.*' => ['string', 'max:100'],
            'role' => ['nullable', 'string', 'max:255'],
            'goal' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced,any'],
        ]);

        $payload = [
            'topics' => array_values(array_unique(array_map(
                fn ($topic) => strtolower(trim((string) $topic)),
                $validated['topics'] ?? []
            ))),
            'role' => $validated['role'] ?? null,
            'goal' => $validated['goal'] ?? null,
            'difficulty' => $validated['difficulty'],
        ];

        $existing = $user->preference;

        if ($existing) {
            Gate::authorize('update', $existing);
            $existing->update($payload);
            $preference = $existing->fresh();
        } else {
            Gate::authorize('create', UserPreference::class);
            $preference = UserPreference::query()->create(array_merge($payload, ['user_id' => $user->id]));
        }

        $this->lastSavedAt = optional($preference->updated_at)->toDateTimeString();
        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'preferences_saved',
            'entity_type' => 'user_preference',
            'entity_id' => (int) $preference->id,
            'metadata' => [
                'topics_count' => count($payload['topics']),
                'difficulty' => $payload['difficulty'],
                'role' => $payload['role'],
            ],
        ]);
        session()->flash('status', 'Preferences saved.');
    }

    public function render()
    {
        return view('livewire.user-preferences-page');
    }
}
