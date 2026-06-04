<?php

namespace Modules\Connections\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\Meta\MetaOAuthService;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class MetaConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        Config::set('connections.meta.app_id', 'test-app-id');
        Config::set('connections.meta.app_secret', 'test-secret');
        Config::set('connections.meta.api_version', 'v22.0');
    }

    public function test_agency_admin_can_start_meta_oauth_redirect(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        $this->actingAs($admin)
            ->get(route('workspaces.connections.meta.redirect', $workspace))
            ->assertRedirect();
    }

    public function test_meta_callback_creates_encrypted_connection(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        Http::fake([
            'graph.facebook.com/*' => Http::sequence()
                ->push(['access_token' => 'short-token', 'user_id' => '123'])
                ->push(['access_token' => 'long-token', 'expires_in' => 3600]),
        ]);

        $state = app(MetaOAuthService::class)->authorizationUrl($workspace, $admin->id);
        parse_str(parse_url($state, PHP_URL_QUERY), $query);
        $stateToken = $query['state'];

        $this->actingAs($admin)
            ->get(route('connections.meta.callback', [
                'code' => 'auth-code',
                'state' => $stateToken,
            ]))
            ->assertRedirect(route('workspaces.connections.index', $workspace));

        $connection = PlatformConnection::query()->first();

        $this->assertNotNull($connection);
        $this->assertSame(Platform::Meta, $connection->platform);
        $this->assertSame('long-token', $connection->access_token);
        $this->assertSame(ConnectionStatus::Active, $connection->status);
    }

    public function test_asset_cannot_be_monitored_in_two_workspaces(): void
    {
        [$workspaceA, $admin] = $this->agencyAdminContext();

        $workspaceB = Workspace::query()->create([
            'agency_id' => $workspaceA->agency_id,
            'name' => 'Cliente B',
            'timezone' => 'UTC',
        ]);

        $connectionA = PlatformConnection::query()->create([
            'workspace_id' => $workspaceA->id,
            'platform' => Platform::Meta,
            'access_token' => 'token-a',
            'status' => ConnectionStatus::Active,
        ]);

        $connectionB = PlatformConnection::query()->create([
            'workspace_id' => $workspaceB->id,
            'platform' => Platform::Meta,
            'access_token' => 'token-b',
            'status' => ConnectionStatus::Active,
        ]);

        ConnectedAsset::query()->create([
            'connection_id' => $connectionA->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => 'page-123',
            'name' => 'Página Demo',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('workspaces.connections.assets.sync', [$workspaceB, $connectionB]), [
                'assets' => [[
                    'type' => AssetType::FacebookPage->value,
                    'id' => 'page-123',
                    'name' => 'Página Demo',
                    'selected' => true,
                    'metadata' => [],
                ]],
            ])
            ->assertSessionHasErrors('assets');
    }

    /**
     * @return array{0: Workspace, 1: User}
     */
    private function agencyAdminContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente A',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        return [$workspace, $admin];
    }
}
