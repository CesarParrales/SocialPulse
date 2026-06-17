<?php

namespace Modules\Notifications\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Notifications\Jobs\DispatchTokenExpiryWarningsJob;
use Nwidart\Modules\Support\ModuleServiceProvider;

class NotificationsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Notifications';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'notifications';

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

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->job(new DispatchTokenExpiryWarningsJob)
            ->dailyAt('06:00')
            ->timezone('UTC')
            ->name('notifications:token-expiry-warnings')
            ->withoutOverlapping();
    }
}
