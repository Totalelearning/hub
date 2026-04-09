<?php

namespace App\Support;

final class ScormRuntimeMetrics
{
    public static function parseSessionSeconds(?string $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d+):(\d{1,2}):(\d{1,2})(?:\.\d+)?$/', $value, $matches) === 1) {
            return ((int) $matches[1] * 3600) + ((int) $matches[2] * 60) + (int) $matches[3];
        }

        if (preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?$/i', $value, $matches) === 1) {
            $hours = (int) ($matches[1] ?? 0);
            $minutes = (int) ($matches[2] ?? 0);
            $seconds = (int) floor((float) ($matches[3] ?? 0));

            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        return null;
    }

    public static function formatSeconds(?int $seconds): string
    {
        if ($seconds === null || $seconds <= 0) {
            return 'n/a';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours.'h';
        }

        if ($minutes > 0) {
            $parts[] = $minutes.'m';
        }

        if ($remainingSeconds > 0 || $parts === []) {
            $parts[] = $remainingSeconds.'s';
        }

        return implode(' ', $parts);
    }
}
