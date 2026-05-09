<?php

namespace App\Services;

use App\Models\GamificationSetting;
use Illuminate\Support\Facades\Schema;

class GamificationSettingsService
{
    /**
     * Return all XP settings merged with DB overrides.
     */
    public function all(): array
    {
        $defaults = $this->defaults();

        if (! Schema::hasTable('gamification_settings')) {
            return $defaults;
        }

        $overrides = GamificationSetting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->map(fn ($value) => (int) $value)
            ->all();

        return array_replace($defaults, $overrides);
    }

    /**
     * Get a single setting value.
     */
    public function get(string $key, mixed $fallback = null): mixed
    {
        $all = $this->all();

        return $all[$key] ?? $fallback;
    }

    /**
     * Persist settings to the database.
     */
    public function update(array $settings): void
    {
        $defaults = $this->defaults();

        foreach ($settings as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            GamificationSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (int) $value],
            );
        }
    }

    /**
     * Default XP values from config.
     */
    public function defaults(): array
    {
        return collect(config('gamification.xp', []))
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * Remove all overrides — revert to config defaults.
     */
    public function resetToDefaults(): void
    {
        if (! Schema::hasTable('gamification_settings')) {
            return;
        }

        GamificationSetting::query()->delete();
    }
}
