<?php

namespace Modules\Ingestion\Services;

use Carbon\Carbon;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Content\Services\PublishedContentLinkService;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\LinkedIn\LinkedInOrganicClient;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\OrganicPostMetricEntry;
use Modules\Ingestion\Support\IngestionLogger;
use Throwable;

class OrganicLinkedInIngestionService
{
    public function __construct(
        private readonly LinkedInOrganicClient $client,
        private readonly IngestionLogger $logger,
        private readonly PublishedContentLinkService $contentLinks,
    ) {}

    public function ingestAsset(ConnectedAsset $asset): IngestionLog
    {
        $startedAt = microtime(true);
        $recordsIngested = 0;

        try {
            if ($asset->asset_type !== AssetType::LinkedInPage) {
                throw new \InvalidArgumentException('Asset is not a LinkedIn page.');
            }

            if (! $asset->is_active) {
                throw new \InvalidArgumentException('Asset is inactive.');
            }

            $connection = $asset->connection()->with('workspace')->first();

            if ($connection === null) {
                throw new \RuntimeException('Missing platform connection for asset.');
            }

            $accessToken = $connection->access_token;

            if (! is_string($accessToken) || $accessToken === '') {
                throw new \RuntimeException('Missing access token for LinkedIn asset.');
            }

            $capturedAt = now();
            $posts = $this->client->fetchRecentPosts(
                $accessToken,
                $asset->platform_asset_id,
                $connection->workspace?->agency_id,
            );

            foreach ($posts as $postData) {
                $recordsIngested += $this->storePost($asset, $postData, $capturedAt);
            }

            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicLinkedIn,
                IngestionStatus::Success,
                $recordsIngested,
                null,
                $startedAt,
            );
        } catch (Throwable $exception) {
            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicLinkedIn,
                IngestionStatus::Error,
                $recordsIngested,
                $exception->getMessage(),
                $startedAt,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $postData
     */
    private function storePost(ConnectedAsset $asset, array $postData, Carbon $capturedAt): int
    {
        $platformPostId = $postData['id'] ?? null;

        if (! is_string($platformPostId) || $platformPostId === '') {
            return 0;
        }

        $stats = is_array($postData['totalShareStatistics'] ?? null)
            ? $postData['totalShareStatistics']
            : [];

        $metrics = array_filter([
            'impressions' => isset($stats['impressionCount']) ? (float) $stats['impressionCount'] : null,
            'reach' => isset($stats['uniqueImpressionsCount']) ? (float) $stats['uniqueImpressionsCount'] : null,
            'likes' => isset($stats['likeCount']) ? (float) $stats['likeCount'] : null,
            'comments' => isset($stats['commentCount']) ? (float) $stats['commentCount'] : null,
            'shares' => isset($stats['shareCount']) ? (float) $stats['shareCount'] : null,
            'clicks' => isset($stats['clickCount']) ? (float) $stats['clickCount'] : null,
        ], fn ($value) => $value !== null);

        $post = OrganicPost::query()->updateOrCreate(
            [
                'asset_id' => $asset->id,
                'platform_post_id' => $platformPostId,
            ],
            [
                'post_type' => 'feed',
                'published_at' => isset($postData['createdAt'])
                    ? Carbon::createFromTimestampMs((int) $postData['createdAt'])
                    : null,
                'content_preview' => isset($postData['commentary'])
                    ? mb_substr((string) $postData['commentary'], 0, 500)
                    : null,
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
