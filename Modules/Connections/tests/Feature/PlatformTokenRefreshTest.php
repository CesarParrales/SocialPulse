<?php

namespace Modules\Connections\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Jobs\RefreshPlatformTokenJob;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\PlatformTokenRefreshService;
use Modules\Notifications\Notifications\ConnectionTokenRefreshFailedNotification;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class PlatformTokenRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        Config::set('connections.meta.app_id', 'test-app-id');
        Config::set('connections.meta.app_secret', 'test-secret');
        Config::set('connections.meta.api_version', 'v22.0');
        Config::set('connections.google.client_id', 'google-client');
        Config::set('connections.google.client_secret', 'google-secret');
        Config::set('connections.tiktok.client_key', 'tiktok-key');
        Config::set('connections.tiktok.client_secret', 'tiktok-secret');
        Config::set('connections.youtube.client_id', 'youtube-client-id');
        Config::set('connections.youtube.client_secret', 'youtube-client-secret');
    }

    public function test_meta_token_is_refreshed_before_expiry(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'access_token' => 'refreshed-meta-token',
                'expires_in' => 5184000,
            ]),
        ]);

        $connection = $this->metaConnectionExpiringSoon();

        app(PlatformTokenRefreshService::class)->refresh($connection);

        $connection->refresh();

        $this->assertSame('refreshed-meta-token', $connection->access_token);
        $this->assertSame(ConnectionStatus::Active, $connection->status);
        $this->assertTrue($connection->token_expires_at->isFuture());
    }

    public function test_google_token_is_refreshed_with_refresh_token(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'refreshed-google-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $this->workspace()->id,
            'platform' => Platform::Google,
            'access_token' => 'old-google-token',
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addDay(),
            'status' => ConnectionStatus::Active,
        ]);

        app(PlatformTokenRefreshService::class)->refresh($connection);

        $connection->refresh();

        $this->assertSame('refreshed-google-token', $connection->access_token);
        $this->assertSame(ConnectionStatus::Active, $connection->status);
    }

    public function test_tiktok_token_is_refreshed_with_refresh_token(): void
    {
        Http::fake([
            'open.tiktokapis.com/v2/oauth/token/*' => Http::response([
                'data' => [
                    'access_token' => 'refreshed-tiktok-token',
                    'refresh_token' => 'new-refresh-token',
                    'expires_in' => 86400,
                ],
            ]),
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $this->workspace()->id,
            'platform' => Platform::TikTok,
            'access_token' => 'old-tiktok-token',
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addDay(),
            'status' => ConnectionStatus::Active,
        ]);

        app(PlatformTokenRefreshService::class)->refresh($connection);

        $connection->refresh();

        $this->assertSame('refreshed-tiktok-token', $connection->access_token);
        $this->assertSame(ConnectionStatus::Active, $connection->status);
    }

    public function test_youtube_token_is_refreshed_with_refresh_token(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'refreshed-youtube-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $this->workspace()->id,
            'platform' => Platform::YouTube,
            'access_token' => 'old-youtube-token',
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addDay(),
            'status' => ConnectionStatus::Active,
        ]);

        app(PlatformTokenRefreshService::class)->refresh($connection);

        $connection->refresh();

        $this->assertSame('refreshed-youtube-token', $connection->access_token);
        $this->assertSame(ConnectionStatus::Active, $connection->status);
    }

    public function test_failed_refresh_notifies_agency_admin(): void
    {
        Notification::fake();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['error' => 'invalid'], 400),
        ]);

        $agency = Agency::query()->create([
            'name' => 'Agencia Notify',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Notify',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'expiring-meta-token',
            'token_expires_at' => now()->addDays(3),
            'status' => ConnectionStatus::Active,
        ]);

        $job = new RefreshPlatformTokenJob($connection->id);

        try {
            $job->handle(app(PlatformTokenRefreshService::class));
        } catch (\Throwable) {
            // expected
        }

        $job->failed(new \RuntimeException('Token inválido'));

        Notification::assertSentTo(
            $admin,
            ConnectionTokenRefreshFailedNotification::class,
        );

        $this->assertSame(ConnectionStatus::Expired, $connection->fresh()->status);
    }

    private function metaConnectionExpiringSoon(): PlatformConnection
    {
        return PlatformConnection::query()->create([
            'workspace_id' => $this->workspace()->id,
            'platform' => Platform::Meta,
            'access_token' => 'expiring-meta-token',
            'token_expires_at' => now()->addDays(3),
            'status' => ConnectionStatus::Active,
        ]);
    }

    private function workspace(): Workspace
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Tokens',
            'plan' => AgencyPlan::Agency,
        ]);

        return Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Tokens',
            'timezone' => 'UTC',
        ]);
    }
}
