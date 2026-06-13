<?php

namespace App\Providers;

use App\Contracts\PdfTextExtractor;
use App\Contracts\MentorProvider;
use App\Contracts\FeedRanker;
use App\Models\ModuleProgress;
use App\Models\UserPreference;
use App\Policies\ModuleProgressPolicy;
use App\Policies\UserPreferencePolicy;
use App\Providers\Mentor\LocalHeuristicMentorProvider;
use App\Providers\Ranking\DeterministicFeedRanker;
use App\Providers\Ranking\ExternalAiFeedRanker;
use App\Providers\Ranking\LocalAiFeedRanker;
use App\Services\Pdf\SmalotPdfTextExtractor;
use App\Services\RankingSettingsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PdfTextExtractor::class, SmalotPdfTextExtractor::class);
        $this->app->bind(MentorProvider::class, function () {
            return match ((string) config('mentor.provider', 'local')) {
                'local' => app(LocalHeuristicMentorProvider::class),
                default => app(LocalHeuristicMentorProvider::class),
            };
        });
        $this->app->bind(FeedRanker::class, function () {
            $provider = (string) app(RankingSettingsService::class)->get('provider', config('ranking.provider', 'deterministic'));

            return match ($provider) {
                'local_ai' => app(LocalAiFeedRanker::class),
                'external_ai' => app(ExternalAiFeedRanker::class),
                default => app(DeterministicFeedRanker::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ModuleProgress::class, ModuleProgressPolicy::class);
        Gate::policy(UserPreference::class, UserPreferencePolicy::class);
        // Coarse: any admin role (site_admin, trustee, slt_manager, manager)
        Gate::define('admin-access', fn ($user) => $user->hasAdminAccess());

        // Write: site_admin only (create/edit/delete system resources)
        Gate::define('admin-write', fn ($user) => $user->isSiteAdmin());

        // Trustee+: unrestricted cross-location/cross-team view (site_admin, trustee)
        Gate::define('trustee-view', fn ($user) => $user->hasUnrestrictedView());

        // Admin read: view users, reports without write (site_admin, trustee, slt_manager)
        Gate::define('admin-read', fn ($user) => in_array($user->system_role, ['site_admin', 'trustee', 'slt_manager'], true));

        // Team management: assign users to teams, manage team assignments (site_admin, slt_manager)
        Gate::define('manage-teams', fn ($user) => $user->canManageTeamAssignments());

        // Assignment management: create/edit assignment rules and waivers (site_admin, slt_manager)
        Gate::define('manage-assignments', fn ($user) => in_array($user->system_role, ['site_admin', 'slt_manager'], true));
    }
}
