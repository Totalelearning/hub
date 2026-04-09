<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    protected $fillable = [
        'title',
        'description',
        'target_roles',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_roles' => 'array',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(LearningPathStep::class)->orderBy('position');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(
            LearningModule::class,
            'learning_path_steps',
            'learning_path_id',
            'learning_module_id',
        )->withPivot('position')->orderByPivot('position');
    }
}
