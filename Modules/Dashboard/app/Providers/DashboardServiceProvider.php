<?php

namespace Modules\Dashboard\Providers;

use Modules\Dashboard\Services\PublicDashboardShareService;
use Modules\Dashboard\Services\PublicDashboardViewService;
use Modules\Dashboard\Services\WorkspaceAnalyticsService;
use Modules\Dashboard\Services\WorkspaceDashboardService;
use Modules\Dashboard\Services\WorkspaceMetricsAggregator;
use Nwidart\Modules\Support\ModuleServiceProvider;

class DashboardServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Dashboard';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'dashboard';

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

        $this->app->singleton(WorkspaceDashboardService::class);
        $this->app->singleton(WorkspaceAnalyticsService::class);
        $this->app->singleton(WorkspaceMetricsAggregator::class);
        $this->app->singleton(PublicDashboardShareService::class);
        $this->app->singleton(PublicDashboardViewService::class);
    }
}
