<?php

use App\Http\Controllers\Api\AdminAiUsagesController;
use App\Http\Controllers\Api\AdminIngestionAssetsController;
use App\Http\Controllers\Api\AdminRankingHealthController;
use App\Http\Controllers\Api\AdminMentorTracesController;
use App\Http\Controllers\Api\CreateMentorThreadController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\PostMentorThreadMessageController;
use App\Http\Controllers\Api\ShowMentorThreadController;
use App\Http\Controllers\Api\StartLearningAssetIngestionController;
use App\Http\Controllers\Api\SimilarModulesController;
use App\Http\Controllers\Api\UploadLearningAssetController;
use Illuminate\Support\Facades\Route;

Route::get('/modules/similar/{id}', SimilarModulesController::class);
Route::post('/modules/{id}/assets', UploadLearningAssetController::class);
Route::post('/assets/{id}/ingest', StartLearningAssetIngestionController::class);
Route::get('/feed', FeedController::class);
Route::post('/mentor/threads', CreateMentorThreadController::class);
Route::get('/mentor/threads/{id}', ShowMentorThreadController::class);
Route::post('/mentor/threads/{id}/messages', PostMentorThreadMessageController::class);
Route::get('/admin/ai/usages', AdminAiUsagesController::class);
Route::get('/admin/ai/ranking-health', AdminRankingHealthController::class);
Route::get('/admin/mentor/traces', AdminMentorTracesController::class);
Route::get('/admin/ingestion/assets', AdminIngestionAssetsController::class);
