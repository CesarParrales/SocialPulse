<?php

namespace Modules\Ingestion\Services;

use Carbon\Carbon;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Content\Services\PublishedContentLinkService;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Meta\MetaOrganicInstagramClient;
use Modules\Ingestion\Models\AccountMetricDaily;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\OrganicPostMetricEntry;
use Modules\Ingestion\Support\IngestionLogger;
use Throwable;

class OrganicInstagramIngestionService
{
    public function __construct(
        private readonly MetaOrganicInstagramClient $client,
        private readonly InstagramAccessTokenResolver $tokenResolver,
        private readonly IngestionLogger $logger,
        private readonly PublishedContentLinkService $contentLinks,
    ) {}

    public function ingestAsset(ConnectedAsset $asset): IngestionLog
    {
        $startedAt = microtime(true);
        $recordsIngested = 0;

        try {
            if ($asset->asset_type !== AssetType::InstagramAccount) {
                throw new \InvalidArgumentException('Asset is not an Instagram account.');
            }

            if (! $asset->is_active) {
                throw new \InvalidArgumentException('Asset is inactive.');
            }

            $accessToken = $this->tokenResolver->resolve($asset);
            $igUserId = $asset->platform_asset_id;
            $capturedAt = now();

            $accountData = $this->client->fetchAccount($igUserId, $accessToken);
            $recordsIngested += $this->storeAccountMetrics($asset, $accountData, $capturedAt);

            $mediaItems = $this->client->fetchRecentMedia($igUserId, $accessToken);

            foreach ($mediaItems as $mediaData) {
                $recordsIngested += $this->storeMedia($asset, $mediaData, $accessToken, $capturedAt);
            }

            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicInstagram,
                IngestionStatus::Success,
                $recordsIngested,
                null,
                $startedAt,
            );
        } catch (Throwable $exception) {
            return $this->logger->log(
                $asset,
                IngestionJobType::OrganicInstagram,
                IngestionStatus::Error,
                $recordsIngested,
                $exception->getMessage(),
                $startedAt,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $accountData
     */
    private function storeAccountMetrics(ConnectedAsset $asset, array $accountData, Carbon $capturedAt): int
    {
        if (! isset($accountData['followers_count'])) {
            return 0;
        }

        AccountMetricDaily::query()->create([
            'asset_id' => $asset->id,
            'date' => $capturedAt->toDateString(),
            'followers' => (int) $accountData['followers_count'],
            'captured_at' => $capturedAt,
        ]);

        return 1;
    }

    /**
     * @param  array<string, mixed>  $mediaData
     */
    private function storeMedia(
        ConnectedAsset $asset,
        array $mediaData,
        string $accessToken,
        Carbon $capturedAt,
    ): int {
        $platformPostId = $mediaData['id'] ?? null;

        if (! is_string($platformPostId) || $platformPostId === '') {
            return 0;
        }

        $mediaType = strtoupper((string) ($mediaData['media_type'] ?? 'IMAGE'));
        $insights = $this->client->fetchMediaInsights($platformPostId, $mediaType, $accessToken);

        $metrics = [
            'reach' => $insights['reach'] ?? null,
            'impressions' => $insights['impressions'] ?? null,
            'likes' => $insights['likes'] ?? null,
            'comments' => $insights['comments'] ?? null,
            'shares' => $insights['shares'] ?? null,
            'saved' => $insights['saved'] ?? null,
            'plays' => $insights['plays'] ?? null,
            'profile_visits' => $insights['profile_visits'] ?? null,
        ];

        $metrics = array_filter($metrics, fn ($value) => $value !== null);

        if (! empty($mediaData['permalink']) && is_string($mediaData['permalink'])) {
            $metrics['permalink_url'] = $mediaData['permalink'];
        }

        $post = OrganicPost::query()->updateOrCreate(
            [
                'asset_id' => $asset->id,
                'platform_post_id' => $platformPostId,
            ],
            [
                'post_type' => $this->resolvePostType($mediaType),
                'published_at' => isset($mediaData['timestamp'])
                    ? Carbon::parse($mediaData['timestamp'])
                    : null,
                'content_preview' => isset($mediaData['caption'])
                    ? mb_substr((string) $mediaData['caption'], 0, 500)
                    : null,
                'thumbnail_url' => $mediaData['thumbnail_url'] ?? $mediaData['media_url'] ?? null,
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

    private function resolvePostType(string $mediaType): string
    {
        return match ($mediaType) {
            'REELS' => 'reel',
            'VIDEO' => 'video',
            default => 'feed',
        };
    }
}
