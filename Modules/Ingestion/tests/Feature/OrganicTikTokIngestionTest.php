<?php

namespace Modules\Ingestion\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Services\OrganicTikTokIngestionService;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class OrganicTikTokIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_organic_tiktok_ingestion_stores_videos(): void
    {
        $asset = $this->tiktokAccountAsset('open-id-900');

        Http::fake([
            'open.tiktokapis.com/v2/video/list/*' => Http::response([
                'data' => [
                    'videos' => [[
                        'id' => 'video-001',
                        'title' => 'Trend del mes',
                        'create_time' => 1717200000,
                        'cover_image_url' => 'https://cdn.example/cover.jpg',
                        'share_url' => 'https://www.tiktok.com/@marca/video/001',
                        'view_count' => 12000,
                        'like_count' => 850,
                        'comment_count' => 42,
                        'share_count' => 18,
                    ]],
                ],
            ]),
        ]);

        $log = app(OrganicTikTokIngestionService::class)->ingestAsset($asset);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertSame(IngestionJobType::OrganicTikTok, $log->job_type);
        $this->assertSame(1, $log->records_ingested);

        $post = OrganicPost::query()->first();

        $this->assertNotNull($post);
        $this->assertSame('video-001', $post->platform_post_id);
        $this->assertSame(12000, (int) $post->raw_metrics['views']);
    }

    private function tiktokAccountAsset(string $openId): ConnectedAsset
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente TikTok',
            'timezone' => 'UTC',
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::TikTok,
            'access_token' => 'tiktok-token',
            'refresh_token' => 'tiktok-refresh',
            'token_expires_at' => now()->addDay(),
            'status' => ConnectionStatus::Active,
            'external_account_id' => $openId,
            'external_account_name' => 'Marca TikTok',
        ]);

        return ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::TikTokAccount,
            'platform_asset_id' => $openId,
            'name' => 'Marca TikTok',
            'is_active' => true,
            'metadata' => [],
        ]);
    }
}
