<?php

namespace App\Services;

use App\Models\RankingSetting;
use Illuminate\Support\Facades\Schema;

class RankingSettingsService
{
    public function all(): array
    {
        $defaults = $this->defaults();

        if (! Schema::hasTable('ranking_settings')) {
            return $defaults;
        }

        $raw = RankingSetting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->all();

        $settings = $defaults;
        foreach ($raw as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            $settings[$key] = $this->castValue((string) $key, $value);
        }

        return $settings;
    }

    public function get(string $key, mixed $fallback = null): mixed
    {
        $settings = $this->all();

        return $settings[$key] ?? $fallback;
    }

    public function update(array $settings): void
    {
        $defaults = $this->defaults();

        foreach ($settings as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            RankingSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $this->serializeValue((string) $key, $value)],
            );
        }
    }

    public function resetToDefaults(): void
    {
        if (! Schema::hasTable('ranking_settings')) {
            return;
        }

        RankingSetting::query()->delete();
    }

    public function defaults(): array
    {
        return [
            'enabled' => (bool) config('ranking.enabled', false),
            'provider' => (string) config('ranking.provider', 'deterministic'),
            'local_ai_goal_semantic_boost_per_match' => (int) config('ranking.local_ai.goal_semantic_boost_per_match', 4),
            'local_ai_goal_semantic_boost_max' => (int) config('ranking.local_ai.goal_semantic_boost_max', 10),
            'external_ai_attempts' => (int) config('ranking.external_ai.attempts', 2),
            'external_ai_retry_sleep_ms' => (int) config('ranking.external_ai.retry_sleep_ms', 250),
            'external_ai_max_boost' => (int) config('ranking.external_ai.max_boost', 20),
        ];
    }

    private function castValue(string $key, mixed $value): mixed
    {
        return match ($key) {
            'enabled' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'provider' => (string) $value,
            'local_ai_goal_semantic_boost_per_match',
            'local_ai_goal_semantic_boost_max',
            'external_ai_attempts',
            'external_ai_retry_sleep_ms',
            'external_ai_max_boost' => (int) $value,
            default => $value,
        };
    }

    private function serializeValue(string $key, mixed $value): string
    {
        return match ($key) {
            'enabled' => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
