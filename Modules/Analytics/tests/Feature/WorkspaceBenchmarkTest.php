<?php

namespace Modules\Analytics\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Analytics\Support\BenchmarkRating;
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

class WorkspaceBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_benchmark_page_shows_internal_ratings(): void
    {
        [$workspace, $admin, $fbAsset, $adsAsset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-old',
            'post_type' => 'feed',
            'published_at' => now()->subDays(45),
            'raw_metrics' => ['reach' => 100, 'engagement' => 5],
            'captured_at' => now()->subDays(45),
        ]);

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-recent',
            'post_type' => 'feed',
            'published_at' => now()->subDays(3),
            'raw_metrics' => ['reach' => 500, 'engagement' => 50],
            'captured_at' => now(),
        ]);

        $campaign = AdCampaign::query()->create([
            'asset_id' => $adsAsset->id,
            'platform_campaign_id' => 'camp-bench',
            'name' => 'Bench Campaign',
        ]);

        AdMetricDaily::query()->create([
            'campaign_id' => $campaign->id,
            'date' => now()->subDays(40)->toDateString(),
            'placement' => 'Facebook Feed',
            'spend' => 100,
            'impressions' => 10000,
            'is_preliminary' => false,
            'captured_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.benchmarks', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Analytics/Benchmarks')
                ->where('benchmarks.has_baseline', true)
                ->where('benchmarks.industry_benchmark_available', false)
                ->has('benchmarks.metrics.engagement_rate')
                ->has('benchmarks.metrics.reach_per_post')
                ->has('benchmarks.metrics.cpm')
            );

        $this->assertDatabaseHas('benchmark_snapshots', [
            'workspace_id' => $workspace->id,
        ]);
    }

    public function test_benchmark_rating_marks_higher_engagement_as_good(): void
    {
        $rating = BenchmarkRating::rate(5.0, 2.0);

        $this->assertSame('good', $rating['status']);
        $this->assertSame(250.0, $rating['ratio_pct']);
    }

    public function test_benchmark_rating_marks_lower_cpm_as_good(): void
    {
        $rating = BenchmarkRating::rate(8.0, 10.0, higherIsBetter: false);

        $this->assertSame('good', $rating['status']);
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset, 3: ConnectedAsset}
     */
    private function workspaceContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Bench',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Bench',
            'timezone' => 'UTC',
            'industry_category' => 'retail',
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
            'platform_asset_id' => 'page-bench',
            'name' => 'Facebook',
            'is_active' => true,
        ]);

        $adsAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::MetaAds,
            'platform_asset_id' => 'act-bench',
            'name' => 'Ads',
            'is_active' => true,
        ]);

        return [$workspace, $admin, $fbAsset, $adsAsset];
    }
}
