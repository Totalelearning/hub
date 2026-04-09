<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use JsonException;
use RuntimeException;

class ExternalAiRankingClient
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function rank(array $context): array
    {
        $payload = $this->sendRequest(
            'Return only JSON with keys boost and reason for feed ranking.',
            $context
        );

        $parsed = $this->parseJsonObject($payload['payload']);

        return [
            'boost' => (int) ($parsed['boost'] ?? 0),
            'reason' => trim((string) ($parsed['reason'] ?? '')),
            'model' => $payload['model'],
            'request_id' => $payload['request_id'],
            'input_tokens_est' => $payload['input_tokens_est'],
            'output_tokens_est' => $payload['output_tokens_est'],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function probe(array $context): array
    {
        $payload = $this->sendRequest(
            'Return only JSON with keys status and reason.',
            $context
        );

        $parsed = $this->parseJsonObject($payload['payload']);

        return [
            'status' => trim((string) ($parsed['status'] ?? 'ok')),
            'reason' => trim((string) ($parsed['reason'] ?? '')),
            'model' => $payload['model'],
            'request_id' => $payload['request_id'],
            'input_tokens_est' => $payload['input_tokens_est'],
            'output_tokens_est' => $payload['output_tokens_est'],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{payload: array<string, mixed>, model: string, request_id: string|null, input_tokens_est: int|null, output_tokens_est: int|null}
     */
    private function sendRequest(string $systemPrompt, array $context): array
    {
        $apiKey = trim((string) config('services.openai.api_key', ''));
        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $timeout = max(1, (int) config('ranking.external_ai.timeout', 15));
        $attempts = max(1, (int) app(RankingSettingsService::class)->get('external_ai_attempts', config('ranking.external_ai.attempts', 2)));
        $retrySleepMs = max(0, (int) app(RankingSettingsService::class)->get('external_ai_retry_sleep_ms', config('ranking.external_ai.retry_sleep_ms', 250)));
        $model = trim((string) config('ranking.external_ai.model', 'gpt-5-mini'));
        $endpoint = trim((string) config('ranking.external_ai.endpoint', 'https://api.openai.com/v1/chat/completions'));

        $response = Http::withToken($apiKey)
            ->timeout($timeout)
            ->retry($attempts, $retrySleepMs)
            ->acceptJson()
            ->post($endpoint, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->encodeContext($context),
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

        $response->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('External AI provider returned an invalid response payload.');
        }

        return [
            'payload' => $this->extractPayload($payload),
            'model' => (string) ($payload['model'] ?? $model),
            'request_id' => $response->header('x-request-id') ?: ($payload['id'] ?? null),
            'input_tokens_est' => $this->usageInt($payload, ['usage', 'prompt_tokens']),
            'output_tokens_est' => $this->usageInt($payload, ['usage', 'completion_tokens']),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function extractPayload(array $payload): array
    {
        if (array_key_exists('boost', $payload) || array_key_exists('status', $payload)) {
            return $payload;
        }

        $content = $this->extractContentString($payload);

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('External AI provider did not return structured content.');
        }

        $decoded = json_decode($this->stripCodeFences($content), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('External AI provider returned invalid JSON content.');
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractContentString(array $payload): ?string
    {
        $content = data_get($payload, 'choices.0.message.content');

        if (is_string($content)) {
            return $content;
        }

        if (is_array($content)) {
            $parts = collect($content)
                ->map(fn ($item) => is_array($item) ? ($item['text'] ?? null) : null)
                ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                ->values()
                ->all();

            if ($parts !== []) {
                return implode("\n", $parts);
            }
        }

        $outputText = data_get($payload, 'output_text');
        if (is_string($outputText) && trim($outputText) !== '') {
            return $outputText;
        }

        $responseOutputText = data_get($payload, 'output.0.content.0.text');

        return is_string($responseOutputText) ? $responseOutputText : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function parseJsonObject(array $payload): array
    {
        if (! is_array($payload)) {
            throw new RuntimeException('External AI response was not valid JSON.');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $path
     */
    private function usageInt(array $payload, array $path): ?int
    {
        $value = data_get($payload, implode('.', $path));

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function encodeContext(array $context): string
    {
        try {
            return json_encode($context, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Failed to encode external AI ranking context.', 0, $exception);
        }
    }

    private function stripCodeFences(string $content): string
    {
        $content = trim($content);

        if (! str_starts_with($content, '```')) {
            return $content;
        }

        $content = preg_replace('/^```[a-zA-Z0-9_-]*\s*/', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/', '', $content) ?? $content;

        return trim($content);
    }
}
