<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorRetrievalTrace extends Model
{
    protected $fillable = [
        'mentor_message_id',
        'query_text',
        'retrieved_unit_ids',
        'retrieval_scores',
        'retrieval_strategy',
    ];

    protected $casts = [
        'retrieved_unit_ids' => 'array',
        'retrieval_scores' => 'array',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MentorMessage::class, 'mentor_message_id');
    }
}

