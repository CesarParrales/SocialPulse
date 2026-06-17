<?php

namespace Modules\Ingestion\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Jobs\DispatchPaidMetaDailyJob;
use Modules\Ingestion\Jobs\PaidMetaJob;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Services\PaidGoogleIngestionService;
use Modules\Ingestion\Services\PaidMetaIngestionService;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class PaidIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('connections.meta.api_version', 'v22.0');
        Config::set('connections.google.developer_token', 'dev-token');
    }

    public function test_paid_meta_ingestion_stores_campaign_metrics_by_placement(): void
    {
        $asset = $this->metaAdsAsset('123456789');

        Http::fake([
            'graph.facebook.com/v22.0/act_123456789/insights*' => Http::response([
                'data' => [[
                    'campaign_id' => 'camp-1',
                    'campaign_name' => 'Awareness Q2',
                    'publisher_platform' => 'instagram',
                    'platform_position' => 'feed',
                    'spend' => '45.50',
                    'reach' => '1200',
                    'impressions' => '3500',
                    'clicks' => '88',
                    'ctr' => '2.514285',
                    'cpm' => '13.000000',
                    'cpc' => '0.517045',
                ]],
            ]),
        ]);

        $log = app(PaidMetaIngestionService::class)->ingestAsset($asset, preliminary: false);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertSame(1, $log->records_ingested);

        $campaign = AdCampaign::query()->where('platform_campaign_id', 'camp-1')->first();
        $this->assertNotNull($campaign);
        $this->assertSame('Awareness Q2', $campaign->name);

        $this->assertDatabaseHas('ad_metrics_daily', [
            'campaign_id' => $campaign->id,
            'placement' => 'Instagram Feed',
            'is_preliminary' => false,
            'spend' => 45.5,
        ]);
    }

    public function test_paid_meta_preliminary_creates_append_rows(): void
    {
        $asset = $this->metaAdsAsset('999');

        Http::fake([
            'graph.facebook.com/v22.0/act_999/insights*' => Http::response([
                'data' => [[
                    'campaign_id' => 'camp-pre',
                    'campaign_name' => 'Intraday',
                    'publisher_platform' => 'facebook',
                    'platform_position' => 'feed',
                    'spend' => '10.00',
                    'impressions' => '500',
                ]],
            ]),
        ]);

        app(PaidMetaIngestionService::class)->ingestAsset($asset, preliminary: true);
        app(PaidMetaIngestionService::class)->ingestAsset($asset, preliminary: true);

        $campaign = AdCampaign::query()->where('platform_campaign_id', 'camp-pre')->first();
        $this->assertSame(2, AdMetricDaily::query()->where('campaign_id', $campaign->id)->where('is_preliminary', true)->count());
    }

    public function test_paid_google_ingestion_stores_campaign_metrics(): void
    {
        $asset = $this->googleAdsAsset('1234567890');

        Http::fake([
            'googleads.googleapis.com/*' => Http::response([[
                'results' => [[
                    'campaign' => [
                        'id' => '555',
                        'name' => 'Search Brand',
                        'status' => 'ENABLED',
                    ],
                    'metrics' => [
                        'impressions' => 10000,
                        'clicks' => 250,
                        'costMicros' => 125000000,
                        'conversions' => 12,
                        'conversionsValue' => 480,
                        'ctr' => 0.025,
                        'averageCpc' => 500000,
                    ],
                ]],
            ]]),
        ]);

        $log = app(PaidGoogleIngestionService::class)->ingestAsset($asset, preliminary: false);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertDatabaseHas('ad_campaigns', [
            'asset_id' => $asset->id,
            'platform_campaign_id' => '555',
            'name' => 'Search Brand',
        ]);
        $this->assertDatabaseHas('ad_metrics_daily', [
            'placement' => 'google_search',
            'spend' => 125,
            'is_preliminary' => false,
        ]);
    }

    public function test_paid_meta_daily_job_is_dispatched(): void
    {
        Queue::fake();

        $asset = $this->metaAdsAsset('777');

        app(DispatchPaidMetaDailyJob::class)->handle();

        Queue::assertPushed(PaidMetaJob::class, fn (PaidMetaJob $job) => $job->assetId === $asset->id && $job->preliminary === false);
    }

    private function metaAdsAsset(string $adAccountId): ConnectedAsset
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Paid',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente Paid',
            'timezone' => 'UTC',
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'meta-user-token',
            'status' => ConnectionStatus::Active,
        ]);

        return ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::MetaAds,
            'platform_asset_id' => $adAccountId,
            'name' => 'Ad Account Demo',
            'is_active' => true,
        ]);
    }

    private function googleAdsAsset(string $customerId): ConnectedAsset
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Google',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente Google',
            'timezone' => 'UTC',
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Google,
            'access_token' => 'google-access-token',
            'status' => ConnectionStatus::Active,
        ]);

        return ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::GoogleAds,
            'platform_asset_id' => $customerId,
            'name' => 'Google Ads Demo',
            'is_active' => true,
        ]);
    }
}
