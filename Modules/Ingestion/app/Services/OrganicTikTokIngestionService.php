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
use Modules\Ingestion\TikTok\TikTokOrganicClient;
use Throwable;

class OrganicTikTokIngestionService
{
    public function __construct(
        private readonly TikTokOrganicClient $client,
        private readonly IngestionLogger $logger,
        private readonly PublishedContentLinkService $contentLinks,
    ) {}

    public function ingestAsset(ConnectedAsset $asset): IngestionLog
    {
        $startedAt = microtime(true);
        $recordsIngested = 0;

        try {
            if ($asset->asset_type !== AssetType::TikTokAccount) {
                throw new \InvalidArgumentException('Asset is not a TikTok account.');
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
                throw new \RuntimeException('Missing access token for TikTok asset.');
            }

            $capturedAt = now();
            $videos = $this->client->fetchRecentVideos($accessToken);

            foreach ($videos as $videoData) {
                $recordsIngested += $this->storeVideo($asset, $videoData, $capturedAt);
            }

            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicTikTok,
                IngestionStatus::Success,
                $recordsIngested,
                null,
                $startedAt,
            );
        } catch (Throwable $exception) {
            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicTikTok,
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

        $metrics = array_filter([
            'views' => isset($videoData['view_count']) ? (float) $videoData['view_count'] : null,
            'reach' => isset($videoData['view_count']) ? (float) $videoData['view_count'] : null,
            'impressions' => isset($videoData['view_count']) ? (float) $videoData['view_count'] : null,
            'likes' => isset($videoData['like_count']) ? (float) $videoData['like_count'] : null,
            'comments' => isset($videoData['comment_count']) ? (float) $videoData['comment_count'] : null,
            'shares' => isset($videoData['share_count']) ? (float) $videoData['share_count'] : null,
            'permalink_url' => $videoData['share_url'] ?? null,
        ], fn ($value) => $value !== null);

        $post = OrganicPost::query()->updateOrCreate(
            [
                'asset_id' => $asset->id,
                'platform_post_id' => $platformPostId,
            ],
            [
                'post_type' => 'video',
                'published_at' => isset($videoData['create_time'])
                    ? Carbon::createFromTimestamp((int) $videoData['create_time'])
                    : null,
                'content_preview' => isset($videoData['title'])
                    ? mb_substr((string) $videoData['title'], 0, 500)
                    : null,
                'thumbnail_url' => $videoData['cover_image_url'] ?? null,
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
