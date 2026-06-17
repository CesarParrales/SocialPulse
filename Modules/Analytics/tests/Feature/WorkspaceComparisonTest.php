<?php

namespace Modules\Analytics\Tests\Feature;

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

class WorkspaceComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_organic_vs_paid_comparison(): void
    {
        [$workspace, $admin, $fbAsset, $adsAsset] = $this->workspaceWithAssets();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-org',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'raw_metrics' => ['reach' => 400, 'impressions' => 600],
            'captured_at' => now(),
        ]);

        $campaign = AdCampaign::query()->create([
            'asset_id' => $adsAsset->id,
            'platform_campaign_id' => 'camp-1',
            'name' => 'Campaign',
        ]);

        AdMetricDaily::query()->create([
            'campaign_id' => $campaign->id,
            'date' => now()->subDay()->toDateString(),
            'placement' => 'Instagram Feed',
            'spend' => 50,
            'reach' => 800,
            'impressions' => 1200,
            'is_preliminary' => false,
            'captured_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.compare', [
                'workspace' => $workspace,
                'type' => 'organic_vs_paid',
                'period' => '30d',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Analytics/Compare')
                ->where('comparison.mode', 'side_by_side')
                ->where('comparison.left_label', 'Orgánico')
                ->where('comparison.right_label', 'Pagado')
            );
    }

    public function test_facebook_vs_instagram_comparison(): void
    {
        [$workspace, $admin, $fbAsset] = $this->workspaceWithAssets();

        $connection = PlatformConnection::query()->first();

        $igAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::InstagramAccount,
            'platform_asset_id' => 'ig-1',
            'name' => '@marca',
            'is_active' => true,
        ]);

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'fb-post',
            'post_type' => 'feed',
            'published_at' => now()->subDay(),
            'raw_metrics' => ['reach' => 100],
            'captured_at' => now(),
        ]);

        OrganicPost::query()->create([
            'asset_id' => $igAsset->id,
            'platform_post_id' => 'ig-post',
            'post_type' => 'reel',
            'published_at' => now()->subDay(),
            'raw_metrics' => ['reach' => 300],
            'captured_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.compare', [
                'workspace' => $workspace,
                'type' => 'facebook_vs_instagram',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('comparison.rows')
            );
    }

    public function test_period_vs_period_requires_dates(): void
    {
        [$workspace, $admin] = $this->workspaceWithAssets();

        $this->actingAs($admin)
            ->get(route('workspaces.compare', [
                'workspace' => $workspace,
                'type' => 'period_vs_period',
            ]))
            ->assertSessionHasErrors(['left_start', 'left_end', 'right_start', 'right_end']);
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset, 3: ConnectedAsset}
     */
    private function workspaceWithAssets(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Compare',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Compare',
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
            'platform_asset_id' => 'page-1',
            'name' => 'Facebook',
            'is_active' => true,
        ]);

        $adsAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::MetaAds,
            'platform_asset_id' => 'act-1',
            'name' => 'Ads',
            'is_active' => true,
        ]);

        return [$workspace, $admin, $fbAsset, $adsAsset];
    }
}
