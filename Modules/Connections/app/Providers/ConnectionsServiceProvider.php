<?php

namespace Modules\Connections\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Connections\Jobs\DispatchTokenRefreshJob;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Policies\PlatformConnectionPolicy;
use Modules\Connections\Services\Google\GoogleTokenRefreshService;
use Modules\Connections\Services\PlatformTokenRefreshService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ConnectionsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Connections';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'connections';

    public function boot(): void
    {
        parent::boot();

        Gate::policy(PlatformConnection::class, PlatformConnectionPolicy::class);
    }

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

        $this->app->singleton(GoogleTokenRefreshService::class);
        $this->app->singleton(PlatformTokenRefreshService::class);
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->job(new DispatchTokenRefreshJob)
            ->dailyAt('05:00')
            ->timezone('UTC')
            ->name('connections:token-refresh')
            ->withoutOverlapping();
    }
}
