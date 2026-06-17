<?php

namespace Modules\Ingestion\Services;

use Carbon\Carbon;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Content\Services\PublishedContentLinkService;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\OrganicPostMetricEntry;
use Modules\Ingestion\Support\IngestionLogger;
use Modules\Ingestion\YouTube\YouTubeOrganicClient;
use Throwable;

class OrganicYouTubeIngestionService
{
    public function __construct(
        private readonly YouTubeOrganicClient $client,
        private readonly IngestionLogger $logger,
        private readonly PublishedContentLinkService $contentLinks,
    ) {}

    public function ingestAsset(ConnectedAsset $asset): IngestionLog
    {
        $startedAt = microtime(true);
        $recordsIngested = 0;

        try {
            if ($asset->asset_type !== AssetType::YouTubeChannel) {
                throw new \InvalidArgumentException('Asset is not a YouTube channel.');
            }

            if (! $asset->is_active) {
                throw new \InvalidArgumentException('Asset is inactive.');
            }

            $connection = $asset->connection()->first();

            if ($connection === null) {
                throw new \RuntimeException('Missing platform connection for asset.');
            }

            $accessToken = $connection->access_token;

            if (! is_string($accessToken) || $accessToken === '') {
                throw new \RuntimeException('Missing access token for YouTube asset.');
            }

            $uploadsPlaylistId = $asset->metadata['uploads_playlist_id'] ?? null;
            $uploadsPlaylistId = is_string($uploadsPlaylistId) ? $uploadsPlaylistId : null;

            $capturedAt = now();
            $videos = $this->client->fetchRecentVideos(
                $accessToken,
                $asset->platform_asset_id,
                $uploadsPlaylistId,
            );

            foreach ($videos as $videoData) {
                $recordsIngested += $this->storeVideo($asset, $videoData, $capturedAt);
            }

            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicYouTube,
                IngestionStatus::Success,
                $recordsIngested,
                null,
                $startedAt,
            );
        } catch (Throwable $exception) {
            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicYouTube,
                IngestionStatus::Error,
                $recordsIngested,
                $exception->getMessage(),
                $startedAt,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $videoData
     */
    private function storeVideo(ConnectedAsset $asset, array $videoData, Carbon $capturedAt): int
    {
        $platformPostId = $videoData['id'] ?? null;

        if (! is_string($platformPostId) || $platformPostId === '') {
            return 0;
        }

        $statistics = is_array($videoData['statistics'] ?? null) ? $videoData['statistics'] : [];
        $snippet = is_array($videoData['snippet'] ?? null) ? $videoData['snippet'] : [];
        $viewCount = isset($statistics['viewCount']) ? (float) $statistics['viewCount'] : null;

        $metrics = array_filter([
            'views' => $viewCount,
            'reach' => $viewCount,
            'impressions' => $viewCount,
            'likes' => isset($statistics['likeCount']) ? (float) $statistics['likeCount'] : null,
            'comments' => isset($statistics['commentCount']) ? (float) $statistics['commentCount'] : null,
            'permalink_url' => 'https://www.youtube.com/watch?v='.$platformPostId,
        ], fn ($value) => $value !== null);

        $post = OrganicPost::query()->updateOrCreate(
            [
                'asset_id' => $asset->id,
                'platform_post_id' => $platformPostId,
            ],
            [
                'post_type' => 'video',
                'published_at' => isset($snippet['publishedAt'])
                    ? Carbon::parse($snippet['publishedAt'])
                    : null,
                'content_preview' => isset($snippet['title'])
                    ? mb_substr((string) $snippet['title'], 0, 500)
                    : null,
                'thumbnail_url' => $snippet['thumbnails']['medium']['url']
                    ?? $snippet['thumbnails']['default']['url']
                    ?? null,
                'raw_metrics' => $metrics,
                'captured_at' => $capturedAt,
            ],
        );

        if ($metrics !== []) {
            OrganicPostMetricEntry::query()->create([
                'organic_post_id' => $post->id,
                'captured_at' => $capturedAt,
                'metrics' => $metrics,
            ]);
        }

        $this->contentLinks->attachOrganicPost($post);

        return 1;
    }
}
