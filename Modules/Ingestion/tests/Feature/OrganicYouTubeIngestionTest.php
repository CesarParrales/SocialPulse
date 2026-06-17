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
use Modules\Ingestion\Services\OrganicYouTubeIngestionService;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class OrganicYouTubeIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_organic_youtube_ingestion_stores_videos(): void
    {
        $asset = $this->youTubeChannelAsset('UC123456', 'UU123456');

        Http::fake([
            'www.googleapis.com/youtube/v3/playlistItems*' => Http::response([
                'items' => [[
                    'contentDetails' => ['videoId' => 'abc123xyz'],
                    'snippet' => [
                        'publishedAt' => '2024-06-01T12:00:00Z',
                        'title' => 'Video demo',
                        'thumbnails' => [
                            'default' => ['url' => 'https://i.ytimg.com/vi/abc123xyz/default.jpg'],
                        ],
                    ],
                ]],
            ]),
            'www.googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [[
                    'id' => 'abc123xyz',
                    'snippet' => [
                        'publishedAt' => '2024-06-01T12:00:00Z',
                        'title' => 'Video demo',
                        'thumbnails' => [
                            'default' => ['url' => 'https://i.ytimg.com/vi/abc123xyz/default.jpg'],
                        ],
                    ],
                    'statistics' => [
                        'viewCount' => '15000',
                        'likeCount' => '420',
                        'commentCount' => '12',
                    ],
                ]],
            ]),
        ]);

        $log = app(OrganicYouTubeIngestionService::class)->ingestAsset($asset);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertSame(IngestionJobType::OrganicYouTube, $log->job_type);
        $this->assertSame(1, $log->records_ingested);

        $post = OrganicPost::query()->first();

        $this->assertNotNull($post);
        $this->assertSame('abc123xyz', $post->platform_post_id);
        $this->assertSame('video', $post->post_type);
        $this->assertSame(15000, (int) $post->raw_metrics['views']);
        $this->assertSame(15000, (int) $post->raw_metrics['reach']);
    }

    private function youTubeChannelAsset(string $channelId, string $uploadsPlaylistId): ConnectedAsset
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente YouTube',
            'timezone' => 'UTC',
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::YouTube,
            'access_token' => 'youtube-token',
            'refresh_token' => 'youtube-refresh',
            'token_expires_at' => now()->addMonth(),
            'status' => ConnectionStatus::Active,
        ]);

        return ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::YouTubeChannel,
            'platform_asset_id' => $channelId,
            'name' => 'Canal YouTube',
            'is_active' => true,
            'metadata' => [
                'uploads_playlist_id' => $uploadsPlaylistId,
            ],
        ]);
    }
}
