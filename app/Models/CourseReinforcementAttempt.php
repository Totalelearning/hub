<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseReinforcementAttempt extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'token',
        'status',
        'sent_at',
        'started_at',
        'completed_at',
        'score_percent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score_percent' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(CourseReinforcementResponse::class);
    }
}
