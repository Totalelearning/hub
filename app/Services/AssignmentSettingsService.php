<?php

namespace App\Services;

use App\Models\AssignmentSetting;
use Illuminate\Support\Facades\Schema;

class AssignmentSettingsService
{
    public function all(): array
    {
        $defaults = $this->defaults();

        if (! Schema::hasTable('assignment_settings')) {
            return $defaults;
        }

        $overrides = AssignmentSetting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->map(fn ($value) => (int) $value)
            ->all();

        return array_replace($defaults, $overrides);
    }

    public function value(string $key, int $fallback): int
    {
        $values = $this->all();

        return (int) ($values[$key] ?? $fallback);
    }

    public function update(array $values): void
    {
        $defaults = $this->defaults();

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            AssignmentSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (int) $value],
            );
        }
    }

    public function resetToDefaults(): void
    {
        if (! Schema::hasTable('assignment_settings')) {
            return;
        }

        AssignmentSetting::query()->delete();
    }

    public function defaults(): array
    {
        return [
            'inactive_nudge_after_days' => (int) config('learning_assignments.inactive_nudge_after_days', 7),
            'inactive_nudge_cooldown_days' => (int) config('learning_assignments.inactive_nudge_cooldown_days', 3),
            'not_started_nudge_after_days' => (int) config('learning_assignments.not_started_nudge_after_days', 10),
            'not_started_nudge_cooldown_days' => (int) config('learning_assignments.not_started_nudge_cooldown_days', 5),
        ];
    }
}
