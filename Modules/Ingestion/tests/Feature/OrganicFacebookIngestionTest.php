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
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Jobs\DispatchOrganicFacebookDailyJob;
use Modules\Ingestion\Jobs\OrganicFacebookJob;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\OrganicPostMetricEntry;
use Modules\Ingestion\Services\OrganicFacebookIngestionService;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class OrganicFacebookIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('connections.meta.api_version', 'v22.0');
    }

    public function test_organic_facebook_ingestion_stores_posts_and_metrics(): void
    {
        $asset = $this->facebookPageAsset('page-100');

        Http::fake([
            'graph.facebook.com/v22.0/page-100/posts*' => Http::response([
                'data' => [[
                    'id' => 'page-100_post-1',
                    'message' => 'Hola desde SocialPulse',
                    'created_time' => '2026-06-01T12:00:00+0000',
                    'full_picture' => 'https://cdn.example/post.jpg',
                    'status_type' => 'added_video',
                ]],
            ]),
            'graph.facebook.com/v22.0/page-100_post-1/insights*' => Http::response([
                'data' => [
                    [
                        'name' => 'post_impressions',
                        'values' => [['value' => 320]],
                    ],
                    [
                        'name' => 'post_impressions_unique',
                        'values' => [['value' => 210]],
                    ],
                    [
                        'name' => 'post_engaged_users',
                        'values' => [['value' => 45]],
                    ],
                ],
            ]),
            'graph.facebook.com/v22.0/page-100?*' => Http::response([
                'fan_count' => 1500,
                'name' => 'Demo Page',
            ]),
        ]);

        $log = app(OrganicFacebookIngestionService::class)->ingestAsset($asset);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertSame(2, $log->records_ingested);

        $this->assertDatabaseHas('organic_metrics_daily', [
            'asset_id' => $asset->id,
            'metric_type' => 'fan_count',
            'value' => 1500,
        ]);

        $post = OrganicPost::query()->where('platform_post_id', 'page-100_post-1')->first();
        $this->assertNotNull($post);
        $this->assertSame('video', $post->post_type);
        $this->assertSame(210.0, (float) ($post->raw_metrics['reach'] ?? 0));

        $this->assertSame(1, OrganicPostMetricEntry::query()->where('organic_post_id', $post->id)->count());
    }

    public function test_organic_facebook_job_is_dispatched_for_active_pages(): void
    {
        Queue::fake();

        $asset = $this->facebookPageAsset('page-200');

        app(DispatchOrganicFacebookDailyJob::class)->handle();

        Queue::assertPushed(OrganicFacebookJob::class, fn (OrganicFacebookJob $job) => $job->assetId === $asset->id);
    }

    public function test_ingestion_logs_error_when_page_token_is_missing(): void
    {
        $asset = $this->facebookPageAsset('page-300', pageAccessToken: null);

        $log = app(OrganicFacebookIngestionService::class)->ingestAsset($asset);

        $this->assertSame(IngestionStatus::Error, $log->status);
        $this->assertSame(IngestionJobType::OrganicFacebook, $log->job_type);
        $this->assertStringContainsString('page access token', strtolower($log->error_message ?? ''));
    }

    private function facebookPageAsset(string $pageId, ?string $pageAccessToken = 'page-token'): ConnectedAsset
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Ingestion',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Test',
            'timezone' => 'UTC',
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'user-token',
            'status' => ConnectionStatus::Active,
        ]);

        return ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => $pageId,
            'name' => 'Página Facebook',
            'is_active' => true,
            'metadata' => $pageAccessToken !== null
                ? ['page_access_token' => $pageAccessToken]
                : [],
        ]);
    }
}
