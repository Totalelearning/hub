<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class LearningModule extends Model
{
    protected $fillable = [
        'title',
        'description',
        'topic',
        'difficulty',
        'target_roles',
        'owner_user_id',
        'is_required',
        'requires_acknowledgement',
        'review_status',
        'approved_by',
        'approved_at',
        'compliance_area',
        'refresh_interval_days',
        'reinforcement_intervals_days',
        'available_from',
        'available_until',
        'source_type',
        'source_uri',
        'content_text',
        'cover_image',
        'status',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'target_roles' => 'array',
            'reinforcement_intervals_days' => 'array',
            'is_required' => 'boolean',
            'requires_acknowledgement' => 'boolean',
            'approved_at' => 'datetime',
            'available_from' => 'datetime',
            'available_until' => 'datetime',
        ];
    }

    public function scopeWithEmbedding(Builder $query): Builder
    {
        return $query->whereNotNull('embedding');
    }

    public function scopeExcludeModule(Builder $query, int $moduleId): Builder
    {
        return $query->whereKeyNot($moduleId);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(LearningAsset::class);
    }

    public function latestScormAsset(): ?LearningAsset
    {
        return $this->assets()
            ->where('asset_type', 'scorm_package')
            ->where('status', 'processed')
            ->get()
            ->sortByDesc(function (LearningAsset $asset) {
                $activatedAt = $asset->processing_metadata['activated_at'] ?? null;

                return $activatedAt !== null
                    ? strtotime((string) $activatedAt)
                    : ($asset->id ?? 0);
            })
            ->first();
    }

    public function units(): HasMany
    {
        return $this->hasMany(LearningUnit::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class, 'learning_module_id');
    }

    public function reinforcementQuestionSets(): HasMany
    {
        return $this->hasMany(ReinforcementQuestionSet::class, 'learning_module_id');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(ModuleAcknowledgement::class, 'learning_module_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(LearningModuleRevision::class, 'learning_module_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_module')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'learning_module_prerequisites',
            'learning_module_id',
            'prerequisite_learning_module_id',
        );
    }

    public function unlocks(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'learning_module_prerequisites',
            'prerequisite_learning_module_id',
            'learning_module_id',
        );
    }
}
