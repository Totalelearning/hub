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
        Gate::define('admin-access', fn ($user) => $user->hasAdminAccess());
        Gate::define('admin-write', fn ($user) => $user->isSiteAdmin());
        Gate::define('manage-teams', fn ($user) => $user->canManageTeamAssignments());
    }
}
