<?php

namespace Modules\Settings\Providers;

use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Settings\Services\PlatformIntegrationsService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SettingsServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Settings';

    protected string $nameLower = 'settings';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(PlatformIntegrationsService::class);
        $this->app->singleton(IntegrationConfigResolver::class);
    }
}
