<?php

namespace App\Services;

use App\Models\AiProviderUsage;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiOpsLogger
{
    public function recordProviderUsage(
        string $provider,
        string $capability,
        bool $success,
        array $payload = []
    ): void {
        try {
            AiProviderUsage::query()->create([
                'provider' => $provider,
                'capability' => $capability,
                'model' => $payload['model'] ?? null,
                'request_id' => $payload['request_id'] ?? null,
                'input_tokens_est' => $payload['input_tokens_est'] ?? null,
                'output_tokens_est' => $payload['output_tokens_est'] ?? null,
                'latency_ms' => $payload['latency_ms'] ?? null,
                'success' => $success,
                'error_type' => $payload['error_type'] ?? null,
                'error_message' => $payload['error_message'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Failed to write ai_provider_usages audit row.', [
                'provider' => $provider,
                'capability' => $capability,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function recordFailure(
        string $provider,
        string $capability,
        Throwable $exception,
        array $payload = []
    ): void {
        $this->recordProviderUsage($provider, $capability, false, array_merge($payload, [
            'error_type' => get_class($exception),
            'error_message' => $exception->getMessage(),
        ]));
    }
}

