<?php

namespace Modules\Workspaces\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Workspaces\Models\Workspace;
use Modules\Workspaces\Policies\WorkspacePolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class WorkspacesServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Workspaces';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'workspaces';

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Workspace::class, WorkspacePolicy::class);
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
