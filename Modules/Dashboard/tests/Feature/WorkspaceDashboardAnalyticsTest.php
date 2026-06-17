<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_dashboard_returns_analytics_with_period_comparison(): void
    {
        [$workspace, $admin, $asset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $asset->id,
            'platform_post_id' => 'post-historic-1',
            'post_type' => 'feed',
            'published_at' => now()->subDays(30),
            'content_preview' => 'Histórico',
            'raw_metrics' => ['reach' => 50],
            'captured_at' => now()->subDays(30),
        ]);

        OrganicPost::query()->create([
            'asset_id' => $asset->id,
            'platform_post_id' => 'post-current-1',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'content_preview' => 'Post reciente',
            'raw_metrics' => ['reach' => 500, 'impressions' => 800, 'engagement' => 40],
            'captured_at' => now(),
        ]);

        OrganicPost::query()->create([
            'asset_id' => $asset->id,
            'platform_post_id' => 'post-previous-1',
            'post_type' => 'feed',
            'published_at' => now()->subDays(10),
            'content_preview' => 'Post anterior',
            'raw_metrics' => ['reach' => 200, 'impressions' => 300, 'engagement' => 10],
            'captured_at' => now()->subDays(10),
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.dashboard', [$workspace, 'period' => '7d']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard/Workspace')
                ->has('analytics.kpis.reach', fn ($reach) => $reach
                    ->where('current', 500)
                    ->where('previous', 200)
                    ->where('comparable', true)
                    ->etc()
                )
                ->has('analytics.trends.daily_reach')
                ->has('analytics.top_posts.by_reach', 1)
            );
    }

    public function test_dashboard_custom_period_validation(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        $this->actingAs($admin)
            ->get(route('workspaces.dashboard', [
                'workspace' => $workspace,
                'period' => 'custom',
            ]))
            ->assertSessionHasErrors(['from', 'to']);
    }

    public function test_dashboard_filters_analytics_by_selected_asset(): void
    {
        [$workspace, $admin, $fbAsset] = $this->workspaceContext();

        $connection = PlatformConnection::query()->first();

        $igAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::InstagramAccount,
            'platform_asset_id' => 'ig-analytics',
            'name' => 'Instagram Analytics',
            'is_active' => true,
        ]);

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-fb-only',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'content_preview' => 'Solo FB',
            'raw_metrics' => ['reach' => 900],
            'captured_at' => now(),
        ]);

        OrganicPost::query()->create([
            'asset_id' => $igAsset->id,
            'platform_post_id' => 'post-ig-only',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'content_preview' => 'Solo IG',
            'raw_metrics' => ['reach' => 100],
            'captured_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.dashboard', [
                'workspace' => $workspace,
                'period' => '7d',
                'asset_id' => $igAsset->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('assetScope.selected_asset_id', $igAsset->id)
                ->where('analytics.kpis.reach.current', 100)
                ->has('assetScope.assets', 2)
            );

        $this->actingAs($admin)
            ->get(route('workspaces.dashboard', [$workspace, 'period' => '7d']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('assetScope.selected_asset_id', null)
                ->where('analytics.kpis.reach.current', 1000)
            );
    }

    public function test_dashboard_includes_paid_metrics_in_analytics(): void
    {
        [$workspace, $admin, $fbAsset] = $this->workspaceContext();

        $connection = PlatformConnection::query()->first();

        $adsAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::MetaAds,
            'platform_asset_id' => 'act-123',
            'name' => 'Ads Account',
            'is_active' => true,
        ]);

        $campaign = AdCampaign::query()->create([
            'asset_id' => $adsAsset->id,
            'platform_campaign_id' => 'camp-1',
            'name' => 'Campaign Test',
        ]);

        AdMetricDaily::query()->create([
            'campaign_id' => $campaign->id,
            'date' => now()->subDay()->toDateString(),
            'placement' => 'Instagram Feed',
            'spend' => 75.5,
            'reach' => 1200,
            'impressions' => 3000,
            'is_preliminary' => false,
            'captured_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.dashboard', [$workspace, 'period' => '7d']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('analytics.kpis.spend.current', 75.5)
                ->where('analytics.kpis.reach_paid', 1200)
            );
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset}
     */
    private function workspaceContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Analytics',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Analytics',
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
            'platform_asset_id' => 'page-analytics',
            'name' => 'Página Analytics',
            'is_active' => true,
        ]);

        return [$workspace, $admin, $asset];
    }
}
