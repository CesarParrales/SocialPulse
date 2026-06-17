<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_workspace_show_includes_performance_snapshot(): void
    {
        [$workspace, $admin, $asset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $asset->id,
            'platform_post_id' => 'overview-post-1',
            'post_type' => 'feed',
            'published_at' => now()->subDay(),
            'content_preview' => 'Post overview',
            'raw_metrics' => ['reach' => 250, 'engagement' => 20],
            'captured_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.show', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Workspaces/Show')
                ->has('performanceSnapshot.assets', 1)
                ->where('performanceSnapshot.totals.reach', 250)
                ->where('performanceSnapshot.assets.0.id', $asset->id)
                ->where('performanceSnapshot.assets.0.reach', 250)
                ->where('performanceSnapshot.assets.0.has_data', true)
            );
    }

    public function test_workspace_show_performance_snapshot_empty_without_assets(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Overview',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Vacía',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $this->actingAs($admin)
            ->get(route('workspaces.show', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('performanceSnapshot.assets', 0)
                ->where('performanceSnapshot.totals.reach', 0)
            );
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset}
     */
    private function workspaceContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Overview',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Overview',
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
            'platform_asset_id' => 'page-overview',
            'name' => 'Página Overview',
            'is_active' => true,
        ]);

        return [$workspace, $admin, $asset];
    }
}
