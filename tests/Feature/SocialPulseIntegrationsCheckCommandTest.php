<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Tests\TestCase;

class SocialPulseIntegrationsCheckCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_integrations_check_lists_platforms(): void
    {
        Config::set('connections.tiktok.client_key', 'tiktok-key');
        Config::set('connections.tiktok.client_secret', 'tiktok-secret');

        $this->artisan('socialpulse:integrations:check')
            ->assertSuccessful()
            ->expectsOutputToContain('TikTok');
    }

    public function test_integrations_check_fails_when_required_platform_missing(): void
    {
        $this->artisan('socialpulse:integrations:check', ['--require' => 'meta,tiktok'])
            ->assertFailed()
            ->expectsOutputToContain('Faltan:');
    }

    public function test_integrations_check_passes_when_required_platforms_configured(): void
    {
        Config::set('connections.meta.app_id', 'meta-app');
        Config::set('connections.meta.app_secret', 'meta-secret');
        Config::set('connections.tiktok.client_key', 'tiktok-key');
        Config::set('connections.tiktok.client_secret', 'tiktok-secret');

        $this->artisan('socialpulse:integrations:check', ['--require' => 'meta,tiktok'])
            ->assertSuccessful()
            ->expectsOutputToContain('Todas las integraciones requeridas están configuradas.');
    }
}
