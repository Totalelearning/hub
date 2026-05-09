<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpTransaction extends Model
{
    const UPDATED_AT = null;

    const REASON_COURSE_COMPLETED = 'course_completed';
    const REASON_REINFORCEMENT_COMPLETED = 'reinforcement_completed';
    const REASON_PERFECT_SCORE = 'perfect_score';
    const REASON_STREAK_BONUS = 'streak_bonus';
    const REASON_BADGE_BONUS = 'badge_bonus';

    protected $fillable = [
        'user_id',
        'xp_amount',
        'reason',
        'source_type',
        'source_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'xp_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
