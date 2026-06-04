<?php

namespace Modules\Connections\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Policies\PlatformConnectionPolicy;
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

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
