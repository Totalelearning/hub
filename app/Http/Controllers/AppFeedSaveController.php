<?php

namespace App\Http\Controllers;

use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\SavedLearningModule;
use Illuminate\Http\RedirectResponse;

class AppFeedSaveController extends Controller
{
    public function save(LearningModule $module): RedirectResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->back();
        }

        SavedLearningModule::query()->firstOrCreate([
            'user_id' => $userId,
            'learning_module_id' => $module->id,
        ]);
        LearningEvent::query()->create([
            'user_id' => $userId,
            'event_type' => 'module_saved',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'topic' => $module->topic,
            ],
        ]);

        return redirect()->back();
    }

    public function unsave(LearningModule $module): RedirectResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->back();
        }

        SavedLearningModule::query()
            ->where('user_id', $userId)
            ->where('learning_module_id', $module->id)
            ->delete();
        LearningEvent::query()->create([
            'user_id' => $userId,
            'event_type' => 'module_unsaved',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'topic' => $module->topic,
            ],
        ]);

        return redirect()->back();
    }
}
