<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReinforcementQuestionSet extends Model
{
    protected $fillable = [
        'learning_module_id',
        'learning_asset_id',
        'status',
        'generation_mode',
        'title',
        'summary',
        'draft_source_excerpt',
        'generated_at',
        'reviewed_at',
        'reviewed_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_module_id');
    }

    public function learningAsset(): BelongsTo
    {
        return $this->belongsTo(LearningAsset::class, 'learning_asset_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ReinforcementQuestion::class, 'reinforcement_question_set_id')
            ->orderBy('position');
    }
}
