<?php

namespace Modules\Ingestion\Services;

use Carbon\Carbon;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Meta\MetaOrganicInstagramClient;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Models\StorySnapshot;
use Modules\Ingestion\Support\IngestionLogger;
use Throwable;

class StoriesWatcherService
{
    public function __construct(
        private readonly MetaOrganicInstagramClient $client,
        private readonly InstagramAccessTokenResolver $tokenResolver,
        private readonly IngestionLogger $logger,
    ) {}

    public function watchAsset(ConnectedAsset $asset): IngestionLog
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
            $activeStories = $this->client->fetchActiveStories($igUserId, $accessToken);

            if ($activeStories === []) {
                return $this->logger->log(
                    $asset,
                    IngestionJobType::StoriesWatcher,
                    IngestionStatus::Success,
                    0,
                    null,
                    $startedAt,
                );
            }

            $capturedAt = now();

            foreach ($activeStories as $storyData) {
                $recordsIngested += $this->captureStory($asset, $storyData, $accessToken, $capturedAt);
            }

            $this->markExpiredSnapshots($asset);

            return $this->logger->log(
                $asset,
                IngestionJobType::StoriesWatcher,
                IngestionStatus::Success,
                $recordsIngested,
                null,
                $startedAt,
            );
        } catch (Throwable $exception) {
            return $this->logger->log(
                $asset,
                IngestionJobType::StoriesWatcher,
                IngestionStatus::Error,
                $recordsIngested,
                $exception->getMessage(),
                $startedAt,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $storyData
     */
    private function captureStory(
        ConnectedAsset $asset,
        array $storyData,
        string $accessToken,
        Carbon $capturedAt,
    ): int {
        $storyId = $storyData['id'] ?? null;

        if (! is_string($storyId) || $storyId === '') {
            return 0;
        }

        $publishedAt = isset($storyData['timestamp'])
            ? Carbon::parse($storyData['timestamp'])
            : $capturedAt;

        $expiresAt = $publishedAt->copy()->addHours(24);
        $isExpired = $capturedAt->greaterThan($expiresAt);

        if ($isExpired) {
            return 0;
        }

        $insights = $this->client->fetchStoryInsights($storyId, $accessToken);

        StorySnapshot::query()->create([
            'asset_id' => $asset->id,
            'story_id' => $storyId,
            'captured_at' => $capturedAt,
            'reach' => isset($insights['reach']) ? (int) $insights['reach'] : null,
            'impressions' => isset($insights['impressions']) ? (int) $insights['impressions'] : null,
            'taps_forward' => isset($insights['taps_forward']) ? (int) $insights['taps_forward'] : null,
            'taps_back' => isset($insights['taps_back']) ? (int) $insights['taps_back'] : null,
            'exits' => isset($insights['exits']) ? (int) $insights['exits'] : null,
            'replies' => isset($insights['replies']) ? (int) $insights['replies'] : null,
            'expires_at' => $expiresAt,
            'is_expired' => false,
        ]);

        return 1;
    }

    private function markExpiredSnapshots(ConnectedAsset $asset): void
    {
        StorySnapshot::query()
            ->where('asset_id', $asset->id)
            ->where('is_expired', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['is_expired' => true]);
    }
}
