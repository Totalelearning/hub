<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Services\SocialFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __invoke(Request $request, SocialFeedService $socialFeedService): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = (int) ($validated['limit'] ?? 20);
        $posts = $socialFeedService->rankedFeed($limit);

        return response()->json([
            'data' => $posts->map(static function (SocialPost $post): array {
                return [
                    'id' => (int) $post->id,
                    'learning_module_id' => $post->learning_module_id !== null ? (int) $post->learning_module_id : null,
                    'body' => (string) $post->body,
                    'likes_count' => (int) $post->likes_count,
                    'comments_count' => (int) $post->comments_count,
                    'shares_count' => (int) $post->shares_count,
                    'published_at' => optional($post->published_at)->toISOString(),
                    'ranking_score' => isset($post->ranking_score) ? (float) $post->ranking_score : null,
                ];
            })->values(),
        ]);
    }
}

