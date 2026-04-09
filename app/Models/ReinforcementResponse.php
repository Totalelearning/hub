<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReinforcementResponse extends Model
{
    protected $fillable = [
        'reinforcement_touchpoint_id',
        'reinforcement_question_id',
        'user_id',
        'selected_answer',
        'is_correct',
        'answered_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'answered_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function touchpoint(): BelongsTo
    {
        return $this->belongsTo(ReinforcementTouchpoint::class, 'reinforcement_touchpoint_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ReinforcementQuestion::class, 'reinforcement_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
