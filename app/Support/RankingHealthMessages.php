<?php

namespace App\Support;

class RankingHealthMessages
{
    public static function providerMismatch(string $selectedProviderLabel, string $activeProviderLabel): string
    {
        return sprintf(
            'Filtered probe history is showing %s, while the active ranking provider is %s.',
            $selectedProviderLabel,
            $activeProviderLabel,
        );
    }

    public static function probeHistoryEmpty(?string $selectedProvider, string $providerLabel, bool $dashboard = false): string
    {
        if ($selectedProvider === null || $selectedProvider === 'all') {
            return $dashboard ? 'No ranking probes recorded yet.' : 'No probe history recorded yet.';
        }

        return sprintf('No probe history matches provider %s.', $providerLabel);
    }

    public static function severityTransitionsEmpty(?string $selectedTrigger, string $triggerLabel): string
    {
        if ($selectedTrigger === null || $selectedTrigger === 'all') {
            return 'No severity transitions recorded yet.';
        }

        return sprintf('No severity transitions match trigger %s.', $triggerLabel);
    }
}
