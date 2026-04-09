<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningUnit extends Model
{
    protected $fillable = [
        'learning_module_id',
        'position',
        'content_text',
        'content_hash',
        'metadata',
        'embedding',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_module_id');
    }
}

