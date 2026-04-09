<?php

namespace App\Http\Controllers\Api;

use App\Contracts\MentorProvider;
use App\Http\Controllers\Controller;
use App\Models\LearningEvent;
use App\Models\MentorMessage;
use App\Models\MentorRetrievalTrace;
use App\Models\MentorThread;
use App\Services\AiOpsLogger;
use App\Services\MentorRetrievalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class PostMentorThreadMessageController extends Controller
{
    public function __invoke(
        int $id,
        Request $request,
        MentorRetrievalService $mentorRetrievalService,
        MentorProvider $mentorProvider
    ): JsonResponse {
        $rateLimitKey = sprintf('mentor-message:%s', $request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($rateLimitKey, 30)) {
            return response()->json([
                'error' => [
                    'code' => 'rate_limited',
                    'message' => 'Too many mentor requests. Please retry later.',
                ],
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        if (! (bool) config('mentor.enabled', true)) {
            return response()->json([
                'error' => [
                    'code' => 'mentor_provider_disabled',
                    'message' => 'Mentor provider is disabled.',
                ],
            ], 503);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:4000'],
        ]);

        $thread = MentorThread::query()->find($id);

        if ($thread === null) {
            return response()->json([
                'message' => 'Mentor thread not found.',
            ], 404);
        }

        $question = trim((string) $validated['content']);

        if ($question === '') {
            return response()->json([
                'message' => 'The content field is required.',
                'errors' => ['content' => ['The content field is required.']],
            ], 422);
        }

        $retrieval = $mentorRetrievalService->retrieveContext($question, $thread->learning_module_id, 8);

        try {
            $providerResult = $mentorProvider->answer($question, $retrieval['units'], [
                'thread_id' => $thread->id,
                'learning_module_id' => $thread->learning_module_id,
            ]);
        } catch (Throwable $exception) {
            app(AiOpsLogger::class)->recordFailure('local', 'mentor_answer', $exception, [
                'model' => 'heuristic-v1',
                'metadata' => [
                    'thread_id' => $thread->id,
                    'learning_module_id' => $thread->learning_module_id,
                    'environment' => app()->environment(),
                ],
            ]);

            return response()->json([
                'error' => [
                    'code' => 'mentor_provider_error',
                    'message' => 'Mentor provider failed to generate a response.',
                ],
            ], 500);
        }

        $stored = DB::transaction(function () use ($thread, $question, $retrieval, $providerResult): array {
            $userMessage = MentorMessage::query()->create([
                'mentor_thread_id' => $thread->id,
                'role' => 'user',
                'content' => $question,
                'metadata' => null,
            ]);

            $assistantMessage = MentorMessage::query()->create([
                'mentor_thread_id' => $thread->id,
                'role' => 'assistant',
                'content' => (string) ($providerResult['answer_text'] ?? ''),
                'metadata' => [
                    'provider' => $providerResult['provider'] ?? 'local',
                    'model' => $providerResult['model'] ?? 'heuristic-v1',
                    'confidence' => $providerResult['confidence'] ?? 'low',
                    'citations' => array_values(array_map('intval', $providerResult['citations'] ?? [])),
                    'token_estimate' => max(1, intdiv(mb_strlen((string) ($providerResult['answer_text'] ?? '')), 4)),
                    'safety_flags' => [],
                ],
            ]);

            MentorRetrievalTrace::query()->create([
                'mentor_message_id' => $assistantMessage->id,
                'query_text' => $question,
                'retrieved_unit_ids' => array_values(array_map(
                    static fn (array $unit): int => (int) $unit['id'],
                    $retrieval['units']
                )),
                'retrieval_scores' => array_values(array_map(
                    static fn (array $unit): float => (float) $unit['score'],
                    $retrieval['units']
                )),
                'retrieval_strategy' => (string) $retrieval['strategy_used'],
            ]);

            LearningEvent::query()->create([
                'user_id' => $thread->user_id,
                'event_type' => 'mentor_question_asked',
                'entity_type' => 'mentor_thread',
                'entity_id' => $thread->id,
                'metadata' => [
                    'mentor_message_id' => $userMessage->id,
                    'query_length' => mb_strlen($question),
                ],
            ]);

            LearningEvent::query()->create([
                'user_id' => $thread->user_id,
                'event_type' => 'mentor_answer_generated',
                'entity_type' => 'mentor_thread',
                'entity_id' => $thread->id,
                'metadata' => [
                    'mentor_message_id' => $assistantMessage->id,
                    'provider' => $providerResult['provider'] ?? 'local',
                    'model' => $providerResult['model'] ?? 'heuristic-v1',
                    'confidence' => $providerResult['confidence'] ?? 'low',
                ],
            ]);

            return [
                'assistant_message' => $assistantMessage,
                'retrieval' => $retrieval,
                'provider_result' => $providerResult,
            ];
        });

        /** @var MentorMessage $assistantMessage */
        $assistantMessage = $stored['assistant_message'];
        $retrievalData = $stored['retrieval'];
        $providerData = $stored['provider_result'];

        return response()->json([
            'assistant_message' => [
                'id' => (int) $assistantMessage->id,
                'content' => (string) $assistantMessage->content,
                'citations' => array_values(array_map('intval', $providerData['citations'] ?? [])),
                'provider' => $providerData['provider'] ?? 'local',
                'model' => $providerData['model'] ?? 'heuristic-v1',
                'confidence' => $providerData['confidence'] ?? 'low',
            ],
            'retrieval' => [
                'strategy_used' => (string) ($retrievalData['strategy_used'] ?? 'keyword_fallback'),
                'units' => array_values(array_map(static fn (array $unit): array => [
                    'id' => (int) $unit['id'],
                    'score' => (float) $unit['score'],
                ], $retrievalData['units'] ?? [])),
            ],
        ], 201);
    }
}
