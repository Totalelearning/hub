<?php

namespace App\Services;

use App\Models\FeedScoringSetting;
use Illuminate\Support\Facades\Schema;

class FeedScoringSettingsService
{
    public function allWeights(): array
    {
        $defaults = $this->defaults();

        if (! Schema::hasTable('feed_scoring_settings')) {
            return $defaults;
        }

        $overrides = FeedScoringSetting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->map(fn ($value) => (int) $value)
            ->all();

        return array_replace($defaults, $overrides);
    }

    public function weight(string $key, int $fallback): int
    {
        $weights = $this->allWeights();

        return (int) ($weights[$key] ?? $fallback);
    }

    public function update(array $weights): void
    {
        $defaults = $this->defaults();

        foreach ($weights as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            FeedScoringSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (int) $value],
            );
        }
    }

    public function defaults(): array
    {
        return collect(config('learning_assignments.feed_scoring', []))
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    public function resetToDefaults(): void
    {
        if (! Schema::hasTable('feed_scoring_settings')) {
            return;
        }

        FeedScoringSetting::query()->delete();
    }

    public function presets(): array
    {
        $defaults = $this->defaults();
        $configPresets = config('learning_assignments.feed_scoring_presets', []);

        return collect($configPresets)
            ->map(function ($preset, string $key) use ($defaults): array {
                $weights = collect((array) ($preset['weights'] ?? []))
                    ->filter(fn ($value, $weightKey) => array_key_exists((string) $weightKey, $defaults))
                    ->map(fn ($value) => (int) $value)
                    ->all();

                return [
                    'key' => $key,
                    'label' => (string) ($preset['label'] ?? $key),
                    'weights' => $weights,
                ];
            })
            ->all();
    }

    public function applyPreset(string $presetKey): ?array
    {
        $presets = $this->presets();
        $preset = collect($presets)->firstWhere('key', $presetKey);

        if (! is_array($preset)) {
            return null;
        }

        $weights = array_replace($this->defaults(), $preset['weights'] ?? []);
        $this->update($weights);

        return $preset;
    }

    public function detectCurrentPreset(array $weights): ?array
    {
        $defaults = $this->defaults();

        foreach ($this->presets() as $preset) {
            $expected = array_replace($defaults, $preset['weights'] ?? []);
            if ($expected === $weights) {
                return $preset;
            }
        }

        return null;
    }
}
