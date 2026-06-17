<?php

namespace Modules\Connections\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\MetaAuthMode;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\Meta\MetaGraphService;
use Modules\Connections\Services\Meta\MetaSystemUserService;
use Modules\Connections\Services\PlatformTokenRefreshService;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class MetaSystemUserConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        Config::set('connections.meta.api_version', 'v22.0');
        Config::set('connections.meta.system_user_access_token', 'system-user-token');
        Config::set('connections.meta.system_user_id', 'system-user-1');
        Config::set('connections.meta.business_id', 'business-123');
    }

    public function test_agency_admin_can_connect_meta_via_system_user(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['data' => [['id' => 'page-1']]]),
        ]);

        $this->actingAs($admin)
            ->post(route('workspaces.connections.meta.system-user', $workspace))
            ->assertRedirect(route('workspaces.connections.index', $workspace));

        $connection = PlatformConnection::query()->first();

        $this->assertNotNull($connection);
        $this->assertSame(MetaAuthMode::SystemUser, $connection->metaAuthMode());
        $this->assertNull($connection->token_expires_at);
        $this->assertSame('system-user-token', $connection->access_token);
    }

    public function test_system_user_discovers_business_owned_assets(): void
    {
        Http::fake([
            'graph.facebook.com/*/business-123/owned_pages*' => Http::response([
                'data' => [[
                    'id' => 'page-99',
                    'name' => 'Página BM',
                    'access_token' => 'page-token',
                    'instagram_business_account' => [
                        'id' => 'ig-99',
                        'username' => 'marca_ig',
                    ],
                ]],
            ]),
            'graph.facebook.com/*/business-123/owned_ad_accounts*' => Http::response([
                'data' => [[
                    'id' => 'act_123',
                    'name' => 'Ads BM',
                    'account_status' => 1,
                ]],
            ]),
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $this->agencyAdminContext()[0]->id,
            'platform' => Platform::Meta,
            'access_token' => 'system-user-token',
            'status' => ConnectionStatus::Active,
            'metadata' => [
                'auth_mode' => MetaAuthMode::SystemUser->value,
                'business_id' => 'business-123',
            ],
        ]);

        $assets = app(MetaGraphService::class)->discoverAssets($connection);

        $this->assertTrue($assets->contains(
            fn (array $asset) => $asset['type'] === AssetType::FacebookPage->value
                && $asset['id'] === 'page-99'
                && $asset['metadata']['page_access_token'] === 'page-token',
        ));
        $this->assertTrue($assets->contains(
            fn (array $asset) => $asset['type'] === AssetType::InstagramAccount->value
                && $asset['id'] === 'ig-99',
        ));
        $this->assertTrue($assets->contains(
            fn (array $asset) => $asset['type'] === AssetType::MetaAds->value
                && $asset['id'] === 'act_123',
        ));
    }

    public function test_system_user_connection_does_not_require_token_refresh(): void
    {
        $connection = PlatformConnection::query()->create([
            'workspace_id' => $this->agencyAdminContext()[0]->id,
            'platform' => Platform::Meta,
            'access_token' => 'system-user-token',
            'status' => ConnectionStatus::Active,
            'metadata' => [
                'auth_mode' => MetaAuthMode::SystemUser->value,
                'business_id' => 'business-123',
            ],
        ]);

        $service = app(PlatformTokenRefreshService::class);

        $this->assertFalse($service->needsRefresh($connection));
        $this->assertTrue($service->refresh($connection)->is($connection));
    }

    public function test_system_user_connect_fails_when_not_configured(): void
    {
        Config::set('connections.meta.system_user_access_token', null);

        [$workspace, $admin] = $this->agencyAdminContext();

        $this->actingAs($admin)
            ->post(route('workspaces.connections.meta.system-user', $workspace))
            ->assertStatus(503);
    }

    public function test_system_user_service_validates_business_access(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['error' => 'invalid'], 400),
        ]);

        [$workspace] = $this->agencyAdminContext();

        $this->expectException(RequestException::class);

        app(MetaSystemUserService::class)->connect($workspace);
    }

    /**
     * @return array{0: Workspace, 1: User}
     */
    private function agencyAdminContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia System User',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente SU',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        return [$workspace, $admin];
    }
}
