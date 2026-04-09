<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedLearningModule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'learning_module_id',
        'created_at',
    ];

    public function learningModule(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class);
    }
}
