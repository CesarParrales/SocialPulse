<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Dashboard\Services\PublicDashboardShareService;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class PublicDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_public_dashboard_accessible_with_valid_token(): void
    {
        [$workspace, , $fbAsset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'public-post',
            'post_type' => 'feed',
            'published_at' => now()->subDay(),
            'raw_metrics' => ['reach' => 250, 'impressions' => 300],
            'captured_at' => now(),
        ]);

        $share = app(PublicDashboardShareService::class);
        $share->enable($workspace);
        $workspace->refresh();

        $this->get(route('public.dashboard', ['token' => $workspace->public_dashboard_token]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/Public')
                ->where('workspace.name', $workspace->name)
                ->has('analytics.kpis')
                ->missing('ingestionHealth')
            );
    }

    public function test_public_dashboard_returns_404_when_disabled(): void
    {
        [$workspace] = $this->workspaceContext();

        app(PublicDashboardShareService::class)->enable($workspace);
        $workspace->refresh();
        $token = $workspace->public_dashboard_token;

        app(PublicDashboardShareService::class)->disable($workspace);

        $this->get(route('public.dashboard', ['token' => $token]))
            ->assertNotFound();
    }

    public function test_agency_admin_can_enable_public_dashboard_link(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        $this->actingAs($admin)
            ->post(route('workspaces.public-dashboard.enable', $workspace))
            ->assertRedirect();

        $workspace->refresh();

        $this->assertTrue($workspace->isPublicDashboardEnabled());
        $this->assertNotNull($workspace->public_dashboard_token);
    }

    public function test_regenerate_invalidates_previous_token(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        $share = app(PublicDashboardShareService::class);
        $share->enable($workspace);
        $workspace->refresh();
        $oldToken = $workspace->public_dashboard_token;

        $this->actingAs($admin)
            ->post(route('workspaces.public-dashboard.regenerate', $workspace))
            ->assertRedirect();

        $workspace->refresh();

        $this->assertNotSame($oldToken, $workspace->public_dashboard_token);

        $this->get(route('public.dashboard', ['token' => $oldToken]))
            ->assertNotFound();

        $this->get(route('public.dashboard', ['token' => $workspace->public_dashboard_token]))
            ->assertOk();
    }

    public function test_workspace_settings_exposes_public_dashboard_state(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        app(PublicDashboardShareService::class)->enable($workspace);

        $this->actingAs($admin)
            ->get(route('settings.workspace.edit', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('publicDashboard.enabled', true)
                ->where('publicDashboard.url', fn ($url) => $url !== null)
            );
    }

    public function test_operator_cannot_manage_public_dashboard(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Op',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Op',
            'timezone' => 'UTC',
        ]);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);

        $this->actingAs($operator)
            ->post(route('workspaces.public-dashboard.enable', $workspace))
            ->assertForbidden();
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset}
     */
    private function workspaceContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Public',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Pública',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'token',
            'status' => ConnectionStatus::Active,
        ]);

        $fbAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => 'page-public',
            'name' => 'Facebook',
            'is_active' => true,
        ]);

        return [$workspace, $admin, $fbAsset];
    }
}
