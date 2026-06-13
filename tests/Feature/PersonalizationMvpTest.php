<?php

namespace Tests\Feature;

use App\Livewire\UserPreferencesPage;
use App\Livewire\AssignmentRulesPage;
use App\Livewire\ModuleProgressPanel;
use App\Models\AiProviderUsage;
use App\Models\AssignmentAuditEvent;
use App\Models\AssignmentWaiver;
use App\Models\ComplianceRoleRule;
use App\Models\LearningEvent;
use App\Models\LearningAsset;
use App\Models\LearningModule;
use App\Models\LearningPath;
use App\Models\ModuleAcknowledgement;
use App\Models\ModuleProgress;
use App\Models\RankingSetting;
use App\Models\AssignmentReminder;
use App\Models\ReinforcementQuestion;
use App\Models\ReinforcementQuestionSet;
use App\Models\ReinforcementTouchpoint;
use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\AssignmentReminderNotification;
use App\Services\AssignmentService;
use App\Services\FeedRankingService;
use App\Services\FeedScoringService;
use App\Services\ReinforcementService;
use Database\Seeders\LearningModuleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class PersonalizationMvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(UserPreferencesPage::class)
            ->set('topics', ['math', 'science'])
            ->set('difficulty', 'beginner')
            ->set('role', 'student')
            ->set('goal', 'pass exam')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'difficulty' => 'beginner',
            'role' => 'student',
            'goal' => 'pass exam',
        ]);

        $preference = UserPreference::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($preference);
        $this->assertSame(['math', 'science'], $preference->topics);
        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'preferences_saved',
            'entity_type' => 'user_preference',
            'entity_id' => $preference->id,
        ]);
    }

    public function test_feed_ordering_changes_when_preferences_change(): void
    {
        $user = User::factory()->create();

        $mathModule = LearningModule::query()->create([
            'title' => 'Module Math',
            'description' => 'Math module',
            'status' => 'published',
            'topic' => 'math',
            'difficulty' => 'beginner',
        ]);

        $historyModule = LearningModule::query()->create([
            'title' => 'Module History',
            'description' => 'History module',
            'status' => 'published',
            'topic' => 'history',
            'difficulty' => 'advanced',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['math'],
            'difficulty' => 'beginner',
            'role' => 'student',
            'goal' => 'first pass',
        ]);

        $this->actingAs($user);

        $firstView = app(\App\Http\Controllers\AppFeedController::class)->index();
        $firstModules = $firstView->getData()['modules'];
        $this->assertSame($mathModule->id, $firstModules->first()->id);

        $preference = UserPreference::query()->where('user_id', $user->id)->firstOrFail();
        $preference->topics = ['history'];
        $preference->difficulty = 'advanced';
        $preference->save();
        auth()->setUser($user->fresh());

        $secondView = app(\App\Http\Controllers\AppFeedController::class)->index();
        $secondModules = $secondView->getData()['modules'];
        $this->assertSame($historyModule->id, $secondModules->first()->id);
    }

    public function test_feed_ordering_changes_when_goal_changes(): void
    {
        $user = User::factory()->create();

        $auditModule = LearningModule::query()->create([
            'title' => 'Compliance Audit Fundamentals',
            'description' => 'Audit evidence and compliance reporting workflow.',
            'status' => 'published',
            'topic' => 'compliance',
            'difficulty' => 'beginner',
        ]);

        $coachingModule = LearningModule::query()->create([
            'title' => 'Leadership Coaching Essentials',
            'description' => 'Coaching conversations and manager feedback loops.',
            'status' => 'published',
            'topic' => 'leadership',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => [],
            'difficulty' => 'any',
            'role' => 'manager',
            'goal' => 'build compliance audit readiness',
        ]);

        $this->actingAs($user);

        $firstView = app(\App\Http\Controllers\AppFeedController::class)->index();
        $firstModules = $firstView->getData()['modules'];
        $this->assertSame($auditModule->id, $firstModules->first()->id);

        $preference = UserPreference::query()->where('user_id', $user->id)->firstOrFail();
        $preference->goal = 'improve manager coaching effectiveness';
        $preference->save();
        auth()->setUser($user->fresh());

        $secondView = app(\App\Http\Controllers\AppFeedController::class)->index();
        $secondModules = $secondView->getData()['modules'];
        $this->assertSame($coachingModule->id, $secondModules->first()->id);
    }

    public function test_goal_affinity_adds_score_breakdown_points(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Compliance Audit Playbook',
            'description' => 'Prepare audit packs and compliance controls.',
            'status' => 'published',
            'topic' => 'compliance',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => [],
            'difficulty' => 'any',
            'role' => 'manager',
            'goal' => 'improve compliance audit readiness',
        ]);

        $score = app(FeedScoringService::class)->scoreWithBreakdown($user, $module, null);

        $this->assertSame(10, $score['breakdown']['goal_affinity']);
        $this->assertGreaterThanOrEqual(25, $score['score']);
    }

    public function test_feed_scoring_uses_configurable_weights(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Configurable Required Module',
            'description' => 'Config weight check',
            'status' => 'published',
            'is_required' => true,
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => [],
            'difficulty' => 'any',
            'role' => 'manager',
            'goal' => null,
        ]);

        config()->set('learning_assignments.feed_scoring.required_module', 120);
        config()->set('learning_assignments.feed_scoring.not_completed', 10);

        $score = app(FeedScoringService::class)->scoreWithBreakdown($user, $module, null);

        $this->assertSame(120, $score['breakdown']['required_module']);
        $this->assertSame(10, $score['breakdown']['not_completed']);
        $this->assertSame(130, $score['score']);
    }

    public function test_goal_affinity_respects_configurable_max(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Compliance Audit Safety Pack',
            'description' => 'Audit safety compliance controls and reporting',
            'status' => 'published',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => [],
            'difficulty' => 'any',
            'role' => 'manager',
            'goal' => 'compliance audit safety controls reporting',
        ]);

        config()->set('learning_assignments.feed_scoring.goal_affinity_per_keyword', 3);
        config()->set('learning_assignments.feed_scoring.goal_affinity_max', 6);

        $score = app(FeedScoringService::class)->scoreWithBreakdown($user, $module, null);

        $this->assertSame(6, $score['breakdown']['goal_affinity']);
    }

    public function test_required_incomplete_modules_rank_above_optional_matches(): void
    {
        $user = User::factory()->create();

        $optionalMatch = LearningModule::query()->create([
            'title' => 'Optional Match',
            'description' => 'Topic match only',
            'status' => 'published',
            'topic' => 'math',
            'difficulty' => 'beginner',
            'is_required' => false,
        ]);

        $requiredModule = LearningModule::query()->create([
            'title' => 'Required Compliance Module',
            'description' => 'Required module',
            'status' => 'published',
            'topic' => 'safety',
            'difficulty' => 'advanced',
            'is_required' => true,
            'compliance_area' => 'workplace-safety',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['math'],
            'difficulty' => 'beginner',
            'role' => 'operator',
            'goal' => 'stay compliant',
        ]);

        $this->actingAs($user);

        $view = app(\App\Http\Controllers\AppFeedController::class)->index();
        $modules = $view->getData()['modules'];
        $score = app(FeedScoringService::class)->scoreWithBreakdown($user, $requiredModule, null);

        $this->assertSame($requiredModule->id, $modules->first()->id);
        $this->assertSame(75, $score['breakdown']['required_module']);
        $this->assertSame($optionalMatch->id, $modules->get(1)->id);
    }

    public function test_app_feed_requires_authentication(): void
    {
        $this->get('/app/feed')->assertRedirect('/login');
    }

    public function test_users_cannot_access_others_preferences_via_policy(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $preferenceB = UserPreference::query()->create([
            'user_id' => $userB->id,
            'topics' => ['history'],
            'difficulty' => 'advanced',
            'role' => 'teacher',
            'goal' => 'plan content',
        ]);

        $this->assertFalse($userA->can('view', $preferenceB));
        $this->assertFalse($userA->can('update', $preferenceB));
        $this->assertTrue($userB->can('view', $preferenceB));
        $this->assertTrue($userB->can('update', $preferenceB));
    }

    public function test_learning_module_seeder_is_idempotent(): void
    {
        $this->seed(LearningModuleSeeder::class);
        $this->seed(LearningModuleSeeder::class);

        $this->assertSame(6, LearningModule::query()->count());
        $this->assertDatabaseHas('learning_modules', [
            'title' => 'Mastering Prompt Basics for Study',
            'topic' => 'ai-literacy',
            'difficulty' => 'beginner',
        ]);
    }

    public function test_module_detail_page_shows_score_reasoning(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'AI Safety Basics',
            'description' => 'Required AI safety content',
            'status' => 'published',
            'topic' => 'ai-literacy',
            'difficulty' => 'beginner',
            'is_required' => true,
            'compliance_area' => 'ai-safety',
            'refresh_interval_days' => 180,
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['ai-literacy'],
            'difficulty' => 'beginner',
            'role' => 'manager',
            'goal' => 'ship safely',
        ]);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk()
            ->assertSee('Why this is in your feed')
            ->assertSee('Goal alignment')
            ->assertSee('Required module')
            ->assertSee('ai-safety');
    }

    public function test_module_detail_records_learning_event(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Event Tracked Module',
            'description' => 'Module view event target',
            'status' => 'published',
            'difficulty' => 'beginner',
            'topic' => 'privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk();

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ]);
    }

    public function test_recent_learning_event_adds_topic_activity_score(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');

        $user = User::factory()->create();
        $viewedModule = LearningModule::query()->create([
            'title' => 'Viewed Topic Module',
            'description' => 'Viewed module',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);
        $candidateMatch = LearningModule::query()->create([
            'title' => 'Candidate Match Module',
            'description' => 'Should get recent topic score',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'advanced',
        ]);
        $candidateOther = LearningModule::query()->create([
            'title' => 'Candidate Other Module',
            'description' => 'No recent topic score',
            'status' => 'published',
            'topic' => 'safety',
            'difficulty' => 'advanced',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $viewedModule->id,
            'metadata' => ['topic' => 'privacy'],
            'created_at' => now()->subDay(),
        ]);

        $scorer = app(FeedScoringService::class);
        $privacyScore = $scorer->scoreWithBreakdown($user, $candidateMatch, null);
        $otherScore = $scorer->scoreWithBreakdown($user, $candidateOther, null);

        $this->assertSame(5, $privacyScore['breakdown']['recent_topic_activity']);
        $this->assertSame(0, $otherScore['breakdown']['recent_topic_activity']);
        $this->assertGreaterThan($otherScore['score'], $privacyScore['score']);

        Carbon::setTestNow();
    }

    public function test_recent_module_reengagement_adds_score_for_same_module(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');

        $user = User::factory()->create();
        $engagedModule = LearningModule::query()->create([
            'title' => 'Engaged Module',
            'description' => 'Module recently interacted with',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);
        $otherModule = LearningModule::query()->create([
            'title' => 'Other Module',
            'description' => 'Comparable module',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_progress_updated',
            'entity_type' => 'learning_module',
            'entity_id' => $engagedModule->id,
            'metadata' => ['percent_complete' => 20],
            'created_at' => now()->subDay(),
        ]);

        $scorer = app(FeedScoringService::class);
        $engagedScore = $scorer->scoreWithBreakdown($user, $engagedModule, null);
        $otherScore = $scorer->scoreWithBreakdown($user, $otherModule, null);

        $this->assertSame(12, $engagedScore['breakdown']['recent_module_reengagement']);
        $this->assertSame(0, $otherScore['breakdown']['recent_module_reengagement']);
        $this->assertGreaterThan($otherScore['score'], $engagedScore['score']);

        Carbon::setTestNow();
    }

    public function test_recent_module_reengagement_decays_for_older_events(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');

        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Aged Engagement Module',
            'description' => 'Recently engaged but older',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);

        $event = LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'test'],
        ]);
        $event->forceFill([
            'created_at' => now()->subDays(10),
        ])->save();

        $score = app(FeedScoringService::class)->scoreWithBreakdown($user, $module, null);

        $this->assertSame(6, $score['breakdown']['recent_module_reengagement']);

        Carbon::setTestNow();
    }

    public function test_recent_module_reengagement_window_is_configurable(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');
        config()->set('learning_assignments.feed_scoring.recent_module_reengagement_full_days', 1);
        config()->set('learning_assignments.feed_scoring.recent_module_reengagement_mid_days', 2);
        config()->set('learning_assignments.feed_scoring.recent_module_reengagement_window_days', 3);

        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Window Config Module',
            'description' => 'Window config check',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);

        $event = LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'test'],
        ]);
        $event->forceFill([
            'created_at' => now()->subDays(4),
        ])->save();

        $score = app(FeedScoringService::class)->scoreWithBreakdown($user, $module, null);
        $this->assertSame(0, $score['breakdown']['recent_module_reengagement']);

        Carbon::setTestNow();
    }

    public function test_next_learning_path_step_adds_score_boost(): void
    {
        $user = User::factory()->create();

        $nextStepModule = LearningModule::query()->create([
            'title' => 'Path Step One',
            'description' => 'First step module',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);
        $otherModule = LearningModule::query()->create([
            'title' => 'Non Path Module',
            'description' => 'Comparable non-path module',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);

        $path = LearningPath::query()->create([
            'title' => 'Manager Path',
            'description' => 'Role based path',
            'target_roles' => ['manager'],
            'status' => 'published',
        ]);
        $path->steps()->create([
            'learning_module_id' => $nextStepModule->id,
            'position' => 1,
            'delay_days' => 0,
        ]);

        $scorer = app(FeedScoringService::class);
        $nextStepScore = $scorer->scoreWithBreakdown($user, $nextStepModule, null);
        $otherScore = $scorer->scoreWithBreakdown($user, $otherModule, null);

        $this->assertSame(30, $nextStepScore['breakdown']['path_next_step']);
        $this->assertSame(0, $otherScore['breakdown']['path_next_step']);
        $this->assertSame(['Manager Path'], $nextStepScore['explanations']['path_next_step_paths']);
        $this->assertGreaterThan($otherScore['score'], $nextStepScore['score']);
    }

    public function test_module_detail_shows_path_and_recent_engagement_explanations(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');

        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Explained Module',
            'description' => 'Explanation card target',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);

        $path = LearningPath::query()->create([
            'title' => 'Manager Priority Path',
            'description' => 'Path for explanation',
            'target_roles' => ['manager'],
            'status' => 'published',
        ]);
        $path->steps()->create([
            'learning_module_id' => $module->id,
            'position' => 1,
            'delay_days' => 0,
        ]);

        $event = LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'test'],
        ]);
        $event->forceFill([
            'created_at' => now()->subDays(2),
        ])->save();

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk()
            ->assertSee('Next step in path: Manager Priority Path')
            ->assertSee('You engaged 2 day(s) ago')
            ->assertSee('Path(s): Manager Priority Path')
            ->assertSee('Last engaged 2 day(s) ago');

        Carbon::setTestNow();
    }

    public function test_feed_controller_sets_rank_highlights_on_modules(): void
    {
        $user = User::factory()->create();

        $module = LearningModule::query()->create([
            'title' => 'Highlight Module',
            'description' => 'Top reasons test module',
            'status' => 'published',
            'topic' => 'security',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['security'],
            'difficulty' => 'beginner',
            'role' => 'manager',
            'goal' => 'improve security compliance readiness',
        ]);

        $this->actingAs($user);
        $view = app(\App\Http\Controllers\AppFeedController::class)->index();
        $modules = $view->getData()['modules'];
        $first = $modules->firstWhere('id', $module->id);

        $this->assertNotNull($first);
        $highlights = $first->feed_highlights ?? [];
        $this->assertNotEmpty($highlights);
        $labels = collect($highlights)->pluck('label')->implode(' | ');
        $this->assertStringContainsString('Topic preference match', $labels);
    }

    public function test_recent_topic_activity_window_is_configurable(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');
        config()->set('learning_assignments.feed_scoring.recent_topic_activity_window_days', 1);

        $user = User::factory()->create();
        $viewedModule = LearningModule::query()->create([
            'title' => 'Viewed Topic Window Module',
            'description' => 'Viewed module',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);
        $candidate = LearningModule::query()->create([
            'title' => 'Candidate Window Module',
            'description' => 'Should not get recent topic score',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'advanced',
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
            'topics' => [],
        ]);

        $event = LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $viewedModule->id,
            'metadata' => ['topic' => 'privacy'],
        ]);
        $event->forceFill([
            'created_at' => now()->subDays(2),
        ])->save();

        $score = app(FeedScoringService::class)->scoreWithBreakdown($user, $candidate, null);
        $this->assertSame(0, $score['breakdown']['recent_topic_activity']);

        Carbon::setTestNow();
    }

    public function test_saving_module_records_learning_event(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Savable Module',
            'description' => 'Save event target',
            'status' => 'published',
            'difficulty' => 'beginner',
            'topic' => 'productivity',
        ]);

        $this->actingAs($user)
            ->post("/app/feed/{$module->id}/save")
            ->assertRedirect();

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'module_saved',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ]);
    }

    public function test_unsaving_module_records_learning_event(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Unsavable Module',
            'description' => 'Unsave event target',
            'status' => 'published',
            'difficulty' => 'beginner',
            'topic' => 'productivity',
        ]);

        $this->actingAs($user)
            ->post("/app/feed/{$module->id}/save")
            ->assertRedirect();

        $this->actingAs($user)
            ->post("/app/feed/{$module->id}/unsave")
            ->assertRedirect();

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'module_unsaved',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ]);
    }

    public function test_authenticated_user_can_record_learning_event_via_endpoint(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Endpoint Event Module',
            'description' => 'Event endpoint target',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);

        $this->actingAs($user)
            ->postJson('/app/events', [
                'event_type' => 'module_progressed',
                'entity_type' => 'learning_module',
                'entity_id' => $module->id,
                'metadata' => [
                    'percent_complete' => 55,
                ],
            ])
            ->assertCreated()
            ->assertJson(['status' => 'recorded']);

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'module_progressed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ]);
    }

    public function test_learning_event_endpoint_requires_authentication(): void
    {
        $module = LearningModule::query()->create([
            'title' => 'Auth Required Event Module',
            'description' => 'Auth gate check',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);

        $this->postJson('/app/events', [
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ])->assertUnauthorized();
    }

    public function test_learning_event_endpoint_blocks_module_events_for_hidden_module(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Restricted Module',
            'description' => 'Role restricted content',
            'status' => 'published',
            'difficulty' => 'beginner',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        $this->actingAs($user)
            ->postJson('/app/events', [
                'event_type' => 'module_viewed',
                'entity_type' => 'learning_module',
                'entity_id' => $module->id,
            ])
            ->assertForbidden();
    }

    public function test_learning_event_endpoint_blocks_events_for_other_users_preference(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        UserPreference::query()->create([
            'user_id' => $userA->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $preferenceB = UserPreference::query()->create([
            'user_id' => $userB->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        $this->actingAs($userA)
            ->postJson('/app/events', [
                'event_type' => 'preferences_viewed',
                'entity_type' => 'user_preference',
                'entity_id' => $preferenceB->id,
            ])
            ->assertForbidden();
    }

    public function test_learning_event_endpoint_allows_events_for_own_preference(): void
    {
        $user = User::factory()->create();
        $preference = UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $this->actingAs($user)
            ->postJson('/app/events', [
                'event_type' => 'preferences_saved',
                'entity_type' => 'user_preference',
                'entity_id' => $preference->id,
                'metadata' => ['source' => 'ui'],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'preferences_saved',
            'entity_type' => 'user_preference',
            'entity_id' => $preference->id,
        ]);
    }

    public function test_learning_event_endpoint_blocks_learning_path_events_for_hidden_path(): void
    {
        $user = User::factory()->create();
        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        $path = LearningPath::query()->create([
            'title' => 'Manager Only Path',
            'description' => 'Restricted path',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);

        $this->actingAs($user)
            ->postJson('/app/events', [
                'event_type' => 'path_viewed',
                'entity_type' => 'learning_path',
                'entity_id' => $path->id,
            ])
            ->assertForbidden();
    }

    public function test_completed_required_module_returns_to_top_when_refresh_is_due(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $user = User::factory()->create();

        $freshOptional = LearningModule::query()->create([
            'title' => 'Fresh Optional Module',
            'description' => 'Optional module',
            'status' => 'published',
            'topic' => 'productivity',
            'difficulty' => 'beginner',
            'is_required' => false,
        ]);

        $renewalModule = LearningModule::query()->create([
            'title' => 'Annual Safety Refresher',
            'description' => 'Required annual refresher',
            'status' => 'published',
            'topic' => 'safety',
            'difficulty' => 'beginner',
            'is_required' => true,
            'compliance_area' => 'workplace-safety',
            'refresh_interval_days' => 30,
        ]);

        ModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $renewalModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(45),
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['productivity'],
            'difficulty' => 'beginner',
            'role' => 'operator',
        ]);

        $this->actingAs($user);

        $view = app(\App\Http\Controllers\AppFeedController::class)->index();
        $modules = $view->getData()['modules'];
        $score = app(FeedScoringService::class)->scoreWithBreakdown(
            $user,
            $renewalModule,
            ModuleProgress::query()->where('user_id', $user->id)->where('learning_module_id', $renewalModule->id)->first(),
        );

        $this->assertSame($renewalModule->id, $modules->first()->id);
        $this->assertTrue($score['renewal']['is_due']);
        $this->assertSame(90, $score['breakdown']['renewal_due']);

        Carbon::setTestNow();
    }

    public function test_feed_separates_required_learning_from_recommendations(): void
    {
        $user = User::factory()->create();

        $requiredModule = LearningModule::query()->create([
            'title' => 'Required Privacy Module',
            'description' => 'Privacy training',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        $recommendedModule = LearningModule::query()->create([
            'title' => 'Optional Productivity Module',
            'description' => 'Productivity training',
            'status' => 'published',
            'topic' => 'productivity',
            'difficulty' => 'beginner',
            'is_required' => false,
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['productivity'],
            'difficulty' => 'beginner',
            'role' => 'manager',
        ]);

        $this->actingAs($user);

        $view = app(\App\Http\Controllers\AppFeedController::class)->index();
        $data = $view->getData();

        $this->assertCount(1, $data['requiredModules']);
        $this->assertCount(1, $data['recommendedModules']);
        $this->assertSame($requiredModule->id, $data['requiredModules']->first()->id);
        $this->assertSame($recommendedModule->id, $data['recommendedModules']->first()->id);

        $this->get('/app/feed')
            ->assertOk()
            ->assertSee('Courses');
    }

    public function test_assignment_service_classifies_required_module_states(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Annual Conduct Refresher',
            'description' => 'Conduct refresher',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $service = app(AssignmentService::class);

        $initial = $service->forUser($user, $module, null);
        $this->assertTrue($initial['is_required']);
        $this->assertTrue($initial['is_incomplete_required']);
        $this->assertSame('required', $initial['urgency']);

        $progress = ModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $overdue = $service->forUser($user, $module, $progress);
        $this->assertTrue($overdue['is_overdue']);
        $this->assertSame('overdue', $overdue['urgency']);

        Carbon::setTestNow();
    }

    public function test_feed_shows_assignment_summary_counts(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $user = User::factory()->create();

        $overdueModule = LearningModule::query()->create([
            'title' => 'Overdue Required Module',
            'description' => 'Overdue content',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        $dueSoonModule = LearningModule::query()->create([
            'title' => 'Due Soon Required Module',
            'description' => 'Due soon content',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $overdueModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        ModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $dueSoonModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(25),
            'last_activity_at' => now()->subDays(25),
        ]);

        $this->actingAs($user);

        $view = app(\App\Http\Controllers\AppFeedController::class)->index();
        $summary = $view->getData()['assignmentSummary'];

        $this->assertSame(2, $summary['required_total']);
        $this->assertSame(1, $summary['required_overdue']);
        $this->assertSame(1, $summary['required_due_soon']);

        $this->get('/app/feed')
            ->assertOk()
            ->assertSee('Required')
            ->assertSee('Next Due');

        Carbon::setTestNow();
    }

    public function test_required_module_with_compliance_area_is_assigned_by_role_inheritance(): void
    {
        $manager = User::factory()->create();
        $specialist = User::factory()->create();

        $module = LearningModule::query()->create([
            'title' => 'AI Safety Annual Refresher',
            'description' => 'Required AI safety refresher',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'ai-safety',
            'refresh_interval_days' => 180,
        ]);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        UserPreference::query()->create([
            'user_id' => $specialist->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        $service = app(AssignmentService::class);

        $managerAssignment = $service->forUser($manager, $module, null);
        $specialistAssignment = $service->forUser($specialist, $module, null);

        $this->assertTrue($managerAssignment['is_assigned']);
        $this->assertTrue($managerAssignment['is_required']);
        $this->assertTrue($managerAssignment['compliance_targeting']['matches']);

        $this->assertFalse($specialistAssignment['is_assigned']);
        $this->assertFalse($specialistAssignment['compliance_targeting']['matches']);
    }

    public function test_database_rule_overrides_config_fallback_for_compliance_assignment(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Specialist Safety Drill',
            'description' => 'Safety drill',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'workplace-safety',
            'refresh_interval_days' => 30,
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        ComplianceRoleRule::query()->create([
            'role' => 'specialist',
            'compliance_area' => 'workplace-safety',
        ]);

        $assignment = app(AssignmentService::class)->forUser($user, $module, null);

        $this->assertTrue($assignment['is_assigned']);
        $this->assertTrue($assignment['compliance_targeting']['matches']);
    }

    public function test_assignment_rules_page_can_save_rules(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        Livewire::test(AssignmentRulesPage::class)
            ->set('rules', [
                ['role' => 'manager', 'compliance_area' => 'ai-safety'],
                ['role' => 'specialist', 'compliance_area' => 'workplace-safety'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('compliance_role_rules', [
            'role' => 'manager',
            'compliance_area' => 'ai-safety',
        ]);

        $this->assertDatabaseHas('compliance_role_rules', [
            'role' => 'specialist',
            'compliance_area' => 'workplace-safety',
        ]);
    }

    public function test_assignment_rules_page_requires_admin_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/assignment-rules')
            ->assertForbidden();
    }

    public function test_admin_can_open_assignment_rules_page(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/app/assignment-rules')
            ->assertOk()
            ->assertSee('Assignment Rules');
    }

    public function test_admin_can_view_feed_scoring_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/scoring')
            ->assertOk()
            ->assertSee('Feed Scoring Weights')
            ->assertSee('Core Priorities')
            ->assertSee('Activity Windows (Days)')
            ->assertSee('Required Module Priority')
            ->assertSee('Topic Activity Window (days)');
    }

    public function test_admin_scoring_page_shows_current_preset_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/app/admin/scoring/preset', [
                'preset' => 'engagement_first',
            ])
            ->assertRedirect('/app/admin/scoring');

        $this->actingAs($admin)
            ->get('/app/admin/scoring')
            ->assertOk()
            ->assertSee('Current Profile:')
            ->assertSee('Engagement First');
    }

    public function test_non_admin_cannot_view_feed_scoring_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/scoring')
            ->assertForbidden();
    }

    public function test_admin_can_update_feed_scoring_settings_and_changes_apply(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        $module = LearningModule::query()->create([
            'title' => 'Configurable Topic Module',
            'description' => 'Weight update target',
            'status' => 'published',
            'topic' => 'compliance',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'topics' => ['compliance'],
            'difficulty' => 'any',
            'role' => 'manager',
            'goal' => null,
        ]);

        $payload = [
            'weights' => array_merge(config('learning_assignments.feed_scoring'), [
                'topic_match' => 77,
            ]),
        ];

        $this->actingAs($admin)
            ->post('/app/admin/scoring', $payload)
            ->assertRedirect('/app/admin/scoring');

        $this->assertDatabaseHas('feed_scoring_settings', [
            'key' => 'topic_match',
            'value' => 77,
        ]);

        $this->assertDatabaseHas('feed_scoring_settings', [
            'key' => 'required_module',
            'value' => (int) config('learning_assignments.feed_scoring.required_module'),
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'feed_scoring_settings_updated',
            'entity_type' => 'feed_scoring_settings',
        ]);

        $score = app(FeedScoringService::class)->scoreWithBreakdown($learner, $module, null);
        $this->assertSame(77, $score['breakdown']['topic_match']);
    }

    public function test_admin_cannot_save_invalid_reengagement_window_order(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'weights' => array_merge(config('learning_assignments.feed_scoring'), [
                'recent_module_reengagement_full_days' => 10,
                'recent_module_reengagement_mid_days' => 5,
                'recent_module_reengagement_window_days' => 14,
            ]),
        ];

        $this->actingAs($admin)
            ->from('/app/admin/scoring')
            ->post('/app/admin/scoring', $payload)
            ->assertRedirect('/app/admin/scoring')
            ->assertSessionHasErrors(['weights.recent_module_reengagement_mid_days']);

        $this->assertDatabaseMissing('feed_scoring_settings', [
            'key' => 'recent_module_reengagement_full_days',
            'value' => 10,
        ]);
    }

    public function test_admin_can_save_valid_reengagement_window_order(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'weights' => array_merge(config('learning_assignments.feed_scoring'), [
                'recent_module_reengagement_full_days' => 2,
                'recent_module_reengagement_mid_days' => 6,
                'recent_module_reengagement_window_days' => 12,
            ]),
        ];

        $this->actingAs($admin)
            ->post('/app/admin/scoring', $payload)
            ->assertRedirect('/app/admin/scoring');

        $this->assertDatabaseHas('feed_scoring_settings', [
            'key' => 'recent_module_reengagement_full_days',
            'value' => 2,
        ]);
        $this->assertDatabaseHas('feed_scoring_settings', [
            'key' => 'recent_module_reengagement_mid_days',
            'value' => 6,
        ]);
        $this->assertDatabaseHas('feed_scoring_settings', [
            'key' => 'recent_module_reengagement_window_days',
            'value' => 12,
        ]);
    }

    public function test_admin_can_apply_feed_scoring_preset(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        $module = LearningModule::query()->create([
            'title' => 'Preset Topic Module',
            'description' => 'Preset check',
            'status' => 'published',
            'topic' => 'compliance',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'topics' => ['compliance'],
            'difficulty' => 'any',
            'role' => 'manager',
            'goal' => null,
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/scoring/preset', [
                'preset' => 'engagement_first',
            ])
            ->assertRedirect('/app/admin/scoring');

        $this->assertDatabaseHas('feed_scoring_settings', [
            'key' => 'topic_match',
            'value' => 70,
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'feed_scoring_preset_applied',
            'entity_type' => 'feed_scoring_settings',
        ]);

        $score = app(FeedScoringService::class)->scoreWithBreakdown($learner, $module, null);
        $this->assertSame(70, $score['breakdown']['topic_match']);
    }

    public function test_non_admin_cannot_apply_feed_scoring_preset(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/app/admin/scoring/preset', [
                'preset' => 'engagement_first',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_reset_feed_scoring_settings_to_defaults(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        $module = LearningModule::query()->create([
            'title' => 'Resettable Topic Module',
            'description' => 'Reset weight target',
            'status' => 'published',
            'topic' => 'compliance',
            'difficulty' => 'beginner',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'topics' => ['compliance'],
            'difficulty' => 'any',
            'role' => 'manager',
            'goal' => null,
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/scoring', [
                'weights' => array_merge(config('learning_assignments.feed_scoring'), [
                    'topic_match' => 12,
                ]),
            ])
            ->assertRedirect('/app/admin/scoring');

        $this->assertDatabaseHas('feed_scoring_settings', [
            'key' => 'topic_match',
            'value' => 12,
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/scoring/reset')
            ->assertRedirect('/app/admin/scoring');

        $this->assertDatabaseMissing('feed_scoring_settings', [
            'key' => 'topic_match',
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'feed_scoring_settings_reset',
            'entity_type' => 'feed_scoring_settings',
        ]);

        $score = app(FeedScoringService::class)->scoreWithBreakdown($learner, $module, null);
        $this->assertSame((int) config('learning_assignments.feed_scoring.topic_match'), $score['breakdown']['topic_match']);
    }

    public function test_non_admin_cannot_reset_feed_scoring_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/app/admin/scoring/reset')
            ->assertForbidden();
    }

    public function test_admin_can_view_reminder_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/reminder-settings')
            ->assertOk()
            ->assertSee('Reminder Settings')
            ->assertSee('inactive nudge after days');
    }

    public function test_non_admin_cannot_view_reminder_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/reminder-settings')
            ->assertForbidden();
    }

    public function test_admin_can_view_ranking_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/ranking')
            ->assertOk()
            ->assertSee('Ranking Settings')
            ->assertSee('Enable AI Ranking Layer')
            ->assertSee('External AI Retry Attempts')
            ->assertSee('External AI Retry Backoff (ms)')
            ->assertSee('Provider Readiness')
            ->assertSee('All providers')
            ->assertSee('Refresh now')
            ->assertSee('Copy API URL')
            ->assertSee('Clear filters')
            ->assertSee('Open API')
            ->assertSee('Last updated on page load')
            ->assertSee('/api/admin/ai/ranking-health?limit=10')
            ->assertSee('/app/admin/ranking/export/probes', false)
            ->assertSee('/app/admin/ranking/export/severity-transitions', false)
            ->assertSee('OPENAI_API_KEY is not configured.');
    }

    public function test_admin_ranking_page_shows_external_ai_ready_when_key_exists(): void
    {
        $admin = User::factory()->admin()->create();

        config()->set('services.openai.api_key', 'test-key');

        $this->actingAs($admin)
            ->get('/app/admin/ranking')
            ->assertOk()
            ->assertSee('External AI Provider')
            ->assertSee('Api Key Configured')
            ->assertSee('Yes')
            ->assertDontSee('OPENAI_API_KEY is not configured.');
    }

    public function test_admin_ranking_page_shows_last_probe_result(): void
    {
        $admin = User::factory()->admin()->create();

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'model' => 'gpt-5-mini',
            'request_id' => 'req_last_probe',
            'latency_ms' => 321,
            'success' => false,
            'error_type' => 'RuntimeException',
            'error_message' => 'Probe failed',
            'metadata' => [
                'message' => 'Probe failed due to timeout.',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/ranking')
            ->assertOk()
            ->assertSee('Last Probe Result')
            ->assertSee('Last Successful Probe')
            ->assertSee('external_ai')
            ->assertSee('Failure')
            ->assertSee('321 ms')
            ->assertSee('req_last_probe')
            ->assertSee('Probe failed due to timeout.')
            ->assertSee('RuntimeException')
            ->assertSee('avg 321 ms');
    }

    public function test_admin_ranking_page_shows_last_export_status(): void
    {
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_settings',
            'action' => 'ranking_incident_bundle_exported',
            'meta' => [
                'bundle_id' => 'ranking-incident-abc123def4567890',
                'provider' => 'external_ai',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);
        \Illuminate\Support\Facades\DB::table('assignment_audit_events')
            ->where('action', 'ranking_incident_bundle_exported')
            ->update(['created_at' => '2026-03-09 10:15:00']);

        $this->actingAs($admin)
            ->get('/app/admin/ranking')
            ->assertOk()
            ->assertSee('Last export: Incident Bundle at 2026-03-09 10:15')
            ->assertSee('/app/admin/assignments/audit?action=ranking_incident_bundle_exported')
            ->assertSee('Copy bundle ID')
            ->assertSee('ranking-incident-abc123def4567890')
            ->assertSee('provider external_ai')
            ->assertSee('trigger ranking_provider_tested');
    }

    public function test_admin_ranking_page_shows_recent_probe_history(): void
    {
        Carbon::setTestNow('2026-03-09 12:00:00');

        $admin = User::factory()->admin()->create();
        RankingSetting::query()->create(['key' => 'enabled', 'value' => '1']);
        RankingSetting::query()->create(['key' => 'provider', 'value' => 'external_ai']);

        $successfulProbe = AiProviderUsage::query()->create([
            'provider' => 'local_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 120,
            'success' => true,
            'metadata' => ['message' => 'Local probe ok.'],
        ]);
        \Illuminate\Support\Facades\DB::table('ai_provider_usages')
            ->where('id', $successfulProbe->id)
            ->update([
                'created_at' => now()->subMinutes(45),
            ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'request_id' => 'req_hist_2',
            'latency_ms' => 410,
            'success' => false,
            'error_message' => 'External timeout',
            'metadata' => ['message' => 'External timeout'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_live_hist_1',
            'latency_ms' => 380,
            'success' => false,
            'error_type' => 'RuntimeException',
            'error_message' => 'Live ranking fallback triggered.',
            'metadata' => ['message' => 'Live ranking fallback triggered.'],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'healthy',
                'before_label' => 'Healthy',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'after_reason' => 'Active provider is not ready.',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'degraded',
                'before_label' => 'Degraded',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'after_reason' => 'Active provider is not ready.',
                'trigger' => 'ranking_settings_updated',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/ranking')
            ->assertOk()
            ->assertSee('Critical')
            ->assertSee('Active provider is not ready.')
            ->assertSee('Recent Probe History')
            ->assertSee('scope: global')
            ->assertSee('0 active filters')
            ->assertSee('Provider filters probe rows. Trigger filters severity-transition audit entries.')
            ->assertSee('Filters: provider=All providers, trigger=All triggers')
            ->assertSee('Top Failure Reasons')
            ->assertSee('Recent Live Ranking Failures')
            ->assertSee('Recent Severity Transitions')
            ->assertSee('Export CSV')
            ->assertSee('All triggers 2')
            ->assertSee('Provider Tested 1')
            ->assertSee('Settings Updated 1')
            ->assertSee('trigger ranking_provider_tested by '.$admin->name)
            ->assertSee('Last Successful Probe')
            ->assertSee('Success 1')
            ->assertSee('Failure 1')
            ->assertSee('avg 265 ms')
            ->assertSee('min 120 ms')
            ->assertSee('max 410 ms')
            ->assertSee('local_ai')
            ->assertSee('external_ai')
            ->assertSee('120 ms')
            ->assertSee('410 ms')
            ->assertSee('req_hist_2')
            ->assertSee('Local probe ok.')
            ->assertSee('External timeout')
            ->assertSee('count 1; providers external_ai')
            ->assertSee('sources probe')
            ->assertSee('req_live_hist_1')
            ->assertSee('Live ranking fallback triggered.')
            ->assertSee('/app/admin/ai/usages?provider=external_ai&amp;capability=feed_ranking&amp;success=0&amp;request_id=req_live_hist_1&amp;limit=10', false)
            ->assertSee('Last Successful Probe')
            ->assertSee('local_ai')
            ->assertSee('Last known healthy probe was');

        Carbon::setTestNow();
    }

    public function test_admin_ranking_page_can_filter_probe_history_by_provider(): void
    {
        $admin = User::factory()->admin()->create();

        RankingSetting::query()->create(['key' => 'provider', 'value' => 'local_ai']);

        AiProviderUsage::query()->create([
            'provider' => 'local_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 120,
            'success' => true,
            'metadata' => ['message' => 'Local probe ok.'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 410,
            'success' => false,
            'error_message' => 'External timeout',
            'metadata' => ['message' => 'External timeout'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_live_filtered',
            'latency_ms' => 390,
            'success' => false,
            'error_message' => 'Filtered live ranking failure',
            'metadata' => ['message' => 'Filtered live ranking failure'],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/ranking?ranking_provider=external_ai')
            ->assertOk()
            ->assertSee('scope: filtered')
            ->assertSee('1 active filter')
            ->assertSee('Viewing External AI')
            ->assertSee('while the active ranking provider is Local AI')
            ->assertSee('Success 0')
            ->assertSee('Failure 1')
            ->assertSee('avg 410 ms')
            ->assertSee('external_ai')
            ->assertSee('Recent Live Ranking Failures')
            ->assertSee('req_live_filtered')
            ->assertSee('Filtered live ranking failure')
            ->assertSee('/app/admin/ai/usages?provider=external_ai&amp;capability=feed_ranking&amp;success=0&amp;request_id=req_live_filtered&amp;limit=10', false)
            ->assertDontSee('Local probe ok.');
    }

    public function test_admin_ranking_page_can_filter_severity_transitions_by_trigger(): void
    {
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_label' => 'Healthy',
                'after_label' => 'Degraded',
                'trigger' => 'ranking_settings_updated',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_label' => 'Degraded',
                'after_label' => 'Critical',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/ranking?ranking_severity_trigger=ranking_settings_updated')
            ->assertOk()
            ->assertSee('scope: filtered')
            ->assertSee('1 active filter')
            ->assertSee('Showing Settings Updated.')
            ->assertSee('Filters: provider=All providers, trigger=Settings Updated')
            ->assertSee('/app/admin/assignments/audit?action=ranking_severity_changed&amp;q=ranking_settings_updated', false)
            ->assertSee('trigger ranking_settings_updated by '.$admin->name)
            ->assertDontSee('trigger ranking_provider_tested by '.$admin->name);
    }

    public function test_admin_ranking_page_shows_filtered_empty_messages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/ranking?ranking_provider=external_ai')
            ->assertOk()
            ->assertSee('No probe history matches provider External AI.');

        $this->actingAs($admin)
            ->get('/app/admin/ranking?ranking_severity_trigger=ranking_settings_updated')
            ->assertOk()
            ->assertSee('No severity transitions match trigger Settings Updated.');
    }

    public function test_admin_ranking_page_shows_two_active_filters_when_provider_and_trigger_are_combined(): void
    {
        $admin = User::factory()->admin()->create();

        RankingSetting::query()->create(['key' => 'provider', 'value' => 'local_ai']);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 410,
            'success' => false,
            'error_message' => 'External timeout',
            'metadata' => ['message' => 'External timeout'],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_label' => 'Healthy',
                'after_label' => 'Degraded',
                'trigger' => 'ranking_settings_updated',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/ranking?ranking_provider=external_ai&ranking_severity_trigger=ranking_settings_updated')
            ->assertOk()
            ->assertSee('scope: filtered')
            ->assertSee('2 active filters')
            ->assertSee('Filters: provider=External AI, trigger=Settings Updated')
            ->assertSee('Viewing External AI.')
            ->assertSee('Showing Settings Updated.');
    }

    public function test_admin_ranking_page_preserves_export_date_window_in_links(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/ranking?ranking_provider=external_ai&ranking_severity_trigger=ranking_provider_tested&ranking_export_from=2026-03-01&ranking_export_to=2026-03-09')
            ->assertOk()
            ->assertSee('/app/admin/ranking/export/incident-bundle?ranking_provider=external_ai&amp;ranking_severity_trigger=ranking_provider_tested&amp;ranking_export_from=2026-03-01&amp;ranking_export_to=2026-03-09', false)
            ->assertSee('/app/admin/ranking/export/probes?ranking_provider=external_ai&amp;ranking_export_from=2026-03-01&amp;ranking_export_to=2026-03-09', false)
            ->assertSee('/app/admin/ranking/export/severity-transitions?ranking_severity_trigger=ranking_provider_tested&amp;ranking_export_from=2026-03-01&amp;ranking_export_to=2026-03-09', false)
            ->assertSee('name="ranking_export_from"', false)
            ->assertSee('name="ranking_export_to"', false);
    }

    public function test_admin_can_export_ranking_incident_bundle_as_json(): void
    {
        Carbon::setTestNow('2026-03-09 12:00:00');

        $admin = User::factory()->admin()->create(['name' => 'Ranking Admin']);
        RankingSetting::query()->create(['key' => 'enabled', 'value' => '1']);
        RankingSetting::query()->create(['key' => 'provider', 'value' => 'external_ai']);

        $probe = AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 410,
            'success' => false,
            'request_id' => 'req_bundle',
            'error_type' => 'RuntimeException',
            'error_message' => 'Bundle timeout',
            'metadata' => ['message' => 'Bundle timeout'],
        ]);
        \Illuminate\Support\Facades\DB::table('ai_provider_usages')
            ->where('id', $probe->id)
            ->update(['created_at' => '2026-03-09 09:00:00']);

        $transition = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'healthy',
                'before_label' => 'Healthy',
                'after_level' => 'degraded',
                'after_label' => 'Degraded',
                'after_reason' => 'Bundle probe failed.',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);
        \Illuminate\Support\Facades\DB::table('assignment_audit_events')
            ->where('id', $transition->id)
            ->update(['created_at' => '2026-03-09 09:05:00']);

        $response = $this->actingAs($admin)->get('/app/admin/ranking/export/incident-bundle?ranking_provider=external_ai&ranking_severity_trigger=ranking_provider_tested&ranking_export_from=2026-03-05&ranking_export_to=2026-03-09');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json; charset=UTF-8');

        $payload = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('external_ai', $payload['filters']['provider']);
        $this->assertSame('ranking_provider_tested', $payload['filters']['trigger']);
        $this->assertSame('2026-03-05', $payload['filters']['export_from']);
        $this->assertSame('2026-03-09', $payload['filters']['export_to']);
        $this->assertSame('ranking_incident_bundle', $payload['manifest']['format']);
        $this->assertSame(1, $payload['manifest']['version']);
        $this->assertStringContainsString('/app/admin/ranking?', $payload['manifest']['source']['page']);
        $this->assertSame('app.admin.ranking.export.incident-bundle', $payload['manifest']['source']['route']);
        $this->assertMatchesRegularExpression('/^ranking-incident-[a-f0-9]{16}$/', $payload['manifest']['bundle_id']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $payload['manifest']['checksum_sha256']);
        $this->assertSame(1, $payload['manifest']['counts']['recent_probes']);
        $this->assertSame(0, $payload['manifest']['counts']['recent_live_failures']);
        $this->assertSame(1, $payload['manifest']['counts']['recent_severity_transitions']);
        $this->assertSame('external_ai', $payload['summary']['provider']);
        $this->assertCount(1, $payload['recent_probes']);
        $this->assertSame('req_bundle', $payload['recent_probes'][0]['request_id']);
        $this->assertCount(0, $payload['recent_live_failures']);
        $this->assertCount(1, $payload['recent_severity_transitions']);
        $this->assertSame('ranking_provider_tested', $payload['recent_severity_transitions'][0]['trigger']);

        $expectedChecksum = hash('sha256', json_encode([
            'generated_at' => $payload['generated_at'],
            'filters' => $payload['filters'],
            'summary' => $payload['summary'],
            'recent_probes' => $payload['recent_probes'],
            'recent_live_failures' => $payload['recent_live_failures'],
            'recent_severity_transitions' => $payload['recent_severity_transitions'],
        ], JSON_UNESCAPED_SLASHES));

        $this->assertSame($expectedChecksum, $payload['manifest']['checksum_sha256']);
        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'action' => 'ranking_incident_bundle_exported',
            'entity_type' => 'ranking_settings',
        ]);

        Carbon::setTestNow();
    }

    public function test_non_admin_cannot_export_ranking_incident_bundle_as_json(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/ranking/export/incident-bundle')
            ->assertForbidden();
    }

    public function test_assignment_audit_shows_ranking_incident_bundle_exported_action(): void
    {
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_settings',
            'action' => 'ranking_incident_bundle_exported',
            'meta' => [
                'bundle_id' => 'ranking-incident-abc123def4567890',
                'provider' => 'external_ai',
                'trigger' => 'ranking_provider_tested',
                'probe_count' => 2,
                'severity_transition_count' => 1,
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=ranking_incident_bundle_exported')
            ->assertOk()
            ->assertSee('Incident Bundle Exported')
            ->assertSee('ranking incident bundle exported')
            ->assertSee('bundle ranking-incident-abc123def4567890')
            ->assertSee('provider external_ai')
            ->assertSee('trigger ranking_provider_tested')
            ->assertSee('probes 2')
            ->assertSee('transitions 1');
    }

    public function test_admin_can_export_ranking_probe_history_as_csv(): void
    {
        $admin = User::factory()->admin()->create();

        AiProviderUsage::query()->create([
            'provider' => 'local_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 120,
            'success' => true,
            'request_id' => 'req_probe_local',
            'metadata' => ['message' => 'Local probe ok.'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 410,
            'success' => false,
            'request_id' => 'req_probe_external',
            'error_type' => 'RuntimeException',
            'error_message' => 'External timeout',
            'metadata' => ['message' => 'External timeout'],
        ]);

        $response = $this->actingAs($admin)->get('/app/admin/ranking/export/probes?ranking_provider=external_ai');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('when,provider,status,latency_ms,request_id,model,error_type,message', $content);
        $this->assertStringContainsString('external_ai,failure,410,req_probe_external,,RuntimeException,"External timeout"', $content);
        $this->assertStringNotContainsString('req_probe_local', $content);
        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'action' => 'ranking_probe_history_exported',
            'entity_type' => 'ranking_settings',
        ]);
    }

    public function test_admin_can_export_ranking_probe_history_as_csv_with_date_window(): void
    {
        Carbon::setTestNow('2026-03-09 12:00:00');

        $admin = User::factory()->admin()->create();

        $older = AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 401,
            'success' => false,
            'request_id' => 'req_probe_old',
            'error_message' => 'Older timeout',
            'metadata' => ['message' => 'Older timeout'],
        ]);
        \Illuminate\Support\Facades\DB::table('ai_provider_usages')
            ->where('id', $older->id)
            ->update(['created_at' => '2026-03-01 09:00:00']);

        $newer = AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 410,
            'success' => false,
            'request_id' => 'req_probe_new',
            'error_message' => 'Newer timeout',
            'metadata' => ['message' => 'Newer timeout'],
        ]);
        \Illuminate\Support\Facades\DB::table('ai_provider_usages')
            ->where('id', $newer->id)
            ->update(['created_at' => '2026-03-09 09:00:00']);

        $response = $this->actingAs($admin)->get('/app/admin/ranking/export/probes?ranking_provider=external_ai&ranking_export_from=2026-03-05&ranking_export_to=2026-03-09');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('req_probe_new', $content);
        $this->assertStringNotContainsString('req_probe_old', $content);

        Carbon::setTestNow();
    }

    public function test_non_admin_cannot_export_ranking_probe_history_as_csv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/ranking/export/probes')
            ->assertForbidden();
    }

    public function test_admin_can_export_ranking_severity_transitions_as_csv(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ranking Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'healthy',
                'before_label' => 'Healthy',
                'after_level' => 'degraded',
                'after_label' => 'Degraded',
                'after_reason' => 'Most recent probe failed.',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'degraded',
                'before_label' => 'Degraded',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'after_reason' => 'No successful probe has been recorded.',
                'trigger' => 'ranking_settings_reset',
            ],
        ]);

        $response = $this->actingAs($admin)->get('/app/admin/ranking/export/severity-transitions?ranking_severity_trigger=ranking_provider_tested');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('when,actor,trigger,before_level,before_label,after_level,after_label,after_reason', $content);
        $this->assertStringContainsString('"Ranking Admin",ranking_provider_tested,healthy,Healthy,degraded,Degraded,"Most recent probe failed."', $content);
        $this->assertStringNotContainsString('ranking_settings_reset', $content);
        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'action' => 'ranking_severity_transitions_exported',
            'entity_type' => 'ranking_settings',
        ]);
    }

    public function test_admin_can_export_ranking_severity_transitions_as_csv_with_date_window(): void
    {
        Carbon::setTestNow('2026-03-09 12:00:00');

        $admin = User::factory()->admin()->create(['name' => 'Ranking Admin']);

        $older = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'healthy',
                'before_label' => 'Healthy',
                'after_level' => 'degraded',
                'after_label' => 'Degraded',
                'after_reason' => 'Old probe failed.',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);
        \Illuminate\Support\Facades\DB::table('assignment_audit_events')
            ->where('id', $older->id)
            ->update(['created_at' => '2026-03-01 09:00:00']);

        $newer = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'degraded',
                'before_label' => 'Degraded',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'after_reason' => 'New probe failed.',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);
        \Illuminate\Support\Facades\DB::table('assignment_audit_events')
            ->where('id', $newer->id)
            ->update(['created_at' => '2026-03-09 09:00:00']);

        $response = $this->actingAs($admin)->get('/app/admin/ranking/export/severity-transitions?ranking_severity_trigger=ranking_provider_tested&ranking_export_from=2026-03-05&ranking_export_to=2026-03-09');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('New probe failed.', $content);
        $this->assertStringNotContainsString('Old probe failed.', $content);

        Carbon::setTestNow();
    }

    public function test_non_admin_cannot_export_ranking_severity_transitions_as_csv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/ranking/export/severity-transitions')
            ->assertForbidden();
    }

    public function test_assignment_audit_shows_ranking_csv_export_actions(): void
    {
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_settings',
            'action' => 'ranking_probe_history_exported',
            'meta' => [
                'provider' => 'external_ai',
                'probe_count' => 3,
                'export_from' => '2026-03-01',
                'export_to' => '2026-03-09',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_settings',
            'action' => 'ranking_severity_transitions_exported',
            'meta' => [
                'trigger' => 'ranking_provider_tested',
                'severity_transition_count' => 2,
                'export_from' => '2026-03-01',
                'export_to' => '2026-03-09',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=ranking_probe_history_exported')
            ->assertOk()
            ->assertSee('Probe CSV Exported')
            ->assertSee('ranking probe history exported')
            ->assertSee('provider external_ai')
            ->assertSee('probes 3')
            ->assertSee('from 2026-03-01')
            ->assertSee('to 2026-03-09');

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=ranking_severity_transitions_exported')
            ->assertOk()
            ->assertSee('Severity CSV Exported')
            ->assertSee('ranking severity transitions exported')
            ->assertSee('trigger ranking_provider_tested')
            ->assertSee('transitions 2')
            ->assertSee('from 2026-03-01')
            ->assertSee('to 2026-03-09');
    }

    public function test_non_admin_cannot_view_ranking_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/ranking')
            ->assertForbidden();
    }

    public function test_admin_can_update_ranking_settings_and_enable_local_ai_provider(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'topics' => [],
            'difficulty' => 'any',
            'role' => 'manager',
            'goal' => 'improve compliance readiness',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Compliance Readiness Module',
            'description' => 'compliance readiness controls',
            'status' => 'published',
            'topic' => 'compliance',
            'difficulty' => 'beginner',
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/ranking', [
                'settings' => [
                    'enabled' => '1',
                    'provider' => 'local_ai',
                    'local_ai_goal_semantic_boost_per_match' => 5,
                    'local_ai_goal_semantic_boost_max' => 9,
                ],
            ])
            ->assertRedirect('/app/admin/ranking');

        $this->assertDatabaseHas('ranking_settings', [
            'key' => 'enabled',
            'value' => '1',
        ]);
        $this->assertDatabaseHas('ranking_settings', [
            'key' => 'provider',
            'value' => 'local_ai',
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'ranking_settings_updated',
            'entity_type' => 'ranking_settings',
        ]);

        $ranking = app(FeedRankingService::class)->rank($learner, $module, null);
        $this->assertSame('local_ai', $ranking['meta']['provider']);
        $this->assertSame(9, $ranking['result']['breakdown']['ai_semantic_boost'] ?? 0);
    }

    public function test_admin_can_update_ranking_settings_for_external_ai_provider(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/app/admin/ranking', [
                'settings' => [
                    'enabled' => '1',
                    'provider' => 'external_ai',
                    'local_ai_goal_semantic_boost_per_match' => 4,
                    'local_ai_goal_semantic_boost_max' => 10,
                    'external_ai_attempts' => 3,
                    'external_ai_retry_sleep_ms' => 400,
                    'external_ai_max_boost' => 17,
                ],
            ])
            ->assertRedirect('/app/admin/ranking');

        $this->assertDatabaseHas('ranking_settings', [
            'key' => 'provider',
            'value' => 'external_ai',
        ]);
        $this->assertDatabaseHas('ranking_settings', [
            'key' => 'external_ai_max_boost',
            'value' => '17',
        ]);
        $this->assertDatabaseHas('ranking_settings', [
            'key' => 'external_ai_attempts',
            'value' => '3',
        ]);
        $this->assertDatabaseHas('ranking_settings', [
            'key' => 'external_ai_retry_sleep_ms',
            'value' => '400',
        ]);
    }

    public function test_admin_can_reset_ranking_settings_to_defaults(): void
    {
        $admin = User::factory()->admin()->create();

        RankingSetting::query()->create(['key' => 'enabled', 'value' => '1']);
        RankingSetting::query()->create(['key' => 'provider', 'value' => 'local_ai']);

        $this->actingAs($admin)
            ->post('/app/admin/ranking/reset')
            ->assertRedirect('/app/admin/ranking');

        $this->assertDatabaseMissing('ranking_settings', [
            'key' => 'enabled',
            'value' => '1',
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'ranking_settings_reset',
            'entity_type' => 'ranking_settings',
        ]);
    }

    public function test_admin_can_test_local_ai_ranking_provider(): void
    {
        $admin = User::factory()->admin()->create();

        RankingSetting::query()->create(['key' => 'provider', 'value' => 'local_ai']);

        $this->actingAs($admin)
            ->post('/app/admin/ranking/test')
            ->assertRedirect('/app/admin/ranking')
            ->assertSessionHas('status', 'Local AI heuristic ranking is configured and available.');

        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'ranking_provider_tested',
            'entity_type' => 'ranking_settings',
        ]);
        $this->assertDatabaseHas('ai_provider_usages', [
            'provider' => 'local_ai',
            'capability' => 'feed_ranking_probe',
            'success' => true,
        ]);

        $severityEvent = AssignmentAuditEvent::query()
            ->where('action', 'ranking_severity_changed')
            ->latest('id')
            ->first();

        $this->assertNotNull($severityEvent);
        $this->assertSame('critical', $severityEvent->meta['before_level'] ?? null);
        $this->assertSame('healthy', $severityEvent->meta['after_level'] ?? null);
        $this->assertSame('ranking_provider_tested', $severityEvent->meta['trigger'] ?? null);
    }

    public function test_admin_can_test_external_ai_ranking_provider(): void
    {
        $admin = User::factory()->admin()->create();

        config()->set('services.openai.api_key', 'test-key');

        Http::fake([
            '*' => Http::response([
                'id' => 'resp_probe_123',
                'model' => 'gpt-5-mini',
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'status' => 'ok',
                            'reason' => 'External ranking provider responded.',
                        ], JSON_THROW_ON_ERROR),
                    ],
                ]],
            ], 200, ['x-request-id' => 'req_probe_123']),
        ]);

        RankingSetting::query()->create(['key' => 'provider', 'value' => 'external_ai']);
        RankingSetting::query()->create(['key' => 'external_ai_max_boost', 'value' => '18']);

        $this->actingAs($admin)
            ->post('/app/admin/ranking/test')
            ->assertRedirect('/app/admin/ranking')
            ->assertSessionHas('status', 'External ranking provider responded.');

        $this->assertDatabaseHas('ai_provider_usages', [
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'success' => true,
            'request_id' => 'req_probe_123',
        ]);
    }

    public function test_admin_sees_external_ai_probe_failure_when_key_missing(): void
    {
        $admin = User::factory()->admin()->create();

        RankingSetting::query()->create(['key' => 'provider', 'value' => 'external_ai']);

        $this->actingAs($admin)
            ->post('/app/admin/ranking/test')
            ->assertRedirect('/app/admin/ranking')
            ->assertSessionHas('error', 'OPENAI_API_KEY is not configured.');

        $this->assertDatabaseHas('ai_provider_usages', [
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'success' => false,
        ]);
    }

    public function test_non_admin_cannot_test_ranking_provider(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/app/admin/ranking/test')
            ->assertForbidden();
    }

    public function test_assignment_audit_shows_ranking_severity_changed_action(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ops Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'healthy',
                'before_label' => 'Healthy',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=ranking_severity_changed')
            ->assertOk()
            ->assertSee('Ranking Severity Changed');
    }

    public function test_admin_can_update_and_reset_reminder_settings(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Reminder Settings Target Module',
            'description' => 'Reminder settings update target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 20,
            'started_at' => now()->subDays(5),
            'last_activity_at' => now()->subDays(3),
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/reminder-settings', [
                'settings' => [
                    'inactive_nudge_after_days' => 2,
                    'inactive_nudge_cooldown_days' => 3,
                    'not_started_nudge_after_days' => 10,
                    'not_started_nudge_cooldown_days' => 5,
                ],
            ])
            ->assertRedirect('/app/admin/reminder-settings');
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'reminder_settings_updated',
            'entity_type' => 'assignment_settings',
        ]);

        $this->artisan('assignments:sync-reminders')
            ->expectsOutput('Synced 1 reminder records.')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'inactive_nudge',
            'status' => 'pending',
        ]);

        \App\Models\AssignmentReminder::query()->delete();

        $this->actingAs($admin)
            ->post('/app/admin/reminder-settings/reset')
            ->assertRedirect('/app/admin/reminder-settings');
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'reminder_settings_reset',
            'entity_type' => 'assignment_settings',
        ]);

        $this->artisan('assignments:sync-reminders')
            ->expectsOutput('Synced 0 reminder records.')
            ->assertSuccessful();

        Carbon::setTestNow();
    }

    public function test_admin_can_view_module_management_index(): void
    {
        $admin = User::factory()->admin()->create();

        LearningModule::query()->create([
            'title' => 'Privacy Basics',
            'description' => 'Required privacy module',
            'status' => 'draft',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/modules')
            ->assertOk()
            ->assertSee('Course Publishing')
            ->assertSee('Privacy Basics');
    }

    public function test_admin_module_index_shows_scorm_summary(): void
    {
        $admin = User::factory()->admin()->create();
        $module = LearningModule::query()->create([
            'title' => 'SCORM Summary Module',
            'description' => 'Required privacy module',
            'status' => 'published',
            'topic' => 'privacy',
            'difficulty' => 'beginner',
            'source_type' => 'scorm',
        ]);

        LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => 'summary.zip',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/summary.zip',
            'extracted_disk' => 'local',
            'extracted_path' => 'learning-assets/scorm-summary',
            'launch_path' => 'index.html',
            'mime_type' => 'application/zip',
            'size_bytes' => 0,
            'status' => 'processed',
            'manifest' => ['title' => 'Summary Package', 'launch_path' => 'index.html'],
        ]);

        $learnerA = User::factory()->create();
        $learnerB = User::factory()->create();

        ModuleProgress::query()->create([
            'user_id' => $learnerA->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'last_activity_at' => now()->subHour(),
            'started_at' => now()->subDay(),
            'completed_at' => now()->subHour(),
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learnerB->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 45,
            'last_activity_at' => now()->subMinutes(30),
            'started_at' => now()->subDay(),
        ]);

        LearningEvent::query()->create([
            'user_id' => $learnerB->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['seeded' => true, 'score_raw' => 91, 'session_time' => '00:12:30', 'session_seconds' => 750],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/modules')
            ->assertOk()
            ->assertSee('SCORM Summary Module')
            ->assertSee('processed')
            ->assertSee('index.html')
            ->assertSee('Launch');
    }

    public function test_admin_can_view_scorm_overview_page(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'SCORM Overview Learner']);
        $secondLearner = User::factory()->create(['name' => 'SCORM Active Learner']);
        $module = LearningModule::query()->create([
            'title' => 'Customer Data Handling Essentials',
            'description' => 'SCORM overview demo',
            'status' => 'published',
            'source_type' => 'scorm',
            'compliance_area' => 'security',
        ]);

        LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => 'overview.zip',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/overview.zip',
            'extracted_disk' => 'local',
            'extracted_path' => 'learning-assets/scorm-overview',
            'launch_path' => 'index.html',
            'mime_type' => 'application/zip',
            'size_bytes' => 0,
            'status' => 'processed',
            'manifest' => ['title' => 'Overview Package', 'launch_path' => 'index.html'],
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subHour(),
            'last_activity_at' => now()->subHour(),
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_launched',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['asset_id' => 1, 'launch_path' => 'index.html'],
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['status' => 'completed', 'score_raw' => 97, 'session_time' => '00:11:00', 'session_seconds' => 660, 'percent_complete' => 100],
        ]);

        LearningEvent::query()->create([
            'user_id' => $secondLearner->id,
            'event_type' => 'scorm_launched',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['asset_id' => 1, 'launch_path' => 'index.html'],
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        LearningEvent::query()->create([
            'user_id' => $secondLearner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['status' => 'in_progress', 'score_raw' => 84, 'session_time' => '00:08:00', 'session_seconds' => 480, 'percent_complete' => 70],
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/scorm')
            ->assertOk()
            ->assertSee('SCORM Overview')
            ->assertSee('SCORM module completion, learner results, attempt history, and score tracking.')
            ->assertSee('Export CSV')
            ->assertSee('Manage Modules')
                ->assertSee('/app/admin/scorm/export', false)
                  ->assertSee('SCORM Modules')
                  ->assertSee('Recent Launches')
                ->assertSee('Recent Attempts')
                ->assertSee('Top Scores')
                ->assertSee('Customer Data Handling Essentials')
                ->assertSee('SCORM Overview Learner')
                ->assertSee('SCORM Active Learner')
                ->assertSee('Launches')
                  ->assertSee('Learner Leaderboard')
                  ->assertSee('Most Active Learners')
                  ->assertSee('/app/admin/modules/'.$module->id.'/edit', false);
      }

      public function test_admin_scorm_overview_shows_recovery_actions_when_live_demo_state_is_stale(): void
      {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'SCORM Stale Learner']);
        $module = LearningModule::query()->create([
            'title' => 'Customer Data Handling Essentials',
            'description' => 'SCORM stale-state demo',
            'status' => 'published',
            'source_type' => 'scorm',
            'compliance_area' => 'security',
        ]);

        LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => 'stale-overview.zip',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/stale-overview.zip',
            'extracted_disk' => 'local',
            'extracted_path' => 'learning-assets/scorm-stale-overview',
            'launch_path' => 'index.html',
            'mime_type' => 'application/zip',
            'size_bytes' => 0,
            'status' => 'processed',
            'manifest' => ['title' => 'Stale Overview Package', 'launch_path' => 'index.html'],
        ]);

        $launchEvent = LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_launched',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['asset_id' => 1, 'launch_path' => 'index.html'],
        ]);

        $runtimeEvent = LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['status' => 'completed', 'score_raw' => 88, 'session_time' => '00:07:00', 'session_seconds' => 420, 'percent_complete' => 100],
        ]);

        $launchEvent->forceFill([
            'created_at' => now()->subDays(2),
        ])->saveQuietly();

        $runtimeEvent->forceFill([
            'created_at' => now()->subDays(2),
        ])->saveQuietly();

        $this->actingAs($admin)
            ->get('/app/admin/scorm')
            ->assertOk()
            ->assertSee('SCORM Overview')
            ->assertSee('SCORM Modules');
      }

      public function test_admin_can_export_scorm_overview_csv(): void
      {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'SCORM Export Learner']);
        $module = LearningModule::query()->create([
            'title' => 'SCORM Export Module',
            'description' => 'SCORM export demo',
            'status' => 'published',
            'source_type' => 'scorm',
            'compliance_area' => 'security',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subHour(),
            'last_activity_at' => now()->subHour(),
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_launched',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['launch_path' => 'index.html'],
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['status' => 'completed', 'score_raw' => 93, 'session_time' => '00:09:00', 'session_seconds' => 540, 'percent_complete' => 100],
        ]);

        $response = $this->actingAs($admin)->get('/app/admin/scorm/export');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('section,key,value_1', $content);
        $this->assertStringContainsString('summary,modules,1', $content);
        $this->assertStringContainsString('module_rows,"SCORM Export Module"', $content);
        $this->assertStringContainsString('recent_launches,', $content);
        $this->assertStringContainsString('recent_attempts,', $content);
        $this->assertStringContainsString('learner_leaderboard,"SCORM Export Learner"', $content);
      }

    public function test_admin_scorm_overview_shows_reinforcement_proof(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'SCORM Reinforcement Learner', 'email' => 'scorm-reinforcement@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'SCORM Reinforcement Module',
            'description' => 'SCORM reinforcement proof',
            'status' => 'published',
            'source_type' => 'scorm',
            'compliance_area' => 'security',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $service = app(ReinforcementService::class);
        $service->syncForUser($learner);
        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();
        $service->completeForUser($touchpoint, $learner);

        $this->actingAs($admin)
            ->get('/app/admin/scorm')
            ->assertOk()
            ->assertSee('SCORM Overview')
            ->assertSee('SCORM Reinforcement Module');
    }

    public function test_admin_reporting_shows_reinforcement_failures_and_remediation(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Failed Reinforcement Learner', 'email' => 'failed-reinforcement@example.com']);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Reinforcement Failure Source',
            'description' => 'Module with failed follow-up',
            'status' => 'published',
            'source_type' => 'scorm',
            'target_roles' => ['manager'],
            'compliance_area' => 'security',
            'is_required' => true,
            'content_text' => 'Follow the right response and escalation path.',
        ]);

        $remediationModule = LearningModule::query()->create([
            'title' => 'Assigned Remediation Module',
            'description' => 'Follow-up learning assigned after a failed check',
            'status' => 'published',
            'source_type' => 'manual',
            'target_roles' => ['manager'],
            'compliance_area' => 'security',
        ]);

        $this->actingAs($admin)->post(route('app.admin.modules.reinforcement-questions.draft', ['module' => $module->id]));
        $questionSet = ReinforcementQuestionSet::query()->where('learning_module_id', $module->id)->firstOrFail();
        $questions = $questionSet->questions()->orderBy('position')->get();

        $this->actingAs($admin)->patch(route('app.admin.modules.reinforcement-questions.update', ['module' => $module->id]), [
            'set_title' => 'Failure review set',
            'set_summary' => 'Reviewed.',
            'questions' => $questions->map(fn (ReinforcementQuestion $question) => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'option_a' => 'Correct answer',
                'option_b' => 'Wrong answer',
                'option_c' => 'Wrong answer',
                'option_d' => 'Wrong answer',
                'correct_answer' => 'A',
                'explanation' => 'Reviewed',
                'remediation_learning_module_id' => $remediationModule->id,
            ])->all(),
        ]);
        $this->actingAs($admin)->patch(route('app.admin.modules.reinforcement-questions.approve', ['module' => $module->id]));

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $touchpoint = app(ReinforcementService::class)->syncForUser($learner)->firstWhere('interval_days', 7);
        $wrongAnswers = $questionSet->questions()->get()->mapWithKeys(fn (ReinforcementQuestion $question) => [$question->id => 'B'])->all();

        $this->actingAs($learner)
            ->post(route('app.reinforcement.submit', ['touchpoint' => $touchpoint->id]), [
                'answers' => $wrongAnswers,
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get('/app/admin/scorm')
            ->assertOk()
            ->assertSee('SCORM Overview');

        $this->actingAs($admin)
            ->get('/app/admin/compliance')
            ->assertOk()
            ->assertSee('Compliance Report');

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Recent reinforcement failures')
            ->assertSee('Assigned Remediation Module');
    }

    public function test_admin_can_view_scorm_demo_handout(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'SCORM Handout Learner']);
        $module = LearningModule::query()->create([
            'title' => 'Customer Data Handling Essentials',
            'description' => 'SCORM handout demo',
            'status' => 'published',
            'source_type' => 'scorm',
            'compliance_area' => 'security',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subHour(),
            'last_activity_at' => now()->subHour(),
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['status' => 'completed', 'score_raw' => 91, 'session_time' => '00:10:00', 'session_seconds' => 600, 'percent_complete' => 100],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/scorm/handout')
            ->assertOk()
            ->assertSee('SCORM Demo Handout')
            ->assertSee('Demo Scenario: Client Walkthrough')
            ->assertSee('Launch Demo Course')
            ->assertSee('Headline Metrics')
            ->assertSee('Demo Access')
            ->assertSee('Recommended Demo Flow')
            ->assertSee('Run Demo Walkthrough')
            ->assertSee('SCORM Hub')
            ->assertSee('Learner Course')
            ->assertSee('Module Admin')
            ->assertSee('Compliance')
            ->assertSee('Export Evidence')
            ->assertSee('Demo Prep Checklist')
            ->assertSee('Prep References')
            ->assertSee('Reset demo data and confirm the restored counts on the SCORM overview.')
            ->assertSee('Top SCORM Modules')
            ->assertSee('Top Learners')
            ->assertSee('Customer Data Handling Essentials')
            ->assertSee('SCORM Handout Learner')
            ->assertSee('admin@totalelearning.local')
            ->assertSee('/app/admin/scorm')
            ->assertSee('/app/admin/scorm/export')
            ->assertSee('/app/admin/modules')
            ->assertSee('/app/admin/compliance?source_type=scorm')
            ->assertSee("/app/modules/{$module->id}", false);
    }

    public function test_admin_can_reset_scorm_demo_data(): void
    {
        $admin = User::factory()->admin()->create();

        $demoLearner = User::factory()->create([
            'name' => 'Wrong Demo Learner',
            'email' => 'ava.carter@example.com',
        ]);

        $demoModule = LearningModule::query()->create([
            'title' => 'Customer Data Handling Essentials',
            'description' => 'Altered demo module',
            'status' => 'draft',
            'source_type' => 'scorm',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $demoLearner->id,
            'learning_module_id' => $demoModule->id,
            'status' => 'not_started',
            'percent_complete' => 0,
        ]);

        LearningEvent::query()->create([
            'user_id' => $demoLearner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $demoModule->id,
            'metadata' => ['status' => 'failed', 'score_raw' => 1, 'session_time' => '00:00:10', 'session_seconds' => 10],
        ]);

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->post('/app/admin/scorm/reset-demo');

        $response->assertOk()
            ->assertSee('SCORM Overview');

        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'entity_type' => 'scorm_demo',
            'action' => 'scorm_demo_reset',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/scorm')
            ->assertOk()
            ->assertSee('SCORM Overview');

        $restoredLearner = User::query()->where('email', 'ava.carter@example.com')->firstOrFail();
        $restoredModule = LearningModule::query()->where('title', 'Customer Data Handling Essentials')->firstOrFail();
        $restoredSecondaryModule = LearningModule::query()->where('title', 'Workplace Safety Decision Lab')->firstOrFail();

        $this->assertSame('Ava Carter', $restoredLearner->name);
        $this->assertSame('published', $restoredModule->status);
        $this->assertSame('published', $restoredSecondaryModule->status);
        $this->assertDatabaseHas('module_progress', [
            'user_id' => $restoredLearner->id,
            'learning_module_id' => $restoredModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
        ]);
        $this->assertDatabaseHas('learning_assets', [
            'learning_module_id' => $restoredModule->id,
            'asset_type' => 'scorm_package',
            'status' => 'processed',
        ]);
        $this->assertDatabaseHas('learning_assets', [
            'learning_module_id' => $restoredSecondaryModule->id,
            'asset_type' => 'scorm_package',
            'status' => 'processed',
        ]);
    }

    public function test_assignment_audit_shows_scorm_demo_reset_action(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'SCORM Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'scorm_demo',
            'action' => 'scorm_demo_reset',
            'meta' => [
                'status' => 'completed',
                'message' => 'SCORM demo data reset completed.',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=scorm_demo_reset')
            ->assertOk()
            ->assertSee('SCORM Demo Reset')
            ->assertSee('scorm demo reset')
            ->assertSee('SCORM demo data reset completed.')
            ->assertSee('status completed');
    }

    public function test_admin_can_create_and_update_learning_module_from_ui(): void
    {
        $admin = User::factory()->admin()->create();

        $createResponse = $this->actingAs($admin)
            ->post('/app/admin/modules', [
                'title' => 'Manager Privacy Core',
                'description' => 'Required privacy content',
                'topic' => 'privacy',
                'difficulty' => 'intermediate',
                'status' => 'published',
                'compliance_area' => 'data-privacy',
                'refresh_interval_days' => 365,
                'target_roles' => 'manager, specialist',
                'is_required' => '1',
                'content_text' => 'Core privacy learning content',
            ]);

        $module = LearningModule::query()->where('title', 'Manager Privacy Core')->firstOrFail();

        $createResponse->assertRedirect("/app/admin/modules/{$module->id}/edit");
        $this->assertSame(['manager', 'specialist'], $module->target_roles);
        $this->assertTrue($module->is_required);

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}", [
                'title' => 'Manager Privacy Core v2',
                'description' => 'Updated privacy content',
                'topic' => 'privacy',
                'difficulty' => 'advanced',
                'status' => 'archived',
                'compliance_area' => 'data-privacy',
                'refresh_interval_days' => 730,
                'target_roles' => 'manager',
                'content_text' => 'Updated privacy learning content',
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit");

        $module->refresh();

        $this->assertSame('Manager Privacy Core v2', $module->title);
        $this->assertSame('advanced', $module->difficulty);
        $this->assertSame('archived', $module->status);
        $this->assertSame(['manager'], $module->target_roles);
        $this->assertFalse($module->is_required);
        $this->assertSame([7, 30], $module->reinforcement_intervals_days);
    }

    public function test_admin_can_open_create_scorm_module_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/modules/create-scorm')
            ->assertOk()
            ->assertSee('Create SCORM Module')
            ->assertSee('Create the module metadata first, then upload the SCORM package from the SCORM Package panel.')
            ->assertSee('Source Type')
            ->assertSee('SCORM');
    }

    public function test_admin_can_create_scorm_module_from_dedicated_flow(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->post('/app/admin/modules', [
                'title' => 'Emergency Response Simulator',
                'description' => 'SCORM-first demo module',
                'topic' => 'safety',
                'difficulty' => 'beginner',
                'status' => 'draft',
                'source_type' => 'scorm',
                'compliance_area' => 'health-safety',
                'target_roles' => 'specialist, manager',
            ]);

        $module = LearningModule::query()->where('title', 'Emergency Response Simulator')->firstOrFail();

        $response->assertRedirect("/app/admin/modules/{$module->id}/edit#scorm-package");
        $response->assertSessionHas('status', 'SCORM module created. Upload a package to continue.');

        $this->assertSame('scorm', $module->source_type);
        $this->assertSame(['specialist', 'manager'], $module->target_roles);
    }

    public function test_admin_can_save_custom_reinforcement_intervals_for_module(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->post('/app/admin/modules', [
                'title' => 'Custom Reinforcement Module',
                'description' => 'Module with custom follow-up cadence',
                'topic' => 'safety',
                'difficulty' => 'beginner',
                'status' => 'draft',
                'source_type' => 'scorm',
                'compliance_area' => 'health-safety',
                'reinforcement_intervals_days' => '3, 14, 45',
                'target_roles' => 'manager',
            ]);

        $module = LearningModule::query()->where('title', 'Custom Reinforcement Module')->firstOrFail();
        $response->assertRedirect("/app/admin/modules/{$module->id}/edit#scorm-package");
        $this->assertSame([3, 14, 45], $module->reinforcement_intervals_days);

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}", [
                'title' => 'Custom Reinforcement Module',
                'description' => 'Module with custom follow-up cadence',
                'topic' => 'safety',
                'difficulty' => 'beginner',
                'status' => 'draft',
                'source_type' => 'scorm',
                'compliance_area' => 'health-safety',
                'reinforcement_intervals_days' => '5, 21',
                'target_roles' => 'manager',
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit");

        $module->refresh();
        $this->assertSame([5, 21], $module->reinforcement_intervals_days);
    }

    public function test_admin_can_upload_scorm_package_for_module(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $module = LearningModule::query()->create([
            'title' => 'SCORM Upload Target',
            'description' => 'SCORM upload target',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'source_type' => 'manual',
        ]);

        $zipPath = tempnam(sys_get_temp_dir(), 'scorm');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('imsmanifest.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<manifest identifier="demo-scorm" version="1.2" xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2">
  <organizations default="ORG1">
    <organization identifier="ORG1">
      <title>Demo Upload Package</title>
    </organization>
  </organizations>
  <resources>
    <resource identifier="RES1" type="webcontent" href="index.html">
      <file href="index.html"/>
    </resource>
  </resources>
</manifest>
XML);
        $zip->addFromString('index.html', '<html><head><title>Demo</title></head><body>Demo SCORM</body></html>');
        $zip->close();

        $file = new UploadedFile($zipPath, 'demo-scorm.zip', 'application/zip', null, true);

        $this->actingAs($admin)
            ->post(route('app.admin.modules.scorm.upload', ['module' => $module->id]), [
                'scorm_package' => $file,
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit#scorm-package")
            ->assertSessionHas('status', 'SCORM package uploaded and processed.')
            ->assertSessionHas('scormUploadStatus', function (array $status): bool {
                return $status['state'] === 'completed'
                    && $status['title'] === 'SCORM package uploaded and processed.'
                    && str_contains($status['message'], 'Launch path: `index.html`.');
            });

        $asset = LearningAsset::query()->where('learning_module_id', $module->id)->firstOrFail();
        $module->refresh();

        $this->assertSame('scorm_package', $asset->asset_type);
        $this->assertSame('processed', $asset->status);
        $this->assertSame('index.html', $asset->launch_path);
        $this->assertSame('scorm', $module->source_type);
        $this->assertSame('index.html', $module->source_uri);
        Storage::disk('local')->assertExists($asset->extracted_path.'/imsmanifest.xml');
        Storage::disk('local')->assertExists($asset->extracted_path.'/index.html');
    }

    public function test_admin_can_generate_review_and_approve_reinforcement_question_set(): void
    {
        $admin = User::factory()->admin()->create();
        $module = LearningModule::query()->create([
            'title' => 'Safe Guarding Essentials',
            'description' => 'Core safeguarding module',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'source_type' => 'scorm',
            'topic' => 'safeguarding',
            'content_text' => 'Learners should identify risks, follow the right response, and escalate concerns appropriately.',
        ]);

        $this->actingAs($admin)
            ->post(route('app.admin.modules.reinforcement-questions.draft', ['module' => $module->id]))
            ->assertRedirect("/app/admin/modules/{$module->id}/edit")
            ->assertSessionHas('status');

        $questionSet = ReinforcementQuestionSet::query()
            ->where('learning_module_id', $module->id)
            ->firstOrFail();

        $this->assertSame('draft', $questionSet->status);
        $this->assertSame('ai_draft', $questionSet->generation_mode);
        $this->assertCount(3, $questionSet->questions);

        $questions = $questionSet->questions()->orderBy('position')->get();

        $this->actingAs($admin)
            ->patch(route('app.admin.modules.reinforcement-questions.update', ['module' => $module->id]), [
                'set_title' => 'Safe Guarding reviewed reinforcement set',
                'set_summary' => 'Reviewed by admin before learner use.',
                'questions' => $questions->map(function (ReinforcementQuestion $question, int $index) use ($module) {
                    return [
                        'id' => $question->id,
                        'question_text' => 'Reviewed question '.($index + 1).' for '.$module->title,
                        'option_a' => 'Correct option',
                        'option_b' => 'Distractor one',
                        'option_c' => 'Distractor two',
                        'option_d' => 'Distractor three',
                        'correct_answer' => 'A',
                        'explanation' => 'Coaching note '.($index + 1),
                        'remediation_learning_module_id' => $module->id,
                    ];
                })->all(),
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit")
            ->assertSessionHas('status', 'Reinforcement draft updated and moved into review.');

        $questionSet->refresh();
        $this->assertSame('in_review', $questionSet->status);
        $this->assertSame('Safe Guarding reviewed reinforcement set', $questionSet->title);

        $this->actingAs($admin)
            ->patch(route('app.admin.modules.reinforcement-questions.approve', ['module' => $module->id]))
            ->assertRedirect("/app/admin/modules/{$module->id}/edit")
            ->assertSessionHas('status', 'Reinforcement question set approved for learner follow-up.');

        $questionSet->refresh();
        $this->assertSame('approved', $questionSet->status);
        $this->assertSame($admin->id, $questionSet->reviewed_by);
        $this->assertNotNull($questionSet->reviewed_at);
        $this->assertDatabaseHas('reinforcement_questions', [
            'reinforcement_question_set_id' => $questionSet->id,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/modules/{$module->id}/edit")
            ->assertOk()
            ->assertSee('Reinforcement Questions')
            ->assertSee('Safe Guarding reviewed reinforcement set')
            ->assertSee('Approved for learner reinforcement');
    }

    public function test_approved_reinforcement_question_set_is_attached_to_touchpoints(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Follow Up Learner', 'email' => 'followup@example.com']);
        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Privacy Refresh',
            'description' => 'Privacy refresher content',
            'status' => 'published',
            'difficulty' => 'beginner',
            'source_type' => 'scorm',
            'target_roles' => ['manager'],
            'content_text' => 'Review the key privacy risks and follow the approved response path.',
        ]);

        $this->actingAs($admin)
            ->post(route('app.admin.modules.reinforcement-questions.draft', ['module' => $module->id]));

        $questionSet = ReinforcementQuestionSet::query()
            ->where('learning_module_id', $module->id)
            ->firstOrFail();

        $questions = $questionSet->questions()->orderBy('position')->get();

        $this->actingAs($admin)->patch(route('app.admin.modules.reinforcement-questions.update', ['module' => $module->id]), [
            'set_title' => 'Privacy reviewed reinforcement set',
            'set_summary' => 'Ready for learner follow-up.',
            'questions' => $questions->map(fn (ReinforcementQuestion $question) => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'option_a' => $question->options['A'] ?? 'A',
                'option_b' => $question->options['B'] ?? 'B',
                'option_c' => $question->options['C'] ?? 'C',
                'option_d' => $question->options['D'] ?? 'D',
                'correct_answer' => 'A',
                'explanation' => 'Reviewed explanation',
                'remediation_learning_module_id' => $module->id,
            ])->all(),
        ]);

        $this->actingAs($admin)
            ->patch(route('app.admin.modules.reinforcement-questions.approve', ['module' => $module->id]));

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(12),
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $touchpoint = app(ReinforcementService::class)
            ->syncForUser($learner)
            ->firstWhere('interval_days', 7);

        $this->assertNotNull($touchpoint);
        $this->assertSame($questionSet->id, $touchpoint->reinforcement_question_set_id);
        $this->assertStringContainsString('reviewed reinforcement question', $touchpoint->prompt);
    }

    public function test_learner_can_answer_approved_reinforcement_questions_and_record_proof(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Reinforcement Answer Learner', 'email' => 'reinforcement-answer@example.com']);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Conduct Refresh',
            'description' => 'Conduct refresher content',
            'status' => 'published',
            'difficulty' => 'beginner',
            'source_type' => 'scorm',
            'target_roles' => ['manager'],
            'content_text' => 'Follow the conduct guidance and apply the correct response.',
        ]);

        $this->actingAs($admin)->post(route('app.admin.modules.reinforcement-questions.draft', ['module' => $module->id]));
        $questionSet = ReinforcementQuestionSet::query()->where('learning_module_id', $module->id)->firstOrFail();
        $questions = $questionSet->questions()->orderBy('position')->get();

        $this->actingAs($admin)->patch(route('app.admin.modules.reinforcement-questions.update', ['module' => $module->id]), [
            'set_title' => 'Conduct review set',
            'set_summary' => 'Reviewed.',
            'questions' => $questions->map(fn (ReinforcementQuestion $question) => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'option_a' => 'Correct answer',
                'option_b' => 'Wrong one',
                'option_c' => 'Wrong two',
                'option_d' => 'Wrong three',
                'correct_answer' => 'A',
                'explanation' => 'Reviewed',
                'remediation_learning_module_id' => $module->id,
            ])->all(),
        ]);
        $this->actingAs($admin)->patch(route('app.admin.modules.reinforcement-questions.approve', ['module' => $module->id]));

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(12),
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $touchpoint = app(ReinforcementService::class)
            ->syncForUser($learner)
            ->firstWhere('interval_days', 7);

        $this->assertNotNull($touchpoint);

        $this->actingAs($learner)
            ->get(route('app.reinforcement.show', ['touchpoint' => $touchpoint->id]))
            ->assertOk()
            ->assertSee('Ongoing reinforcement + proof')
            ->assertSee('Submit answers');

        $answers = $questionSet->questions()->pluck('correct_answer', 'id')->all();

        $this->actingAs($learner)
            ->post(route('app.reinforcement.submit', ['touchpoint' => $touchpoint->id]), [
                'answers' => $answers,
            ])
            ->assertRedirect(route('app.reminders'))
            ->assertSessionHas('status', 'Reinforcement proof recorded.');

        $this->assertDatabaseHas('reinforcement_touchpoints', [
            'id' => $touchpoint->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('reinforcement_responses', [
            'reinforcement_touchpoint_id' => $touchpoint->id,
            'user_id' => $learner->id,
            'is_correct' => true,
        ]);
    }

    public function test_incorrect_reinforcement_answer_assigns_remediation_learning(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Needs Remediation', 'email' => 'needs-remediation@example.com']);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Fraud Awareness Core',
            'description' => 'Fraud awareness core',
            'status' => 'published',
            'difficulty' => 'beginner',
            'source_type' => 'scorm',
            'target_roles' => ['manager'],
            'content_text' => 'Spot the risk and choose the right escalation path.',
        ]);

        $remediationModule = LearningModule::query()->create([
            'title' => 'Fraud Awareness Remediation',
            'description' => 'Remedial learning',
            'status' => 'published',
            'difficulty' => 'beginner',
            'source_type' => 'manual',
            'target_roles' => ['manager'],
        ]);

        $this->actingAs($admin)->post(route('app.admin.modules.reinforcement-questions.draft', ['module' => $module->id]));
        $questionSet = ReinforcementQuestionSet::query()->where('learning_module_id', $module->id)->firstOrFail();
        $questions = $questionSet->questions()->orderBy('position')->get();

        $this->actingAs($admin)->patch(route('app.admin.modules.reinforcement-questions.update', ['module' => $module->id]), [
            'set_title' => 'Fraud review set',
            'set_summary' => 'Reviewed.',
            'questions' => $questions->map(fn (ReinforcementQuestion $question) => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'option_a' => 'Correct answer',
                'option_b' => 'Wrong one',
                'option_c' => 'Wrong two',
                'option_d' => 'Wrong three',
                'correct_answer' => 'A',
                'explanation' => 'Reviewed',
                'remediation_learning_module_id' => $remediationModule->id,
            ])->all(),
        ]);
        $this->actingAs($admin)->patch(route('app.admin.modules.reinforcement-questions.approve', ['module' => $module->id]));

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(12),
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $touchpoint = app(ReinforcementService::class)
            ->syncForUser($learner)
            ->firstWhere('interval_days', 7);

        $wrongAnswers = $questionSet->questions()->get()->mapWithKeys(fn (ReinforcementQuestion $question) => [$question->id => 'B'])->all();

        $this->actingAs($learner)
            ->post(route('app.reinforcement.submit', ['touchpoint' => $touchpoint->id]), [
                'answers' => $wrongAnswers,
            ])
            ->assertRedirect(route('app.reinforcement.show', ['touchpoint' => $touchpoint->id]))
            ->assertSessionHas('status', 'Answers recorded. Extra learning has been assigned where needed.');

        $this->assertDatabaseHas('reinforcement_touchpoints', [
            'id' => $touchpoint->id,
            'status' => 'needs_retry',
        ]);
        $this->assertDatabaseHas('reinforcement_responses', [
            'reinforcement_touchpoint_id' => $touchpoint->id,
            'user_id' => $learner->id,
            'is_correct' => false,
        ]);
        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $remediationModule->id,
            'reminder_type' => 'reinforcement_follow_up',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('learning_events', [
            'user_id' => $learner->id,
            'event_type' => 'reinforcement_failed',
            'entity_type' => 'reinforcement_touchpoint',
            'entity_id' => $touchpoint->id,
        ]);
    }

    public function test_admin_sees_scorm_upload_failure_status_in_panel(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $module = LearningModule::query()->create([
            'title' => 'Broken SCORM Upload Target',
            'description' => 'Broken SCORM upload target',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'source_type' => 'manual',
        ]);

        $zipPath = tempnam(sys_get_temp_dir(), 'scorm-bad');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('index.html', '<html><body>Missing manifest</body></html>');
        $zip->close();

        $file = new UploadedFile($zipPath, 'broken-scorm.zip', 'application/zip', null, true);

        $this->actingAs($admin)
            ->post(route('app.admin.modules.scorm.upload', ['module' => $module->id]), [
                'scorm_package' => $file,
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit#scorm-package")
            ->assertSessionHas('status', 'SCORM package upload failed.')
            ->assertSessionHas('scormUploadStatus', function (array $status): bool {
                return $status['state'] === 'failed'
                    && $status['title'] === 'SCORM package upload failed.'
                    && $status['message'] === 'SCORM package is missing imsmanifest.xml.';
            });

        $asset = LearningAsset::query()->where('learning_module_id', $module->id)->firstOrFail();

        $this->assertSame('failed', $asset->status);
        $this->assertSame('SCORM package is missing imsmanifest.xml.', $asset->error_message);

        $this->actingAs($admin)
            ->withSession([
                'scormUploadStatus' => [
                    'state' => 'failed',
                    'title' => 'SCORM package upload failed.',
                    'message' => 'SCORM package is missing imsmanifest.xml.',
                ],
            ])
            ->get("/app/admin/modules/{$module->id}/edit")
            ->assertOk()
            ->assertSee('SCORM package upload failed.')
            ->assertSee('SCORM package is missing imsmanifest.xml.');
    }

    public function test_admin_can_activate_previous_scorm_package_as_current(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'SCORM Activation Target',
            'description' => 'SCORM activation target',
            'status' => 'published',
            'difficulty' => 'beginner',
            'source_type' => 'scorm',
            'source_uri' => 'new/index.html',
        ]);

        $olderAsset = LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => 'older-package.zip',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/older-package.zip',
            'extracted_disk' => 'local',
            'extracted_path' => 'learning-assets/scorm-older',
            'launch_path' => 'older/index.html',
            'mime_type' => 'application/zip',
            'size_bytes' => 4 * 1024 * 1024,
            'status' => 'processed',
            'processing_metadata' => [
                'processed_at' => now()->subDay()->toIso8601String(),
                'activated_at' => now()->subDay()->toIso8601String(),
            ],
        ]);

        $newerAsset = LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => 'newer-package.zip',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/newer-package.zip',
            'extracted_disk' => 'local',
            'extracted_path' => 'learning-assets/scorm-newer',
            'launch_path' => 'new/index.html',
            'mime_type' => 'application/zip',
            'size_bytes' => 5 * 1024 * 1024,
            'status' => 'processed',
            'processing_metadata' => [
                'processed_at' => now()->subHour()->toIso8601String(),
                'activated_at' => now()->subHour()->toIso8601String(),
            ],
        ]);

        $this->actingAs($admin)
            ->patch(route('app.admin.modules.scorm.activate', ['module' => $module->id, 'asset' => $olderAsset->id]))
            ->assertRedirect("/app/admin/modules/{$module->id}/edit#scorm-package")
            ->assertSessionHas('status', 'SCORM package activated as current.');

        $module->refresh();
        $olderAsset->refresh();

        $this->assertSame('older/index.html', $module->source_uri);
        $this->assertSame($olderAsset->id, $module->latestScormAsset()?->id);
        $this->assertArrayHasKey('activated_at', $olderAsset->processing_metadata ?? []);

        $this->actingAs($admin)
            ->withSession([
                'scormUploadStatus' => [
                    'state' => 'completed',
                    'title' => 'SCORM package activated as current.',
                    'message' => 'Package `older-package.zip` is now the current SCORM package for this module.',
                ],
            ])
            ->get("/app/admin/modules/{$module->id}/edit")
            ->assertOk()
            ->assertSee('SCORM package activated as current.')
            ->assertSee('older-package.zip')
            ->assertSee('Current package')
            ->assertSee('Use as current package')
            ->assertSee('newer-package.zip');
    }

    public function test_admin_module_index_shows_scorm_create_and_upload_shortcuts(): void
    {
        $admin = User::factory()->admin()->create();
        $module = LearningModule::query()->create([
            'title' => 'Index Shortcut Module',
            'description' => 'Shortcut target',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'source_type' => 'manual',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/modules')
            ->assertOk()
            ->assertSee('Create Module')
            ->assertSee('Upload SCORM package')
            ->assertSee("/app/admin/modules/{$module->id}/edit#scorm-package", false);
    }

    public function test_admin_module_edit_shows_scorm_runtime_summary(): void
    {
        $admin = User::factory()->admin()->create();
        $module = LearningModule::query()->create([
            'title' => 'Customer Data Handling Essentials',
            'description' => 'SCORM edit summary target',
            'status' => 'published',
            'topic' => 'compliance',
            'difficulty' => 'beginner',
            'source_type' => 'scorm',
        ]);

        LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => 'failed-package.zip',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/failed-package.zip',
            'mime_type' => 'application/zip',
            'size_bytes' => 3 * 1024 * 1024,
            'status' => 'failed',
            'error_message' => 'SCORM package is missing imsmanifest.xml.',
        ]);

        LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => 'edit-summary.zip',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/edit-summary.zip',
            'extracted_disk' => 'local',
            'extracted_path' => 'learning-assets/scorm-edit-summary',
            'launch_path' => 'index.html',
            'mime_type' => 'application/zip',
            'size_bytes' => 12 * 1024 * 1024,
            'status' => 'processed',
            'manifest' => ['title' => 'Edit Summary Package', 'launch_path' => 'index.html'],
        ]);

        $learner = User::factory()->create();

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'last_activity_at' => now()->subHour(),
            'started_at' => now()->subDay(),
            'completed_at' => now()->subHour(),
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['seeded' => true, 'score_raw' => 88, 'session_time' => '00:09:15', 'session_seconds' => 555],
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/modules/{$module->id}/edit")
            ->assertOk()
            ->assertSee('Demo Scenario: Client Walkthrough')
            ->assertSee('Primary Demo Course')
            ->assertSee('SCORM Runtime Summary')
            ->assertSee('Drop a SCORM package here or click to browse')
            ->assertSee('max size 50 MB')
            ->assertSee('Choose a valid SCORM .zip file under 50 MB.')
            ->assertSee('Upload in progress. Do not leave this page.')
            ->assertSee('No file selected.')
            ->assertSee('Package status:')
            ->assertSee('processed')
            ->assertSee('Launch path:')
            ->assertSee('index.html')
            ->assertSee('Recent SCORM Package Uploads')
            ->assertSee('failed-package.zip')
            ->assertSee('SCORM package is missing imsmanifest.xml.')
            ->assertSee('edit-summary.zip')
            ->assertSee('12 MB')
            ->assertSee('Learners with progress:')
            ->assertSee('Completed:')
            ->assertSee('Average score:')
            ->assertSee('88')
            ->assertSee('Logged session time:')
            ->assertSee('9m 15s')
            ->assertSee('Last runtime:')
            ->assertSee('Recent SCORM Attempts')
            ->assertSee((string) $learner->name)
            ->assertSee('n/a');
    }

    public function test_learner_can_launch_scorm_module_and_runtime_updates_progress(): void
    {
        Storage::fake('local');

        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'SCORM Runtime Module',
            'description' => 'SCORM runtime module',
            'status' => 'published',
            'difficulty' => 'beginner',
            'source_type' => 'scorm',
        ]);

        Storage::disk('local')->put("learning-assets/{$module->id}/scorm/test-package/index.html", '<html><head><title>Runtime Demo</title></head><body>Runtime</body></html>');

        LearningAsset::query()->create([
            'learning_module_id' => $module->id,
            'asset_type' => 'scorm_package',
            'original_filename' => 'runtime-demo.zip',
            'storage_disk' => 'local',
            'storage_path' => "learning-assets/{$module->id}/packages/runtime-demo.zip",
            'extracted_disk' => 'local',
            'extracted_path' => "learning-assets/{$module->id}/scorm/test-package",
            'launch_path' => 'index.html',
            'mime_type' => 'application/zip',
            'size_bytes' => 0,
            'status' => 'processed',
            'manifest' => [
                'title' => 'Runtime Demo',
                'launch_path' => 'index.html',
            ],
        ]);

        $this->actingAs($learner)
            ->get(route('app.modules.scorm.launch', ['module' => $module->id]))
            ->assertOk()
            ->assertSee('SCORM prototype launch')
            ->assertSee(route('app.modules.scorm.asset', ['module' => $module->id, 'path' => 'index.html']), false);

        $this->actingAs($learner)
            ->get(route('app.modules.scorm.asset', ['module' => $module->id, 'path' => 'index.html']))
            ->assertOk()
            ->assertSee('window.API', false)
            ->assertSee('window.API_1484_11', false);

        $this->actingAs($learner)
            ->postJson(route('app.modules.scorm.runtime', ['module' => $module->id]), [
                'lesson_status' => 'completed',
                'score_raw' => 88,
                'session_time' => '00:05:30',
                'lesson_location' => 'final-slide',
                'raw' => ['demo' => true],
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.percent_complete', 100);

        $this->assertDatabaseHas('module_progress', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
        ]);

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ]);

        $this->assertEquals(88.0, LearningEvent::query()->where('event_type', 'scorm_runtime_committed')->latest('id')->first()?->metadata['score_raw']);
        $this->assertSame(330, LearningEvent::query()->where('event_type', 'scorm_runtime_committed')->latest('id')->first()?->metadata['session_seconds']);
    }

    public function test_non_admin_cannot_access_module_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/modules')
            ->assertForbidden();
    }

    public function test_admin_can_add_assignment_rule_from_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/app/admin/assignments/rules', [
                'role' => 'manager',
                'compliance_area' => 'ethics',
            ])
            ->assertRedirect('/app/admin/assignments');

        $this->assertDatabaseHas('compliance_role_rules', [
            'role' => 'manager',
            'compliance_area' => 'ethics',
        ]);

        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'action' => 'rule_saved',
            'entity_type' => 'compliance_role_rule',
        ]);
    }

    public function test_admin_can_remove_assignment_rule_from_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $rule = ComplianceRoleRule::query()->create([
            'role' => 'manager',
            'compliance_area' => 'ethics',
        ]);

        $this->actingAs($admin)
            ->delete("/app/admin/assignments/rules/{$rule->id}")
            ->assertRedirect('/app/admin/assignments');

        $this->assertDatabaseMissing('compliance_role_rules', [
            'id' => $rule->id,
        ]);

        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'action' => 'rule_removed',
            'entity_type' => 'compliance_role_rule',
        ]);
    }

    public function test_non_admin_cannot_mutate_assignment_rules_from_dashboard(): void
    {
        $user = User::factory()->create();
        $rule = ComplianceRoleRule::query()->create([
            'role' => 'manager',
            'compliance_area' => 'ethics',
        ]);

        $this->actingAs($user)
            ->post('/app/admin/assignments/rules', [
                'role' => 'specialist',
                'compliance_area' => 'privacy',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete("/app/admin/assignments/rules/{$rule->id}")
            ->assertForbidden();
    }

    public function test_assignment_dashboard_requires_admin_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/assignments')
            ->assertForbidden();
    }

    public function test_admin_can_view_assignment_dashboard_summary(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $manager = User::factory()->create(['name' => 'Manager User']);

        ComplianceRoleRule::query()->create([
            'role' => 'manager',
            'compliance_area' => 'data-privacy',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Privacy Annual Refresher',
            'description' => 'Required privacy refresher',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'refresh_interval_days' => 30,
        ]);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $manager->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Admin Assignments')
            ->assertSee('Learning Events')
            ->assertSee('Course Completion Statistics')
            ->assertSee('Learner Completion Breakdown')
            ->assertSee('Urgent Assignment')
            ->assertSee('Assigned Courses')
            ->assertSee('Completion Mix')
            ->assertSee('adminAssignmentCompletionChart', false)
            ->assertSee('adminAssignmentCompletionDoughnut', false)
            ->assertSee('vendor/learninguiux/js/component/component-chartjs.js', false)
            ->assertSee('Operational Snapshot')
            ->assertSee('Client walkthrough course data')
            ->assertSee('Jump to Workspace')
            ->assertSee('Open Compliance Report')
            ->assertSee('Open User Management')
            ->assertSee('Open SCORM Overview')
            ->assertSee('Dashboard Sections')
            ->assertSee('Operations')
            ->assertSee('AI Intelligence')
            ->assertSee('Governance')
            ->assertSee('Operations Pressure Mix')
            ->assertSee('adminOperationsUrgencyChart', false)
            ->assertSee('Probe Health Snapshot')
            ->assertSee('adminAiProbeChart', false)
            ->assertSee('Governance Snapshot')
            ->assertSee('Waiver vs Acknowledgement Mix')
            ->assertSee('adminGovernanceMixChart', false)
            ->assertSee('adminGovernanceAuditChart', false)
            ->assertSee('Rules by Role')
            ->assertSee('Overdue by Role')
            ->assertSee('Required Modules by Compliance Area')
            ->assertSee('data-privacy')
            ->assertSee('Privacy Annual Refresher')
            ->assertSee('Manager User')
            ->assertSee('100%');

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_shows_waiver_reporting(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Waived Learner']);
        $module = LearningModule::query()->create([
            'title' => 'Privacy Waiver Module',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Waivers')
            ->assertSee('Waivers by Role')
            ->assertSee('Waiver Leader')
            ->assertSee('Waived Learner')
            ->assertSee('Privacy Waiver Module');
    }

    public function test_admin_dashboard_shows_recent_assignment_activity(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Recent Assignment Activity')
            ->assertSee('Recent Governance Event')
            ->assertSee('Audit Admin')
            ->assertSee('rule saved')
            ->assertSee('manager')
            ->assertSee('data-privacy');
    }

    public function test_admin_dashboard_shows_recent_reminder_batch_activity_details(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => null,
            'entity_type' => 'assignment_reminder_batch',
            'action' => 'reminder_batch_run',
            'meta' => [
                'synced_total' => 3,
                'sent_total' => 2,
                'remaining_pending' => 4,
                'remaining_pending_filtered' => 1,
                'mode' => 'send_only',
                'types' => ['due_soon'],
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Recent Assignment Activity')
            ->assertSee('reminder batch run')
            ->assertSee('synced 3, sent 2, remaining total 4')
            ->assertSee('remaining filtered 1')
            ->assertSee('mode send_only')
            ->assertSee('types due_soon');
    }

    public function test_admin_dashboard_shows_reminder_batches_24h_summary(): void
    {
        Carbon::setTestNow('2026-03-06 12:00:00');

        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        for ($i = 0; $i < 7; $i++) {
            AssignmentAuditEvent::query()->create([
                'actor_user_id' => null,
                'entity_type' => 'assignment_reminder_batch',
                'action' => 'reminder_batch_run',
                'meta' => [
                    'synced_total' => 1,
                    'sent_total' => 1,
                    'remaining_pending' => 0,
                ],
            ]);
        }

        $older = AssignmentAuditEvent::query()->create([
            'actor_user_id' => null,
            'entity_type' => 'assignment_reminder_batch',
            'action' => 'reminder_batch_run',
            'meta' => [
                'synced_total' => 1,
                'sent_total' => 1,
                'remaining_pending' => 0,
            ],
        ]);
        $older->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->save();

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Reminder Batches (24h)')
            ->assertSee('7');

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_shows_not_started_nudge_summary(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Nudge Learner']);
        $module = LearningModule::query()->create([
            'title' => 'Not Started Nudge Summary Module',
            'description' => 'Summary card target',
            'status' => 'published',
            'is_required' => true,
        ]);

        AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'not_started_nudge',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Not Started Nudges')
            ->assertSee('1');
    }

    public function test_admin_dashboard_shows_operational_settings_snapshot(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/app/admin/scoring', [
                'weights' => array_merge(config('learning_assignments.feed_scoring'), [
                    'required_module' => 81,
                    'topic_match' => 42,
                    'goal_affinity_max' => 9,
                ]),
            ])
            ->assertRedirect('/app/admin/scoring');

        $this->actingAs($admin)
            ->post('/app/admin/reminder-settings', [
                'settings' => [
                    'inactive_nudge_after_days' => 6,
                    'inactive_nudge_cooldown_days' => 2,
                    'not_started_nudge_after_days' => 11,
                    'not_started_nudge_cooldown_days' => 4,
                ],
            ])
            ->assertRedirect('/app/admin/reminder-settings');

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Inactive Nudge Window')
            ->assertSee('6 days')
            ->assertSee('Cooldown: 2 days')
            ->assertSee('Not Started Nudge Window')
            ->assertSee('11 days')
            ->assertSee('Cooldown: 4 days')
            ->assertSee('Top Feed Weights')
            ->assertSee('required=81')
            ->assertSee('topic=42')
            ->assertSee('goal_max=9');
    }

    public function test_admin_dashboard_shows_recent_tuning_changes(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ops Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'feed_scoring_settings',
            'action' => 'feed_scoring_settings_updated',
            'meta' => [
                'changed_keys' => ['topic_match', 'goal_affinity_max'],
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_settings',
            'action' => 'reminder_settings_updated',
            'meta' => [
                'changed_keys' => ['inactive_nudge_after_days'],
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Recent Tuning Changes')
            ->assertSee('Ops Admin')
            ->assertSee('feed scoring settings updated')
            ->assertSee('reminder settings updated')
            ->assertSee('changed topic_match, goal_affinity_max')
            ->assertSee('changed inactive_nudge_after_days');
    }

    public function test_admin_dashboard_shows_settings_override_counts_and_keys(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/app/admin/scoring', [
                'weights' => array_merge(config('learning_assignments.feed_scoring'), [
                    'topic_match' => 41,
                    'goal_affinity_max' => 7,
                ]),
            ])
            ->assertRedirect('/app/admin/scoring');

        $this->actingAs($admin)
            ->post('/app/admin/reminder-settings', [
                'settings' => [
                    'inactive_nudge_after_days' => 5,
                    'inactive_nudge_cooldown_days' => 3,
                    'not_started_nudge_after_days' => 10,
                    'not_started_nudge_cooldown_days' => 5,
                ],
            ])
            ->assertRedirect('/app/admin/reminder-settings');

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Settings Overrides')
            ->assertSee('scoring=2')
            ->assertSee('reminder=1')
            ->assertSee('scoring keys: topic_match, goal_affinity_max')
            ->assertSee('reminder keys: inactive_nudge_after_days');
    }

    public function test_admin_dashboard_shows_last_tuning_change_metadata(): void
    {
        Carbon::setTestNow('2026-03-08 12:34:00');

        $admin = User::factory()->admin()->create(['name' => 'Tune Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_settings',
            'action' => 'reminder_settings_updated',
            'meta' => [
                'changed_keys' => ['inactive_nudge_after_days'],
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Last Tuning Change')
            ->assertSee('2026-03-08 12:34')
            ->assertSee('reminder settings updated by Tune Admin');

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_shows_ai_ranking_health(): void
    {
        Carbon::setTestNow('2026-03-09 12:00:00');

        $admin = User::factory()->admin()->create();

        RankingSetting::query()->create(['key' => 'enabled', 'value' => '1']);
        RankingSetting::query()->create(['key' => 'provider', 'value' => 'external_ai']);
        RankingSetting::query()->create(['key' => 'external_ai_max_boost', 'value' => '17']);

        $successfulProbe = AiProviderUsage::query()->create([
            'provider' => 'local_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 140,
            'success' => true,
            'metadata' => ['message' => 'Local probe ok.'],
        ]);
        \Illuminate\Support\Facades\DB::table('ai_provider_usages')
            ->where('id', $successfulProbe->id)
            ->update([
                'created_at' => now()->subHours(2),
            ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 480,
            'success' => false,
            'error_message' => 'OPENAI_API_KEY is not configured.',
            'metadata' => ['message' => 'OPENAI_API_KEY is not configured.'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_dashboard_live_1',
            'latency_ms' => 360,
            'success' => false,
            'error_type' => 'RuntimeException',
            'error_message' => 'Dashboard live ranking fallback.',
            'metadata' => ['message' => 'Dashboard live ranking fallback.'],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'healthy',
                'before_label' => 'Healthy',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'after_reason' => 'Active provider is not ready.',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_level' => 'degraded',
                'before_label' => 'Degraded',
                'after_level' => 'critical',
                'after_label' => 'Critical',
                'after_reason' => 'Active provider is not ready.',
                'trigger' => 'ranking_settings_updated',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('AI Severity')
            ->assertSee('AI Ranking Health')
            ->assertSee('scope: global')
            ->assertSee('0 active filters')
            ->assertSee('Provider filters probe rows. Trigger filters severity-transition audit entries.')
            ->assertSee('Filters: provider=All providers, trigger=All triggers')
            ->assertSee('Recent Severity Transitions')
            ->assertSee('All triggers 2')
            ->assertSee('Provider Tested 1')
            ->assertSee('Settings Updated 1')
            ->assertSee('Healthy')
            ->assertSee('trigger ranking_provider_tested by '.$admin->name)
            ->assertSee('Critical')
            ->assertSee('Active provider is not ready.')
            ->assertSee('Top Failure Reasons')
            ->assertSee('Recent Live Ranking Failures')
            ->assertSee('Refresh now')
            ->assertSee('Copy API URL')
            ->assertSee('Clear filters')
            ->assertSee('Open API')
            ->assertSee('Last updated on page load')
            ->assertSee('/api/admin/ai/ranking-health?limit=5')
            ->assertSee('external_ai')
            ->assertSee('enabled=yes')
            ->assertSee('overrides=3')
            ->assertSee('probes ok=1')
            ->assertSee('fail=1')
            ->assertSee('Recent probe health for the selected ranking provider.')
            ->assertSee('ok 1 / fail 1')
            ->assertSee('avg 310 ms')
            ->assertSee('min 140 ms')
            ->assertSee('max 480 ms')
            ->assertSee('480 ms')
            ->assertSee('OPENAI_API_KEY is not configured.')
            ->assertSee('count 1; providers external_ai')
            ->assertSee('sources probe')
            ->assertSee('req_dashboard_live_1')
            ->assertSee('Dashboard live ranking fallback.')
            ->assertSee('/app/admin/ai/usages?provider=external_ai&amp;capability=feed_ranking&amp;success=0&amp;request_id=req_dashboard_live_1&amp;limit=10', false)
            ->assertSee('last success')
            ->assertSee('local_ai')
            ->assertSee('(140 ms)')
            ->assertSee('healthy');

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_shows_scorm_reporting_card(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Dashboard Scorm Learner']);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $scormModule = LearningModule::query()->create([
            'title' => 'Dashboard SCORM Module',
            'description' => 'SCORM dashboard demo',
            'status' => 'published',
            'is_required' => true,
            'source_type' => 'scorm',
            'compliance_area' => 'security',
            'target_roles' => ['manager'],
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $scormModule->id,
            'status' => 'in_progress',
            'percent_complete' => 55,
            'last_activity_at' => now(),
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $scormModule->id,
            'metadata' => ['score_raw' => 84, 'session_time' => '00:06:45', 'session_seconds' => 405],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Open SCORM Reporting')
            ->assertSee('/app/admin/scorm', false)
            ->assertSee('SCORM Demo Course')
            ->assertSee('SCORM Overview');
    }

    public function test_admin_dashboard_shows_reinforcement_proof_summary(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Proof Learner', 'email' => 'proof-learner@example.com']);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Proof Retention Module',
            'description' => 'Reinforcement dashboard target',
            'status' => 'published',
            'difficulty' => 'beginner',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        ComplianceRoleRule::query()->create([
            'role' => 'manager',
            'compliance_area' => 'data-privacy',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $service = app(ReinforcementService::class);
        $service->syncForUser($learner);
        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();
        $service->completeForUser($touchpoint, $learner);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Reinforcement Proof')
            ->assertSee('Recent reinforcement proof')
            ->assertSee('Proof Learner')
            ->assertSee('Proof Retention Module')
            ->assertSee('Completed 7-day follow-up for Proof Retention Module.');
    }

    public function test_preview_learning_dashboard_shows_admin_section_link_for_admins_only(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        $this->actingAs($admin)
            ->get('/preview/learning-dashboard')
            ->assertOk()
            ->assertSee('Admin Section')
            ->assertSee('/app/admin/assignments', false);

        $this->actingAs($learner)
            ->get('/preview/learning-dashboard')
            ->assertOk()
            ->assertDontSee('Admin Section');
    }

    public function test_admin_dashboard_shows_last_ranking_export_status(): void
    {
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_settings',
            'action' => 'ranking_probe_history_exported',
            'meta' => [
                'provider' => 'external_ai',
            ],
        ]);
        \Illuminate\Support\Facades\DB::table('assignment_audit_events')
            ->where('action', 'ranking_probe_history_exported')
            ->update(['created_at' => '2026-03-09 11:45:00']);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Last export: Probe CSV at 2026-03-09 11:45')
            ->assertSee('provider external_ai')
            ->assertSee('/app/admin/assignments/audit?action=ranking_probe_history_exported');
    }

    public function test_admin_dashboard_shows_copy_bundle_id_for_last_incident_export(): void
    {
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_settings',
            'action' => 'ranking_incident_bundle_exported',
            'meta' => [
                'bundle_id' => 'ranking-incident-feedfacecafebeef',
            ],
        ]);
        \Illuminate\Support\Facades\DB::table('assignment_audit_events')
            ->where('action', 'ranking_incident_bundle_exported')
            ->update(['created_at' => '2026-03-09 12:00:00']);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('/app/admin/assignments/audit?action=ranking_incident_bundle_exported')
            ->assertSee('Copy bundle ID')
            ->assertSee('ranking-incident-feedfacecafebeef');
    }

    public function test_admin_can_view_ai_usage_records_page(): void
    {
        $admin = User::factory()->admin()->create();

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_ops_success',
            'latency_ms' => 180,
            'success' => true,
            'metadata' => ['message' => 'Ops page success.'],
        ]);
        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_ops_page',
            'latency_ms' => 355,
            'success' => false,
            'error_message' => 'Ops page failure.',
            'metadata' => ['message' => 'Ops page failure.'],
        ]);
        AiProviderUsage::query()->create([
            'provider' => 'local_ai',
            'capability' => 'mentor_answer',
            'request_id' => 'req_ops_other',
            'latency_ms' => 90,
            'success' => true,
            'metadata' => ['message' => 'Other provider success.'],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/ai/usages?limit=10')
            ->assertOk()
            ->assertSee('AI Usage Records')
            ->assertSee('Total')
            ->assertSee('Success')
            ->assertSee('Failure')
            ->assertSee('Active Filters')
            ->assertSee('none')
            ->assertSee('Top Providers')
            ->assertSee('Top Capabilities')
            ->assertSee('All providers')
            ->assertSee('All capabilities')
            ->assertSee('external_ai 2')
            ->assertSee('local_ai 1')
            ->assertSee('feed_ranking 2')
            ->assertSee('mentor_answer 1')
            ->assertSee('/app/admin/ai/usages?limit=10', false)
            ->assertSee('/app/admin/ai/usages?success=1&amp;limit=10', false)
            ->assertSee('/app/admin/ai/usages?success=0&amp;limit=10', false)
            ->assertSee('/app/admin/ai/usages?provider=external_ai&amp;limit=10', false)
            ->assertSee('/app/admin/ai/usages?capability=feed_ranking&amp;limit=10', false)
            ->assertSee('3')
            ->assertSee('2')
            ->assertSee('1')
            ->assertSee('/app/admin/ai/usages/export?limit=10', false)
            ->assertSee('req_ops_page')
            ->assertSee('req_ops_success')
            ->assertSee('req_ops_other')
            ->assertSee('Ops page failure.')
            ->assertSee('Ops page success.')
            ->assertSee('Other provider success.')
            ->assertSee('/api/admin/ai/usages?limit=10', false);
    }

    public function test_admin_ai_usage_page_shows_active_filter_summary_and_reset_links(): void
    {
        $admin = User::factory()->admin()->create();

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_filter_summary',
            'latency_ms' => 240,
            'success' => false,
            'error_message' => 'Filtered failure.',
            'metadata' => ['message' => 'Filtered failure.'],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/ai/usages?provider=external_ai&capability=feed_ranking&success=0&limit=10')
            ->assertOk()
            ->assertSee('provider=external_ai')
            ->assertSee('capability=feed_ranking')
            ->assertSee('success=failure')
            ->assertSee('/app/admin/ai/usages?limit=10', false)
            ->assertSee('/app/admin/ai/usages?provider=external_ai&amp;capability=feed_ranking&amp;success=1&amp;limit=10', false)
            ->assertSee('/app/admin/ai/usages?provider=external_ai&amp;capability=feed_ranking&amp;success=0&amp;limit=10', false)
            ->assertSee('/app/admin/ai/usages?capability=feed_ranking&amp;success=0&amp;limit=10', false)
            ->assertSee('/app/admin/ai/usages?provider=external_ai&amp;success=0&amp;limit=10', false);
    }

    public function test_admin_can_view_user_management_page(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ops Admin', 'email' => 'ops@example.com']);
        $learner = User::factory()->create(['name' => 'Test Learner', 'email' => 'learner@example.com']);

        $this->actingAs($admin)
            ->get('/app/admin/users?q=learner&role=all&account_status=all&limit=10')
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee('Test Learner')
            ->assertSee('learner@example.com')
            ->assertSee('Needs Attention')
            ->assertSee('All Attention')
            ->assertSee('name="account_status"', false)
            ->assertSee('All Roles')
            ->assertSee('Learners')
            ->assertSee('All Verification')
            ->assertSee('Verified')
            ->assertSee('All Inactivity')
            ->assertSee('Never Logged In')
            ->assertSee('Inactive 30+ Days')
            ->assertSee('name="attention_status"', false)
            ->assertSee('Active')
            ->assertSee('Unverified')
            ->assertSee('Select visible')
            ->assertSee('Clear selection')
            ->assertSee('0 selected')
            ->assertSee('Visible rows only')
            ->assertSee('Select an action and at least one visible user.')
            ->assertSee('Select users to continue')
            ->assertSee('Queue presets')
            ->assertSee('Verification Follow-up')
            ->assertSee('Mark Verified Queue')
            ->assertSee('Suspend Inactive 30+')
            ->assertSee('Restore Suspended')
            ->assertDontSee('Preset applied:')
            ->assertSee('bulk_action=resend_verification', false)
            ->assertSee('Copy selected IDs')
            ->assertSee('Selected user IDs')
            ->assertSee('None selected.')
            ->assertSee('data-user-bulk-submit', false)
            ->assertSee('name="inactivity_status"', false)
            ->assertSee('name="sort"', false)
            ->assertSee('name="sort_dir"', false)
            ->assertSee('/app/admin/users?', false)
            ->assertSee('role=admin', false)
            ->assertSee('account_status=active', false)
            ->assertSee('attention_status=needs_attention', false)
            ->assertSee('/app/admin/users/'.$learner->id.'/edit', false)
            ->assertSee('/app/admin/assignments/users/'.$learner->id, false)
            ->assertSee('/app/admin/assignments/audit?target='.$learner->id, false);
    }

    public function test_admin_user_management_shows_applied_bulk_preset(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'email' => 'preset-unverified@example.com',
            'email_verified_at' => null,
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?attention_status=needs_attention&verification_status=unverified&bulk_action=resend_verification&limit=10')
            ->assertOk()
            ->assertSee('Preset applied: Verification Follow-up')
            ->assertSee('0 selected in preset')
            ->assertSee('Targets unverified users already in Needs Attention for verification reminders.')
            ->assertSee('Select visible in Verification Follow-up')
            ->assertSee('Select none in Verification Follow-up')
            ->assertSee('/app/admin/assignments/audit?action=user_verification_link_sent', false)
            ->assertSee('Open Audit')
            ->assertSee('Clear preset')
            ->assertSee('btn-primary', false)
            ->assertSee('bulk_action=resend_verification', false);
    }

    public function test_admin_user_management_shows_suspend_preset_confirmation_state(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'email' => 'preset-inactive-suspend@example.com',
            'email_verified_at' => null,
            'last_login_at' => now()->subDays(45),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?attention_status=needs_attention&inactivity_status=inactive_30&bulk_action=suspend&limit=10')
            ->assertOk()
            ->assertSee('Preset applied: Suspend Inactive 30+')
            ->assertSee('Select visible in Suspend Inactive 30+')
            ->assertSee('Bulk suspension requires confirmation before submit.')
            ->assertSee('data-user-bulk-active-preset="Suspend Inactive 30+"', false);
    }

    public function test_admin_user_management_shows_restore_preset_confirmation_state(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'email' => 'preset-restore-suspended@example.com',
            'suspended_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?account_status=suspended&bulk_action=restore&limit=10')
            ->assertOk()
            ->assertSee('Preset applied: Restore Suspended')
            ->assertSee('Select visible in Restore Suspended')
            ->assertSee('Bulk restore requires confirmation before submit.')
            ->assertSee('data-user-bulk-active-preset="Restore Suspended"', false);
    }

    public function test_admin_user_management_shows_bulk_preset_counts(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'email' => 'preset-count-unverified-a@example.com',
            'email_verified_at' => null,
            'last_login_at' => now()->subDays(2),
        ]);
        User::factory()->create([
            'email' => 'preset-count-unverified-b@example.com',
            'email_verified_at' => null,
            'last_login_at' => now()->subDays(3),
        ]);
        User::factory()->create([
            'email' => 'preset-count-suspended@example.com',
            'suspended_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?limit=10')
            ->assertOk()
            ->assertSee('Verification Follow-up (')
            ->assertSee('/app/admin/assignments/audit?action=user_verification_link_sent', false)
            ->assertSee('Mark Verified Queue (')
            ->assertSee('/app/admin/assignments/audit?action=user_verification_marked', false)
            ->assertSee('Suspend Inactive 30+ (')
            ->assertSee('/app/admin/assignments/audit?action=user_suspended', false)
            ->assertSee('Restore Suspended (')
            ->assertSee('/app/admin/assignments/audit?action=user_restored', false);
    }

    public function test_admin_user_management_renders_preset_aware_status_badge(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->withSession([
                'status' => 'Verification Follow-up: Verification email resend completed for 2 user(s). 1 user(s) skipped.',
                'status_context' => 'bulk_user_action',
            ])
            ->get('/app/admin/users?verification_status=unverified&attention_status=needs_attention&limit=10')
            ->assertOk()
            ->assertSee('Verification Follow-up')
            ->assertSee('Verification email resend completed for 2 user(s). 1 user(s) skipped.');
    }

    public function test_admin_user_management_renders_suspend_status_with_mutation_styling(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->withSession([
                'status' => 'Suspend Inactive 30+: Account suspension completed for 1 user(s). 1 user(s) skipped.',
                'status_context' => 'bulk_user_action',
            ])
            ->get('/app/admin/users?attention_status=needs_attention&inactivity_status=inactive_30&limit=10')
            ->assertOk()
            ->assertSee('Suspend Inactive 30+')
            ->assertSee('Account suspension completed for 1 user(s). 1 user(s) skipped.')
            ->assertSee('border-amber-200 bg-amber-50 text-amber-900', false)
            ->assertSee('border-amber-200 bg-white text-amber-800', false);
    }

    public function test_admin_can_sort_user_management_by_name(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'name' => 'Zulu User',
            'email' => 'zulu-user@example.com',
        ]);
        User::factory()->create([
            'name' => 'Alpha User',
            'email' => 'alpha-user@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/users?sort=name&sort_dir=asc&limit=10');

        $response->assertOk()
            ->assertSee('/app/admin/users?sort=name&amp;sort_dir=desc&amp;limit=10', false)
            ->assertSee('/app/admin/users/export?sort=name&amp;sort_dir=asc&amp;limit=10', false);

        $content = $response->getContent();

        $this->assertLessThan(
            strpos($content, 'Zulu User'),
            strpos($content, 'Alpha User')
        );
    }

    public function test_admin_can_filter_user_management_by_role_chip(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create([
            'name' => 'Admin Scope User',
            'email' => 'admin-scope@example.com',
        ]);
        User::factory()->create([
            'name' => 'Learner Scope User',
            'email' => 'learner-scope@example.com',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?role=admin&account_status=all&limit=10')
            ->assertOk()
            ->assertSee('Admin Scope User')
            ->assertDontSee('learner-scope@example.com')
            ->assertSee('role=learner', false)
            ->assertSee('role=admin', false)
            ->assertSee('/app/admin/users/export?', false)
            ->assertSee('role=admin', false);
    }

    public function test_admin_can_filter_user_management_by_account_status(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'name' => 'Active Learner',
            'email' => 'active-learner@example.com',
            'suspended_at' => null,
        ]);
        User::factory()->create([
            'name' => 'Suspended Learner',
            'email' => 'suspended-learner@example.com',
            'suspended_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?account_status=suspended&limit=10')
            ->assertOk()
            ->assertSee('Suspended Learner')
            ->assertSee('suspended-learner@example.com')
            ->assertDontSee('active-learner@example.com')
            ->assertSee('account_status=active', false)
            ->assertSee('account_status=suspended', false)
            ->assertSee('/app/admin/users/export?', false)
            ->assertSee('account_status=suspended', false);
    }

    public function test_admin_can_filter_user_management_by_verification_status(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'name' => 'Verified Learner',
            'email' => 'verified-learner@example.com',
            'email_verified_at' => now(),
        ]);
        User::factory()->create([
            'name' => 'Unverified Learner',
            'email' => 'unverified-learner@example.com',
            'email_verified_at' => null,
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?verification_status=verified&limit=10')
            ->assertOk()
            ->assertSee('Verified Learner')
            ->assertSee('verified-learner@example.com')
            ->assertDontSee('unverified-learner@example.com')
            ->assertSee('verification_status=unverified', false)
            ->assertSee('verification_status=verified', false)
            ->assertSee('/app/admin/users/export?', false)
            ->assertSee('verification_status=verified', false);
    }

    public function test_admin_can_filter_user_management_by_inactivity_status(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'name' => 'Never Logged User',
            'email' => 'never-logged@example.com',
            'last_login_at' => null,
        ]);
        User::factory()->create([
            'name' => 'Inactive User',
            'email' => 'inactive-user@example.com',
            'last_login_at' => now()->subDays(45),
        ]);
        User::factory()->create([
            'name' => 'Recent User',
            'email' => 'recent-user@example.com',
            'last_login_at' => now()->subDays(5),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?inactivity_status=never&limit=10')
            ->assertOk()
            ->assertSee('Never Logged User')
            ->assertDontSee('inactive-user@example.com')
            ->assertDontSee('recent-user@example.com')
            ->assertSee('inactivity_status=never', false)
            ->assertSee('/app/admin/users/export?', false)
            ->assertSee('inactivity_status=never', false);

        $this->actingAs($admin)
            ->get('/app/admin/users?inactivity_status=inactive_30&limit=10')
            ->assertOk()
            ->assertSee('Inactive User')
            ->assertDontSee('never-logged@example.com')
            ->assertDontSee('recent-user@example.com')
            ->assertSee('inactivity_status=inactive_30', false)
            ->assertSee('/app/admin/users/export?', false)
            ->assertSee('inactivity_status=inactive_30', false);
    }

    public function test_admin_can_filter_user_management_by_attention_status(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'name' => 'Needs Attention Suspended',
            'email' => 'needs-attention-suspended@example.com',
            'suspended_at' => now(),
            'email_verified_at' => now(),
            'last_login_at' => now()->subDays(2),
        ]);
        User::factory()->create([
            'name' => 'Needs Attention Unverified',
            'email' => 'needs-attention-unverified@example.com',
            'email_verified_at' => null,
            'last_login_at' => now()->subDays(2),
        ]);
        User::factory()->create([
            'name' => 'Healthy Recent User',
            'email' => 'healthy-recent@example.com',
            'email_verified_at' => now(),
            'last_login_at' => now()->subDays(2),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users?attention_status=needs_attention&limit=10')
            ->assertOk()
            ->assertSee('Needs Attention Suspended')
            ->assertSee('Needs Attention Unverified')
            ->assertDontSee('healthy-recent@example.com')
            ->assertSee('attention_status=needs_attention', false)
            ->assertSee('/app/admin/users/export?', false)
            ->assertSee('attention_status=needs_attention', false);
    }

    public function test_admin_can_bulk_resend_verification_from_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $unverifiedA = User::factory()->create([
            'email' => 'bulk-unverified-a@example.com',
            'email_verified_at' => null,
        ]);
        $unverifiedB = User::factory()->create([
            'email' => 'bulk-unverified-b@example.com',
            'email_verified_at' => null,
        ]);
        $verified = User::factory()->create([
            'email' => 'bulk-verified@example.com',
            'email_verified_at' => now(),
        ]);

        Notification::fake();

        $this->actingAs($admin)
            ->post('/app/admin/users/bulk', [
                'action' => 'resend_verification',
                'user_ids' => [$unverifiedA->id, $unverifiedB->id, $verified->id],
                'attention_status' => 'needs_attention',
                'verification_status' => 'unverified',
                'limit' => 10,
            ])
            ->assertRedirect('/app/admin/users?verification_status=unverified&attention_status=needs_attention&limit=10')
            ->assertSessionHas('status', 'Verification Follow-up: Verification email resend completed for 2 user(s). 1 user(s) skipped.');

        Notification::assertSentTo($unverifiedA, VerifyEmail::class);
        Notification::assertSentTo($unverifiedB, VerifyEmail::class);
        Notification::assertNotSentTo($verified, VerifyEmail::class);

        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_verification_link_sent',
            'target_user_id' => $unverifiedA->id,
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_verification_link_sent',
            'target_user_id' => $unverifiedB->id,
        ]);
    }

    public function test_admin_can_bulk_suspend_from_user_management_without_self_lockout(): void
    {
        $admin = User::factory()->admin()->create([
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);
        $target = User::factory()->create([
            'email' => 'bulk-suspend-target@example.com',
            'suspended_at' => null,
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/users/bulk', [
                'action' => 'suspend',
                'user_ids' => [$target->id, $admin->id],
                'attention_status' => 'needs_attention',
                'limit' => 10,
            ])
            ->assertRedirect('/app/admin/users?attention_status=needs_attention&limit=10');

        $target->refresh();
        $admin->refresh();

        $this->assertNotNull($target->suspended_at);
        $this->assertNull($admin->suspended_at);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_suspended',
            'target_user_id' => $target->id,
            'entity_type' => 'user',
            'entity_id' => $target->id,
        ]);
    }

    public function test_admin_can_bulk_send_password_reset_links_from_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $first = User::factory()->create([
            'email' => 'bulk-reset-first@example.com',
        ]);
        $second = User::factory()->create([
            'email' => 'bulk-reset-second@example.com',
        ]);

        Notification::fake();

        $this->actingAs($admin)
            ->post('/app/admin/users/bulk', [
                'action' => 'send_password_reset_link',
                'user_ids' => [$first->id, $second->id],
                'attention_status' => 'needs_attention',
                'limit' => 10,
            ])
            ->assertRedirect('/app/admin/users?attention_status=needs_attention&limit=10');

        Notification::assertSentTo($first, ResetPassword::class);
        Notification::assertSentTo($second, ResetPassword::class);

        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_password_reset_link_sent',
            'target_user_id' => $first->id,
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_password_reset_link_sent',
            'target_user_id' => $second->id,
        ]);
    }

    public function test_admin_can_bulk_mark_verified_from_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $first = User::factory()->create([
            'email' => 'bulk-mark-verified-first@example.com',
            'email_verified_at' => null,
        ]);
        $second = User::factory()->create([
            'email' => 'bulk-mark-verified-second@example.com',
            'email_verified_at' => null,
        ]);
        $alreadyVerified = User::factory()->create([
            'email' => 'bulk-mark-verified-existing@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/users/bulk', [
                'action' => 'mark_verified',
                'user_ids' => [$first->id, $second->id, $alreadyVerified->id],
                'verification_status' => 'unverified',
                'limit' => 10,
            ])
            ->assertRedirect('/app/admin/users?verification_status=unverified&limit=10');

        $first->refresh();
        $second->refresh();
        $alreadyVerified->refresh();

        $this->assertNotNull($first->email_verified_at);
        $this->assertNotNull($second->email_verified_at);
        $this->assertNotNull($alreadyVerified->email_verified_at);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_verification_marked',
            'target_user_id' => $first->id,
            'entity_type' => 'user',
            'entity_id' => $first->id,
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_verification_marked',
            'target_user_id' => $second->id,
            'entity_type' => 'user',
            'entity_id' => $second->id,
        ]);
    }

    public function test_admin_can_bulk_restore_from_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $suspended = User::factory()->create([
            'email' => 'bulk-restore-suspended@example.com',
            'suspended_at' => now()->subDay(),
        ]);
        $active = User::factory()->create([
            'email' => 'bulk-restore-active@example.com',
            'suspended_at' => null,
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/users/bulk', [
                'action' => 'restore',
                'user_ids' => [$suspended->id, $active->id],
                'account_status' => 'suspended',
                'limit' => 10,
            ])
            ->assertRedirect('/app/admin/users?account_status=suspended&limit=10')
            ->assertSessionHas('status', 'Restore Suspended: Account restore completed for 1 user(s). 1 user(s) skipped.');

        $suspended->refresh();
        $active->refresh();

        $this->assertNull($suspended->suspended_at);
        $this->assertNull($active->suspended_at);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_restored',
            'target_user_id' => $suspended->id,
            'entity_type' => 'user',
            'entity_id' => $suspended->id,
        ]);
    }

    public function test_admin_can_open_create_user_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/users/create')
            ->assertOk()
            ->assertSee('Create User')
            ->assertSee('Create a learner or admin account directly from the admin area.')
            ->assertSee('Create User');
    }

    public function test_admin_can_open_user_edit_page_with_audit_shortcut(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get('/app/admin/users/'.$user->id.'/edit')
            ->assertOk()
            ->assertSee('/app/admin/users/'.$user->id, false)
            ->assertSee('/app/admin/assignments/audit?target='.$user->id, false);
    }

    public function test_admin_can_view_user_detail_page(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ops Admin', 'email' => 'ops@example.com']);
        $user = User::factory()->create([
            'name' => 'Detail Learner',
            'email' => 'detail-learner@example.com',
            'email_verified_at' => now(),
            'last_login_at' => now()->subDay(),
        ]);
        $module = LearningModule::query()->create([
            'title' => 'Detail Required Module',
            'slug' => 'detail-required-module',
            'description' => 'Required module for detail view.',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'detail-area',
            'target_roles' => ['detail-role'],
            'refresh_interval_days' => 30,
        ]);
        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['detail'],
            'role' => 'detail-role',
            'difficulty' => 'any',
        ]);
        ModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 25,
            'started_at' => now()->subDays(40),
            'last_activity_at' => now()->subDays(10),
        ]);
        AssignmentReminder::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);
        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'detail-test'],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'action' => 'user_updated',
            'meta' => ['changed_keys' => ['email']],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/users/'.$user->id)
            ->assertOk()
            ->assertSee('User Detail')
            ->assertSee('Detail Learner')
            ->assertSee('detail-learner@example.com')
            ->assertSee('Verified')
            ->assertSee('Last Login')
            ->assertSee('Quick Actions')
            ->assertSee('Grant Admin Access')
            ->assertSee('Suspend Account')
            ->assertSee('Clear Verification')
            ->assertSee('Send Password Reset Link')
            ->assertSee('Assignments')
            ->assertSee('Overdue')
            ->assertSee('Waived')
            ->assertSee('Pending Reminders')
            ->assertSee('1')
            ->assertSee('Audit Shortcuts')
            ->assertSee('User Updates')
            ->assertSee('Password Resets')
            ->assertSee('Reset Links')
            ->assertSee('Suspensions')
            ->assertSee('Restores')
            ->assertSee('Verification Marks')
            ->assertSee('Verification Clears')
            ->assertSee('Recent Learning Events')
            ->assertSee('module_viewed')
            ->assertSee('Detail Required Module')
            ->assertSee('Recent Reminder Activity')
            ->assertSee('Overdue')
            ->assertSee('Pending')
            ->assertSee('Recent User Audit Activity')
            ->assertSee('User Updated')
            ->assertSee('Changed: email')
            ->assertSee($user->last_login_at->format('Y-m-d H:i'))
            ->assertSee('/app/admin/users/'.$user->id.'/edit', false)
            ->assertSee('/app/admin/users/'.$user->id.'/export', false)
            ->assertSee('/app/admin/assignments/users/'.$user->id, false)
            ->assertSee('/app/admin/assignments/audit?target='.$user->id, false)
            ->assertSee('/app/admin/assignments/users/'.$user->id.'/events', false);
    }

    public function test_admin_can_export_user_detail_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Export Learner',
            'email' => 'export-learner@example.com',
            'email_verified_at' => now(),
        ]);
        $module = LearningModule::query()->create([
            'title' => 'Export Module',
            'slug' => 'export-module',
            'description' => 'Export module description.',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['export-role'],
        ]);
        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['export'],
            'role' => 'export-role',
            'difficulty' => 'any',
        ]);
        AssignmentReminder::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);
        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'export-test'],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'action' => 'user_updated',
            'meta' => ['changed_keys' => ['email']],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/users/'.$user->id.'/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('section,label,value,context', $content);
        $this->assertStringContainsString('account,name,"Export Learner"', $content);
        $this->assertStringContainsString('assignment_summary,required_total,1,', $content);
        $this->assertStringContainsString('learning_event,module_viewed,', $content);
        $this->assertStringContainsString('Export Module', $content);
        $this->assertStringContainsString('reminder,overdue,pending,', $content);
        $this->assertStringContainsString('audit_event,user_updated,', $content);
    }

    public function test_admin_can_sort_user_management_by_last_login(): void
    {
        $admin = User::factory()->admin()->create();
        $older = User::factory()->create([
            'name' => 'Older Login',
            'email' => 'older-login@example.com',
            'last_login_at' => now()->subDays(3),
        ]);
        $newer = User::factory()->create([
            'name' => 'Newer Login',
            'email' => 'newer-login@example.com',
            'last_login_at' => now()->subDay(),
        ]);
        $never = User::factory()->create([
            'name' => 'Never Logged In',
            'email' => 'never-login@example.com',
            'last_login_at' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/users?sort=last_login_at&sort_dir=desc');

        $response->assertOk()
            ->assertSee('Last Login')
            ->assertSee('Never');

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertLessThan(
            strpos($content, 'Older Login'),
            strpos($content, 'Newer Login')
        );
        $this->assertTrue(
            strpos($content, 'Never Logged In') < strpos($content, 'Older Login')
        );
    }

    public function test_admin_can_use_user_detail_quick_actions(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'is_admin' => false,
            'suspended_at' => null,
            'email_verified_at' => null,
        ]);
        Notification::fake();

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id.'/admin-access')
            ->assertRedirect('/app/admin/users/'.$user->id);

        $user->refresh();
        $this->assertTrue($user->is_admin);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_updated',
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id.'/account-access')
            ->assertRedirect('/app/admin/users/'.$user->id);

        $user->refresh();
        $this->assertNotNull($user->suspended_at);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_suspended',
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/users/'.$user->id.'/email-verification-link')
            ->assertRedirect('/app/admin/users/'.$user->id);

        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_verification_link_sent',
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id.'/email-verification')
            ->assertRedirect('/app/admin/users/'.$user->id);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_verification_marked',
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/users/'.$user->id.'/password-reset-link')
            ->assertRedirect('/app/admin/users/'.$user->id);

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_password_reset_link_sent',
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);
    }

    public function test_admin_cannot_lock_self_out_from_user_detail_quick_actions(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Self Protect',
            'email' => 'self-protect@example.com',
        ]);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$admin->id.'/admin-access')
            ->assertRedirect('/app/admin/users/'.$admin->id);

        $admin->refresh();
        $this->assertTrue($admin->is_admin);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$admin->id.'/account-access')
            ->assertRedirect('/app/admin/users/'.$admin->id);

        $admin->refresh();
        $this->assertNull($admin->suspended_at);
    }

    public function test_admin_can_create_user_from_user_management(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->post('/app/admin/users', [
                'name' => 'Created User',
                'email' => 'created-user@example.com',
                'role' => 'classroom_teacher',
                'team' => 'teaching_staff',
                'system_role' => 'site_admin',
                'password' => 'created-user-password',
                'password_confirmation' => 'created-user-password',
            ]);

        $user = User::query()->where('email', 'created-user@example.com')->first();

        $this->assertNotNull($user);
        $response->assertRedirect('/app/admin/users/'.$user->id.'/edit');
        $this->assertSame('Created User', $user->name);
        $this->assertTrue($user->is_admin);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('created-user-password', $user->password));
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'role' => 'Classroom Teacher',
            'team' => 'Teaching Staff',
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_created',
            'actor_user_id' => $admin->id,
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);
    }

    public function test_admin_can_export_users_csv(): void
    {
        $admin = User::factory()->admin()->create();

        User::factory()->create([
            'name' => 'Export Learner',
            'email' => 'export-learner@example.com',
            'is_admin' => false,
            'last_login_at' => null,
        ]);
        User::factory()->admin()->create([
            'name' => 'Export Admin',
            'email' => 'export-admin@example.com',
            'last_login_at' => now()->subDays(2),
        ]);
        User::factory()->create([
            'name' => 'Suspended Export Learner',
            'email' => 'suspended-export-learner@example.com',
            'suspended_at' => now(),
            'last_login_at' => now()->subDays(90),
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/users/export?role=admin&account_status=active&sort=name&sort_dir=asc&limit=10');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('id,name,email,system_role,location,team,staff_role,account_status,suspended_at,email_verified_at,last_login_at,created_at', $content);
        $this->assertStringContainsString('"Export Admin",export-admin@example.com,"Site Administrator"', $content);
        $this->assertStringNotContainsString('export-learner@example.com', $content);
        $this->assertStringNotContainsString('suspended-export-learner@example.com', $content);
    }

    public function test_admin_can_update_user_email_password_and_role(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target@example.com',
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'role' => 'business_manager',
                'team' => 'administration_office_staff',
                'system_role' => 'site_admin',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect('/app/admin/users/'.$user->id.'/edit');

        $user->refresh();

        $this->assertSame('Updated User', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertTrue($user->is_admin);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'role' => 'Business Manager',
            'team' => 'Administration & Office Staff',
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_updated',
            'actor_user_id' => $admin->id,
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_password_reset',
            'actor_user_id' => $admin->id,
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);
    }

    public function test_admin_can_import_users_from_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $existingUser = User::factory()->create([
            'name' => 'Existing Teacher',
            'email' => 'existing-teacher@example.com',
            'is_admin' => false,
        ]);

        $csv = implode("\n", [
            'name,email,role,team,is_admin,account_active,password',
            'Imported Teacher,imported-teacher@example.com,Classroom Teacher,Teaching Staff,0,1,imported-password',
            'Existing Teacher Updated,existing-teacher@example.com,business_manager,administration_office_staff,1,0,updated-password',
        ]);

        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $this->actingAs($admin)
            ->post('/app/admin/users/import', [
                'users_csv' => $file,
            ])
            ->assertRedirect('/app/admin/users');

        $importedUser = User::query()->where('email', 'imported-teacher@example.com')->first();
        $this->assertNotNull($importedUser);
        $this->assertFalse($importedUser->is_admin);
        $this->assertTrue(Hash::check('imported-password', $importedUser->password));
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $importedUser->id,
            'role' => 'Classroom Teacher',
            'team' => 'Teaching Staff',
        ]);

        $existingUser->refresh();
        $this->assertSame('Existing Teacher Updated', $existingUser->name);
        $this->assertTrue($existingUser->is_admin);
        $this->assertNotNull($existingUser->suspended_at);
        $this->assertTrue(Hash::check('updated-password', $existingUser->password));
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $existingUser->id,
            'role' => 'Business Manager',
            'team' => 'Administration & Office Staff',
        ]);
    }

    public function test_admin_can_suspend_and_restore_user_from_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Suspended User',
            'email' => 'suspend-me@example.com',
        ]);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => 'Suspended User',
                'email' => 'suspend-me@example.com',
                'account_active' => '0',
            ])
            ->assertRedirect('/app/admin/users/'.$user->id.'/edit');

        $user->refresh();
        $this->assertNotNull($user->suspended_at);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$user->id, [
                'name' => 'Suspended User',
                'email' => 'suspend-me@example.com',
                'account_active' => '1',
            ])
            ->assertRedirect('/app/admin/users/'.$user->id.'/edit');

        $user->refresh();
        $this->assertNull($user->suspended_at);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_suspended',
            'actor_user_id' => $admin->id,
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'user_restored',
            'actor_user_id' => $admin->id,
            'target_user_id' => $user->id,
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);
    }

    public function test_admin_cannot_remove_own_admin_access_from_user_management(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Self Admin',
            'email' => 'self-admin@example.com',
        ]);

        $this->actingAs($admin)
            ->patch('/app/admin/users/'.$admin->id, [
                'name' => 'Self Admin Updated',
                'email' => 'self-admin-updated@example.com',
                'system_role' => 'learner',
            ])
            ->assertRedirect('/app/admin/users/'.$admin->id.'/edit');

        $admin->refresh();

        $this->assertSame('Self Admin Updated', $admin->name);
        $this->assertSame('self-admin-updated@example.com', $admin->email);
        $this->assertTrue($admin->is_admin);
        $this->assertSame('site_admin', $admin->system_role);

        $this->actingAs($admin)
            ->get('/app/admin/users/'.$admin->id.'/edit')
            ->assertOk()
            ->assertSee('Your admin access was preserved.');
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $user = User::factory()->create();
        $managedUser = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/users')
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/app/admin/users/'.$managedUser->id)
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/app/admin/users/'.$managedUser->id.'/edit')
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/app/admin/users/'.$managedUser->id.'/export')
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/app/admin/users/create')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/app/admin/users', [
                'name' => 'Blocked Create',
                'email' => 'blocked-create@example.com',
                'password' => 'blocked-password',
                'password_confirmation' => 'blocked-password',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/app/admin/users/export')
            ->assertForbidden();

        $this->actingAs($user)
            ->patch('/app/admin/users/'.$managedUser->id, [
                'name' => 'Blocked Update',
                'email' => 'blocked@example.com',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->patch('/app/admin/users/'.$managedUser->id.'/admin-access')
            ->assertForbidden();

        $this->actingAs($user)
            ->patch('/app/admin/users/'.$managedUser->id.'/account-access')
            ->assertForbidden();

        $this->actingAs($user)
            ->patch('/app/admin/users/'.$managedUser->id.'/email-verification')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/app/admin/users/'.$managedUser->id.'/password-reset-link')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/app/admin/users/'.$managedUser->id.'/email-verification-link')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/app/admin/users/bulk', [
                'action' => 'suspend',
                'user_ids' => [$managedUser->id],
            ])
            ->assertForbidden();
    }

    public function test_assignment_audit_shows_user_management_actions(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);
        $target = User::factory()->create(['name' => 'Managed Learner', 'email' => 'managed-learner@example.com']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'entity_type' => 'user',
            'entity_id' => $target->id,
            'action' => 'user_created',
            'meta' => ['changed_keys' => ['name', 'email', 'is_admin', 'password']],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'entity_type' => 'user',
            'entity_id' => $target->id,
            'action' => 'user_password_reset',
            'meta' => ['reason' => 'Password reset via user management.'],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'entity_type' => 'user',
            'entity_id' => $target->id,
            'action' => 'user_password_reset_link_sent',
            'meta' => ['reason' => 'Password reset link sent via user detail quick action.'],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'entity_type' => 'user',
            'entity_id' => $target->id,
            'action' => 'user_suspended',
            'meta' => ['reason' => 'Account suspended via user management.'],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'entity_type' => 'user',
            'entity_id' => $target->id,
            'action' => 'user_verification_link_sent',
            'meta' => ['reason' => 'Email verification link sent via user detail quick action.'],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'entity_type' => 'user',
            'entity_id' => $target->id,
            'action' => 'user_verification_marked',
            'meta' => ['reason' => 'Email marked verified via user detail quick action.'],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=user_verification_marked')
            ->assertOk()
            ->assertSee('User Created')
            ->assertSee('User Password Reset')
            ->assertSee('User Password Reset Link Sent')
            ->assertSee('User Suspended')
            ->assertSee('User Verification Link Sent')
            ->assertSee('User Verification Marked')
            ->assertSee('Email marked verified via user detail quick action.')
            ->assertSee('managed-learner@example.com');
    }

    public function test_non_admin_cannot_view_ai_usage_records_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/ai/usages')
            ->assertForbidden();
    }

    public function test_admin_can_export_ai_usage_records_csv(): void
    {
        $admin = User::factory()->admin()->create();

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_export_ops',
            'latency_ms' => 366,
            'success' => false,
            'error_type' => 'RuntimeException',
            'error_message' => 'Exported ops failure.',
            'metadata' => ['message' => 'Exported ops failure.'],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/ai/usages/export?provider=external_ai&capability=feed_ranking&success=0&request_id=req_export_ops&limit=10');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('when,provider,capability,status,latency_ms,request_id,model,error_type,message', $content);
        $this->assertStringContainsString('external_ai,feed_ranking,failure,366,req_export_ops,,RuntimeException,"Exported ops failure."', $content);
    }

    public function test_non_admin_cannot_export_ai_usage_records_csv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/ai/usages/export')
            ->assertForbidden();
    }

    public function test_admin_dashboard_can_filter_probe_history_by_provider(): void
    {
        $admin = User::factory()->admin()->create();

        RankingSetting::query()->create(['key' => 'provider', 'value' => 'local_ai']);

        AiProviderUsage::query()->create([
            'provider' => 'local_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 140,
            'success' => true,
            'metadata' => ['message' => 'Local probe ok.'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 480,
            'success' => false,
            'error_message' => 'OPENAI_API_KEY is not configured.',
            'metadata' => ['message' => 'OPENAI_API_KEY is not configured.'],
        ]);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking',
            'request_id' => 'req_dashboard_live_filtered',
            'latency_ms' => 355,
            'success' => false,
            'error_message' => 'Dashboard filtered live failure.',
            'metadata' => ['message' => 'Dashboard filtered live failure.'],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments?ranking_provider=external_ai')
            ->assertOk()
            ->assertSee('scope: filtered')
            ->assertSee('1 active filter')
            ->assertSee('Viewing External AI')
            ->assertSee('while the active ranking provider is Local AI')
            ->assertSee('ok 0 / fail 1')
            ->assertSee('avg 480 ms')
            ->assertSee('external_ai')
            ->assertSee('Recent Live Ranking Failures')
            ->assertSee('req_dashboard_live_filtered')
            ->assertSee('Dashboard filtered live failure.')
            ->assertSee('/app/admin/ai/usages?provider=external_ai&amp;capability=feed_ranking&amp;success=0&amp;request_id=req_dashboard_live_filtered&amp;limit=10', false)
            ->assertDontSee('Local probe ok.');
    }

    public function test_admin_dashboard_can_filter_severity_transitions_by_trigger(): void
    {
        $admin = User::factory()->admin()->create();

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_label' => 'Healthy',
                'after_label' => 'Degraded',
                'trigger' => 'ranking_settings_reset',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_label' => 'Degraded',
                'after_label' => 'Critical',
                'trigger' => 'ranking_provider_tested',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments?ranking_severity_trigger=ranking_settings_reset')
            ->assertOk()
            ->assertSee('scope: filtered')
            ->assertSee('1 active filter')
            ->assertSee('Showing Settings Reset.')
            ->assertSee('Filters: provider=All providers, trigger=Settings Reset')
            ->assertSee('/app/admin/assignments/audit?action=ranking_severity_changed&amp;q=ranking_settings_reset', false)
            ->assertSee('trigger ranking_settings_reset by '.$admin->name)
            ->assertDontSee('trigger ranking_provider_tested by '.$admin->name);
    }

    public function test_admin_dashboard_shows_filtered_empty_messages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/assignments?ranking_provider=external_ai')
            ->assertOk()
            ->assertSee('No probe history matches provider External AI.');

        $this->actingAs($admin)
            ->get('/app/admin/assignments?ranking_severity_trigger=ranking_settings_reset')
            ->assertOk()
            ->assertSee('No severity transitions match trigger Settings Reset.');
    }

    public function test_admin_dashboard_shows_two_active_filters_when_provider_and_trigger_are_combined(): void
    {
        $admin = User::factory()->admin()->create();

        RankingSetting::query()->create(['key' => 'provider', 'value' => 'local_ai']);

        AiProviderUsage::query()->create([
            'provider' => 'external_ai',
            'capability' => 'feed_ranking_probe',
            'latency_ms' => 480,
            'success' => false,
            'error_message' => 'OPENAI_API_KEY is not configured.',
            'metadata' => ['message' => 'OPENAI_API_KEY is not configured.'],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'ranking_health',
            'action' => 'ranking_severity_changed',
            'meta' => [
                'before_label' => 'Healthy',
                'after_label' => 'Degraded',
                'trigger' => 'ranking_settings_reset',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments?ranking_provider=external_ai&ranking_severity_trigger=ranking_settings_reset')
            ->assertOk()
            ->assertSee('scope: filtered')
            ->assertSee('2 active filters')
            ->assertSee('Filters: provider=External AI, trigger=Settings Reset')
            ->assertSee('Viewing External AI.')
            ->assertSee('Showing Settings Reset.');
    }

    public function test_admin_can_view_assignment_audit_page_with_action_filter(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Temporary exception',
                'module_title' => 'Privacy Core',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=rule_saved')
            ->assertOk()
            ->assertSee('Assignment Audit')
            ->assertSee('rule saved')
            ->assertSee('manager')
            ->assertSee('data-privacy')
            ->assertDontSee('Temporary exception');
    }

    public function test_admin_audit_pages_fail_gracefully_when_audit_table_is_missing(): void
    {
        $admin = User::factory()->admin()->create();

        Schema::dropIfExists('assignment_audit_events');

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit')
            ->assertOk()
            ->assertSee('Assignment Audit')
            ->assertSee('No audit events match the current filter.');

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Audit Events')
            ->assertSee('0');
    }

    public function test_admin_can_export_assignment_audit_as_csv(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/audit/export?action=rule_saved');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'when,actor,action,target_user,module,details',
            $response->streamedContent(),
        );
        $this->assertStringContainsString(
            '"Audit Admin",rule_saved',
            $response->streamedContent(),
        );
        $this->assertStringContainsString(
            '"manager / data-privacy"',
            $response->streamedContent(),
        );
    }

    public function test_assignment_audit_export_includes_reminder_batch_details(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => null,
            'entity_type' => 'assignment_reminder_batch',
            'action' => 'reminder_batch_run',
            'meta' => [
                'synced_total' => 3,
                'sent_total' => 2,
                'remaining_pending' => 1,
                'mode' => 'send_only',
                'types' => ['due_soon'],
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/audit/export?action=reminder_batch_run');

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('reminder_batch_run', $content);
        $this->assertStringContainsString('synced 3, sent 2, remaining total 1', $content);
        $this->assertStringContainsString('mode send_only', $content);
        $this->assertStringContainsString('types due_soon', $content);
    }

    public function test_admin_can_search_assignment_audit_page(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);
        $learner = User::factory()->create(['name' => 'Target Learner', 'email' => 'learner@example.com']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $learner->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Medical leave',
                'module_title' => 'Privacy Core',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?q=medical')
            ->assertOk()
            ->assertSee('Medical leave')
            ->assertDontSee('data-privacy');
    }

    public function test_admin_can_filter_assignment_audit_by_reminder_batch_action(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => null,
            'entity_type' => 'assignment_reminder_batch',
            'action' => 'reminder_batch_run',
            'meta' => [
                'synced_total' => 2,
                'sent_total' => 1,
                'remaining_pending' => 1,
                'mode' => 'sync_and_send',
                'types' => ['overdue', 'due_soon'],
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Temporary exception',
                'module_title' => 'Privacy Core',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=reminder_batch_run')
            ->assertOk()
            ->assertSee('Reminder Batches')
            ->assertSee('reminder batch run')
            ->assertSee('synced 2, sent 1, remaining total 1')
            ->assertSee('mode sync_and_send')
            ->assertSee('types overdue|due_soon')
            ->assertDontSee('Temporary exception');
    }

    public function test_admin_can_filter_assignment_audit_by_scoring_settings_action(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'feed_scoring_settings',
            'action' => 'feed_scoring_settings_updated',
            'meta' => [
                'changed_keys' => ['topic_match', 'goal_affinity_max'],
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Temporary exception',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=feed_scoring_settings_updated')
            ->assertOk()
            ->assertSee('feed scoring settings updated')
            ->assertSee('changed topic_match, goal_affinity_max')
            ->assertDontSee('Temporary exception');
    }

    public function test_assignment_audit_shows_scoring_preset_detail(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'feed_scoring_settings',
            'action' => 'feed_scoring_preset_applied',
            'meta' => [
                'preset' => 'engagement_first',
                'preset_label' => 'Engagement First',
                'changed_keys' => ['topic_match', 'recent_topic_activity'],
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?action=feed_scoring_preset_applied')
            ->assertOk()
            ->assertSee('feed scoring preset applied')
            ->assertSee('preset Engagement First')
            ->assertSee('changed topic_match, recent_topic_activity');
    }

    public function test_assignment_audit_page_shows_settings_tuning_summary_cards(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');

        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'feed_scoring_settings',
            'action' => 'feed_scoring_settings_updated',
            'meta' => [
                'changed_keys' => ['topic_match'],
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'feed_scoring_settings',
            'action' => 'feed_scoring_settings_reset',
            'meta' => [],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_settings',
            'action' => 'reminder_settings_updated',
            'meta' => [
                'changed_keys' => ['inactive_nudge_days'],
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_settings',
            'action' => 'reminder_settings_reset',
            'meta' => [],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?from=2026-03-01&to=2026-03-04')
            ->assertOk()
            ->assertSee('Scoring Updated')
            ->assertSee('Scoring Reset')
            ->assertSee('Reminder Settings Updated')
            ->assertSee('Reminder Settings Reset')
            ->assertSee('feed scoring settings updated')
            ->assertSee('reminder settings updated');

        Carbon::setTestNow();
    }

    public function test_assignment_audit_export_respects_search_filter(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);
        $learner = User::factory()->create(['name' => 'Target Learner', 'email' => 'learner@example.com']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $learner->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Medical leave',
                'module_title' => 'Privacy Core',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/audit/export?q=medical');

        $response->assertOk();
        $this->assertStringContainsString('Medical leave', $response->streamedContent());
        $this->assertStringNotContainsString('manager / data-privacy', $response->streamedContent());
    }

    public function test_admin_can_search_assignment_audit_page_by_reminder_batch_metadata(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => null,
            'entity_type' => 'assignment_reminder_batch',
            'action' => 'reminder_batch_run',
            'meta' => [
                'synced_total' => 2,
                'sent_total' => 1,
                'remaining_pending' => 1,
                'mode' => 'send_only',
                'types' => ['due_soon'],
            ],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Temporary exception',
                'module_title' => 'Privacy Core',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?q=send_only')
            ->assertOk()
            ->assertSee('reminder batch run')
            ->assertDontSee('Temporary exception');
    }

    public function test_admin_can_search_assignment_audit_page_by_reminder_type_metadata(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Reminder Search Module',
            'description' => 'Search target module',
            'status' => 'published',
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'entity_type' => 'assignment_reminder',
            'action' => 'reminder_marked_sent',
            'meta' => [
                'reminder_type' => 'inactive_nudge',
                'module_title' => $module->title,
            ],
        ]);
        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?q=inactive_nudge')
            ->assertOk()
            ->assertSee('reminder marked sent')
            ->assertDontSee('data-privacy');
    }

    public function test_admin_can_filter_assignment_audit_by_date_range(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');

        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        $olderEvent = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);
        $olderEvent->forceFill([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ])->save();

        $recentEvent = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Recent exception',
                'module_title' => 'Privacy Core',
            ],
        ]);
        $recentEvent->forceFill([
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ])->save();

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?from=2026-03-01&to=2026-03-04')
            ->assertOk()
            ->assertSee('Recent exception')
            ->assertDontSee('data-privacy');

        Carbon::setTestNow();
    }

    public function test_assignment_audit_page_shows_summary_counts_for_filtered_window(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');

        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        $ruleEvent = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);
        $ruleEvent->forceFill([
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ])->save();

        $waiverEvent = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Recent exception',
                'module_title' => 'Privacy Core',
            ],
        ]);
        $waiverEvent->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->save();

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?from=2026-03-01&to=2026-03-04')
            ->assertOk()
            ->assertSee('Events')
            ->assertSee('Waivers Added')
            ->assertSee('1')
            ->assertDontSee('data-privacy');

        Carbon::setTestNow();
    }

    public function test_assignment_audit_export_respects_date_range_filter(): void
    {
        Carbon::setTestNow('2026-03-04 12:00:00');

        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);

        $olderEvent = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'compliance_role_rule',
            'action' => 'rule_saved',
            'meta' => [
                'role' => 'manager',
                'compliance_area' => 'data-privacy',
            ],
        ]);
        $olderEvent->forceFill([
            'created_at' => now()->subDays(12),
            'updated_at' => now()->subDays(12),
        ])->save();

        $recentEvent = AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Recent exception',
                'module_title' => 'Privacy Core',
            ],
        ]);
        $recentEvent->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->save();

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/audit/export?from=2026-03-01&to=2026-03-04');

        $response->assertOk();
        $this->assertStringContainsString('Recent exception', $response->streamedContent());
        $this->assertStringNotContainsString('manager / data-privacy', $response->streamedContent());

        Carbon::setTestNow();
    }

    public function test_admin_can_filter_assignment_audit_by_target_user_scope(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);
        $targetLearner = User::factory()->create(['name' => 'Target Learner', 'email' => 'target@example.com']);
        $otherLearner = User::factory()->create(['name' => 'Other Learner', 'email' => 'other@example.com']);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $targetLearner->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Target exception',
                'module_title' => 'Privacy Core',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $otherLearner->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Other exception',
                'module_title' => 'Security Core',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/audit?target=' . $targetLearner->id)
            ->assertOk()
            ->assertSee('Target exception')
            ->assertSee('Learner #' . $targetLearner->id)
            ->assertDontSee('Other exception');
    }

    public function test_assignment_audit_export_respects_module_scope(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);
        $module = LearningModule::query()->create([
            'title' => 'Privacy Core',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);
        $otherModule = LearningModule::query()->create([
            'title' => 'Security Core',
            'description' => 'Required security content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'security-awareness',
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'learning_module_id' => $module->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Privacy exception',
                'module_title' => 'Privacy Core',
            ],
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'learning_module_id' => $otherModule->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Security exception',
                'module_title' => 'Security Core',
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/audit/export?module=' . $module->id);

        $response->assertOk();
        $this->assertStringContainsString('Privacy exception', $response->streamedContent());
        $this->assertStringNotContainsString('Security exception', $response->streamedContent());
    }

    public function test_admin_dashboard_focus_filter_shows_only_waived_rows(): void
    {
        $admin = User::factory()->admin()->create();
        $waivedLearner = User::factory()->create(['name' => 'Waived Learner']);
        $overdueLearner = User::factory()->create(['name' => 'Overdue Learner']);

        $waivedModule = LearningModule::query()->create([
            'title' => 'Waived Privacy Module',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        $overdueModule = LearningModule::query()->create([
            'title' => 'Overdue Privacy Module',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'refresh_interval_days' => 30,
        ]);

        UserPreference::query()->create([
            'user_id' => $waivedLearner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        UserPreference::query()->create([
            'user_id' => $overdueLearner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $waivedLearner->id,
            'learning_module_id' => $waivedModule->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $overdueLearner->id,
            'learning_module_id' => $overdueModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments?focus=waived')
            ->assertOk()
            ->assertSee('Focused Assignments')
            ->assertSee('Waived Privacy Module')
            ->assertSee('Temporary exception')
            ->assertDontSee('Overdue Learner');
    }

    public function test_admin_dashboard_focus_filter_shows_only_overdue_rows(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $waivedLearner = User::factory()->create(['name' => 'Waived Learner']);
        $overdueLearner = User::factory()->create(['name' => 'Overdue Learner']);

        $waivedModule = LearningModule::query()->create([
            'title' => 'Waived Security Module',
            'description' => 'Required security content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'security-awareness',
        ]);

        $overdueModule = LearningModule::query()->create([
            'title' => 'Overdue Security Module',
            'description' => 'Required security content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'security-awareness',
            'refresh_interval_days' => 30,
        ]);

        UserPreference::query()->create([
            'user_id' => $waivedLearner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        UserPreference::query()->create([
            'user_id' => $overdueLearner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $waivedLearner->id,
            'learning_module_id' => $waivedModule->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $overdueLearner->id,
            'learning_module_id' => $overdueModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments?focus=overdue')
            ->assertOk()
            ->assertSee('Focused Assignments')
            ->assertSee('Overdue Security Module')
            ->assertSee('Overdue Learner')
            ->assertDontSee('Temporary exception');

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_focus_filter_shows_only_inactive_rows(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $inactiveLearner = User::factory()->create(['name' => 'Inactive Learner']);
        $overdueLearner = User::factory()->create(['name' => 'Overdue Learner']);

        $inactiveModule = LearningModule::query()->create([
            'title' => 'Inactive Security Module',
            'description' => 'Required security content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'security-awareness',
            'target_roles' => ['manager'],
        ]);

        $overdueModule = LearningModule::query()->create([
            'title' => 'Overdue Security Module',
            'description' => 'Required security content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'security-awareness',
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $inactiveLearner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        UserPreference::query()->create([
            'user_id' => $overdueLearner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $inactiveLearner->id,
            'learning_module_id' => $inactiveModule->id,
            'status' => 'in_progress',
            'percent_complete' => 20,
            'started_at' => now()->subDays(15),
            'last_activity_at' => now()->subDays(8),
        ]);

        ModuleProgress::query()->create([
            'user_id' => $overdueLearner->id,
            'learning_module_id' => $overdueModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments?focus=inactive')
            ->assertOk()
            ->assertSee('Focused Assignments')
            ->assertSee('Inactive Security Module')
            ->assertSee('Inactive Learner')
            ->assertSee('inactive nudge');

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_focus_filter_shows_not_started_nudge_rows(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Not Started Learner', 'email' => 'notstarted@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Not Started Focus Module',
            'description' => 'Not started focus target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
            'available_from' => now()->subDays(14),
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments?focus=inactive')
            ->assertOk()
            ->assertSee('Focused Assignments')
            ->assertSee('Not Started Learner')
            ->assertSee('Not Started Focus Module')
            ->assertSee('not started nudge');

        Carbon::setTestNow();
    }

    public function test_admin_can_export_focused_assignments_as_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Waived Learner', 'email' => 'waived@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Waived Privacy Module',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/export?focus=waived');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'role,learner_name,learner_email,module_title,compliance_area,urgency,due_date,notes',
            $response->streamedContent(),
        );
        $this->assertStringContainsString(
            'manager,"Waived Learner",waived@example.com,"Waived Privacy Module",data-privacy,waived,,"Temporary exception"',
            $response->streamedContent(),
        );
    }

    public function test_admin_can_export_inactive_focused_assignments_as_csv(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Inactive Learner', 'email' => 'inactive@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Inactive Privacy Module',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 45,
            'started_at' => now()->subDays(15),
            'last_activity_at' => now()->subDays(8),
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/export?focus=inactive');

        $response->assertOk();
        $this->assertStringContainsString('inactive_nudge', $response->streamedContent());
        $this->assertStringContainsString('Inactive Privacy Module', $response->streamedContent());

        Carbon::setTestNow();
    }

    public function test_non_admin_cannot_export_focused_assignments(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/assignments/export?focus=overdue')
            ->assertForbidden();
    }

    public function test_admin_can_export_assignment_settings_as_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/app/admin/scoring', [
                'weights' => array_merge(config('learning_assignments.feed_scoring'), [
                    'topic_match' => 88,
                ]),
            ])
            ->assertRedirect('/app/admin/scoring');

        $this->actingAs($admin)
            ->post('/app/admin/reminder-settings', [
                'settings' => [
                    'inactive_nudge_after_days' => 6,
                    'inactive_nudge_cooldown_days' => 2,
                    'not_started_nudge_after_days' => 11,
                    'not_started_nudge_cooldown_days' => 4,
                ],
            ])
            ->assertRedirect('/app/admin/reminder-settings');

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/settings-export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('category,key,value', $content);
        $this->assertStringContainsString('reminder,inactive_nudge_after_days,6', $content);
        $this->assertStringContainsString('feed_scoring,topic_match,88', $content);
    }

    public function test_non_admin_cannot_export_assignment_settings_as_csv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/assignments/settings-export')
            ->assertForbidden();
    }

    public function test_admin_can_view_assignment_role_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->create(['name' => 'Manager Person', 'email' => 'manager@example.com']);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        LearningModule::query()->create([
            'title' => 'Leadership Privacy Refresher',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/roles/manager')
            ->assertOk()
            ->assertSee('Assignment Role Detail')
            ->assertSee('Manager Person')
            ->assertSee('Leadership Privacy Refresher');
    }

    public function test_assignment_role_detail_shows_waived_count(): void
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->create(['name' => 'Manager Person', 'email' => 'manager@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Waived Leadership Privacy Refresher',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $manager->id,
            'learning_module_id' => $module->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/roles/manager')
            ->assertOk()
            ->assertSee('Waived')
            ->assertSee('Waived Leadership Privacy Refresher')
            ->assertSee('waived');
    }

    public function test_admin_can_view_assignment_compliance_area_detail(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $manager = User::factory()->create(['name' => 'Manager Person', 'email' => 'manager@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Privacy Annual Refresher',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'refresh_interval_days' => 30,
        ]);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $manager->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/compliance-areas/data-privacy')
            ->assertOk()
            ->assertSee('Compliance Area Detail')
            ->assertSee('Privacy Annual Refresher')
            ->assertSee('Manager Person');

        Carbon::setTestNow();
    }

    public function test_assignment_compliance_area_detail_shows_waived_users(): void
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->create(['name' => 'Manager Person', 'email' => 'manager@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Waived Privacy Annual Refresher',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'refresh_interval_days' => 30,
        ]);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $manager->id,
            'learning_module_id' => $module->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments/compliance-areas/data-privacy')
            ->assertOk()
            ->assertSee('Waived')
            ->assertSee('Waived Privacy Annual Refresher')
            ->assertSee('Manager Person');
    }

    public function test_admin_can_view_assignment_user_detail(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Security Annual Refresher',
            'description' => 'Required security refresher',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'security-awareness',
            'refresh_interval_days' => 30,
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['status' => 'completed', 'score_raw' => 95, 'session_time' => '00:08:00', 'session_seconds' => 480, 'percent_complete' => 100, 'lesson_location' => 'final-quiz'],
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/assignments/users/{$learner->id}")
            ->assertOk()
            ->assertSee('Learner Assignment Detail')
            ->assertSee('Learning Events')
            ->assertSee('Recent SCORM Attempts')
            ->assertSee('Learner Person')
            ->assertSee('Security Annual Refresher')
            ->assertSee('95')
            ->assertSee('8m')
            ->assertSee('final-quiz')
            ->assertSee('overdue');

        Carbon::setTestNow();
    }

    public function test_assignment_user_detail_shows_recent_activity_for_learner(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Audit Admin']);
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Privacy Core',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Temporary exception',
                'module_title' => 'Privacy Core',
            ],
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/assignments/users/{$learner->id}")
            ->assertOk()
            ->assertSee('Recent Assignment Activity')
            ->assertSee('Audit Admin')
            ->assertSee('waiver saved')
            ->assertSee('Privacy Core')
            ->assertSee('Temporary exception');
    }

    public function test_admin_can_view_assignment_user_events_timeline(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Event Learner', 'email' => 'event-learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Timeline Security Basics',
            'description' => 'Timeline label rendering',
            'status' => 'published',
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'feature-test'],
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'preferences_saved',
            'entity_type' => 'user_preference',
            'entity_id' => $learner->id,
            'metadata' => ['source' => 'feature-test-preference'],
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_launched',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['asset_id' => 12, 'launch_path' => 'index.html'],
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['status' => 'completed', 'score_raw' => 93, 'session_time' => '00:06:00', 'session_seconds' => 360, 'percent_complete' => 100, 'lesson_location' => 'wrap-up'],
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/assignments/users/{$learner->id}/events?event_type=scorm_runtime_committed&entity_type=learning_module")
            ->assertOk()
            ->assertSee('Learner Event Timeline')
            ->assertSee('SCORM Runtime')
            ->assertSee('SCORM Launches')
            ->assertSee('Runtime Commits')
            ->assertSee('scorm_runtime_committed')
            ->assertSee('Timeline Security Basics')
            ->assertSee('status=completed; score=93; session=6m; percent=100; location=wrap-up');
    }

    public function test_admin_can_export_assignment_user_events_timeline_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Event Export Learner', 'email' => 'event-export@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Export Label Module',
            'description' => 'CSV label rendering',
            'status' => 'published',
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'module_completed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'feature-test-export'],
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'preferences_saved',
            'entity_type' => 'user_preference',
            'entity_id' => $learner->id,
            'metadata' => ['source' => 'feature-test-preference-export'],
        ]);

        $response = $this->actingAs($admin)
            ->get("/app/admin/assignments/users/{$learner->id}/events/export?event_type=module_completed&entity_type=learning_module");

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('module_completed', $content);
        $this->assertStringContainsString('event-export@example.com', $content);
        $this->assertStringContainsString('Export Label Module', $content);
        $this->assertStringNotContainsString('preferences_saved', $content);
    }

    public function test_non_admin_cannot_view_assignment_user_events_timeline(): void
    {
        $user = User::factory()->create();
        $learner = User::factory()->create();

        $this->actingAs($user)
            ->get("/app/admin/assignments/users/{$learner->id}/events")
            ->assertForbidden();
    }

    public function test_admin_can_export_learner_assignment_detail_as_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Privacy Core',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        AssignmentAuditEvent::query()->create([
            'actor_user_id' => $admin->id,
            'target_user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'entity_type' => 'assignment_waiver',
            'action' => 'waiver_saved',
            'meta' => [
                'reason' => 'Temporary exception',
                'module_title' => 'Privacy Core',
            ],
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $service = app(ReinforcementService::class);
        $service->syncForUser($learner);
        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();
        $service->completeForUser($touchpoint, $learner);

        $response = $this->actingAs($admin)
            ->get("/app/admin/assignments/users/{$learner->id}/export");

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'record_type,learner_name,learner_email,module_title,compliance_area,urgency_or_action,due_or_when,details',
            $response->streamedContent(),
        );
        $this->assertStringContainsString(
            'assignment,"Learner Person",learner@example.com,"Privacy Core",data-privacy,waived,,',
            $response->streamedContent(),
        );
        $this->assertStringContainsString(
            'audit_event,"Learner Person",learner@example.com,"Privacy Core",data-privacy,waiver_saved,',
            $response->streamedContent(),
        );
        $this->assertStringContainsString(
            'latest_reinforcement_proof,"Learner Person",learner@example.com,"Privacy Core",,"7-day knowledge_check",',
            $response->streamedContent(),
        );
    }

    public function test_admin_assignment_user_routes_reject_non_numeric_ids(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/app/admin/assignments/users/%7Bid%7D')
            ->assertNotFound();

        $this->actingAs($admin)
            ->get('/app/admin/assignments/users/%7Bid%7D/export')
            ->assertNotFound();
    }

    public function test_admin_can_waive_assignment_for_learner(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Privacy Core',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $this->actingAs($admin)
            ->post("/app/admin/assignments/users/{$learner->id}/modules/{$module->id}/waiver", [
                'reason' => 'Temporary exception',
            ])
            ->assertRedirect("/app/admin/assignments/users/{$learner->id}");

        $this->assertDatabaseHas('assignment_waivers', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reason' => 'Temporary exception',
        ]);

        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'action' => 'waiver_saved',
            'entity_type' => 'assignment_waiver',
        ]);
    }

    public function test_admin_can_restore_waived_assignment_for_learner(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Privacy Core',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        $this->actingAs($admin)
            ->delete("/app/admin/assignments/users/{$learner->id}/modules/{$module->id}/waiver")
            ->assertRedirect("/app/admin/assignments/users/{$learner->id}");

        $this->assertDatabaseMissing('assignment_waivers', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
        ]);

        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'action' => 'waiver_removed',
            'entity_type' => 'assignment_waiver',
        ]);
    }

    public function test_waived_assignment_is_removed_from_learner_feed_but_visible_to_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person']);
        $module = LearningModule::query()->create([
            'title' => 'Waivable Privacy Core',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        AssignmentWaiver::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'created_by' => $admin->id,
            'reason' => 'Temporary exception',
        ]);

        $this->actingAs($learner)
            ->get('/app/feed')
            ->assertOk()
            ->assertDontSee('Waivable Privacy Core');

        $this->actingAs($admin)
            ->get("/app/admin/assignments/users/{$learner->id}")
            ->assertOk()
            ->assertSee('Waivable Privacy Core')
            ->assertSee('waived')
            ->assertSee('Temporary exception');
    }

    public function test_non_admin_cannot_mutate_assignment_waivers(): void
    {
        $user = User::factory()->create();
        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Privacy Core',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        $this->actingAs($user)
            ->post("/app/admin/assignments/users/{$learner->id}/modules/{$module->id}/waiver", [
                'reason' => 'Nope',
            ])
            ->assertForbidden();
    }

    public function test_required_compliance_module_is_hidden_from_roles_without_inherited_domain(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Workplace Safety Refresher',
            'description' => 'Required safety content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'workplace-safety',
            'refresh_interval_days' => 90,
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        $this->actingAs($user)
            ->get('/app/feed')
            ->assertOk()
            ->assertDontSee('Workplace Safety Refresher');

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertNotFound();
    }

    public function test_module_detail_page_shows_compliance_assignment_reasoning(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Data Privacy Core',
            'description' => 'Required privacy content',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'refresh_interval_days' => 365,
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk()
            ->assertSee('Compliance assignment')
            ->assertSee('data-privacy');
    }

    public function test_feed_and_module_detail_show_scorm_demo_guidance(): void
    {
        $user = User::factory()->create();

        LearningModule::query()->create([
            'title' => 'Customer Data Handling Essentials',
            'description' => 'SCORM-backed training for the prototype walkthrough.',
            'status' => 'published',
            'source_type' => 'scorm',
            'topic' => 'security',
            'difficulty' => 'beginner',
        ]);

        $feedResponse = $this->actingAs($user)->get('/app/feed');
        $feedResponse->assertOk();
        $this->assertTrue(
            $feedResponse->viewData('modules')->contains('title', 'Customer Data Handling Essentials'),
            'SCORM module should be in feed modules collection'
        );

        $module = LearningModule::query()->where('title', 'Customer Data Handling Essentials')->firstOrFail();

        $module->assets()->create([
            'asset_type' => 'scorm_package',
            'original_filename' => 'demo-scorm-security-basics.zip',
            'storage_disk' => 'local',
            'storage_path' => 'learning-assets/demo-scorm-security-basics.zip',
            'extracted_disk' => 'local',
            'extracted_path' => 'learning-assets/demo-scorm-security-basics',
            'launch_path' => 'story.html',
            'mime_type' => 'application/zip',
            'size_bytes' => 0,
            'status' => 'processed',
            'manifest' => ['title' => 'SCORM Demo Security Basics', 'launch_path' => 'story.html'],
        ]);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk()
            ->assertSee('SCORM Demo Course')
            ->assertSee('Demo Scenario: Client Walkthrough')
            ->assertSee('Primary Demo Course')
            ->assertSee('embedded SCORM player used for the client prototype')
            ->assertSee('Your SCORM Activity')
            ->assertSee('Demo Handoff')
            ->assertSee('Admin review paths:')
            ->assertSee('/app/admin/scorm', false)
            ->assertSee('/app/admin/compliance?source_type=scorm', false)
            ->assertSee('Launches')
            ->assertSee('Runtime attempts')
            ->assertSee('Latest launch')
            ->assertSee('Not launched yet')
            ->assertSee('Your Latest SCORM Result')
            ->assertSee('No SCORM runtime has been recorded yet')
            ->assertSee('Launch path:')
            ->assertSee('story.html')
            ->assertSee('Package status:')
            ->assertSee('processed')
            ->assertSee(route('app.modules.scorm.launch', ['module' => $module->id]), false);

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'scorm_launched',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'asset_id' => $module->latestScormAsset()?->id,
                'launch_path' => 'story.html',
            ],
        ]);

        LearningEvent::query()->create([
            'user_id' => $user->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'status' => 'completed',
                'score_raw' => 94,
                'session_time' => '00:07:15',
                'session_seconds' => 435,
                'percent_complete' => 100,
                'lesson_location' => 'final-check',
            ],
        ]);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk()
            ->assertSee('Your SCORM Activity')
            ->assertSee('Demo Handoff')
            ->assertSee('Launches')
            ->assertSee('1')
            ->assertSee('Runtime attempts')
            ->assertSee('Latest launch')
            ->assertSee('Your Latest SCORM Result')
            ->assertSee('Completion state')
            ->assertSee('completed')
            ->assertSee('Score')
            ->assertSee('94')
            ->assertSee('Session time')
            ->assertSee('7m 15s')
            ->assertSee('Progress')
            ->assertSee('100%')
            ->assertSee('Location: final-check');
    }

    public function test_role_targeted_modules_only_appear_for_matching_users(): void
    {
        $manager = User::factory()->create();
        $specialist = User::factory()->create();

        $managerModule = LearningModule::query()->create([
            'title' => 'Manager Coaching Essentials',
            'description' => 'Manager-only content',
            'status' => 'published',
            'topic' => 'leadership',
            'difficulty' => 'intermediate',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'topics' => ['leadership'],
            'difficulty' => 'intermediate',
            'role' => 'manager',
        ]);

        UserPreference::query()->create([
            'user_id' => $specialist->id,
            'topics' => ['leadership'],
            'difficulty' => 'intermediate',
            'role' => 'specialist',
        ]);

        $managerFeed = $this->actingAs($manager)->get('/app/feed');
        $managerFeed->assertOk();
        $this->assertTrue(
            $managerFeed->viewData('modules')->contains('title', 'Manager Coaching Essentials'),
            'Manager should see role-targeted module in feed data'
        );

        $specialistFeed = $this->actingAs($specialist)->get('/app/feed');
        $specialistFeed->assertOk();
        $this->assertFalse(
            $specialistFeed->viewData('modules')->contains('title', 'Manager Coaching Essentials'),
            'Specialist should not see manager-targeted module in feed data'
        );
    }

    public function test_role_match_adds_score_and_shows_on_module_detail(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'New Starter Orientation',
            'description' => 'Role-targeted onboarding content',
            'status' => 'published',
            'topic' => 'productivity',
            'difficulty' => 'beginner',
            'target_roles' => ['new-starter'],
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['productivity'],
            'difficulty' => 'beginner',
            'role' => 'new-starter',
        ]);

        $score = app(FeedScoringService::class)->scoreWithBreakdown($user, $module, null);

        $this->assertSame(40, $score['breakdown']['role_match']);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk()
            ->assertSee('Role targeting')
            ->assertSee('new-starter');
    }

    public function test_role_targeted_module_detail_is_hidden_from_non_matching_user(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Manager Escalation Path',
            'description' => 'Manager-only path',
            'status' => 'published',
            'topic' => 'leadership',
            'difficulty' => 'advanced',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['leadership'],
            'difficulty' => 'advanced',
            'role' => 'specialist',
        ]);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertNotFound();
    }

    public function test_module_detail_page_shows_due_soon_state(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Security Refresher',
            'description' => 'Required security refresher',
            'status' => 'published',
            'topic' => 'security',
            'difficulty' => 'intermediate',
            'is_required' => true,
            'compliance_area' => 'security-awareness',
            'refresh_interval_days' => 30,
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['security'],
            'difficulty' => 'intermediate',
            'role' => 'manager',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(40),
            'completed_at' => now()->subDays(25),
            'last_activity_at' => now()->subDays(25),
        ]);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk()
            ->assertSee('Refresh Soon')
            ->assertSee('Due ');

        Carbon::setTestNow();
    }

    public function test_admin_can_save_module_prerequisites_from_ui(): void
    {
        $admin = User::factory()->admin()->create();

        $intro = LearningModule::query()->create([
            'title' => 'Intro Module',
            'description' => 'Start here',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);

        $advanced = LearningModule::query()->create([
            'title' => 'Advanced Module',
            'description' => 'Next step',
            'status' => 'draft',
            'difficulty' => 'advanced',
        ]);

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$advanced->id}", [
                'title' => 'Advanced Module',
                'description' => 'Next step',
                'topic' => 'leadership',
                'difficulty' => 'advanced',
                'status' => 'published',
                'compliance_area' => '',
                'refresh_interval_days' => '',
                'source_type' => 'manual',
                'source_uri' => '',
                'content_text' => '',
                'target_roles' => '',
                'prerequisite_ids' => [$intro->id],
            ])
            ->assertRedirect("/app/admin/modules/{$advanced->id}/edit");

        $this->assertDatabaseHas('learning_module_prerequisites', [
            'learning_module_id' => $advanced->id,
            'prerequisite_learning_module_id' => $intro->id,
        ]);
    }

    public function test_admin_can_publish_module_from_index(): void
    {
        $admin = User::factory()->admin()->create();

        $module = LearningModule::query()->create([
            'title' => 'Draft Module',
            'description' => 'Still in review',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'review_status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}/status", [
                'status' => 'published',
            ])
            ->assertRedirect('/app/admin/modules');

        $this->assertDatabaseHas('learning_modules', [
            'id' => $module->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_can_bulk_transition_module_status_with_publish_guardrails(): void
    {
        $admin = User::factory()->admin()->create();

        $approvedDraft = LearningModule::query()->create([
            'title' => 'Approved Draft Module',
            'description' => 'Ready for publish',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'review_status' => 'approved',
        ]);

        $unapprovedDraft = LearningModule::query()->create([
            'title' => 'Unapproved Draft Module',
            'description' => 'Not ready for publish',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'review_status' => 'in_review',
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/modules/bulk-status', [
                'status' => 'published',
                'module_ids' => [$approvedDraft->id, $unapprovedDraft->id],
            ])
            ->assertRedirect('/app/admin/modules');

        $this->assertDatabaseHas('learning_modules', [
            'id' => $approvedDraft->id,
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('learning_modules', [
            'id' => $unapprovedDraft->id,
            'status' => 'draft',
            'review_status' => 'in_review',
        ]);
    }

    public function test_non_admin_cannot_bulk_transition_module_status(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Protected Module',
            'description' => 'Should not be mutable by learner',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'review_status' => 'approved',
        ]);

        $this->actingAs($user)
            ->post('/app/admin/modules/bulk-status', [
                'status' => 'published',
                'module_ids' => [$module->id],
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('learning_modules', [
            'id' => $module->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_require_acknowledgement_on_module(): void
    {
        $admin = User::factory()->admin()->create();

        $module = LearningModule::query()->create([
            'title' => 'Policy Module',
            'description' => 'Requires acknowledgement',
            'status' => 'draft',
            'difficulty' => 'beginner',
        ]);

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}", [
                'title' => 'Policy Module',
                'description' => 'Requires acknowledgement',
                'topic' => 'policy',
                'difficulty' => 'beginner',
                'status' => 'published',
                'compliance_area' => 'policy',
                'refresh_interval_days' => '',
                'source_type' => 'manual',
                'source_uri' => '',
                'content_text' => '',
                'target_roles' => 'manager',
                'requires_acknowledgement' => '1',
                'prerequisite_ids' => [],
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit");

        $this->assertDatabaseHas('learning_modules', [
            'id' => $module->id,
            'requires_acknowledgement' => true,
        ]);
    }

    public function test_module_with_unmet_prerequisite_is_hidden_from_feed_and_detail(): void
    {
        $user = User::factory()->create();

        $intro = LearningModule::query()->create([
            'title' => 'Intro Safety',
            'description' => 'Complete first',
            'status' => 'published',
            'topic' => 'safety',
            'difficulty' => 'beginner',
        ]);

        $advanced = LearningModule::query()->create([
            'title' => 'Advanced Safety',
            'description' => 'Locked until intro is complete',
            'status' => 'published',
            'topic' => 'safety',
            'difficulty' => 'intermediate',
        ]);

        $advanced->prerequisites()->attach($intro->id);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['safety'],
            'difficulty' => 'intermediate',
            'role' => 'manager',
        ]);

        $this->actingAs($user)
            ->get('/app/feed')
            ->assertOk()
            ->assertDontSee('Advanced Safety');

        $this->actingAs($user)
            ->get("/app/modules/{$advanced->id}")
            ->assertNotFound();
    }

    public function test_module_with_completed_prerequisite_becomes_visible(): void
    {
        $user = User::factory()->create();

        $intro = LearningModule::query()->create([
            'title' => 'Intro Leadership',
            'description' => 'Complete first',
            'status' => 'published',
            'topic' => 'leadership',
            'difficulty' => 'beginner',
        ]);

        $advanced = LearningModule::query()->create([
            'title' => 'Advanced Leadership',
            'description' => 'Unlocked after intro',
            'status' => 'published',
            'topic' => 'leadership',
            'difficulty' => 'advanced',
        ]);

        $advanced->prerequisites()->attach($intro->id);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'topics' => ['leadership'],
            'difficulty' => 'advanced',
            'role' => 'manager',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $intro->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        $assignment = app(AssignmentService::class)->forUser($user, $advanced, null);

        $this->assertTrue($assignment['prerequisites']['is_unlocked']);
        $this->assertTrue($assignment['is_assigned']);

        $feedResponse = $this->actingAs($user)->get('/app/feed');
        $feedResponse->assertOk();
        $this->assertTrue(
            $feedResponse->viewData('modules')->contains('title', 'Advanced Leadership'),
            'Module with completed prerequisite should be in feed data'
        );

        $this->actingAs($user)
            ->get("/app/modules/{$advanced->id}")
            ->assertOk()
            ->assertSee('Prerequisites')
            ->assertSee('Unlocked');
    }

    public function test_completed_required_module_stays_open_until_acknowledged(): void
    {
        $user = User::factory()->create();

        $module = LearningModule::query()->create([
            'title' => 'Conduct Policy',
            'description' => 'Read and acknowledge',
            'status' => 'published',
            'is_required' => true,
            'requires_acknowledgement' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $progress = ModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        $assignment = app(AssignmentService::class)->forUser($user, $module, $progress);

        $this->assertTrue($assignment['acknowledgement']['is_required']);
        $this->assertFalse($assignment['acknowledgement']['is_acknowledged']);
        $this->assertTrue($assignment['is_incomplete_required']);
    }

    public function test_learner_can_acknowledge_required_module(): void
    {
        $user = User::factory()->create();

        $module = LearningModule::query()->create([
            'title' => 'Security Policy',
            'description' => 'Read and acknowledge',
            'status' => 'published',
            'is_required' => true,
            'requires_acknowledgement' => true,
            'compliance_area' => 'security-awareness',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        Livewire::actingAs($user)
            ->test(ModuleProgressPanel::class, ['module' => $module])
            ->call('acknowledge')
            ->assertSet('isAcknowledged', true);

        $this->assertDatabaseHas('module_acknowledgements', [
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
        ]);

        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $user->id,
            'target_user_id' => $user->id,
            'learning_module_id' => $module->id,
            'action' => 'acknowledgement_recorded',
            'entity_type' => 'module_acknowledgement',
        ]);
        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'module_acknowledged',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ]);

        $this->actingAs($user)
            ->get("/app/modules/{$module->id}")
            ->assertOk()
            ->assertSee('Acknowledgement')
            ->assertSee('Acknowledged');
    }

    public function test_module_progress_panel_records_progress_and_completion_events(): void
    {
        $user = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Evented Progress Module',
            'description' => 'Progress event target',
            'status' => 'published',
            'difficulty' => 'beginner',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        Livewire::actingAs($user)
            ->test(ModuleProgressPanel::class, ['module' => $module])
            ->call('incrementForTesting')
            ->call('markCompleted');

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'module_progress_updated',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ]);
        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'module_completed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
        ]);
    }

    public function test_admin_assignment_user_detail_shows_acknowledgement_state(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Code of Conduct',
            'description' => 'Acknowledge this policy',
            'status' => 'published',
            'is_required' => true,
            'requires_acknowledgement' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        ModuleAcknowledgement::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'acknowledged_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/assignments/users/{$learner->id}")
            ->assertOk()
            ->assertSee('Acknowledged')
            ->assertSee('Code of Conduct')
            ->assertSee('acknowledged');
    }

    public function test_admin_dashboard_shows_acknowledgement_reporting(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person']);

        $module = LearningModule::query()->create([
            'title' => 'Security Policy',
            'description' => 'Acknowledge this policy',
            'status' => 'published',
            'is_required' => true,
            'requires_acknowledgement' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleAcknowledgement::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'acknowledged_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Acknowledgements')
            ->assertSee('Acknowledgements by Role')
            ->assertSee('Acknowledgement Leader')
            ->assertSee('Learner Person')
            ->assertSee('Security Policy');
    }

    public function test_admin_can_sync_assignment_reminder_queue(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Annual Security Refresher',
            'description' => 'Overdue refresher',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/assignments/reminders/sync')
            ->assertRedirect('/app/admin/assignments');

        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Reminder Queue')
            ->assertSee('Annual Security Refresher')
            ->assertSee('Learner Person');

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_shows_reminder_mix_chart_and_cards(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Reminder Learner', 'email' => 'reminder-learner@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Reminder Mix Module',
            'description' => 'Reminder mix chart target',
            'status' => 'published',
            'is_required' => true,
        ]);

        foreach (['overdue', 'due_soon', 'inactive_nudge', 'not_started_nudge'] as $index => $type) {
            AssignmentReminder::query()->create([
                'user_id' => $learner->id,
                'learning_module_id' => $module->id,
                'reminder_type' => $type,
                'due_on' => now()->addDays($index)->toDateString(),
                'status' => 'pending',
            ]);
        }

        $this->actingAs($admin)
            ->get('/app/admin/assignments')
            ->assertOk()
            ->assertSee('Reminder Queue')
            ->assertSee('Reminder Mix')
            ->assertSee('adminReminderMixChart', false)
            ->assertSee('overdue')
            ->assertSee('inactive nudge');

        Carbon::setTestNow();
    }

    public function test_admin_can_run_assignment_reminders_from_dashboard(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Dashboard Run Module',
            'description' => 'Dashboard run target',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        Notification::fake();

        $response = $this->actingAs($admin)
            ->post('/app/admin/assignments/reminders/run', [
                'mode' => 'sync_and_send',
                'limit' => 5,
                'types' => 'overdue',
                'dry_run' => 0,
            ]);

        $response->assertRedirect('/app/admin/assignments');
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'status' => 'sent',
        ]);

        Carbon::setTestNow();
    }

    public function test_admin_can_run_assignment_reminders_from_dashboard_in_dry_run_mode(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Dashboard Dry Run Module',
            'description' => 'Dashboard dry run target',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $response = $this->actingAs($admin)
            ->post('/app/admin/assignments/reminders/run', [
                'mode' => 'sync_and_send',
                'limit' => 5,
                'types' => 'overdue',
                'dry_run' => 1,
            ]);

        $response->assertRedirect('/app/admin/assignments');
        $response->assertSessionHas('status', fn (string $status) => str_contains($status, 'Dry run mode enabled'));
        $this->assertDatabaseMissing('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
        ]);

        Carbon::setTestNow();
    }

    public function test_non_admin_cannot_run_assignment_reminders_from_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/app/admin/assignments/reminders/run', [
                'mode' => 'sync_and_send',
                'limit' => 5,
            ])
            ->assertForbidden();
    }

    public function test_admin_can_mark_assignment_reminder_sent(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Due Soon Privacy Reminder',
            'description' => 'Reminder target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $reminder = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'due_soon',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch("/app/admin/assignments/reminders/{$reminder->id}/sent")
            ->assertRedirect('/app/admin/assignments');

        $this->assertDatabaseHas('assignment_reminders', [
            'id' => $reminder->id,
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('assignment_audit_events', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'action' => 'reminder_marked_sent',
            'entity_type' => 'assignment_reminder',
        ]);
    }

    public function test_assignment_sync_reminders_command_builds_pending_queue(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Annual Privacy Refresher',
            'description' => 'Overdue refresher',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $this->artisan('assignments:sync-reminders')
            ->expectsOutput('Synced 1 reminder records.')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'status' => 'pending',
        ]);

        Carbon::setTestNow();
    }

    public function test_assignment_send_reminders_command_marks_pending_as_sent(): void
    {
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Due Soon Policy Reminder',
            'description' => 'Reminder target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
        ]);

        $reminder = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'due_soon',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Notification::fake();

        $this->artisan('assignments:send-reminders --limit=1')
            ->expectsOutput("Sent due_soon reminder to learner@example.com for Due Soon Policy Reminder.")
            ->expectsOutput('Marked 1 reminders as sent.')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'id' => $reminder->id,
            'status' => 'sent',
        ]);

        Notification::assertSentTo($learner, AssignmentReminderNotification::class);
    }

    public function test_assignment_run_reminders_command_syncs_and_sends(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Combined Reminder Module',
            'description' => 'Combined command target',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        Notification::fake();

        $this->artisan('assignments:run-reminders --limit=1')
            ->expectsOutput("Sent overdue reminder to learner@example.com for Combined Reminder Module.")
            ->expectsOutput('Run complete. Synced 1 reminder records; marked 1 reminders as sent.')
            ->expectsOutput('Synced by type: overdue=1')
            ->expectsOutput('Sent by type: overdue=1')
            ->expectsOutput('Remaining pending reminders: total=0, filtered=0')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'reminder_batch_run',
            'entity_type' => 'assignment_reminder_batch',
        ]);
        $batchAudit = AssignmentAuditEvent::query()
            ->where('action', 'reminder_batch_run')
            ->latest('id')
            ->first();
        $this->assertNotNull($batchAudit);
        $this->assertSame(1, (int) ($batchAudit->meta['synced_total'] ?? 0));
        $this->assertSame(1, (int) ($batchAudit->meta['sent_total'] ?? 0));
        $this->assertSame(0, (int) ($batchAudit->meta['remaining_pending'] ?? 0));
        $this->assertSame(0, (int) ($batchAudit->meta['remaining_pending_filtered'] ?? 0));

        Notification::assertSentTo($learner, AssignmentReminderNotification::class);
        Carbon::setTestNow();
    }

    public function test_assignment_run_reminders_command_dry_run_does_not_mutate(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Dry Run Reminder Module',
            'description' => 'Dry run command target',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        Notification::fake();

        $this->artisan('assignments:run-reminders --limit=1 --dry-run')
            ->expectsOutput('Dry run mode enabled. No reminder records were changed.')
            ->expectsOutput('Would sync 1 reminder records and mark 1 reminders as sent.')
            ->expectsOutput('Would sync by type: overdue=1')
            ->expectsOutput('Would send by type: overdue=1')
            ->assertSuccessful();

        $this->assertDatabaseMissing('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
        ]);
        $this->assertDatabaseMissing('assignment_audit_events', [
            'action' => 'reminder_batch_run',
            'entity_type' => 'assignment_reminder_batch',
        ]);

        Notification::assertNothingSent();
        Carbon::setTestNow();
    }

    public function test_assignment_run_reminders_command_respects_types_filter(): void
    {
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Type Filter Reminder Module',
            'description' => 'Type filter target',
            'status' => 'published',
            'is_required' => true,
        ]);

        $dueSoonReminder = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'due_soon',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);
        $overdueReminder = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Notification::fake();

        $this->artisan('assignments:run-reminders --limit=10 --types=due_soon')
            ->expectsOutput("Sent due_soon reminder to learner@example.com for Type Filter Reminder Module.")
            ->expectsOutput('Run complete. Synced 0 reminder records; marked 1 reminders as sent.')
            ->expectsOutput('Sent by type: due_soon=1')
            ->expectsOutput('Send filter: due_soon')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'id' => $dueSoonReminder->id,
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('assignment_reminders', [
            'id' => $overdueReminder->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('assignment_audit_events', [
            'action' => 'reminder_batch_run',
            'entity_type' => 'assignment_reminder_batch',
        ]);
    }

    public function test_assignment_run_reminders_command_sync_only_mode(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Sync Only Module',
            'description' => 'Sync only target',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        Notification::fake();

        $this->artisan('assignments:run-reminders --sync-only')
            ->expectsOutput('Run complete. Synced 1 reminder records; marked 0 reminders as sent.')
            ->expectsOutput('Mode: sync_only')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'status' => 'pending',
        ]);
        Notification::assertNothingSent();

        Carbon::setTestNow();
    }

    public function test_assignment_run_reminders_command_send_only_mode(): void
    {
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Send Only Module',
            'description' => 'Send only target',
            'status' => 'published',
            'is_required' => true,
        ]);

        $reminder = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'due_soon',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Notification::fake();

        $this->artisan('assignments:run-reminders --send-only --limit=1')
            ->expectsOutput("Sent due_soon reminder to learner@example.com for Send Only Module.")
            ->expectsOutput('Run complete. Synced 0 reminder records; marked 1 reminders as sent.')
            ->expectsOutput('Mode: send_only')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'id' => $reminder->id,
            'status' => 'sent',
        ]);
    }

    public function test_assignment_run_reminders_command_rejects_conflicting_modes(): void
    {
        $this->artisan('assignments:run-reminders --sync-only --send-only')
            ->expectsOutput('Options --sync-only and --send-only cannot be combined.')
            ->assertExitCode(2);
    }

    public function test_assignment_run_reminders_command_rejects_invalid_types(): void
    {
        $this->artisan('assignments:run-reminders --types=overdue,invalid_type')
            ->expectsOutput('Invalid reminder types: invalid_type. Allowed: overdue, due_soon, inactive_nudge, not_started_nudge.')
            ->assertExitCode(2);
    }

    public function test_assignment_sync_reminders_command_builds_inactive_nudge_queue(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'In Progress Security Basics',
            'description' => 'Nudge target module',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 40,
            'started_at' => now()->subDays(14),
            'last_activity_at' => now()->subDays(8),
        ]);

        $this->artisan('assignments:sync-reminders')
            ->expectsOutput('Synced 1 reminder records.')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'inactive_nudge',
            'status' => 'pending',
        ]);

        Carbon::setTestNow();
    }

    public function test_assignment_send_reminders_command_sends_inactive_nudge(): void
    {
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Long Running Module',
            'description' => 'Nudge send target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $reminder = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'inactive_nudge',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Notification::fake();

        $this->artisan('assignments:send-reminders --limit=1')
            ->expectsOutput("Sent inactive_nudge reminder to learner@example.com for Long Running Module.")
            ->expectsOutput('Marked 1 reminders as sent.')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'id' => $reminder->id,
            'status' => 'sent',
        ]);

        Notification::assertSentTo($learner, AssignmentReminderNotification::class);
    }

    public function test_assignment_send_reminders_command_sends_not_started_nudge(): void
    {
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Never Started Module',
            'description' => 'Not started send target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $reminder = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'not_started_nudge',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Notification::fake();

        $this->artisan('assignments:send-reminders --limit=1')
            ->expectsOutput("Sent not_started_nudge reminder to learner@example.com for Never Started Module.")
            ->expectsOutput('Marked 1 reminders as sent.')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'id' => $reminder->id,
            'status' => 'sent',
        ]);

        Notification::assertSentTo($learner, AssignmentReminderNotification::class);
    }

    public function test_assignment_sync_reminders_command_respects_inactive_nudge_cooldown(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Cooldown Nudge Module',
            'description' => 'Cooldown target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 30,
            'started_at' => now()->subDays(14),
            'last_activity_at' => now()->subDays(8),
        ]);

        $existing = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'inactive_nudge',
            'due_on' => now()->subDay()->toDateString(),
            'status' => 'sent',
            'sent_at' => now()->subDay(),
        ]);
        $existing->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->saveQuietly();

        $this->artisan('assignments:sync-reminders')
            ->expectsOutput('Synced 0 reminder records.')
            ->assertSuccessful();

        $this->assertSame(
            1,
            AssignmentReminder::query()
                ->where('user_id', $learner->id)
                ->where('learning_module_id', $module->id)
                ->where('reminder_type', 'inactive_nudge')
                ->count()
        );

        Carbon::setTestNow();
    }

    public function test_assignment_sync_reminders_command_creates_inactive_nudge_after_cooldown_window(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Cooldown Elapsed Nudge Module',
            'description' => 'Cooldown elapsed target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 30,
            'started_at' => now()->subDays(20),
            'last_activity_at' => now()->subDays(9),
        ]);

        $existing = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'inactive_nudge',
            'due_on' => now()->subDays(5)->toDateString(),
            'status' => 'sent',
            'sent_at' => now()->subDays(5),
        ]);
        $existing->forceFill([
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ])->saveQuietly();

        $this->artisan('assignments:sync-reminders')
            ->expectsOutput('Synced 1 reminder records.')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'inactive_nudge',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Carbon::setTestNow();
    }

    public function test_assignment_sync_reminders_command_builds_not_started_nudge_queue(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Not Started Safety Basics',
            'description' => 'Not started nudge target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
            'available_from' => now()->subDays(14),
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $this->artisan('assignments:sync-reminders')
            ->expectsOutput('Synced 1 reminder records.')
            ->assertSuccessful();

        $this->assertDatabaseHas('assignment_reminders', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'not_started_nudge',
            'status' => 'pending',
        ]);

        Carbon::setTestNow();
    }

    public function test_assignment_sync_reminders_command_respects_not_started_nudge_cooldown(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Not Started Cooldown Module',
            'description' => 'Cooldown target',
            'status' => 'published',
            'is_required' => true,
            'target_roles' => ['manager'],
            'available_from' => now()->subDays(15),
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $existing = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'not_started_nudge',
            'due_on' => now()->subDay()->toDateString(),
            'status' => 'sent',
            'sent_at' => now()->subDay(),
        ]);
        $existing->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->saveQuietly();

        $this->artisan('assignments:sync-reminders')
            ->expectsOutput('Synced 0 reminder records.')
            ->assertSuccessful();

        $this->assertSame(
            1,
            AssignmentReminder::query()
                ->where('user_id', $learner->id)
                ->where('learning_module_id', $module->id)
                ->where('reminder_type', 'not_started_nudge')
                ->count()
        );

        Carbon::setTestNow();
    }

    public function test_learner_can_view_and_mark_reminder_notification_read(): void
    {
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Unread Reminder Module',
            'description' => 'Reminder target',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);

        $reminder = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $learner->notify(new AssignmentReminderNotification($reminder->loadMissing('module')));

        $notification = $learner->notifications()->firstOrFail();

        $this->actingAs($learner)
            ->get('/app/reminders')
            ->assertOk();

        $this->actingAs($learner)
            ->patch("/app/reminders/{$notification->id}/read")
            ->assertRedirect('/app/reminders');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_learner_can_mark_all_reminders_read(): void
    {
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);
        $moduleA = LearningModule::query()->create([
            'title' => 'Unread Reminder A',
            'description' => 'Reminder target A',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);
        $moduleB = LearningModule::query()->create([
            'title' => 'Unread Reminder B',
            'description' => 'Reminder target B',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);

        $reminderA = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $moduleA->id,
            'reminder_type' => 'overdue',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);
        $reminderB = AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $moduleB->id,
            'reminder_type' => 'inactive_nudge',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $learner->notify(new AssignmentReminderNotification($reminderA->loadMissing('module')));
        $learner->notify(new AssignmentReminderNotification($reminderB->loadMissing('module')));

        $this->actingAs($learner)
            ->patch('/app/reminders/read-all')
            ->assertRedirect('/app/reminders');

        $this->assertSame(0, $learner->fresh()->unreadNotifications()->count());
    }

    public function test_learner_reminders_page_shows_reinforcement_section(): void
    {
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);

        $this->actingAs($learner)
            ->get('/app/reminders')
            ->assertOk()
            ->assertSee('Ongoing reinforcement + proof');
    }

    public function test_completed_module_creates_reinforcement_touchpoints_and_records_proof(): void
    {
        $learner = User::factory()->create(['name' => 'Reinforcement Learner', 'email' => 'reinforcement@example.com']);
        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Retention Essentials',
            'description' => 'Completed module for reinforcement',
            'status' => 'published',
            'difficulty' => 'beginner',
            'target_roles' => ['manager'],
            'source_type' => 'scorm',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $feedResponse = $this->actingAs($learner)->get('/app/feed');
        $feedResponse->assertOk();
        $this->assertTrue(
            $feedResponse->viewData('modules')->contains('title', 'Retention Essentials'),
            'Completed module should be in feed data'
        );

        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();

        $this->actingAs($learner)
            ->post("/app/reinforcement/{$touchpoint->id}/complete")
            ->assertRedirect();

        $this->assertDatabaseHas('reinforcement_touchpoints', [
            'id' => $touchpoint->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $learner->id,
            'event_type' => 'reinforcement_completed',
            'entity_type' => 'reinforcement_touchpoint',
            'entity_id' => $touchpoint->id,
        ]);

        $this->actingAs($learner)
            ->get('/app/reminders')
            ->assertOk()
            ->assertSee('Ongoing reinforcement + proof')
            ->assertSee('Completed 7-day follow-up for Retention Essentials.');
    }

    public function test_completed_module_uses_module_specific_reinforcement_intervals(): void
    {
        $learner = User::factory()->create(['name' => 'Custom Interval Learner', 'email' => 'custom-interval@example.com']);
        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Custom Cadence Essentials',
            'description' => 'Completed module for custom cadence',
            'status' => 'published',
            'difficulty' => 'beginner',
            'target_roles' => ['manager'],
            'source_type' => 'scorm',
            'reinforcement_intervals_days' => [5, 21],
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        app(ReinforcementService::class)->syncForUser($learner);

        $this->assertDatabaseHas('reinforcement_touchpoints', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'interval_days' => 5,
        ]);
        $this->assertDatabaseHas('reinforcement_touchpoints', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'interval_days' => 21,
        ]);
        $this->assertDatabaseMissing('reinforcement_touchpoints', [
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'interval_days' => 7,
        ]);
    }

    public function test_admin_can_view_compliance_report(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Privacy Annual Refresher',
            'description' => 'Required privacy refresher',
            'status' => 'published',
            'is_required' => true,
            'requires_acknowledgement' => true,
            'compliance_area' => 'data-privacy',
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        ModuleAcknowledgement::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'acknowledged_at' => now(),
        ]);

        AssignmentReminder::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'reminder_type' => 'overdue',
            'due_on' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/compliance')
            ->assertOk()
            ->assertSee('Compliance Report')
            ->assertSee('Course Completions');

        Carbon::setTestNow();
    }

    public function test_admin_compliance_report_shows_reinforcement_proof(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Compliance Reinforcement Learner', 'email' => 'compliance-reinforcement@example.com']);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ComplianceRoleRule::query()->create([
            'role' => 'manager',
            'compliance_area' => 'data-privacy',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Compliance Reinforcement Module',
            'description' => 'Compliance reinforcement proof',
            'status' => 'published',
            'source_type' => 'scorm',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $service = app(ReinforcementService::class);
        $service->syncForUser($learner);
        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();
        $service->completeForUser($touchpoint, $learner);

        $this->actingAs($admin)
            ->get('/app/admin/compliance')
            ->assertOk()
            ->assertSee('Compliance Report')
            ->assertSee('Recent Knowledge Check Results');
    }

    public function test_admin_compliance_report_can_filter_to_scorm_source_type(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Scorm Learner', 'email' => 'scorm-learner@example.com']);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $scormModule = LearningModule::query()->create([
            'title' => 'SCORM Compliance Demo',
            'description' => 'SCORM course',
            'status' => 'published',
            'is_required' => true,
            'source_type' => 'scorm',
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        $manualModule = LearningModule::query()->create([
            'title' => 'Manual Compliance Demo',
            'description' => 'Manual course',
            'status' => 'published',
            'is_required' => true,
            'source_type' => 'manual',
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $scormModule->id,
            'status' => 'in_progress',
            'percent_complete' => 45,
            'last_activity_at' => now(),
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $scormModule->id,
            'metadata' => ['score_raw' => 92, 'session_time' => '00:07:30', 'session_seconds' => 450],
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $manualModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/compliance?source_type=scorm')
            ->assertOk()
            ->assertSee('Compliance Report');

        $this->actingAs($admin)
            ->get('/app/admin/compliance/learners?source_type=scorm')
            ->assertOk()
            ->assertSee('Source Type')
            ->assertSee('SCORM Rows')
            ->assertSee('SCORM Compliance Demo')
            ->assertDontSee('Manual Compliance Demo')
            ->assertSee('92')
            ->assertSee('7m 30s')
            ->assertSee('scorm');
    }

    public function test_non_admin_cannot_view_compliance_report(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/compliance')
            ->assertForbidden();
    }

    public function test_admin_can_view_compliance_learner_matrix(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Matrix Learner', 'email' => 'matrix@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Matrix Required Module',
            'description' => 'Learner matrix target',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $service = app(ReinforcementService::class);
        $service->syncForUser($learner);
        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();
        $service->completeForUser($touchpoint, $learner);

        $this->actingAs($admin)
            ->get('/app/admin/compliance/learners')
            ->assertOk()
            ->assertSee('Compliance Learner Matrix')
            ->assertSee('Matrix Learner')
            ->assertSee('Matrix Required Module');
    }

    public function test_admin_compliance_learner_matrix_shows_reinforcement_proof(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Matrix Reinforcement Learner', 'email' => 'matrix-reinforcement@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Matrix Reinforcement Module',
            'description' => 'Learner matrix reinforcement target',
            'status' => 'published',
            'is_required' => true,
            'source_type' => 'scorm',
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ComplianceRoleRule::query()->create([
            'role' => 'manager',
            'compliance_area' => 'data-privacy',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $service = app(ReinforcementService::class);
        $service->syncForUser($learner);
        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();
        $service->completeForUser($touchpoint, $learner);

        $this->actingAs($admin)
            ->get('/app/admin/compliance/learners')
            ->assertOk()
            ->assertSee('Reinforcement Proof')
            ->assertSee('Latest Reinforcement Proof')
            ->assertSee('Matrix Reinforcement Learner')
            ->assertSee('Matrix Reinforcement Module')
            ->assertSee('Completed 7-day follow-up for Matrix Reinforcement Module.');
    }

    public function test_non_admin_cannot_view_compliance_learner_matrix(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/compliance/learners')
            ->assertForbidden();
    }

    public function test_admin_can_export_compliance_learner_matrix_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Matrix Export Learner', 'email' => 'matrix-export@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Matrix Export Module',
            'description' => 'Learner matrix export target',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(8),
            'last_activity_at' => now()->subDays(8),
        ]);

        $service = app(ReinforcementService::class);
        $service->syncForUser($learner);
        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();
        $service->completeForUser($touchpoint, $learner);

        $response = $this->actingAs($admin)
            ->get('/app/admin/compliance/learners/export?role=manager');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('latest_reinforcement_proof', $content);
        $this->assertStringContainsString('Matrix Export Learner', $content);
        $this->assertStringContainsString('Matrix Export Module', $content);
        $this->assertStringContainsString('Completed 7-day follow-up for Matrix Export Module.', $content);
    }

    public function test_admin_can_export_scorm_overview_csv_with_latest_completion_proof(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'SCORM Export Learner', 'email' => 'scorm-export@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'SCORM Export Module',
            'description' => 'SCORM export proof target',
            'status' => 'published',
            'is_required' => true,
            'source_type' => 'scorm',
            'compliance_area' => 'data-privacy',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'status' => 'completed',
                'score_raw' => 94,
                'session_time' => '00:06:00',
                'session_seconds' => 360,
                'percent_complete' => 100,
                'lesson_location' => 'assessment',
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/scorm/export');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('latest_completion_proof', $content);
        $this->assertStringContainsString('SCORM Export Module', $content);
        $this->assertStringContainsString('SCORM Export Learner', $content);
        $this->assertStringContainsString('assessment', $content);
    }

    public function test_admin_can_export_assignment_dashboard_csv_with_latest_scorm_proof(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Assignments Export Learner', 'email' => 'assignments-export@example.com']);
        $module = LearningModule::query()->create([
            'title' => 'Assignments Export SCORM',
            'description' => 'Assignments export proof target',
            'status' => 'published',
            'is_required' => true,
            'source_type' => 'scorm',
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'status' => 'completed',
                'score_raw' => 91,
                'session_time' => '00:05:00',
                'session_seconds' => 300,
                'percent_complete' => 100,
                'lesson_location' => 'wrap-up',
            ],
        ]);

        $service = app(ReinforcementService::class);
        $service->syncForUser($learner);
        $touchpoint = ReinforcementTouchpoint::query()
            ->where('user_id', $learner->id)
            ->where('learning_module_id', $module->id)
            ->where('interval_days', 7)
            ->firstOrFail();
        $service->completeForUser($touchpoint, $learner);

        $response = $this->actingAs($admin)
            ->get('/app/admin/assignments/export?focus=all');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('latest_scorm_proof', $content);
        $this->assertStringContainsString('latest_reinforcement_proof', $content);
        $this->assertStringContainsString('Assignments Export Learner', $content);
        $this->assertStringContainsString('Assignments Export SCORM', $content);
        $this->assertStringContainsString('score=91; session=5m; percent=100; location=wrap-up', $content);
        $this->assertStringContainsString('Completed 7-day follow-up for Assignments Export SCORM.', $content);
    }

    public function test_admin_can_filter_compliance_learner_matrix_by_status(): void
    {
        Carbon::setTestNow('2026-03-06 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Status Matrix Learner', 'email' => 'status-matrix@example.com']);

        $overdueModule = LearningModule::query()->create([
            'title' => 'Matrix Overdue Module',
            'description' => 'Overdue matrix target',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);
        $inProgressModule = LearningModule::query()->create([
            'title' => 'Matrix In Progress Module',
            'description' => 'In progress matrix target',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $overdueModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);
        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $inProgressModule->id,
            'status' => 'in_progress',
            'percent_complete' => 20,
            'last_activity_at' => now()->subDays(1),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/compliance/learners?status=overdue')
            ->assertOk()
            ->assertSee('Matrix Overdue Module')
            ->assertDontSee('Matrix In Progress Module');

        Carbon::setTestNow();
    }

    public function test_compliance_learner_matrix_export_respects_status_filter(): void
    {
        Carbon::setTestNow('2026-03-06 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Status Export Learner', 'email' => 'status-export@example.com']);

        $overdueModule = LearningModule::query()->create([
            'title' => 'Export Overdue Matrix Module',
            'description' => 'Overdue export target',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);
        $inProgressModule = LearningModule::query()->create([
            'title' => 'Export In Progress Matrix Module',
            'description' => 'In progress export target',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $overdueModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);
        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $inProgressModule->id,
            'status' => 'in_progress',
            'percent_complete' => 20,
            'last_activity_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/compliance/learners/export?status=in_progress');

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Export In Progress Matrix Module', $content);
        $this->assertStringNotContainsString('Export Overdue Matrix Module', $content);
        $this->assertStringContainsString('status', $content);

        Carbon::setTestNow();
    }

    public function test_admin_can_view_learning_events_report(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Events Learner']);
        $module = LearningModule::query()->create([
            'title' => 'Events Report Module',
            'description' => 'Event report target',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'test'],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/events')
            ->assertOk()
            ->assertSee('Learning Events Report')
            ->assertSee('module_viewed')
            ->assertSee('Events Report Module');
    }

    public function test_admin_can_filter_learning_events_report_by_entity_type(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Entity Filter Module',
            'description' => 'Entity filter report target',
            'status' => 'published',
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'module_viewed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'module-entity'],
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'preferences_saved',
            'entity_type' => 'user_preference',
            'entity_id' => $learner->id,
            'metadata' => ['source' => 'preference-entity'],
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/events?entity_type=learning_module')
            ->assertOk()
            ->assertSee('module-entity')
            ->assertDontSee('preference-entity');
    }

    public function test_non_admin_cannot_view_learning_events_report(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/events')
            ->assertForbidden();
    }

    public function test_admin_can_export_learning_events_report_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Events Export Module',
            'description' => 'Event export target',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);

        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'module_saved',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => ['source' => 'test'],
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'preferences_saved',
            'entity_type' => 'user_preference',
            'entity_id' => $learner->id,
            'metadata' => ['source' => 'preference-test'],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/events/export?event_type=module_saved&entity_type=learning_module');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('module_saved', $content);
        $this->assertStringContainsString('Events Export Module', $content);
        $this->assertStringNotContainsString('preferences_saved', $content);
    }

    public function test_admin_can_export_compliance_report_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Export Compliance Module',
            'description' => 'Exportable module',
            'status' => 'published',
            'is_required' => true,
            'source_type' => 'scorm',
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);
        LearningEvent::query()->create([
            'user_id' => $learner->id,
            'event_type' => 'scorm_runtime_committed',
            'entity_type' => 'learning_module',
            'entity_id' => $module->id,
            'metadata' => [
                'status' => 'completed',
                'score_raw' => 96,
                'session_time' => '00:09:00',
                'session_seconds' => 540,
                'percent_complete' => 100,
                'lesson_location' => 'certificate',
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/compliance/export');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Section,Label,Value', $content);
        $this->assertStringContainsString('summary,total_enrollments,', $content);
    }

    public function test_non_admin_cannot_export_compliance_report_csv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/app/admin/compliance/export')
            ->assertForbidden();
    }

    public function test_admin_can_filter_compliance_report_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->create(['name' => 'Manager Learner']);
        $specialist = User::factory()->create(['name' => 'Specialist Learner']);
        $module = LearningModule::query()->create([
            'title' => 'Filtered Compliance Module',
            'description' => 'Filter role check',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'ai-safety',
        ]);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);
        UserPreference::query()->create([
            'user_id' => $specialist->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $manager->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 50,
            'last_activity_at' => now(),
        ]);

        ModuleProgress::query()->create([
            'user_id' => $specialist->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 20,
            'last_activity_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/compliance?role=manager')
            ->assertOk()
            ->assertSee('manager')
            ->assertDontSee('<td class="px-5 py-3 font-medium text-gray-900">specialist</td>', false);
    }

    public function test_compliance_export_respects_role_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->create();
        $specialist = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Export Filter Module',
            'description' => 'Filter export check',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $manager->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);
        UserPreference::query()->create([
            'user_id' => $specialist->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $manager->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 30,
            'last_activity_at' => now(),
        ]);

        ModuleProgress::query()->create([
            'user_id' => $specialist->id,
            'learning_module_id' => $module->id,
            'status' => 'in_progress',
            'percent_complete' => 30,
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/compliance/export?role=manager');

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('role,manager', $content);
        $this->assertStringNotContainsString('role,specialist', $content);
    }

    public function test_admin_can_filter_compliance_report_by_compliance_area(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        $privacyModule = LearningModule::query()->create([
            'title' => 'Privacy Filter Module',
            'description' => 'privacy module',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);
        LearningModule::query()->create([
            'title' => 'Safety Filter Module',
            'description' => 'safety module',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'workplace-safety',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $privacyModule->id,
            'status' => 'in_progress',
            'percent_complete' => 20,
            'last_activity_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/compliance')
            ->assertOk()
            ->assertSee('Compliance Report');
    }

    public function test_compliance_export_respects_compliance_area_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        LearningModule::query()->create([
            'title' => 'Export Privacy Module',
            'description' => 'privacy module',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);
        LearningModule::query()->create([
            'title' => 'Export Safety Module',
            'description' => 'safety module',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'workplace-safety',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/compliance/export');

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Section,Label,Value', $content);
    }

    public function test_admin_can_filter_compliance_report_by_status(): void
    {
        Carbon::setTestNow('2026-03-06 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        $overdueModule = LearningModule::query()->create([
            'title' => 'Summary Overdue Module',
            'description' => 'summary overdue',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'compliance_area' => 'data-privacy',
        ]);
        $inProgressModule = LearningModule::query()->create([
            'title' => 'Summary In Progress Module',
            'description' => 'summary in progress',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $overdueModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);
        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $inProgressModule->id,
            'status' => 'in_progress',
            'percent_complete' => 40,
            'last_activity_at' => now()->subDays(1),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/compliance?status=in_progress')
            ->assertOk()
            ->assertSee('Compliance Report');

        Carbon::setTestNow();
    }

    public function test_compliance_export_respects_status_filter(): void
    {
        Carbon::setTestNow('2026-03-06 12:00:00');

        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create();

        $overdueModule = LearningModule::query()->create([
            'title' => 'Export Summary Overdue Module',
            'description' => 'export summary overdue',
            'status' => 'published',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'compliance_area' => 'data-privacy',
        ]);
        $inProgressModule = LearningModule::query()->create([
            'title' => 'Export Summary In Progress Module',
            'description' => 'export summary in progress',
            'status' => 'published',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $overdueModule->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);
        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $inProgressModule->id,
            'status' => 'in_progress',
            'percent_complete' => 40,
            'last_activity_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($admin)
            ->get('/app/admin/compliance/export?status=in_progress');

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Section,Label,Value', $content);

        Carbon::setTestNow();
    }

    public function test_module_revisions_are_recorded_for_create_update_and_status_change(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Module Admin']);

        $createResponse = $this->actingAs($admin)
            ->post('/app/admin/modules', [
                'title' => 'Revisioned Module',
                'description' => 'Initial draft',
                'topic' => 'policy',
                'difficulty' => 'beginner',
                'status' => 'draft',
                'compliance_area' => '',
                'refresh_interval_days' => '',
                'source_type' => 'manual',
                'source_uri' => '',
                'content_text' => 'v1',
                'target_roles' => 'manager',
                'prerequisite_ids' => [],
            ]);

        $module = LearningModule::query()->where('title', 'Revisioned Module')->firstOrFail();

        $createResponse->assertRedirect("/app/admin/modules/{$module->id}/edit");

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}", [
                'title' => 'Revisioned Module',
                'description' => 'Updated draft',
                'topic' => 'policy',
                'difficulty' => 'beginner',
                'status' => 'draft',
                'review_status' => 'approved',
                'compliance_area' => '',
                'refresh_interval_days' => '',
                'source_type' => 'manual',
                'source_uri' => '',
                'content_text' => 'v2',
                'target_roles' => 'manager',
                'prerequisite_ids' => [],
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit");

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}/status", [
                'status' => 'published',
            ])
            ->assertRedirect('/app/admin/modules');

        $this->assertDatabaseHas('learning_module_revisions', [
            'learning_module_id' => $module->id,
            'revision_number' => 1,
            'change_type' => 'created',
            'status' => 'draft',
            'user_id' => $admin->id,
        ]);

        $this->assertDatabaseHas('learning_module_revisions', [
            'learning_module_id' => $module->id,
            'revision_number' => 2,
            'change_type' => 'updated',
            'status' => 'draft',
            'user_id' => $admin->id,
        ]);

        $this->assertDatabaseHas('learning_module_revisions', [
            'learning_module_id' => $module->id,
            'revision_number' => 3,
            'change_type' => 'status_changed',
            'status' => 'published',
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/modules/{$module->id}/edit")
            ->assertOk()
            ->assertSee('Revision History')
            ->assertSee('r3')
            ->assertSee('status changed')
            ->assertSee('Module Admin');
    }

    public function test_admin_can_assign_owner_and_review_status_to_module(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create(['name' => 'Content Owner', 'email' => 'owner@example.com']);

        $module = LearningModule::query()->create([
            'title' => 'Governed Module',
            'description' => 'Needs owner and approval',
            'status' => 'draft',
            'difficulty' => 'beginner',
        ]);

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}", [
                'title' => 'Governed Module',
                'description' => 'Needs owner and approval',
                'topic' => 'policy',
                'difficulty' => 'beginner',
                'status' => 'draft',
                'owner_user_id' => $owner->id,
                'review_status' => 'approved',
                'compliance_area' => '',
                'refresh_interval_days' => '',
                'source_type' => 'manual',
                'source_uri' => '',
                'content_text' => '',
                'target_roles' => '',
                'prerequisite_ids' => [],
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit");

        $this->assertDatabaseHas('learning_modules', [
            'id' => $module->id,
            'owner_user_id' => $owner->id,
            'review_status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/modules')
            ->assertOk()
            ->assertSee('Owner')
            ->assertSee('Governed Module');
    }

    public function test_module_cannot_be_published_before_review_approval(): void
    {
        $admin = User::factory()->admin()->create();

        $module = LearningModule::query()->create([
            'title' => 'Unapproved Module',
            'description' => 'Still in review',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'review_status' => 'in_review',
        ]);

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}/status", [
                'status' => 'published',
            ])
            ->assertRedirect('/app/admin/modules');

        $this->assertDatabaseHas('learning_modules', [
            'id' => $module->id,
            'status' => 'draft',
            'review_status' => 'in_review',
        ]);
    }

    public function test_feed_hides_module_before_available_from_and_shows_after(): void
    {
        Carbon::setTestNow('2026-03-04 09:00:00');

        $user = User::factory()->create();
        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        LearningModule::query()->create([
            'title' => 'Scheduled Start Module',
            'description' => 'Becomes visible later',
            'status' => 'published',
            'difficulty' => 'beginner',
            'available_from' => now()->addDay(),
        ]);

        $beforeResponse = $this->actingAs($user)->get('/app/feed');
        $beforeResponse->assertOk();
        $this->assertFalse(
            $beforeResponse->viewData('modules')->contains('title', 'Scheduled Start Module'),
            'Module should not be in feed data before available_from'
        );

        Carbon::setTestNow('2026-03-05 10:00:00');

        $afterResponse = $this->actingAs($user)->get('/app/feed');
        $afterResponse->assertOk();
        $this->assertTrue(
            $afterResponse->viewData('modules')->contains('title', 'Scheduled Start Module'),
            'Module should be in feed data after available_from'
        );

        Carbon::setTestNow();
    }

    public function test_feed_hides_module_after_available_until(): void
    {
        Carbon::setTestNow('2026-03-06 09:00:00');

        $user = User::factory()->create();
        UserPreference::query()->create([
            'user_id' => $user->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        LearningModule::query()->create([
            'title' => 'Expired Window Module',
            'description' => 'No longer visible',
            'status' => 'published',
            'difficulty' => 'beginner',
            'available_until' => now()->subMinute(),
        ]);

        $this->actingAs($user)
            ->get('/app/feed')
            ->assertOk()
            ->assertDontSee('Expired Window Module');

        Carbon::setTestNow();
    }

    public function test_admin_can_save_module_availability_window(): void
    {
        $admin = User::factory()->admin()->create();
        $module = LearningModule::query()->create([
            'title' => 'Windowed Module',
            'description' => 'Window update target',
            'status' => 'draft',
            'difficulty' => 'beginner',
        ]);

        $this->actingAs($admin)
            ->patch("/app/admin/modules/{$module->id}", [
                'title' => 'Windowed Module',
                'description' => 'Window update target',
                'topic' => 'policy',
                'difficulty' => 'beginner',
                'status' => 'draft',
                'review_status' => 'draft',
                'compliance_area' => '',
                'refresh_interval_days' => '',
                'available_from' => '2026-03-10T09:30',
                'available_until' => '2026-03-20T18:00',
                'source_type' => 'manual',
                'source_uri' => '',
                'content_text' => '',
                'target_roles' => '',
                'prerequisite_ids' => [],
            ])
            ->assertRedirect("/app/admin/modules/{$module->id}/edit");

        $module = $module->fresh();
        $this->assertNotNull($module->available_from);
        $this->assertNotNull($module->available_until);
        $this->assertSame('2026-03-10 09:30', $module->available_from?->format('Y-m-d H:i'));
        $this->assertSame('2026-03-20 18:00', $module->available_until?->format('Y-m-d H:i'));
    }

    public function test_admin_module_pages_show_live_learner_visibility_impact(): void
    {
        $admin = User::factory()->admin()->create();

        $matchingLearner = User::factory()->create(['name' => 'Visible Manager']);
        UserPreference::query()->create([
            'user_id' => $matchingLearner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        $nonMatchingLearner = User::factory()->create(['name' => 'Hidden Specialist']);
        UserPreference::query()->create([
            'user_id' => $nonMatchingLearner->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Live Visibility Module',
            'description' => 'Visibility summary target',
            'status' => 'published',
            'difficulty' => 'beginner',
            'review_status' => 'approved',
            'is_required' => true,
            'compliance_area' => 'data-privacy',
            'target_roles' => ['manager'],
        ]);

        ComplianceRoleRule::query()->create([
            'role' => 'manager',
            'compliance_area' => 'data-privacy',
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/modules/{$module->id}/edit")
            ->assertOk()
            ->assertSee('Live Visibility Module');

        $this->actingAs($admin)
            ->get('/app/admin/modules')
            ->assertOk()
            ->assertSee('Live Visibility Module');
    }

    public function test_publish_ready_module_can_warn_when_no_live_audience_exists(): void
    {
        $admin = User::factory()->admin()->create();

        $nonMatchingLearner = User::factory()->create(['name' => 'Only Specialist']);
        UserPreference::query()->create([
            'user_id' => $nonMatchingLearner->id,
            'role' => 'specialist',
            'difficulty' => 'any',
        ]);

        $module = LearningModule::query()->create([
            'title' => 'Audience Gap Module',
            'description' => 'Ready but unseen',
            'status' => 'published',
            'difficulty' => 'beginner',
            'review_status' => 'approved',
            'target_roles' => ['manager'],
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/modules/{$module->id}/edit")
            ->assertOk()
            ->assertSee('Audience Gap Module');

        $this->actingAs($admin)
            ->get('/app/admin/modules')
            ->assertOk()
            ->assertSee('Live, No Audience')
            ->assertSee('Fix visibility')
            ->assertSee('no live audience');
    }

    public function test_module_index_fix_visibility_links_to_the_relevant_field(): void
    {
        $admin = User::factory()->admin()->create();

        $module = LearningModule::query()->create([
            'title' => 'Draft Visibility Fix Module',
            'description' => 'Needs publishing first',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'review_status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/modules')
            ->assertOk()
            ->assertSee("/app/admin/modules/{$module->id}/edit#field-status", false);
    }

    public function test_module_index_fix_publishing_links_to_scorm_panel_when_package_is_missing(): void
    {
        $admin = User::factory()->admin()->create();

        $module = LearningModule::query()->create([
            'title' => 'SCORM Publish Fix Module',
            'description' => 'Needs package upload',
            'status' => 'draft',
            'difficulty' => 'beginner',
            'review_status' => 'approved',
            'source_type' => 'scorm',
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/modules')
            ->assertOk()
            ->assertSee("/app/admin/modules/{$module->id}/edit#scorm-package", false)
            ->assertSee('Fix publishing');
    }

    public function test_admin_can_create_learning_path(): void
    {
        $admin = User::factory()->admin()->create();
        $first = LearningModule::query()->create([
            'title' => 'Path Step One',
            'description' => 'First module',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);
        $second = LearningModule::query()->create([
            'title' => 'Path Step Two',
            'description' => 'Second module',
            'status' => 'published',
            'difficulty' => 'intermediate',
        ]);

        $this->actingAs($admin)
            ->post('/app/admin/paths', [
                'title' => 'Manager Onboarding Path',
                'description' => 'Ordered manager journey',
                'status' => 'published',
                'target_roles' => 'manager',
                'module_ids' => [$first->id, $second->id],
                'step_delays' => [
                    $first->id => 0,
                    $second->id => 7,
                ],
            ])
            ->assertRedirect();

        $path = LearningPath::query()->where('title', 'Manager Onboarding Path')->firstOrFail();

        $this->assertSame(['manager'], $path->target_roles);
        $this->assertDatabaseHas('learning_path_steps', [
            'learning_path_id' => $path->id,
            'learning_module_id' => $first->id,
            'position' => 1,
            'delay_days' => 0,
        ]);
        $this->assertDatabaseHas('learning_path_steps', [
            'learning_path_id' => $path->id,
            'learning_module_id' => $second->id,
            'position' => 2,
            'delay_days' => 7,
        ]);
    }

    public function test_learner_can_view_role_based_learning_paths(): void
    {
        $learner = User::factory()->create();
        $first = LearningModule::query()->create([
            'title' => 'Manager Step One',
            'description' => 'First manager module',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);
        $second = LearningModule::query()->create([
            'title' => 'Manager Step Two',
            'description' => 'Second manager module',
            'status' => 'published',
            'difficulty' => 'intermediate',
        ]);

        $path = LearningPath::query()->create([
            'title' => 'Manager Path',
            'description' => 'Manager sequence',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);
        $path->steps()->createMany([
            ['learning_module_id' => $first->id, 'position' => 1],
            ['learning_module_id' => $second->id, 'position' => 2],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $first->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        $this->actingAs($learner)
            ->get('/app/paths')
            ->assertOk()
            ->assertSee('Manager Path')
            ->assertSee('1/2 complete')
            ->assertSee('Manager Step One')
            ->assertSee('Manager Step Two');
    }

    public function test_learner_paths_page_locks_delayed_step_until_window_opens(): void
    {
        Carbon::setTestNow('2026-03-06 09:00:00');

        $learner = User::factory()->create();
        $first = LearningModule::query()->create([
            'title' => 'Cadence Step One',
            'description' => 'First cadence module',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);
        $second = LearningModule::query()->create([
            'title' => 'Cadence Step Two',
            'description' => 'Second cadence module',
            'status' => 'published',
            'difficulty' => 'intermediate',
        ]);

        $path = LearningPath::query()->create([
            'title' => 'Cadence Path',
            'description' => 'Spaced delivery journey',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);
        $path->steps()->createMany([
            ['learning_module_id' => $first->id, 'position' => 1, 'delay_days' => 0],
            ['learning_module_id' => $second->id, 'position' => 2, 'delay_days' => 3],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $first->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        $this->actingAs($learner)
            ->get('/app/paths')
            ->assertOk()
            ->assertSee('Cadence Path')
            ->assertSee('until 2026-03-09 09:00')
            ->assertSee('Cadence Step Two');

        Carbon::setTestNow('2026-03-09 09:01:00');

        $this->actingAs($learner)
            ->get('/app/paths')
            ->assertOk()
            ->assertSee('Cadence Step Two')
            ->assertDontSee('until 2026-03-09 09:00');

        Carbon::setTestNow();
    }

    public function test_compliance_report_includes_learning_path_coverage(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person']);

        $first = LearningModule::query()->create([
            'title' => 'Path Compliance One',
            'description' => 'First module',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);
        $second = LearningModule::query()->create([
            'title' => 'Path Compliance Two',
            'description' => 'Second module',
            'status' => 'published',
            'difficulty' => 'intermediate',
        ]);

        $path = LearningPath::query()->create([
            'title' => 'Manager Compliance Path',
            'description' => 'Compliance sequence',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);
        $path->steps()->createMany([
            ['learning_module_id' => $first->id, 'position' => 1],
            ['learning_module_id' => $second->id, 'position' => 2],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $first->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/app/admin/compliance')
            ->assertOk()
            ->assertSee('Learning Paths')
            ->assertSee('Path Coverage by Role')
            ->assertSee('Manager Compliance Path')
            ->assertSee('manager')
            ->assertSee('50%');
    }

    public function test_admin_can_view_learning_path_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['name' => 'Learner Person', 'email' => 'learner@example.com']);

        $first = LearningModule::query()->create([
            'title' => 'Detail Step One',
            'description' => 'First detail step',
            'status' => 'published',
            'difficulty' => 'beginner',
        ]);
        $second = LearningModule::query()->create([
            'title' => 'Detail Step Two',
            'description' => 'Second detail step',
            'status' => 'published',
            'difficulty' => 'intermediate',
        ]);

        $path = LearningPath::query()->create([
            'title' => 'Detail Path',
            'description' => 'Path detail check',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);
        $path->steps()->createMany([
            ['learning_module_id' => $first->id, 'position' => 1],
            ['learning_module_id' => $second->id, 'position' => 2],
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $first->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $first->update([
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        $this->actingAs($admin)
            ->get("/app/admin/paths/{$path->id}")
            ->assertOk()
            ->assertSee('Learner Coverage')
            ->assertSee('Learner Person')
            ->assertSee('1/2')
            ->assertSee('1')
            ->assertSee('Detail Step Two');
    }

    public function test_learner_paths_page_shows_overdue_step_count(): void
    {
        Carbon::setTestNow('2026-03-03 12:00:00');

        $learner = User::factory()->create();
        $module = LearningModule::query()->create([
            'title' => 'Overdue Path Step',
            'description' => 'Required path step',
            'status' => 'published',
            'difficulty' => 'beginner',
            'is_required' => true,
            'refresh_interval_days' => 30,
            'target_roles' => ['manager'],
        ]);

        $path = LearningPath::query()->create([
            'title' => 'Urgency Path',
            'description' => 'Path with overdue step',
            'status' => 'published',
            'target_roles' => ['manager'],
        ]);
        $path->steps()->create([
            'learning_module_id' => $module->id,
            'position' => 1,
        ]);

        UserPreference::query()->create([
            'user_id' => $learner->id,
            'role' => 'manager',
            'difficulty' => 'any',
        ]);

        ModuleProgress::query()->create([
            'user_id' => $learner->id,
            'learning_module_id' => $module->id,
            'status' => 'completed',
            'percent_complete' => 100,
            'completed_at' => now()->subDays(31),
            'last_activity_at' => now()->subDays(31),
        ]);

        $this->actingAs($learner)
            ->get('/app/paths')
            ->assertOk()
            ->assertSee('Urgency Path')
            ->assertSee('1 overdue step(s)');

        Carbon::setTestNow();
    }
}
