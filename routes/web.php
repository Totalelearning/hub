<?php

use App\Http\Controllers\AdminAssignmentDashboardController;
use App\Http\Controllers\AdminAiUsageController;
use App\Http\Controllers\AdminComplianceReportController;
use App\Http\Controllers\AdminCourseAnalyticsController;
use App\Http\Controllers\AdminGamificationController;
use App\Http\Controllers\AdminLocationComparisonController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\AdminTopicController;
use App\Http\Controllers\AdminFeedScoringController;
use App\Http\Controllers\AdminLearningEventsReportController;
use App\Http\Controllers\AdminLearningPathController;
use App\Http\Controllers\AdminLearningModuleController;
use App\Http\Controllers\AdminRankingSettingsController;
use App\Http\Controllers\AdminReminderSettingsController;
use App\Http\Controllers\AdminRolesTeamsController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AppBadgesController;
use App\Http\Controllers\AppFeedController;
use App\Http\Controllers\CourseReinforcementController;
use App\Http\Controllers\AppLeaderboardController;
use App\Http\Controllers\AppLearningPathController;
use App\Http\Controllers\AppLearningEventController;
use App\Http\Controllers\AppFeedSaveController;
use App\Http\Controllers\AppCourseController;
use App\Http\Controllers\AppModuleController;
use App\Http\Controllers\AppScormController;
use App\Http\Controllers\AppReminderController;
use App\Http\Controllers\AppReinforcementController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (auth()->user()->isParent()) {
        return redirect()->route('app.parent.dashboard');
    }

    return \Illuminate\Support\Facades\Gate::allows('admin-access')
        ? redirect()->route('app.admin.assignments')
        : redirect()->route('app.feed');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Totale Learning app routes
|--------------------------------------------------------------------------
| Restored after Breeze install overwrote routes/web.php
*/
/*
|--------------------------------------------------------------------------
| Parent portal routes
|--------------------------------------------------------------------------
*/
Route::prefix('app/parent')->middleware(['auth', \App\Http\Middleware\EnsureUserIsParent::class])->group(function () {
    Route::get('/', [ParentDashboardController::class, 'index'])->name('app.parent.dashboard');
    Route::get('courses/{course}', [AppCourseController::class, 'show'])->name('app.parent.courses.show');
    Route::get('modules/{module}', [AppModuleController::class, 'show'])->name('app.parent.modules.show');
    Route::get('modules/{module}/scorm', [AppScormController::class, 'launch'])->name('app.parent.modules.scorm.launch');
    Route::get('modules/{module}/scorm/content/{path?}', [AppScormController::class, 'asset'])
        ->where('path', '.*')
        ->name('app.parent.modules.scorm.asset');
    Route::post('modules/{module}/scorm/runtime', [AppScormController::class, 'runtime'])
        ->name('app.parent.modules.scorm.runtime');
});

Route::prefix('app')->middleware('auth')->group(function () {
    Route::get('admin/assignments', AdminAssignmentDashboardController::class)
        ->name('app.admin.assignments');
    Route::get('admin/users', [AdminUserController::class, 'index'])
        ->name('app.admin.users.index');
    Route::get('admin/users/create', [AdminUserController::class, 'create'])
        ->name('app.admin.users.create');
    Route::post('admin/users', [AdminUserController::class, 'store'])
        ->name('app.admin.users.store');
    Route::post('admin/users/import', [AdminUserController::class, 'import'])
        ->name('app.admin.users.import');
    Route::get('admin/users/export', [AdminUserController::class, 'export'])
        ->name('app.admin.users.export');
    Route::post('admin/users/bulk', [AdminUserController::class, 'bulkUpdate'])
        ->name('app.admin.users.bulk-update');
    Route::get('admin/users/{user}', [AdminUserController::class, 'show'])
        ->whereNumber('user')
        ->name('app.admin.users.show');
    Route::get('admin/users/{user}/export', [AdminUserController::class, 'showExport'])
        ->whereNumber('user')
        ->name('app.admin.users.show.export');
    Route::patch('admin/users/{user}/admin-access', [AdminUserController::class, 'toggleAdminAccess'])
        ->whereNumber('user')
        ->name('app.admin.users.toggle-admin-access');
    Route::patch('admin/users/{user}/account-access', [AdminUserController::class, 'toggleAccountAccess'])
        ->whereNumber('user')
        ->name('app.admin.users.toggle-account-access');
    Route::patch('admin/users/{user}/email-verification', [AdminUserController::class, 'toggleEmailVerification'])
        ->whereNumber('user')
        ->name('app.admin.users.toggle-email-verification');
    Route::post('admin/users/{user}/password-reset-link', [AdminUserController::class, 'sendPasswordResetLink'])
        ->whereNumber('user')
        ->name('app.admin.users.send-password-reset-link');
    Route::post('admin/users/{user}/email-verification-link', [AdminUserController::class, 'sendEmailVerificationLink'])
        ->whereNumber('user')
        ->name('app.admin.users.send-email-verification-link');
    Route::get('admin/users/{user}/edit', [AdminUserController::class, 'edit'])
        ->whereNumber('user')
        ->name('app.admin.users.edit');
    Route::patch('admin/users/{user}', [AdminUserController::class, 'update'])
        ->whereNumber('user')
        ->name('app.admin.users.update');
    Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy'])
        ->whereNumber('user')
        ->name('app.admin.users.destroy');
    Route::get('admin/roles-teams', [AdminRolesTeamsController::class, 'index'])
        ->name('app.admin.roles-teams.index');
    Route::post('admin/roles-teams/roles', [AdminRolesTeamsController::class, 'storeRole'])
        ->name('app.admin.roles-teams.roles.store');
    Route::patch('admin/roles-teams/roles/{role}', [AdminRolesTeamsController::class, 'updateRole'])
        ->whereNumber('role')
        ->name('app.admin.roles-teams.roles.update');
    Route::delete('admin/roles-teams/roles/{role}', [AdminRolesTeamsController::class, 'destroyRole'])
        ->whereNumber('role')
        ->name('app.admin.roles-teams.roles.destroy');
    Route::post('admin/roles-teams/teams', [AdminRolesTeamsController::class, 'storeTeam'])
        ->name('app.admin.roles-teams.teams.store');
    Route::patch('admin/roles-teams/teams/{team}', [AdminRolesTeamsController::class, 'updateTeam'])
        ->whereNumber('team')
        ->name('app.admin.roles-teams.teams.update');
    Route::delete('admin/roles-teams/teams/{team}', [AdminRolesTeamsController::class, 'destroyTeam'])
        ->whereNumber('team')
        ->name('app.admin.roles-teams.teams.destroy');
    Route::post('admin/roles-teams/locations', [AdminRolesTeamsController::class, 'storeLocation'])
        ->name('app.admin.roles-teams.locations.store');
    Route::patch('admin/roles-teams/locations/{location}', [AdminRolesTeamsController::class, 'updateLocation'])
        ->whereNumber('location')
        ->name('app.admin.roles-teams.locations.update');
    Route::delete('admin/roles-teams/locations/{location}', [AdminRolesTeamsController::class, 'destroyLocation'])
        ->whereNumber('location')
        ->name('app.admin.roles-teams.locations.destroy');
    Route::get('admin/ai/usages', AdminAiUsageController::class)
        ->name('app.admin.ai-usages');
    Route::get('admin/ai/usages/export', [AdminAiUsageController::class, 'export'])
        ->name('app.admin.ai-usages.export');
    Route::get('admin/compliance', AdminComplianceReportController::class)
        ->name('app.admin.compliance');
    Route::get('admin/compliance/export', [AdminComplianceReportController::class, 'export'])
        ->name('app.admin.compliance.export');
    Route::get('admin/compliance/learners', [AdminComplianceReportController::class, 'learners'])
        ->name('app.admin.compliance.learners');
    Route::get('admin/compliance/learners/export', [AdminComplianceReportController::class, 'learnersExport'])
        ->name('app.admin.compliance.learners.export');
    Route::get('admin/events', [AdminLearningEventsReportController::class, 'index'])
        ->name('app.admin.events.index');
    Route::get('admin/scoring', [AdminFeedScoringController::class, 'edit'])
        ->name('app.admin.scoring.edit');
    Route::post('admin/scoring', [AdminFeedScoringController::class, 'update'])
        ->name('app.admin.scoring.update');
    Route::post('admin/scoring/reset', [AdminFeedScoringController::class, 'reset'])
        ->name('app.admin.scoring.reset');
    Route::post('admin/scoring/preset', [AdminFeedScoringController::class, 'applyPreset'])
        ->name('app.admin.scoring.preset');
    Route::get('admin/ranking', [AdminRankingSettingsController::class, 'edit'])
        ->name('app.admin.ranking.edit');
    Route::get('admin/ranking/export/probes', [AdminRankingSettingsController::class, 'exportProbes'])
        ->name('app.admin.ranking.export.probes');
    Route::get('admin/ranking/export/severity-transitions', [AdminRankingSettingsController::class, 'exportSeverityTransitions'])
        ->name('app.admin.ranking.export.severity-transitions');
    Route::get('admin/ranking/export/incident-bundle', [AdminRankingSettingsController::class, 'exportIncidentBundle'])
        ->name('app.admin.ranking.export.incident-bundle');
    Route::post('admin/ranking', [AdminRankingSettingsController::class, 'update'])
        ->name('app.admin.ranking.update');
    Route::post('admin/ranking/test', [AdminRankingSettingsController::class, 'testProvider'])
        ->name('app.admin.ranking.test');
    Route::post('admin/ranking/reset', [AdminRankingSettingsController::class, 'reset'])
        ->name('app.admin.ranking.reset');
    Route::get('admin/reminder-settings', [AdminReminderSettingsController::class, 'edit'])
        ->name('app.admin.reminder-settings.edit');
    Route::post('admin/reminder-settings', [AdminReminderSettingsController::class, 'update'])
        ->name('app.admin.reminder-settings.update');
    Route::post('admin/reminder-settings/reset', [AdminReminderSettingsController::class, 'reset'])
        ->name('app.admin.reminder-settings.reset');
    Route::get('admin/events/export', [AdminLearningEventsReportController::class, 'export'])
        ->name('app.admin.events.export');
    Route::get('admin/modules', [AdminLearningModuleController::class, 'index'])
        ->name('app.admin.modules.index');
    Route::get('admin/scorm', [AdminLearningModuleController::class, 'scormOverview'])
        ->name('app.admin.scorm.index');
    Route::get('admin/scorm/handout', [AdminLearningModuleController::class, 'scormHandout'])
        ->name('app.admin.scorm.handout');
    Route::get('admin/scorm/export', [AdminLearningModuleController::class, 'scormOverviewExport'])
        ->name('app.admin.scorm.export');
    Route::post('admin/scorm/reset-demo', [AdminLearningModuleController::class, 'resetDemoData'])
        ->name('app.admin.scorm.reset-demo');
    Route::get('admin/paths', [AdminLearningPathController::class, 'index'])
        ->name('app.admin.paths.index');
    Route::get('admin/paths/create', [AdminLearningPathController::class, 'create'])
        ->name('app.admin.paths.create');
    Route::post('admin/paths', [AdminLearningPathController::class, 'store'])
        ->name('app.admin.paths.store');
    Route::get('admin/paths/{path}/edit', [AdminLearningPathController::class, 'edit'])
        ->whereNumber('path')
        ->name('app.admin.paths.edit');
    Route::get('admin/paths/{path}', [AdminLearningPathController::class, 'show'])
        ->whereNumber('path')
        ->name('app.admin.paths.show');
    Route::patch('admin/paths/{path}', [AdminLearningPathController::class, 'update'])
        ->whereNumber('path')
        ->name('app.admin.paths.update');
    Route::get('admin/modules/create', [AdminLearningModuleController::class, 'create'])
        ->name('app.admin.modules.create');
    Route::get('admin/modules/create-scorm', [AdminLearningModuleController::class, 'createScorm'])
        ->name('app.admin.modules.create-scorm');
    Route::post('admin/modules', [AdminLearningModuleController::class, 'store'])
        ->name('app.admin.modules.store');
    Route::get('admin/modules/{module}/edit', [AdminLearningModuleController::class, 'edit'])
        ->whereNumber('module')
        ->name('app.admin.modules.edit');
    Route::patch('admin/modules/{module}', [AdminLearningModuleController::class, 'update'])
        ->whereNumber('module')
        ->name('app.admin.modules.update');
    Route::post('admin/modules/{module}/scorm-package', [AdminLearningModuleController::class, 'uploadScorm'])
        ->whereNumber('module')
        ->name('app.admin.modules.scorm.upload');
    Route::post('admin/modules/{module}/reinforcement-questions/draft', [AdminLearningModuleController::class, 'draftReinforcementQuestions'])
        ->whereNumber('module')
        ->name('app.admin.modules.reinforcement-questions.draft');
    Route::patch('admin/modules/{module}/reinforcement-questions', [AdminLearningModuleController::class, 'updateReinforcementQuestions'])
        ->whereNumber('module')
        ->name('app.admin.modules.reinforcement-questions.update');
    Route::patch('admin/modules/{module}/reinforcement-questions/approve', [AdminLearningModuleController::class, 'approveReinforcementQuestions'])
        ->whereNumber('module')
        ->name('app.admin.modules.reinforcement-questions.approve');
    Route::patch('admin/modules/{module}/scorm-package/{asset}/activate', [AdminLearningModuleController::class, 'activateScormPackage'])
        ->whereNumber('module')
        ->whereNumber('asset')
        ->name('app.admin.modules.scorm.activate');
    Route::patch('admin/modules/{module}/status', [AdminLearningModuleController::class, 'transition'])
        ->whereNumber('module')
        ->name('app.admin.modules.transition');
    Route::post('admin/modules/bulk-status', [AdminLearningModuleController::class, 'bulkTransition'])
        ->name('app.admin.modules.bulk-transition');

    Route::get('admin/courses', [AdminCourseController::class, 'index'])
        ->name('app.admin.courses.index');
    Route::get('admin/courses/create', [AdminCourseController::class, 'create'])
        ->name('app.admin.courses.create');
    Route::post('admin/courses', [AdminCourseController::class, 'store'])
        ->name('app.admin.courses.store');
    Route::get('admin/courses/{course}/edit', [AdminCourseController::class, 'edit'])
        ->name('app.admin.courses.edit');
    Route::patch('admin/courses/{course}', [AdminCourseController::class, 'update'])
        ->name('app.admin.courses.update');
    Route::delete('admin/courses/{course}', [AdminCourseController::class, 'destroy'])
        ->name('app.admin.courses.destroy');
    Route::post('admin/courses/bulk-transition', [AdminCourseController::class, 'bulkTransition'])
        ->name('app.admin.courses.bulk-transition');
    Route::get('admin/course-analytics', [AdminCourseAnalyticsController::class, 'index'])
        ->name('app.admin.course-analytics');
    Route::get('admin/course-analytics/export', [AdminCourseAnalyticsController::class, 'export'])
        ->name('app.admin.course-analytics.export');
    Route::get('admin/course-analytics/courses-json', [AdminCourseAnalyticsController::class, 'coursesJson'])
        ->name('app.admin.course-analytics.courses-json');
    Route::get('admin/course-analytics/hotspots-json', [AdminCourseAnalyticsController::class, 'hotspotsJson'])
        ->name('app.admin.course-analytics.hotspots-json');
    Route::get('admin/course-analytics/attempts-json', [AdminCourseAnalyticsController::class, 'attemptsJson'])
        ->name('app.admin.course-analytics.attempts-json');
    Route::get('admin/course-analytics/gaps', [AdminCourseAnalyticsController::class, 'gapsJson'])
        ->name('app.admin.course-analytics.gaps');
    Route::get('admin/course-analytics/summary-json', [AdminCourseAnalyticsController::class, 'summaryJson'])
        ->name('app.admin.course-analytics.summary-json');
    Route::get('admin/course-analytics/location-comparison-json', [AdminCourseAnalyticsController::class, 'locationComparisonJson'])
        ->middleware('can:trustee-view')
        ->name('app.admin.course-analytics.location-comparison-json');
    Route::get('admin/course-analytics/team-comparison-json', [AdminCourseAnalyticsController::class, 'teamComparisonJson'])
        ->middleware('can:trustee-view')
        ->name('app.admin.course-analytics.team-comparison-json');
    Route::get('admin/course-analytics/role-comparison-json', [AdminCourseAnalyticsController::class, 'roleComparisonJson'])
        ->middleware('can:trustee-view')
        ->name('app.admin.course-analytics.role-comparison-json');
    Route::get('admin/course-analytics/attempts/{attempt}', [AdminCourseAnalyticsController::class, 'showAttempt'])
        ->whereNumber('attempt')
        ->name('app.admin.course-analytics.attempt');

    Route::get('admin/gamification', [AdminGamificationController::class, 'index'])
        ->name('app.admin.gamification');
    Route::get('admin/gamification/export', [AdminGamificationController::class, 'export'])
        ->name('app.admin.gamification.export');
    Route::get('admin/gamification/settings', [AdminGamificationController::class, 'settings'])
        ->name('app.admin.gamification.settings');
    Route::post('admin/gamification/settings', [AdminGamificationController::class, 'updateSettings'])
        ->name('app.admin.gamification.settings.update');
    Route::post('admin/gamification/settings/reset', [AdminGamificationController::class, 'resetSettings'])
        ->name('app.admin.gamification.settings.reset');

    Route::get('admin/locations', [AdminLocationComparisonController::class, 'index'])
        ->name('app.admin.locations.index');
    Route::get('admin/locations/export', [AdminLocationComparisonController::class, 'export'])
        ->name('app.admin.locations.export');

    Route::post('admin/topics', [AdminTopicController::class, 'store'])
        ->name('app.admin.topics.store');
    Route::get('admin/topics/{topic}/check', [AdminTopicController::class, 'check'])
        ->name('app.admin.topics.check');
    Route::delete('admin/topics/{topic}', [AdminTopicController::class, 'destroy'])
        ->name('app.admin.topics.destroy');

    Route::get('admin/assignments/export', [AdminAssignmentDashboardController::class, 'export'])
        ->name('app.admin.assignments.export');
    Route::get('admin/assignments/settings-export', [AdminAssignmentDashboardController::class, 'exportSettings'])
        ->name('app.admin.assignments.settings-export');
    Route::post('admin/assignments/reminders/sync', [AdminAssignmentDashboardController::class, 'syncReminders'])
        ->name('app.admin.assignments.reminders.sync');
    Route::post('admin/assignments/reminders/run', [AdminAssignmentDashboardController::class, 'runReminders'])
        ->name('app.admin.assignments.reminders.run');
    Route::patch('admin/assignments/reminders/{reminder}/sent', [AdminAssignmentDashboardController::class, 'markReminderSent'])
        ->whereNumber('reminder')
        ->name('app.admin.assignments.reminders.sent');
    Route::get('admin/assignments/audit', [AdminAssignmentDashboardController::class, 'audit'])
        ->name('app.admin.assignments.audit');
    Route::get('admin/assignments/audit/export', [AdminAssignmentDashboardController::class, 'auditExport'])
        ->name('app.admin.assignments.audit.export');
    Route::post('admin/assignments/rules', [AdminAssignmentDashboardController::class, 'storeRule'])
        ->name('app.admin.assignments.rules.store');
    Route::delete('admin/assignments/rules/{rule}', [AdminAssignmentDashboardController::class, 'destroyRule'])
        ->whereNumber('rule')
        ->name('app.admin.assignments.rules.destroy');
    Route::post('admin/assignments/users/{user}/modules/{module}/waiver', [AdminAssignmentDashboardController::class, 'storeWaiver'])
        ->whereNumber('user')
        ->whereNumber('module')
        ->name('app.admin.assignments.waivers.store');
    Route::delete('admin/assignments/users/{user}/modules/{module}/waiver', [AdminAssignmentDashboardController::class, 'destroyWaiver'])
        ->whereNumber('user')
        ->whereNumber('module')
        ->name('app.admin.assignments.waivers.destroy');
    Route::get('admin/assignments/roles/{role}', [AdminAssignmentDashboardController::class, 'role'])
        ->name('app.admin.assignments.role');
    Route::get('admin/assignments/users/{user}/export', [AdminAssignmentDashboardController::class, 'userExport'])
        ->whereNumber('user')
        ->name('app.admin.assignments.user.export');
    Route::get('admin/assignments/users/{user}/events', [AdminAssignmentDashboardController::class, 'userEvents'])
        ->whereNumber('user')
        ->name('app.admin.assignments.user.events');
    Route::get('admin/assignments/users/{user}/events/export', [AdminAssignmentDashboardController::class, 'userEventsExport'])
        ->whereNumber('user')
        ->name('app.admin.assignments.user.events.export');
    Route::get('admin/assignments/users/{user}', [AdminAssignmentDashboardController::class, 'user'])
        ->whereNumber('user')
        ->name('app.admin.assignments.user');
    Route::get('admin/assignments/compliance-areas/{area}', [AdminAssignmentDashboardController::class, 'complianceArea'])
        ->name('app.admin.assignments.compliance-area');
    Route::get('leaderboard', [AppLeaderboardController::class, 'index'])->name('app.leaderboard');
    Route::get('badges', [AppBadgesController::class, 'index'])->name('app.badges');
    Route::get('feed', [AppFeedController::class, 'index'])->name('app.feed');
    Route::get('feed/required', [AppFeedController::class, 'required'])->name('app.feed.required');
    Route::get('feed/recommended', [AppFeedController::class, 'recommended'])->name('app.feed.recommended');
    Route::get('feed/saved', [AppFeedController::class, 'saved'])->name('app.feed.saved');

    Route::get('reinforcement/course/{token}', [CourseReinforcementController::class, 'show'])->name('course-reinforcement.show');
    Route::post('reinforcement/course/{token}', [CourseReinforcementController::class, 'submit'])->name('course-reinforcement.submit');
    Route::get('reinforcement/course/{token}/result', [CourseReinforcementController::class, 'result'])->name('course-reinforcement.result');
    Route::get('paths', [AppLearningPathController::class, 'index'])->name('app.paths');
    Route::get('reminders', [AppReminderController::class, 'index'])->name('app.reminders');
    Route::patch('reminders/{notification}/read', [AppReminderController::class, 'markRead'])
        ->name('app.reminders.read');
    Route::patch('reminders/read-all', [AppReminderController::class, 'markAllRead'])
        ->name('app.reminders.read-all');
    Route::get('reinforcement/{touchpoint}', [AppReinforcementController::class, 'show'])
        ->whereNumber('touchpoint')
        ->name('app.reinforcement.show');
    Route::post('reinforcement/{touchpoint}/submit', [AppReinforcementController::class, 'submit'])
        ->whereNumber('touchpoint')
        ->name('app.reinforcement.submit');
    Route::post('reinforcement/{touchpoint}/complete', [AppReinforcementController::class, 'complete'])
        ->whereNumber('touchpoint')
        ->name('app.reinforcement.complete');
    Route::post('events', [AppLearningEventController::class, 'store'])
        ->name('app.events.store');
    Route::get('preferences', fn () => view('app.preferences'))
        ->name('app.preferences');
    Route::get('assignment-rules', function () {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('admin-access'), 403);

        return view('app.assignment-rules');
    })->name('app.assignment-rules');
    Route::get('test-livewire', fn () => view('app.test-livewire'))
        ->name('app.test-livewire');

    Route::post('feed/{module}/save', [AppFeedSaveController::class, 'save'])
        ->name('app.feed.save');

    Route::post('feed/{module}/unsave', [AppFeedSaveController::class, 'unsave'])
        ->name('app.feed.unsave');

    Route::get('courses/{course}', [AppCourseController::class, 'show'])
        ->whereNumber('course')
        ->name('app.courses.show');

    Route::get('modules/{module}', [AppModuleController::class, 'show'])
        ->whereNumber('module')
        ->name('app.modules.show');
    Route::get('modules/{module}/scorm', [AppScormController::class, 'launch'])
        ->whereNumber('module')
        ->name('app.modules.scorm.launch');
    Route::get('modules/{module}/scorm/content/{path?}', [AppScormController::class, 'asset'])
        ->whereNumber('module')
        ->where('path', '.*')
        ->name('app.modules.scorm.asset');
    Route::post('modules/{module}/scorm/runtime', [AppScormController::class, 'runtime'])
        ->whereNumber('module')
        ->name('app.modules.scorm.runtime');
  });


/*
|--------------------------------------------------------------------------
| LearningUIUX preview routes
|--------------------------------------------------------------------------
| Register /preview routes for all learning-*.blade.php template pages.
*/
$learningPreviewViews = collect(glob(resource_path('views/pages/learning-*.blade.php')) ?: [])
    ->map(fn (string $path) => basename($path, '.blade.php'))
    ->filter()
    ->unique()
    ->sort()
    ->values();

Route::prefix('preview')->group(function () use ($learningPreviewViews) {
    Route::get('dashboard', fn () => redirect()->route('preview.learning-dashboard'))
        ->name('preview.dashboard');
    Route::get('components', fn () => view('pages.components'))
        ->name('preview.components');

    foreach ($learningPreviewViews as $base) {
        Route::get($base, fn () => view("pages.$base"))->name("preview.$base");
    }

    if (!$learningPreviewViews->contains('learning-student-home')) {
        Route::get('learning-student-home', fn () => view('pages.learning-student-home'))
            ->name('preview.learning-student-home');
    }
});
require __DIR__.'/auth.php';

