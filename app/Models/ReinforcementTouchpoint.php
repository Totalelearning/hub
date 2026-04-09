<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReinforcementTouchpoint extends Model
{
    protected $fillable = [
        'user_id',
        'learning_module_id',
        'module_progress_id',
        'reinforcement_question_set_id',
        'touchpoint_key',
        'interval_days',
        'title',
        'prompt',
        'proof_type',
        'due_on',
        'status',
        'completed_at',
        'proof_summary',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_module_id');
    }

    public function moduleProgress(): BelongsTo
    {
        return $this->belongsTo(ModuleProgress::class, 'module_progress_id');
    }

    public function reinforcementQuestionSet(): BelongsTo
    {
        return $this->belongsTo(ReinforcementQuestionSet::class, 'reinforcement_question_set_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ReinforcementResponse::class, 'reinforcement_touchpoint_id');
    }
}
