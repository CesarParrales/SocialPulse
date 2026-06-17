<?php

namespace Modules\Analytics\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Analytics\Jobs\AggregateIndustryBenchmarksJob;
use Modules\Analytics\Models\CompetitorAccount;
use Modules\Analytics\Policies\CompetitorAccountPolicy;
use Modules\Analytics\Services\IndustryBenchmarkAggregatorService;
use Modules\Analytics\Services\IndustryBenchmarkResolver;
use Modules\Analytics\Services\WorkspaceBenchmarkService;
use Modules\Analytics\Services\WorkspaceComparisonService;
use Modules\Dashboard\Services\WorkspaceMetricsAggregator;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AnalyticsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Analytics';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'analytics';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(WorkspaceMetricsAggregator::class);
        $this->app->singleton(WorkspaceComparisonService::class);
        $this->app->singleton(IndustryBenchmarkAggregatorService::class);
        $this->app->singleton(IndustryBenchmarkResolver::class);
        $this->app->singleton(WorkspaceBenchmarkService::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::policy(CompetitorAccount::class, CompetitorAccountPolicy::class);
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->job(new AggregateIndustryBenchmarksJob)
            ->weeklyOn(1, '04:00')
            ->timezone('UTC')
            ->name('analytics:industry-benchmarks')
            ->withoutOverlapping();
    }
}
