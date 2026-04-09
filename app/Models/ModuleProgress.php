<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleProgress extends Model
{
    protected $table = 'module_progress';

    protected $fillable = [
        'user_id',
        'learning_module_id',
        'status',
        'percent_complete',
        'last_position',
        'last_activity_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_position' => 'array',
            'last_activity_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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
}

