<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAuditEvent;
use App\Models\AssignmentReminder;
use App\Models\LearningModule;
use App\Models\LearningEvent;
use App\Models\Location;
use App\Models\ModuleProgress;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\AssignmentService;
use App\Support\AdminUserBulkPresets;
use App\Support\AdminUserIndexFilters;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    private static ?array $cachedRoleFilterOptions = null;
    private static ?array $cachedTeamFilterOptions = null;
    private static ?array $cachedLocationFilterOptions = null;

    private static function roleFilterOptions(): array
    {
        return self::$cachedRoleFilterOptions ??= Role::optionsWithAll();
    }

    private static function teamFilterOptions(): array
    {
        return self::$cachedTeamFilterOptions ??= Team::optionsWithAll();
    }

    private static function locationFilterOptions(): array
    {
        return self::$cachedLocationFilterOptions ??= Location::optionsWithAll();
    }

    private const TRAINING_COMPLIANCE_OPTIONS = [
        'all' => 'All',
        'compliant' => 'Compliant',
        'overdue' => 'Overdue',
        'not_started' => 'Not Started',
    ];

    private const LEGACY_ROLE_FILTERS = ['admin', 'learner'];

    public function create(): View
    {
        Gate::authorize('admin-access');

        return view('app.admin-users-form', [
            'managedUser' => new User([
                'is_admin' => false,
            ]),
            'formAction' => route('app.admin.users.store'),
            'formMethod' => 'POST',
            'pageTitle' => 'Create User',
            'pageDescription' => 'Create a learner or admin account directly from the admin area.',
            'submitLabel' => 'Create User',
            'availableRoleOptions' => $this->formRoleOptions(),
            'availableTeamOptions' => $this->formTeamOptions(),
            'availableLocationOptions' => $this->formLocationOptions(),
        ]);
    }

    public function index(Request $request): View
    {
        Gate::authorize('admin-access');

        $validated = $this->validatedIndexFilters($request);
        $filters = AdminUserIndexFilters::normalize($validated);
        $selectedBulkAction = $this->selectedBulkAction($request);
        $selectedBulkPreset = $this->selectedBulkPreset($filters, $selectedBulkAction);
        $selectedBulkPresetDescription = $selectedBulkPreset ? AdminUserBulkPresets::descriptions()[$selectedBulkPreset] ?? null : null;
        $selectedBulkPresetAuditAction = $selectedBulkPreset ? AdminUserBulkPresets::auditActions()[$selectedBulkPreset] ?? null : null;
        $bulkPresetCounts = $this->bulkPresetCounts();
        $bulkPresetAuditActions = AdminUserBulkPresets::auditActions();
        $statusBanners = $this->statusBanners($request);
        $query = AdminUserIndexFilters::filteredQuery($filters, $request->user());

        $users = AdminUserIndexFilters::applySorting(clone $query, $filters)
            ->with(['preference.location'])
            ->paginate($filters['limit'])
            ->appends($request->query());

        $summary = [
            'total' => (clone $query)->count(),
            'admins' => (clone $query)->where('is_admin', true)->count(),
            'learners' => (clone $query)->where('is_admin', false)->count(),
            'active' => (clone $query)->whereNull('suspended_at')->count(),
            'suspended' => (clone $query)->whereNotNull('suspended_at')->count(),
            'verified' => (clone $query)->whereNotNull('email_verified_at')->count(),
            'unverified' => (clone $query)->whereNull('email_verified_at')->count(),
            'never_logged_in' => (clone $query)->whereNull('last_login_at')->count(),
            'inactive_30' => (clone $query)->whereNotNull('last_login_at')->where('last_login_at', '<', now()->subDays(30))->count(),
            'needs_attention' => (clone $query)->where(function ($query) {
                $query->whereNotNull('suspended_at')
                    ->orWhereNull('email_verified_at')
                    ->orWhereNull('last_login_at')
                    ->orWhere('last_login_at', '<', now()->subDays(30));
            })->count(),
        ];

        return view('app.admin-users-index', [
            'users' => $users,
            'filters' => $filters,
            'summary' => $summary,
            'availableRoleFilters' => self::roleFilterOptions(),
            'availableTeamFilters' => self::teamFilterOptions(),
            'availableLocationFilters' => self::locationFilterOptions(),
            'availableTrainingComplianceFilters' => self::TRAINING_COMPLIANCE_OPTIONS,
            'selectedBulkAction' => $selectedBulkAction,
            'selectedBulkPreset' => $selectedBulkPreset,
            'selectedBulkPresetDescription' => $selectedBulkPresetDescription,
            'selectedBulkPresetAuditAction' => $selectedBulkPresetAuditAction,
            'bulkPresetCounts' => $bulkPresetCounts,
            'bulkPresetAuditActions' => $bulkPresetAuditActions,
            'pageStatus' => $statusBanners['page'],
            'bulkStatusBanner' => $statusBanners['bulk'],
            'csvImportColumns' => ['name', 'email', 'role', 'team', 'location', 'is_admin', 'account_active', 'password'],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('admin-access');

        $validated = $this->validatedIndexFilters($request);
        $filters = AdminUserIndexFilters::normalize($validated);
        $users = AdminUserIndexFilters::applySorting(AdminUserIndexFilters::filteredQuery($filters, $request->user()), $filters)
            ->with(['preference.location'])
            ->limit(1000)
            ->get();

        $filename = 'users-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($users): void {
            $handle = fopen('php://output', 'wb');

            $csv = fn (array $row) => fputcsv($handle, $row, ',', '"', '');
            $csv(['id', 'name', 'email', 'system_role', 'location', 'team', 'staff_role', 'account_status', 'suspended_at', 'email_verified_at', 'last_login_at', 'created_at']);

            foreach ($users as $user) {
                $csv([
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->systemRoleLabel(),
                    $user->preference?->location?->name ?? '',
                    $user->preference?->team ?? '',
                    $user->preference?->role ?? '',
                    $user->suspended_at ? 'suspended' : 'active',
                    $user->suspended_at?->format('Y-m-d H:i:s'),
                    $user->email_verified_at?->format('Y-m-d H:i:s'),
                    $user->last_login_at?->format('Y-m-d H:i:s'),
                    $user->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'action' => ['required', 'in:resend_verification,mark_verified,send_password_reset_link,suspend,restore'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'q' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in(array_merge(['all'], array_keys(self::roleFilterOptions()), self::LEGACY_ROLE_FILTERS))],
            'team' => ['nullable', Rule::in(array_keys(self::teamFilterOptions()))],
            'location' => ['nullable', Rule::in(array_keys(self::locationFilterOptions()))],
            'account_status' => ['nullable', 'in:all,active,suspended'],
            'verification_status' => ['nullable', 'in:all,verified,unverified'],
            'inactivity_status' => ['nullable', 'in:all,never,inactive_30'],
            'attention_status' => ['nullable', 'in:all,needs_attention'],
            'training_compliance' => ['nullable', Rule::in(array_keys(self::TRAINING_COMPLIANCE_OPTIONS))],
            'sort' => ['nullable', 'in:created_at,name,email,last_login_at'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'limit' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ]);

        $normalizedFilters = AdminUserIndexFilters::normalize($validated);
        $selectedBulkPreset = $this->selectedBulkPreset($normalizedFilters, $validated['action']);

        $users = User::query()
            ->whereIn('id', $validated['user_ids'])
            ->get()
            ->keyBy('id');

        $processed = 0;
        $skipped = 0;

        foreach ($validated['user_ids'] as $userId) {
            $user = $users->get((int) $userId);

            if (! $user) {
                $skipped++;
                continue;
            }

            if ($validated['action'] === 'resend_verification') {
                if ($user->email_verified_at !== null) {
                    $skipped++;
                    continue;
                }

                $user->sendEmailVerificationNotification();
                $this->recordAuditEvent('user_verification_link_sent', $user, [
                    'reason' => 'Email verification link sent via bulk user action.',
                ]);
                $processed++;
                continue;
            }

            if ($validated['action'] === 'mark_verified') {
                if ($user->email_verified_at !== null) {
                    $skipped++;
                    continue;
                }

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();

                $this->recordAuditEvent('user_verification_marked', $user, [
                    'reason' => 'Email marked verified via bulk user action.',
                ]);
                $processed++;
                continue;
            }

            if ($validated['action'] === 'send_password_reset_link') {
                PasswordBroker::broker()->sendResetLink([
                    'email' => $user->email,
                ]);

                $this->recordAuditEvent('user_password_reset_link_sent', $user, [
                    'reason' => 'Password reset link sent via bulk user action.',
                ]);
                $processed++;
                continue;
            }

            if ($validated['action'] === 'suspend') {
                if ($user->suspended_at !== null || $user->id === $request->user()->id) {
                    $skipped++;
                    continue;
                }

                $user->forceFill([
                    'suspended_at' => now(),
                ])->save();

                $this->recordAuditEvent('user_suspended', $user, [
                    'reason' => 'Account suspended via bulk user action.',
                ]);
                $processed++;
                continue;
            }

            if ($validated['action'] === 'restore') {
                if ($user->suspended_at === null) {
                    $skipped++;
                    continue;
                }

                $user->forceFill([
                    'suspended_at' => null,
                ])->save();

                $this->recordAuditEvent('user_restored', $user, [
                    'reason' => 'Account restored via bulk user action.',
                ]);
                $processed++;
            }
        }

        $actionLabel = $validated['action'] === 'resend_verification'
            ? 'verification email resend'
            : ($validated['action'] === 'mark_verified'
                ? 'email verification update'
            : ($validated['action'] === 'send_password_reset_link'
                ? 'password reset link delivery'
                : ($validated['action'] === 'restore'
                    ? 'account restore'
                    : 'account suspension')));

        $status = $processed > 0
            ? ucfirst($actionLabel).' completed for '.$processed.' user(s).'
            : 'No users were updated.';

        if ($skipped > 0) {
            $status .= ' '.$skipped.' user(s) skipped.';
        }

        if ($selectedBulkPreset) {
            $status = $selectedBulkPreset.': '.$status;
        }

        return redirect()
            ->route('app.admin.users.index', AdminUserIndexFilters::compact($normalizedFilters))
            ->with('status', $status)
            ->with('status_context', 'bulk_user_action');
    }

    public function show(User $user, AssignmentService $assignments): View
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        return view('app.admin-users-show', [
            'managedUser' => $user,
        ] + $this->userDetailData($user, $assignments));
    }

    public function import(Request $request): RedirectResponse
    {
        Gate::authorize('admin-access');

        $validated = $request->validate([
            'users_csv' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ]);

        $file = $validated['users_csv'];
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            return redirect()
                ->route('app.admin.users.index')
                ->with('status', 'The CSV file could not be opened.');
        }

        $header = fgetcsv($handle) ?: [];
        $headerMap = collect($header)
            ->map(fn ($value) => Str::of((string) $value)->trim()->lower()->replace(' ', '_')->value())
            ->values()
            ->all();

        $requiredColumns = ['name', 'email'];

        foreach ($requiredColumns as $requiredColumn) {
            if (! in_array($requiredColumn, $headerMap, true)) {
                fclose($handle);

                return redirect()
                    ->route('app.admin.users.index')
                    ->with('status', 'CSV import needs the columns: name, email, and optional role, team, location, is_admin, account_active, password.');
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowData = $this->csvRowData($headerMap, $row);

            if (($rowData['name'] ?? '') === '' && ($rowData['email'] ?? '') === '') {
                continue;
            }

            $email = trim((string) ($rowData['email'] ?? ''));
            $name = trim((string) ($rowData['name'] ?? ''));

            if ($email === '' || $name === '') {
                $skipped++;
                continue;
            }

            $normalizedRole = $this->normalizeImportedRole($rowData['role'] ?? null);
            $normalizedTeam = $this->normalizeImportedTeam($rowData['team'] ?? null);
            $password = trim((string) ($rowData['password'] ?? ''));
            $isAdmin = $this->normalizeImportedBoolean($rowData['is_admin'] ?? null, false);
            $accountActive = $this->normalizeImportedBoolean($rowData['account_active'] ?? null, true);

            $user = User::query()->firstWhere('email', $email);
            $isNewUser = ! $user;

            if ($isNewUser) {
                $user = User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'is_admin' => $isAdmin,
                    'password' => $password !== '' ? $password : Str::password(16),
                    'suspended_at' => $accountActive ? null : now(),
                    'email_verified_at' => null,
                ]);

                $this->recordAuditEvent('user_imported', $user, [
                    'reason' => 'User created via CSV import.',
                ]);

                $created++;
            } else {
                $attributes = [
                    'name' => $name,
                    'is_admin' => $isAdmin,
                    'suspended_at' => $accountActive ? null : now(),
                ];

                if ($password !== '') {
                    $attributes['password'] = $password;
                }

                $user->fill($attributes);
                $user->save();

                $this->recordAuditEvent('user_imported', $user, [
                    'reason' => 'User updated via CSV import.',
                ]);

                $updated++;
            }

            $normalizedLocation = $this->normalizeImportedLocation($rowData['location'] ?? null);
            $this->syncUserPreference($user, [
                'role' => $normalizedRole,
                'team' => $normalizedTeam,
                'location_id' => $normalizedLocation,
            ]);
        }

        fclose($handle);

        return redirect()
            ->route('app.admin.users.index')
            ->with('status', "CSV import completed. {$created} created, {$updated} updated, {$skipped} skipped.");
    }

    public function showExport(User $user, AssignmentService $assignments): StreamedResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        $detailData = $this->userDetailData($user, $assignments);
        $filename = sprintf('user-detail-%s-%s.csv', $user->id, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($user, $detailData): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['section', 'label', 'value', 'context']);

            fputcsv($handle, ['account', 'user_id', (string) $user->id, '']);
            fputcsv($handle, ['account', 'name', $user->name, '']);
            fputcsv($handle, ['account', 'email', $user->email, '']);
            fputcsv($handle, ['account', 'role', $user->is_admin ? 'admin' : 'learner', '']);
            fputcsv($handle, ['account', 'account_status', $user->suspended_at ? 'suspended' : 'active', '']);
            fputcsv($handle, ['account', 'verification_status', $user->email_verified_at ? 'verified' : 'unverified', '']);
            fputcsv($handle, ['account', 'created_at', $user->created_at?->toDateTimeString() ?? '', '']);
            fputcsv($handle, ['account', 'updated_at', $user->updated_at?->toDateTimeString() ?? '', '']);

            foreach ($detailData['assignmentSummary'] as $key => $value) {
                fputcsv($handle, ['assignment_summary', $key, (string) $value, '']);
            }

            foreach ($detailData['recentLearningEvents'] as $event) {
                $context = $event->entity_type === 'learning_module'
                    ? ($detailData['eventModuleTitles'][(int) $event->entity_id] ?? ('#'.$event->entity_id))
                    : '#'.$event->entity_id;

                fputcsv($handle, [
                    'learning_event',
                    $event->event_type,
                    $event->created_at?->toDateTimeString() ?? '',
                    $event->entity_type.' | '.$context,
                ]);
            }

            foreach ($detailData['recentReminders'] as $reminder) {
                fputcsv($handle, [
                    'reminder',
                    $reminder->reminder_type,
                    $reminder->status,
                    ($reminder->module?->title ?? 'Module removed')
                        .($reminder->due_on ? ' | due '.$reminder->due_on->toDateString() : ''),
                ]);
            }

            foreach ($detailData['recentAuditEvents'] as $event) {
                fputcsv($handle, [
                    'audit_event',
                    $event->action,
                    $event->created_at?->toDateTimeString() ?? '',
                    (string) (($event->meta['reason'] ?? null)
                        ?? (! empty($event->meta['changed_keys']) && is_array($event->meta['changed_keys'])
                            ? 'Changed: '.implode(', ', $event->meta['changed_keys'])
                            : '')),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function edit(User $user): View
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        return view('app.admin-users-form', [
            'managedUser' => $user,
            'formAction' => route('app.admin.users.update', ['user' => $user->id]),
            'formMethod' => 'PATCH',
            'pageTitle' => 'Edit User',
            'pageDescription' => 'Manage identity, role, and password for this account.',
            'submitLabel' => 'Save User',
            'availableRoleOptions' => $this->formRoleOptions(),
            'availableTeamOptions' => $this->formTeamOptions(),
            'availableLocationOptions' => $this->formLocationOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('admin-access');

        $sendMagicLink = $request->boolean('send_magic_link');

        $validated = $this->validatedData($request, null, passwordRequired: ! $sendMagicLink);

        $admin = $request->user();
        $systemRole = $admin->isSiteAdmin() ? ($request->input('system_role', 'learner')) : 'learner';
        $isAdmin = in_array($systemRole, ['site_admin', 'slt_manager', 'manager'], true);

        $user = User::query()->create([
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'is_admin' => $isAdmin,
            'system_role' => $systemRole,
            'managed_teams' => $admin->isSiteAdmin() && in_array($systemRole, ['manager', 'slt_manager'], true)
                ? $request->input('managed_teams', [])
                : null,
            'managed_locations' => $admin->isSiteAdmin() && in_array($systemRole, ['manager', 'slt_manager'], true)
                ? $request->input('managed_locations', [])
                : null,
            'password' => $validated['password'] ?? Str::random(32),
            'suspended_at' => null,
            'email_verified_at' => null,
        ]);

        $this->syncUserPreference($user, $validated);

        // Regular managers auto-assign new users to their own team
        if (! $admin->canManageTeamAssignments() && ! empty($admin->managed_teams)) {
            UserPreference::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['team' => $admin->managed_teams[0]],
            );
        }

        $this->recordAuditEvent('user_created', $user, [
            'changed_keys' => ['name', 'email', 'system_role', 'password', 'role', 'team'],
        ]);

        $status = 'User created.';

        if ($sendMagicLink) {
            $url = URL::temporarySignedRoute(
                'magic-link.verify',
                now()->addHours(48),
                ['user' => $user->id]
            );

            Mail::to($user)->send(new \App\Mail\MagicLinkMail($url));
            $status = 'User created and magic link sent to ' . $user->email . '.';
        }

        return redirect()
            ->route('app.admin.users.edit', ['user' => $user->id])
            ->with('status', $status);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        $before = [
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => (bool) $user->is_admin,
            'suspended_at' => $user->suspended_at?->toDateTimeString(),
            'role' => $user->preference?->role,
            'team' => $user->preference?->team,
        ];
        $validated = $this->validatedData($request, $user);

        $admin = $request->user();

        $attributes = [
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'suspended_at' => $request->boolean('account_active') ? null : now(),
        ];

        // Only site admins can change system role and managed teams
        if ($admin->isSiteAdmin()) {
            $systemRole = $request->input('system_role', $user->system_role ?? 'learner');
            $attributes['system_role'] = $systemRole;
            $attributes['is_admin'] = in_array($systemRole, ['site_admin', 'slt_manager', 'manager'], true);
            $attributes['managed_teams'] = in_array($systemRole, ['manager', 'slt_manager'], true)
                ? $request->input('managed_teams', [])
                : null;
            $attributes['managed_locations'] = in_array($systemRole, ['manager', 'slt_manager'], true)
                ? $request->input('managed_locations', [])
                : null;
        }

        $status = 'User updated.';

        if ($admin->isSiteAdmin() && $user->id === $admin->id && ! ($attributes['is_admin'] ?? true)) {
            $attributes['is_admin'] = true;
            $attributes['system_role'] = 'site_admin';
            $attributes['managed_teams'] = null;
            $status = 'User updated. Your admin access was preserved.';
        }

        if ($user->id === $request->user()->id && $attributes['suspended_at'] !== null) {
            $attributes['suspended_at'] = null;
            $status = $status === 'User updated.'
                ? 'User updated. Your account remained active.'
                : $status.' Your account remained active.';
        }

        if ($attributes['email'] !== $user->email) {
            $attributes['email_verified_at'] = null;
        }

        if (($validated['password'] ?? '') !== '') {
            $attributes['password'] = $validated['password'];
            $status = $status === 'User updated.'
                ? 'User updated and password reset.'
                : $status.' Password reset was also applied.';
        }

        $user->fill($attributes);

        if (array_key_exists('email_verified_at', $attributes)) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->syncUserPreference($user, $validated);

        $changedKeys = collect([
            'name' => $before['name'] !== $user->name,
            'email' => $before['email'] !== $user->email,
            'is_admin' => $before['is_admin'] !== (bool) $user->is_admin,
            'role' => $before['role'] !== ($user->preference?->role ?? null),
            'team' => $before['team'] !== ($user->preference?->team ?? null),
        ])->filter()->keys()->values()->all();

        if ($changedKeys !== []) {
            $this->recordAuditEvent('user_updated', $user, [
                'changed_keys' => $changedKeys,
            ]);
        }

        if (($validated['password'] ?? '') !== '') {
            $this->recordAuditEvent('user_password_reset', $user, [
                'reason' => 'Password reset via user management.',
            ]);
        }

        $beforeSuspended = $before['suspended_at'] !== null;
        $afterSuspended = $user->suspended_at !== null;

        if (! $beforeSuspended && $afterSuspended) {
            $this->recordAuditEvent('user_suspended', $user, [
                'reason' => 'Account suspended via user management.',
            ]);
        }

        if ($beforeSuspended && ! $afterSuspended) {
            $this->recordAuditEvent('user_restored', $user, [
                'reason' => 'Account restored via user management.',
            ]);
        }

        return redirect()
            ->route('app.admin.users.edit', ['user' => $user->id])
            ->with('status', $status);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('app.admin.users.show', ['user' => $user->id])
                ->with('status', 'You cannot delete your own account.');
        }

        $this->recordAuditEvent('user_deleted', $user, [
            'reason' => 'User permanently deleted via admin.',
            'deleted_email' => $user->email,
            'deleted_name' => $user->name,
        ]);

        $user->preference()?->delete();
        $user->delete();

        return redirect()
            ->route('app.admin.users.index')
            ->with('status', 'User deleted successfully.');
    }

    public function toggleAdminAccess(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('admin-write');
        $this->authorizeTeamAccess($user);

        $makeAdmin = ! $user->is_admin;

        if ($user->id === $request->user()->id && ! $makeAdmin) {
            return redirect()
                ->route('app.admin.users.show', ['user' => $user->id])
                ->with('status', 'Admin access was preserved for your account.');
        }

        $user->forceFill([
            'is_admin' => $makeAdmin,
        ])->save();

        $this->recordAuditEvent('user_updated', $user, [
            'changed_keys' => ['is_admin'],
        ]);

        return redirect()
            ->route('app.admin.users.show', ['user' => $user->id])
            ->with('status', $makeAdmin ? 'Admin access granted.' : 'Admin access removed.');
    }

    public function toggleAccountAccess(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        $suspendAccount = $user->suspended_at === null;

        if ($user->id === $request->user()->id && $suspendAccount) {
            return redirect()
                ->route('app.admin.users.show', ['user' => $user->id])
                ->with('status', 'Your account remained active.');
        }

        $user->forceFill([
            'suspended_at' => $suspendAccount ? now() : null,
        ])->save();

        if ($suspendAccount) {
            $this->recordAuditEvent('user_suspended', $user, [
                'reason' => 'Account suspended via user detail quick action.',
            ]);
        } else {
            $this->recordAuditEvent('user_restored', $user, [
                'reason' => 'Account restored via user detail quick action.',
            ]);
        }

        return redirect()
            ->route('app.admin.users.show', ['user' => $user->id])
            ->with('status', $suspendAccount ? 'Account suspended.' : 'Account restored.');
    }

    public function toggleEmailVerification(User $user): RedirectResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        $markVerified = $user->email_verified_at === null;

        $user->forceFill([
            'email_verified_at' => $markVerified ? now() : null,
        ])->save();

        $this->recordAuditEvent($markVerified ? 'user_verification_marked' : 'user_verification_cleared', $user, [
            'reason' => $markVerified
                ? 'Email marked verified via user detail quick action.'
                : 'Email verification cleared via user detail quick action.',
        ]);

        return redirect()
            ->route('app.admin.users.show', ['user' => $user->id])
            ->with('status', $markVerified ? 'Email marked verified.' : 'Email verification cleared.');
    }

    public function sendPasswordResetLink(User $user): RedirectResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        PasswordBroker::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        $this->recordAuditEvent('user_password_reset_link_sent', $user, [
            'reason' => 'Password reset link sent via user detail quick action.',
        ]);

        return redirect()
            ->route('app.admin.users.show', ['user' => $user->id])
            ->with('status', 'Password reset link sent.');
    }

    public function sendEmailVerificationLink(User $user): RedirectResponse
    {
        Gate::authorize('admin-access');
        $this->authorizeTeamAccess($user);

        if ($user->email_verified_at !== null) {
            return redirect()
                ->route('app.admin.users.show', ['user' => $user->id])
                ->with('status', 'Email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        $this->recordAuditEvent('user_verification_link_sent', $user, [
            'reason' => 'Email verification link sent via user detail quick action.',
        ]);

        return redirect()
            ->route('app.admin.users.show', ['user' => $user->id])
            ->with('status', 'Email verification link sent.');
    }

    private function validatedData(Request $request, ?User $user = null, bool $passwordRequired = false): array
    {
        $passwordRules = [$passwordRequired ? 'required' : 'nullable', 'confirmed', Password::defaults()];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['nullable', Rule::in(array_keys($this->formRoleOptions()))],
            'team' => ['nullable', Rule::in(array_keys($this->formTeamOptions()))],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'is_admin' => ['nullable', 'boolean'],
            'account_active' => ['nullable', 'boolean'],
            'password' => $passwordRules,
        ]);
    }

    private function formRoleOptions(): array
    {
        return Role::options();
    }

    private function formTeamOptions(): array
    {
        return Team::options();
    }

    private function formLocationOptions(): array
    {
        return Location::ordered()->pluck('name', 'id')->toArray();
    }

    private function syncUserPreference(User $user, array $validated): void
    {
        $roleKey = $validated['role'] ?? null;
        $admin = auth()->user();

        $fields = [
            'role' => $roleKey ? ($this->formRoleOptions()[$roleKey] ?? null) : null,
        ];

        // Only site admins and SLT managers can change team and location assignments
        if ($admin->canManageTeamAssignments()) {
            $teamKey = $validated['team'] ?? null;
            $fields['team'] = $teamKey ? ($this->formTeamOptions()[$teamKey] ?? null) : null;
            $fields['location_id'] = $validated['location_id'] ?? null;
        }

        UserPreference::query()->updateOrCreate(
            ['user_id' => $user->id],
            $fields,
        );
    }

    private function csvRowData(array $headerMap, array $row): array
    {
        $data = [];

        foreach ($headerMap as $index => $header) {
            $data[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }

        return $data;
    }

    private function normalizeImportedRole(?string $role): ?string
    {
        $value = trim((string) $role);

        if ($value === '') {
            return null;
        }

        $normalized = Str::of($value)->lower()->replace([' / ', '/', '(', ')', '-'], ['_', '_', '', '', '_'])->replace(' ', '_')->replace('__', '_')->trim('_')->value();

        if (array_key_exists($normalized, $this->formRoleOptions())) {
            return $normalized;
        }

        foreach ($this->formRoleOptions() as $key => $label) {
            if (Str::lower($label) === Str::lower($value)) {
                return $key;
            }
        }

        return null;
    }

    private function normalizeImportedTeam(?string $team): ?string
    {
        $value = trim((string) $team);

        if ($value === '') {
            return null;
        }

        $normalized = Str::of($value)->lower()->replace([' / ', '/', '(', ')', '-'], ['_', '_', '', '', '_'])->replace(' ', '_')->replace('__', '_')->trim('_')->value();

        if (array_key_exists($normalized, $this->formTeamOptions())) {
            return $normalized;
        }

        foreach ($this->formTeamOptions() as $key => $label) {
            if (Str::lower($label) === Str::lower($value)) {
                return $key;
            }
        }

        return null;
    }

    private function normalizeImportedLocation(?string $location): ?int
    {
        $value = trim((string) $location);

        if ($value === '') {
            return null;
        }

        // Try exact name match (case-insensitive)
        $found = Location::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($value)])
            ->first();

        if ($found) {
            return $found->id;
        }

        // Try slug match
        $found = Location::query()->where('slug', Str::slug($value, '_'))->first();

        return $found?->id;
    }

    private function normalizeImportedBoolean(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return in_array(Str::lower((string) $value), ['1', 'true', 'yes', 'y', 'active', 'admin'], true);
    }

    private function validatedIndexFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in(array_merge(array_keys(self::roleFilterOptions()), self::LEGACY_ROLE_FILTERS))],
            'team' => ['nullable', Rule::in(array_keys(self::teamFilterOptions()))],
            'location' => ['nullable', Rule::in(array_keys(self::locationFilterOptions()))],
            'account_status' => ['nullable', 'in:all,active,suspended'],
            'verification_status' => ['nullable', 'in:all,verified,unverified'],
            'inactivity_status' => ['nullable', 'in:all,never,inactive_30'],
            'attention_status' => ['nullable', 'in:all,needs_attention'],
            'training_compliance' => ['nullable', Rule::in(array_keys(self::TRAINING_COMPLIANCE_OPTIONS))],
            'sort' => ['nullable', 'in:created_at,name,email,last_login_at'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'limit' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ]);
    }

    private function selectedBulkAction(Request $request): string
    {
        $validated = $request->validate([
            'bulk_action' => ['nullable', 'in:resend_verification,mark_verified,send_password_reset_link,suspend,restore'],
        ]);

        return $validated['bulk_action'] ?? 'resend_verification';
    }

    private function selectedBulkPreset(array $filters, string $selectedBulkAction): ?string
    {
        foreach (AdminUserBulkPresets::definitions() as $label => $preset) {
            $matches = true;

            foreach ($preset as $key => $value) {
                $actual = $key === 'bulk_action' ? $selectedBulkAction : ($filters[$key] ?? null);

                if ($actual !== $value) {
                    $matches = false;
                    break;
                }
            }

            if ($matches) {
                return $label;
            }
        }

        return null;
    }

    private function bulkPresetCounts(): array
    {
        $counts = [];
        $admin = auth()->user();

        foreach (AdminUserBulkPresets::definitions() as $label => $preset) {
            $counts[$label] = AdminUserIndexFilters::filteredQuery(AdminUserIndexFilters::normalize([
                'role' => 'all',
                'team' => 'all',
                'account_status' => $preset['account_status'] ?? 'all',
                'verification_status' => $preset['verification_status'] ?? 'all',
                'inactivity_status' => $preset['inactivity_status'] ?? 'all',
                'attention_status' => $preset['attention_status'] ?? 'all',
                'training_compliance' => 'all',
            ]), $admin)->count();
        }

        return $counts;
    }

    private function statusBanners(Request $request): array
    {
        $status = $request->session()->get('status');
        $statusContext = $request->session()->get('status_context');

        if (! is_string($status) || $status === '') {
            return [
                'page' => null,
                'bulk' => null,
            ];
        }

        if ($statusContext !== 'bulk_user_action') {
            return [
                'page' => $status,
                'bulk' => null,
            ];
        }

        $preset = null;
        $message = $status;

        if (str_contains($status, ': ')) {
            [$possiblePreset, $possibleMessage] = explode(': ', $status, 2);

            if (array_key_exists($possiblePreset, AdminUserBulkPresets::definitions())) {
                $preset = $possiblePreset;
                $message = $possibleMessage;
            }
        }

        return [
            'page' => null,
            'bulk' => [
                'preset' => $preset,
                'message' => $message,
                'styles' => AdminUserBulkPresets::styles($preset),
            ],
        ];
    }

    private function userAssignmentSummary(User $user, AssignmentService $assignments): array
    {
        $requiredModules = LearningModule::query()
            ->where('status', 'published')
            ->where('is_required', true)
            ->get();

        $progressByModuleId = ModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('learning_module_id', $requiredModules->pluck('id'))
            ->get()
            ->keyBy('learning_module_id');

        $assignmentRows = $requiredModules
            ->map(function (LearningModule $module) use ($assignments, $progressByModuleId, $user) {
                $progress = $progressByModuleId->get($module->id);
                $assignment = $assignments->forUser($user, $module, $progress);

                if (! $assignment['is_required'] && ! $assignment['is_waived']) {
                    return null;
                }

                return $assignment;
            })
            ->filter()
            ->values();

        $pendingRemindersCount = Schema::hasTable('assignment_reminders')
            ? AssignmentReminder::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->count()
            : 0;

        return [
            'required_total' => $assignmentRows->count(),
            'overdue_total' => $assignmentRows->where('is_overdue', true)->count(),
            'waived_total' => $assignmentRows->where('is_waived', true)->count(),
            'pending_reminders_count' => $pendingRemindersCount,
        ];
    }

    private function userDetailData(User $user, AssignmentService $assignments): array
    {
        $recentAuditEvents = AssignmentAuditEvent::query()
            ->with(['actor:id,name,email', 'targetUser:id,name,email'])
            ->where(function ($query) use ($user) {
                $query->where('target_user_id', $user->id)
                    ->orWhere(function ($query) use ($user) {
                        $query->where('entity_type', 'user')
                            ->where('entity_id', $user->id);
                    });
            })
            ->latest('created_at')
            ->limit(10)
            ->get();

        $assignmentSummary = $this->userAssignmentSummary($user, $assignments);
        $recentReminders = Schema::hasTable('assignment_reminders')
            ? AssignmentReminder::query()
                ->with('module:id,title')
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->limit(6)
                ->get()
            : collect();
        $recentLearningEvents = Schema::hasTable('learning_events')
            ? LearningEvent::query()
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->limit(6)
                ->get()
            : collect();
        $eventModuleTitles = LearningModule::query()
            ->whereIn(
                'id',
                $recentLearningEvents
                    ->where('entity_type', 'learning_module')
                    ->pluck('entity_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
            )
            ->pluck('title', 'id')
            ->toArray();

        return [
            'assignmentSummary' => $assignmentSummary,
            'recentAuditEvents' => $recentAuditEvents,
            'recentReminders' => $recentReminders,
            'recentLearningEvents' => $recentLearningEvents,
            'eventModuleTitles' => $eventModuleTitles,
        ];
    }

    private function recordAuditEvent(string $action, User $user, array $meta = []): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('assignment_audit_events')) {
            return;
        }

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => auth()->id(),
            'target_user_id' => $user->id,
            'learning_module_id' => null,
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'action' => $action,
            'meta' => $meta,
        ]);
    }

    private function authorizeTeamAccess(User $user): void
    {
        $admin = auth()->user();
        if ($admin->isSiteAdmin()) {
            return;
        }
        if (! $admin->canManageTeam($user->preference?->team)) {
            abort(403);
        }
        if (! $admin->canManageLocation($user->preference?->location?->name)) {
            abort(403);
        }
    }
}
