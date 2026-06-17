<?php

namespace Modules\Connections\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\TikTok\TikTokOAuthService;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class TikTokConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        Config::set('connections.tiktok.client_key', 'test-client-key');
        Config::set('connections.tiktok.client_secret', 'test-client-secret');
    }

    public function test_agency_admin_can_start_tiktok_oauth_redirect(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        $this->actingAs($admin)
            ->get(route('workspaces.connections.tiktok.redirect', $workspace))
            ->assertRedirect();
    }

    public function test_tiktok_callback_creates_encrypted_connection(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        Http::fake([
            'open.tiktokapis.com/v2/oauth/token/*' => Http::response([
                'data' => [
                    'access_token' => 'tiktok-access-token',
                    'refresh_token' => 'tiktok-refresh-token',
                    'expires_in' => 86400,
                    'open_id' => 'open-id-123',
                    'scope' => 'user.info.basic,video.list',
                    'token_type' => 'Bearer',
                ],
            ]),
            'open.tiktokapis.com/v2/user/info/*' => Http::response([
                'data' => [
                    'user' => [
                        'open_id' => 'open-id-123',
                        'display_name' => 'Marca Alfa TikTok',
                        'username' => 'marcaalfa',
                    ],
                ],
            ]),
        ]);

        $state = app(TikTokOAuthService::class)->authorizationUrl($workspace, $admin->id);
        parse_str(parse_url($state, PHP_URL_QUERY), $query);
        $stateToken = $query['state'];

        $this->actingAs($admin)
            ->get(route('connections.tiktok.callback', [
                'code' => 'auth-code',
                'state' => $stateToken,
            ]))
            ->assertRedirect(route('workspaces.connections.index', $workspace));

        $connection = PlatformConnection::query()->first();

        $this->assertNotNull($connection);
        $this->assertSame(Platform::TikTok, $connection->platform);
        $this->assertSame('tiktok-access-token', $connection->access_token);
        $this->assertSame(ConnectionStatus::Active, $connection->status);
        $this->assertSame('open-id-123', $connection->external_account_id);
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
