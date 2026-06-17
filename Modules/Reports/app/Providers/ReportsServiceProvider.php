<?php

namespace Modules\Reports\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Reports\Contracts\ReportPdfGenerator;
use Modules\Reports\Services\BrowsershotReportPdfGenerator;
use Modules\Reports\Services\FakeReportPdfGenerator;
use Modules\Reports\Services\ReportDataAssembler;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ReportsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Reports';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'reports';

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

        $this->app->singleton(ReportDataAssembler::class);

        $this->app->bind(ReportPdfGenerator::class, function ($app) {
            if ($app->environment('testing')) {
                return new FakeReportPdfGenerator;
            }

            return new BrowsershotReportPdfGenerator;
        });
    }

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
