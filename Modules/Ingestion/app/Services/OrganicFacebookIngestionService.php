<?php

namespace Modules\Ingestion\Services;

use Carbon\Carbon;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Content\Services\PublishedContentLinkService;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Meta\MetaOrganicFacebookClient;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Models\OrganicMetricDaily;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\OrganicPostMetricEntry;
use Modules\Ingestion\Support\IngestionLogger;
use Throwable;

class OrganicFacebookIngestionService
{
    public function __construct(
        private readonly MetaOrganicFacebookClient $client,
        private readonly IngestionLogger $logger,
        private readonly PublishedContentLinkService $contentLinks,
    ) {}

    public function ingestAsset(ConnectedAsset $asset): IngestionLog
    {
        $startedAt = microtime(true);
        $recordsIngested = 0;

        try {
            if ($asset->asset_type !== AssetType::FacebookPage) {
                throw new \InvalidArgumentException('Asset is not a Facebook page.');
            }

            if (! $asset->is_active) {
                throw new \InvalidArgumentException('Asset is inactive.');
            }

            $pageAccessToken = $asset->metadata['page_access_token'] ?? null;

            if (! is_string($pageAccessToken) || $pageAccessToken === '') {
                throw new \RuntimeException('Missing page access token for asset.');
            }

            $pageId = $asset->platform_asset_id;
            $capturedAt = now();

            $pageData = $this->client->fetchPage($pageId, $pageAccessToken);
            $recordsIngested += $this->storePageMetrics($asset, $pageData, $capturedAt);

            $posts = $this->client->fetchRecentPosts($pageId, $pageAccessToken);

            foreach ($posts as $postData) {
                $recordsIngested += $this->storePost($asset, $postData, $pageAccessToken, $capturedAt);
            }

            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicFacebook,
                IngestionStatus::Success,
                $recordsIngested,
                null,
                $startedAt,
            );
        } catch (Throwable $exception) {
            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicFacebook,
                IngestionStatus::Error,
                $recordsIngested,
                $exception->getMessage(),
                $startedAt,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $pageData
     */
    private function storePageMetrics(ConnectedAsset $asset, array $pageData, Carbon $capturedAt): int
    {
        $count = 0;
        $date = $capturedAt->toDateString();

        if (isset($pageData['fan_count'])) {
            OrganicMetricDaily::query()->create([
                'asset_id' => $asset->id,
                'date' => $date,
                'metric_type' => 'fan_count',
                'value' => (float) $pageData['fan_count'],
                'platform' => 'meta',
                'captured_at' => $capturedAt,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $postData
     */
    private function storePost(
        ConnectedAsset $asset,
        array $postData,
        string $pageAccessToken,
        Carbon $capturedAt,
    ): int {
        $platformPostId = $postData['id'] ?? null;

        if (! is_string($platformPostId) || $platformPostId === '') {
            return 0;
        }

        $insights = $this->client->fetchPostInsights($platformPostId, $pageAccessToken);

        $metrics = [
            'reach' => $insights['post_impressions_unique'] ?? null,
            'impressions' => $insights['post_impressions'] ?? null,
            'engagement' => $insights['post_engaged_users'] ?? null,
            'reactions' => $insights['post_reactions_by_type_total'] ?? null,
            'clicks' => $insights['post_clicks'] ?? null,
            'video_views' => $insights['post_video_views'] ?? null,
        ];

        $metrics = array_filter($metrics, fn ($value) => $value !== null);

        if (! empty($postData['permalink_url']) && is_string($postData['permalink_url'])) {
            $metrics['permalink_url'] = $postData['permalink_url'];
        }

        $post = OrganicPost::query()->updateOrCreate(
            [
                'asset_id' => $asset->id,
                'platform_post_id' => $platformPostId,
            ],
            [
                'post_type' => $this->resolvePostType($postData),
                'published_at' => isset($postData['created_time'])
                    ? Carbon::parse($postData['created_time'])
                    : null,
                'content_preview' => isset($postData['message'])
                    ? mb_substr((string) $postData['message'], 0, 500)
                    : null,
                'thumbnail_url' => $postData['full_picture'] ?? null,
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

    /**
     * @param  array<string, mixed>  $postData
     */
    private function resolvePostType(array $postData): string
    {
        $statusType = strtolower((string) ($postData['status_type'] ?? ''));

        if (str_contains($statusType, 'video')) {
            return 'video';
        }

        if (str_contains($statusType, 'reel')) {
            return 'reel';
        }

        return 'feed';
    }
}
