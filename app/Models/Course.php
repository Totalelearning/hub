<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'slug',
        'topic',
        'difficulty',
        'target_roles',
        'status',
        'estimated_minutes',
        'reinforcement_delay_days',
        'cover_image',
        'owner_user_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_roles' => 'array',
            'reinforcement_delay_days' => 'integer',
        ];
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(LearningModule::class, 'course_module')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withPivot('status', 'completed_at', 'reinforcement_sent_at', 'reinforcement_status')
            ->withTimestamps();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function reinforcementAttempts(): HasMany
    {
        return $this->hasMany(CourseReinforcementAttempt::class);
    }

    /**
     * Check if a user has completed all modules in this course.
     */
    public function isCompletedByUser(User $user): bool
    {
        $moduleIds = $this->modules()->pluck('learning_modules.id');

        if ($moduleIds->isEmpty()) {
            return false;
        }

        $completedCount = ModuleProgress::where('user_id', $user->id)
            ->whereIn('learning_module_id', $moduleIds)
            ->where('status', 'completed')
            ->count();

        return $completedCount >= $moduleIds->count();
    }

    /**
     * Get all approved reinforcement question sets across this course's modules.
     */
    public function approvedQuestionSets()
    {
        $moduleIds = $this->modules()->pluck('learning_modules.id');

        return ReinforcementQuestionSet::whereIn('learning_module_id', $moduleIds)
            ->where('status', 'approved')
            ->with(['questions', 'module:id,title'])
            ->get();
    }
}
