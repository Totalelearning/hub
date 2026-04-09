<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentReminder extends Model
{
    protected $fillable = [
        'user_id',
        'learning_module_id',
        'reminder_type',
        'due_on',
        'sent_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'sent_at' => 'datetime',
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
