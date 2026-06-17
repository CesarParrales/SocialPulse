<?php

namespace Modules\Connections\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\YouTube\YouTubeOAuthService;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class YouTubeConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        Config::set('connections.youtube.client_id', 'youtube-client-id');
        Config::set('connections.youtube.client_secret', 'youtube-client-secret');
    }

    public function test_agency_admin_can_start_youtube_oauth_redirect(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        $this->actingAs($admin)
            ->get(route('workspaces.connections.youtube.redirect', $workspace))
            ->assertRedirect();
    }

    public function test_youtube_callback_creates_encrypted_connection(): void
    {
        [$workspace, $admin] = $this->agencyAdminContext();

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'youtube-access-token',
                'refresh_token' => 'youtube-refresh-token',
                'expires_in' => 3600,
                'scope' => 'https://www.googleapis.com/auth/youtube.readonly',
                'token_type' => 'Bearer',
            ]),
            'www.googleapis.com/youtube/v3/channels*' => Http::response([
                'items' => [[
                    'id' => 'UC123456',
                    'snippet' => ['title' => 'Canal Demo'],
                    'contentDetails' => [
                        'relatedPlaylists' => ['uploads' => 'UU123456'],
                    ],
                ]],
            ]),
        ]);

        $state = app(YouTubeOAuthService::class)->authorizationUrl($workspace, $admin->id);
        parse_str(parse_url($state, PHP_URL_QUERY), $query);
        $stateToken = $query['state'];

        $this->actingAs($admin)
            ->get(route('connections.youtube.callback', [
                'code' => 'auth-code',
                'state' => $stateToken,
            ]))
            ->assertRedirect(route('workspaces.connections.index', $workspace));

        $connection = PlatformConnection::query()->first();

        $this->assertNotNull($connection);
        $this->assertSame(Platform::YouTube, $connection->platform);
        $this->assertSame('youtube-access-token', $connection->access_token);
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
            'name' => 'Cliente YouTube',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        return [$workspace, $admin];
    }
}
