<?php

namespace Modules\Connections\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\LinkedIn\LinkedInOAuthService;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class LinkedInConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        Config::set('connections.linkedin.client_id', 'linkedin-client-id');
        Config::set('connections.linkedin.client_secret', 'linkedin-client-secret');
    }

    public function test_agency_admin_can_start_linkedin_oauth_redirect(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        $this->actingAs($admin)
            ->get(route('workspaces.connections.linkedin.redirect', $workspace))
            ->assertRedirect();
    }

    public function test_linkedin_callback_creates_encrypted_connection(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        Http::fake([
            'www.linkedin.com/oauth/v2/accessToken' => Http::response([
                'access_token' => 'linkedin-access-token',
                'refresh_token' => 'linkedin-refresh-token',
                'expires_in' => 5184000,
                'scope' => 'r_organization_admin r_organization_social',
            ]),
        ]);

        $state = app(LinkedInOAuthService::class)->authorizationUrl($workspace, $admin->id);
        parse_str(parse_url($state, PHP_URL_QUERY), $query);
        $stateToken = $query['state'];

        $this->actingAs($admin)
            ->get(route('connections.linkedin.callback', [
                'code' => 'auth-code',
                'state' => $stateToken,
            ]))
            ->assertRedirect(route('workspaces.connections.index', $workspace));

        $connection = PlatformConnection::query()->first();

        $this->assertNotNull($connection);
        $this->assertSame(Platform::LinkedIn, $connection->platform);
        $this->assertSame('linkedin-access-token', $connection->access_token);
        $this->assertSame(ConnectionStatus::Active, $connection->status);
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
