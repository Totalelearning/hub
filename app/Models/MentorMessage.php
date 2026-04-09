<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MentorMessage extends Model
{
    protected $fillable = [
        'mentor_thread_id',
        'role',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MentorThread::class, 'mentor_thread_id');
    }

    public function retrievalTrace(): HasOne
    {
        return $this->hasOne(MentorRetrievalTrace::class);
    }
}

