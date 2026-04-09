<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathStep extends Model
{
    protected $fillable = [
        'learning_path_id',
        'learning_module_id',
        'position',
        'delay_days',
    ];

    protected $casts = [
        'delay_days' => 'integer',
    ];

    public function path(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class, 'learning_path_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_module_id');
    }
}
