<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetrievalQualityReview extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'mentor_message_id',
        'reviewer_user_id',
        'rating',
        'flags',
        'notes',
    ];

    protected $casts = [
        'flags' => 'array',
    ];

    public function mentorMessage(): BelongsTo
    {
        return $this->belongsTo(MentorMessage::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}

