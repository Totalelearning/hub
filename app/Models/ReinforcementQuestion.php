<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReinforcementQuestion extends Model
{
    protected $fillable = [
        'reinforcement_question_set_id',
        'position',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'explanation',
        'remediation_learning_module_id',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'metadata' => 'array',
        ];
    }

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(ReinforcementQuestionSet::class, 'reinforcement_question_set_id');
    }

    public function remediationModule(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'remediation_learning_module_id');
    }
}
