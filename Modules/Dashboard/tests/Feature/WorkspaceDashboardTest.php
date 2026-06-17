<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_agency_admin_can_view_workspace_dashboard_with_organic_data(): void
    {
        [$workspace, $admin, $asset] = $this->workspaceWithOrganicData();

        OrganicPost::query()->create([
            'asset_id' => $asset->id,
            'platform_post_id' => 'post-dash-1',
            'post_type' => 'feed',
            'published_at' => now()->subDay(),
            'content_preview' => 'Contenido demo',
            'raw_metrics' => ['reach' => 100],
            'captured_at' => now(),
        ]);

        IngestionLog::query()->create([
            'asset_id' => $asset->id,
            'job_type' => IngestionJobType::OrganicFacebook,
            'status' => IngestionStatus::Success,
            'records_ingested' => 3,
            'executed_at' => now(),
            'duration_ms' => 120,
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.dashboard', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/Workspace')
                ->has('analytics.kpis')
                ->has('ingestionHealth', 1)
                ->has('recentPosts')
                ->has('activeStories')
            );
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset}
     */
    private function workspaceWithOrganicData(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Dash',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Dash',
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

        $asset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => 'page-dash',
            'name' => 'Página Dash',
            'is_active' => true,
            'metadata' => ['page_access_token' => 'page-token'],
        ]);

        return [$workspace, $admin, $asset];
    }
}
