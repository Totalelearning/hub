<?php

namespace App\Support;

use App\Models\Location;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AdminUserIndexFilters
{
    public const DEFAULT_SORT = 'created_at';
    public const DEFAULT_SORT_DIR = 'desc';
    public const DEFAULT_LIMIT = 20;
    public const SORTABLE_COLUMNS = ['created_at', 'name', 'email', 'last_login_at'];
    private static ?array $cachedRoleFilters = null;
    private static ?array $cachedTeamFilters = null;
    private static ?array $cachedLocationFilters = null;

    public static function roleFilters(): array
    {
        return self::$cachedRoleFilters ??= Role::options();
    }

    public static function teamFilters(): array
    {
        return self::$cachedTeamFilters ??= Team::options();
    }

    public static function locationFilters(): array
    {
        return self::$cachedLocationFilters ??= Location::options();
    }

    public static function normalize(array $validated): array
    {
        $sort = $validated['sort'] ?? self::DEFAULT_SORT;

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'location' => $validated['location'] ?? 'all',
            'role' => $validated['role'] ?? 'all',
            'team' => $validated['team'] ?? 'all',
            'account_status' => $validated['account_status'] ?? 'all',
            'verification_status' => $validated['verification_status'] ?? 'all',
            'inactivity_status' => $validated['inactivity_status'] ?? 'all',
            'attention_status' => $validated['attention_status'] ?? 'all',
            'training_compliance' => $validated['training_compliance'] ?? 'all',
            'sort' => in_array($sort, self::SORTABLE_COLUMNS, true) ? $sort : self::DEFAULT_SORT,
            'sort_dir' => ($validated['sort_dir'] ?? self::DEFAULT_SORT_DIR) === 'asc' ? 'asc' : self::DEFAULT_SORT_DIR,
            'limit' => (int) ($validated['limit'] ?? self::DEFAULT_LIMIT),
        ];
    }

    public static function compact(array $filters): array
    {
        return array_filter($filters, function ($value, $key) {
            return match ($key) {
                'q' => $value !== '',
                'location', 'role', 'team', 'account_status', 'verification_status', 'inactivity_status', 'attention_status', 'training_compliance' => $value !== 'all',
                'sort' => $value !== self::DEFAULT_SORT,
                'sort_dir' => $value !== self::DEFAULT_SORT_DIR,
                'limit' => (int) $value !== self::DEFAULT_LIMIT,
                default => true,
            };
        }, ARRAY_FILTER_USE_BOTH);
    }

    public static function filteredQuery(array $filters, ?User $admin = null): Builder
    {
        return User::query()
            ->when($admin, fn (Builder $q) => $q->forManagedScope($admin))
            ->when(($filters['location'] ?? 'all') !== 'all', function (Builder $query) use ($filters) {
                $locationName = self::locationFilters()[$filters['location']] ?? null;
                if ($locationName === null) {
                    return;
                }
                $query->whereHas('preference', function (Builder $q) use ($filters) {
                    $q->whereHas('location', fn (Builder $lq) => $lq->where('slug', $filters['location']));
                });
            })
            ->when($filters['q'] !== '', function (Builder $query) use ($filters) {
                $search = '%'.$filters['q'].'%';

                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when(($filters['role'] ?? 'all') === 'admin', fn (Builder $query) => $query->where('is_admin', true))
            ->when(($filters['role'] ?? 'all') === 'learner', fn (Builder $query) => $query->where('is_admin', false))
            ->when(($filters['role'] ?? 'all') !== 'all', function (Builder $query) use ($filters) {
                if (in_array($filters['role'], ['admin', 'learner'], true)) {
                    return;
                }

                $roleName = self::roleFilters()[$filters['role']] ?? null;

                if ($roleName === null) {
                    return;
                }

                $query->whereHas('preference', function (Builder $query) use ($roleName) {
                    $query->where('role', $roleName);
                });
            })
            ->when(($filters['team'] ?? 'all') !== 'all', function (Builder $query) use ($filters) {
                $teamName = self::teamFilters()[$filters['team']] ?? null;

                if ($teamName === null) {
                    return;
                }

                $query->whereHas('preference', function (Builder $query) use ($teamName) {
                    $query->where('team', $teamName);
                });
            })
            ->when($filters['account_status'] === 'active', fn (Builder $query) => $query->whereNull('suspended_at'))
            ->when($filters['account_status'] === 'suspended', fn (Builder $query) => $query->whereNotNull('suspended_at'))
            ->when($filters['verification_status'] === 'verified', fn (Builder $query) => $query->whereNotNull('email_verified_at'))
            ->when($filters['verification_status'] === 'unverified', fn (Builder $query) => $query->whereNull('email_verified_at'))
            ->when($filters['inactivity_status'] === 'never', fn (Builder $query) => $query->whereNull('last_login_at'))
            ->when($filters['inactivity_status'] === 'inactive_30', fn (Builder $query) => $query->whereNotNull('last_login_at')->where('last_login_at', '<', now()->subDays(30)))
            ->when($filters['attention_status'] === 'needs_attention', function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereNotNull('suspended_at')
                        ->orWhereNull('email_verified_at')
                        ->orWhereNull('last_login_at')
                        ->orWhere('last_login_at', '<', now()->subDays(30));
                });
            })
            ->when($filters['training_compliance'] === 'overdue', function (Builder $query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from('assignment_reminders')
                        ->whereColumn('assignment_reminders.user_id', 'users.id')
                        ->whereIn('assignment_reminders.status', ['pending', 'sent'])
                        ->whereDate('assignment_reminders.due_on', '<', Carbon::today()->toDateString());
                });
            })
            ->when($filters['training_compliance'] === 'compliant', function (Builder $query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from('module_progress')
                        ->whereColumn('module_progress.user_id', 'users.id')
                        ->where('module_progress.status', 'completed');
                })->whereNotExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from('module_progress')
                        ->whereColumn('module_progress.user_id', 'users.id')
                        ->where('module_progress.status', '!=', 'completed');
                })->whereNotExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from('assignment_reminders')
                        ->whereColumn('assignment_reminders.user_id', 'users.id')
                        ->whereIn('assignment_reminders.status', ['pending', 'sent'])
                        ->whereDate('assignment_reminders.due_on', '<', Carbon::today()->toDateString());
                });
            })
            ->when($filters['training_compliance'] === 'not_started', function (Builder $query) {
                $query->whereNotExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from('module_progress')
                        ->whereColumn('module_progress.user_id', 'users.id')
                        ->whereIn('module_progress.status', ['in_progress', 'completed']);
                })->whereNotExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from('assignment_reminders')
                        ->whereColumn('assignment_reminders.user_id', 'users.id')
                        ->whereIn('assignment_reminders.status', ['pending', 'sent'])
                        ->whereDate('assignment_reminders.due_on', '<', Carbon::today()->toDateString());
                });
            });
    }

    public static function applySorting(Builder $query, array $filters): Builder
    {
        $sort = $filters['sort'] ?? self::DEFAULT_SORT;
        $direction = $filters['sort_dir'] ?? self::DEFAULT_SORT_DIR;

        if ($sort === self::DEFAULT_SORT) {
            return $query
                ->orderBy('created_at', $direction)
                ->orderBy('id', $direction);
        }

        return $query
            ->orderByDesc('is_admin')
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc');
    }
}
