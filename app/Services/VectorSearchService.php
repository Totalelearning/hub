<?php

namespace App\Services;

use App\Models\LearningModule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VectorSearchService
{
    public function similarModules(int $moduleId, int $limit = 10): Collection
    {
        $limit = max(1, min($limit, 50));

        $referenceModule = LearningModule::query()->find($moduleId);

        if ($referenceModule === null) {
            throw (new ModelNotFoundException())->setModel(LearningModule::class, [$moduleId]);
        }

        if (blank($referenceModule->embedding)) {
            return new Collection();
        }

        $embedding = (string) $referenceModule->embedding;

        return LearningModule::query()
            ->select(['id', 'title', 'description'])
            ->selectRaw('1 - (embedding <=> ?::vector) as similarity_score', [$embedding])
            ->withEmbedding()
            ->excludeModule((int) $referenceModule->id)
            ->orderByRaw('embedding <=> ?::vector asc', [$embedding])
            ->limit($limit)
            ->get();
    }

    public function similarToModuleId(int $moduleId, int $limit = 10): Collection
    {
        return $this->similarModules($moduleId, $limit);
    }
}