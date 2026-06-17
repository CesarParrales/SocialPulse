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
use Modules\Ingestion\Jobs\DispatchOrganicInstagramDailyJob;
use Modules\Ingestion\Jobs\DispatchStoriesWatcherJob;
use Modules\Ingestion\Jobs\OrganicInstagramJob;
use Modules\Ingestion\Jobs\StoriesWatcherJob;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\StorySnapshot;
use Modules\Ingestion\Services\OrganicInstagramIngestionService;
use Modules\Ingestion\Services\StoriesWatcherService;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class OrganicInstagramIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('connections.meta.api_version', 'v22.0');
    }

    public function test_organic_instagram_ingestion_stores_media_and_followers(): void
    {
        $asset = $this->instagramAssetWithLinkedPage('ig-100', 'page-100');

        Http::fake([
            'graph.facebook.com/v22.0/ig-100/media*' => Http::response([
                'data' => [[
                    'id' => 'media-1',
                    'caption' => 'Post de prueba IG',
                    'timestamp' => '2026-06-02T10:00:00+0000',
                    'media_type' => 'IMAGE',
                    'media_url' => 'https://cdn.example/ig.jpg',
                ]],
            ]),
            'graph.facebook.com/v22.0/media-1/insights*' => Http::response([
                'data' => [
                    ['name' => 'reach', 'values' => [['value' => 500]]],
                    ['name' => 'likes', 'values' => [['value' => 42]]],
                ],
            ]),
            'graph.facebook.com/v22.0/ig-100?*' => Http::response([
                'followers_count' => 3200,
                'username' => 'marca_demo',
            ]),
        ]);

        $log = app(OrganicInstagramIngestionService::class)->ingestAsset($asset);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertSame(2, $log->records_ingested);

        $this->assertDatabaseHas('account_metrics_daily', [
            'asset_id' => $asset->id,
            'followers' => 3200,
        ]);

        $post = OrganicPost::query()->where('platform_post_id', 'media-1')->first();
        $this->assertNotNull($post);
        $this->assertSame('feed', $post->post_type);
    }

    public function test_organic_instagram_job_is_dispatched_for_active_accounts(): void
    {
        Queue::fake();

        $asset = $this->instagramAssetWithLinkedPage('ig-200', 'page-200');

        app(DispatchOrganicInstagramDailyJob::class)->handle();

        Queue::assertPushed(OrganicInstagramJob::class, fn (OrganicInstagramJob $job) => $job->assetId === $asset->id);
    }

    public function test_stories_watcher_skips_api_when_no_active_stories(): void
    {
        $asset = $this->instagramAssetWithLinkedPage('ig-300', 'page-300');

        Http::fake([
            'graph.facebook.com/v22.0/ig-300/stories*' => Http::response(['data' => []]),
        ]);

        $log = app(StoriesWatcherService::class)->watchAsset($asset);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertSame(0, $log->records_ingested);
        $this->assertSame(IngestionJobType::StoriesWatcher, $log->job_type);
        $this->assertSame(0, StorySnapshot::query()->count());
    }

    public function test_stories_watcher_captures_active_stories(): void
    {
        $asset = $this->instagramAssetWithLinkedPage('ig-400', 'page-400');

        Http::fake([
            'graph.facebook.com/v22.0/ig-400/stories*' => Http::response([
                'data' => [[
                    'id' => 'story-1',
                    'timestamp' => now()->subHours(2)->toIso8601String(),
                ]],
            ]),
            'graph.facebook.com/v22.0/story-1/insights*' => Http::response([
                'data' => [
                    ['name' => 'reach', 'values' => [['value' => 120]]],
                    ['name' => 'impressions', 'values' => [['value' => 180]]],
                    ['name' => 'exits', 'values' => [['value' => 5]]],
                ],
            ]),
        ]);

        $log = app(StoriesWatcherService::class)->watchAsset($asset);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertSame(1, $log->records_ingested);
        $this->assertDatabaseHas('stories_snapshots', [
            'asset_id' => $asset->id,
            'story_id' => 'story-1',
            'reach' => 120,
        ]);
    }

    public function test_stories_watcher_job_is_dispatched_for_active_accounts(): void
    {
        Queue::fake();

        $asset = $this->instagramAssetWithLinkedPage('ig-500', 'page-500');

        app(DispatchStoriesWatcherJob::class)->handle();

        Queue::assertPushed(StoriesWatcherJob::class, fn (StoriesWatcherJob $job) => $job->assetId === $asset->id);
    }

    private function instagramAssetWithLinkedPage(string $igId, string $pageId): ConnectedAsset
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia IG',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca IG',
            'timezone' => 'UTC',
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'user-token',
            'status' => ConnectionStatus::Active,
        ]);

        ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => $pageId,
            'name' => 'Página vinculada',
            'is_active' => true,
            'metadata' => ['page_access_token' => 'page-token-'.$pageId],
        ]);

        return ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::InstagramAccount,
            'platform_asset_id' => $igId,
            'name' => '@marca_demo',
            'is_active' => true,
            'metadata' => ['linked_page_id' => $pageId],
        ]);
    }
}
