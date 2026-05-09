<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'system_role',
        'managed_teams',
        'managed_locations',
        'total_xp',
        'suspended_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_admin' => 'boolean',
            'suspended_at' => 'datetime',
            'managed_teams' => 'array',
            'managed_locations' => 'array',
            'total_xp' => 'integer',
            'password' => 'hashed',
        ];
    }

    // ── System role helpers ──────────────────────────────────────

    public function isSiteAdmin(): bool
    {
        return $this->system_role === 'site_admin';
    }

    public function isSltManager(): bool
    {
        return $this->system_role === 'slt_manager';
    }

    public function isManager(): bool
    {
        return $this->system_role === 'manager';
    }

    public function isLearner(): bool
    {
        return $this->system_role === 'learner' || $this->system_role === null;
    }

    public function hasAdminAccess(): bool
    {
        return in_array($this->system_role, ['site_admin', 'slt_manager', 'manager'], true);
    }

    public function canManageTeam(?string $teamName): bool
    {
        if ($this->isSiteAdmin()) {
            return true;
        }

        if (! $teamName || ! $this->hasAdminAccess()) {
            return false;
        }

        return in_array($teamName, $this->managed_teams ?? [], true);
    }

    public function canManageTeamAssignments(): bool
    {
        return in_array($this->system_role, ['site_admin', 'slt_manager'], true);
    }

    public function canManageLocation(?string $locationSlugOrName): bool
    {
        if ($this->isSiteAdmin()) {
            return true;
        }

        if (! $this->hasAdminAccess()) {
            return false;
        }

        // No location restrictions — can manage all locations
        if (empty($this->managed_locations)) {
            return true;
        }

        // Has location restrictions but target has no location
        if (! $locationSlugOrName) {
            return false;
        }

        return in_array($locationSlugOrName, $this->managed_locations, true);
    }

    /** Label for display (e.g. "SLT Manager") */
    public function systemRoleLabel(): string
    {
        return match ($this->system_role) {
            'site_admin' => 'Site Administrator',
            'slt_manager' => 'SLT Manager',
            'manager' => 'Manager',
            default => 'Learner',
        };
    }

    // ── Team-scoping query scope ────────────────────────────────

    /**
     * Scope to users whose team falls within the given admin's managed teams.
     * Site admins see all users (no filter applied).
     */
    public function scopeForManagedTeams(Builder $query, self $admin): Builder
    {
        if ($admin->isSiteAdmin()) {
            return $query;
        }

        $teams = $admin->managed_teams ?? [];

        if (empty($teams)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('preference', fn (Builder $q) => $q->whereIn('team', $teams));
    }

    /**
     * Returns an array of user IDs within the admin's managed teams,
     * or null for site admins (meaning no filtering needed).
     *
     * Usage: ->when($ids !== null, fn ($q) => $q->whereIn('user_id', $ids))
     */
    public static function managedTeamUserIds(self $admin): ?array
    {
        if ($admin->isSiteAdmin()) {
            return null;
        }

        $teams = $admin->managed_teams ?? [];

        if (empty($teams)) {
            return [];
        }

        return \Illuminate\Support\Facades\DB::table('user_preferences')
            ->whereIn('team', $teams)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }

    // ── Location-scoping query scope ──────────────────────────────

    /**
     * Scope to users whose location falls within the admin's managed locations.
     * Site admins see all. If managed_locations is null/empty, no location filter
     * is applied (backward compat with managers who only have team restrictions).
     */
    public function scopeForManagedLocations(Builder $query, self $admin): Builder
    {
        if ($admin->isSiteAdmin()) {
            return $query;
        }

        $locations = $admin->managed_locations ?? [];

        if (empty($locations)) {
            return $query; // No location restriction — backward compat
        }

        return $query->whereHas('preference', function (Builder $q) use ($locations) {
            $q->whereHas('location', fn (Builder $lq) => $lq->whereIn('slug', $locations));
        });
    }

    /**
     * Combined scope: apply both location and team restrictions.
     */
    public function scopeForManagedScope(Builder $query, self $admin): Builder
    {
        return $query->forManagedLocations($admin)->forManagedTeams($admin);
    }

    /**
     * Returns user IDs within the admin's managed locations,
     * or null if no location restriction applies.
     */
    public static function managedLocationUserIds(self $admin): ?array
    {
        if ($admin->isSiteAdmin()) {
            return null;
        }

        $locations = $admin->managed_locations ?? [];

        if (empty($locations)) {
            return null; // No location restriction
        }

        return \Illuminate\Support\Facades\DB::table('user_preferences')
            ->join('locations', 'locations.id', '=', 'user_preferences.location_id')
            ->whereIn('locations.slug', $locations)
            ->pluck('user_preferences.user_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Returns user IDs within BOTH managed locations and managed teams,
     * or null for site admins (meaning no filtering needed).
     */
    public static function managedScopeUserIds(self $admin): ?array
    {
        $locationIds = static::managedLocationUserIds($admin);
        $teamIds = static::managedTeamUserIds($admin);

        // Both null = site admin, no restriction
        if ($locationIds === null && $teamIds === null) {
            return null;
        }

        // If only one is set, use that
        if ($locationIds === null) {
            return $teamIds;
        }
        if ($teamIds === null) {
            return $locationIds;
        }

        // Both set — intersect
        return array_values(array_intersect($locationIds, $teamIds));
    }

    // ── Relationships ───────────────────────────────────────────

    public function moduleProgress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function moduleAcknowledgements(): HasMany
    {
        return $this->hasMany(ModuleAcknowledgement::class);
    }

    // ── Gamification relationships ──────────────────────────────

    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    public function streak(): HasOne
    {
        return $this->hasOne(Streak::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->orderByPivot('earned_at', 'desc');
    }
}
