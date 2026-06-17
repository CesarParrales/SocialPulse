<?php

namespace Modules\Ingestion\Services;

use Carbon\Carbon;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Meta\MetaPaidAdsClient;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Support\IngestionLogger;
use Modules\Ingestion\Support\PaidIngestionDateResolver;
use Throwable;

class PaidMetaIngestionService
{
    public function __construct(
        private readonly MetaPaidAdsClient $client,
        private readonly IngestionLogger $logger,
        private readonly PaidIngestionDateResolver $dateResolver,
    ) {}

    public function ingestAsset(ConnectedAsset $asset, bool $preliminary = false): IngestionLog
    {
        $startedAt = microtime(true);
        $recordsIngested = 0;

        try {
            if ($asset->asset_type !== AssetType::MetaAds) {
                throw new \InvalidArgumentException('Asset is not a Meta Ads account.');
            }

            if (! $asset->is_active) {
                throw new \InvalidArgumentException('Asset is inactive.');
            }

            $accessToken = $this->dateResolver->connectionAccessToken($asset->loadMissing('connection'));
            ['since' => $since, 'until' => $until] = $this->dateResolver->resolveRange($preliminary);

            $rows = $this->client->fetchCampaignInsights(
                $asset->platform_asset_id,
                $accessToken,
                $since,
                $until,
            );

            $capturedAt = now();

            foreach ($rows as $row) {
                $recordsIngested += $this->storeInsightRow($asset, $row, $since, $preliminary, $capturedAt);
            }

            return $this->logger->log(
                $asset,
                IngestionJobType::PaidMeta,
                IngestionStatus::Success,
                $recordsIngested,
                null,
                $startedAt,
            );
        } catch (Throwable $exception) {
            return $this->logger->log(
                $asset,
                IngestionJobType::PaidMeta,
                IngestionStatus::Error,
                $recordsIngested,
                $exception->getMessage(),
                $startedAt,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function storeInsightRow(
        ConnectedAsset $asset,
        array $row,
        Carbon $date,
        bool $preliminary,
        Carbon $capturedAt,
    ): int {
        $platformCampaignId = (string) ($row['campaign_id'] ?? '');

        if ($platformCampaignId === '') {
            return 0;
        }

        $campaign = AdCampaign::query()->updateOrCreate(
            [
                'asset_id' => $asset->id,
                'platform_campaign_id' => $platformCampaignId,
            ],
            [
                'name' => (string) ($row['campaign_name'] ?? $platformCampaignId),
                'status' => null,
            ],
        );

        $placement = $this->normalizePlacement(
            $row['publisher_platform'] ?? null,
            $row['platform_position'] ?? null,
        );

        $spend = (float) ($row['spend'] ?? 0);
        $conversionValue = $this->extractActionValue($row['actions'] ?? [], 'purchase');
        $conversions = $this->extractActionValue($row['actions'] ?? [], 'offsite_conversion');

        $roas = null;
        $purchaseRoas = $row['purchase_roas'] ?? null;

        if (is_array($purchaseRoas) && isset($purchaseRoas[0]['value'])) {
            $roas = (float) $purchaseRoas[0]['value'];
        } elseif ($spend > 0 && $conversionValue > 0) {
            $roas = round($conversionValue / $spend, 4);
        }

        $attributes = [
            'spend' => $spend,
            'reach' => isset($row['reach']) ? (int) $row['reach'] : null,
            'impressions' => isset($row['impressions']) ? (int) $row['impressions'] : null,
            'clicks' => isset($row['clicks']) ? (int) $row['clicks'] : null,
            'ctr' => isset($row['ctr']) ? (float) $row['ctr'] : null,
            'cpm' => isset($row['cpm']) ? (float) $row['cpm'] : null,
            'cpc' => isset($row['cpc']) ? (float) $row['cpc'] : null,
            'conversions' => $conversions > 0 ? $conversions : null,
            'conversion_value' => $conversionValue > 0 ? $conversionValue : null,
            'roas' => $roas,
            'captured_at' => $capturedAt,
        ];

        if ($preliminary) {
            AdMetricDaily::query()->create([
                'campaign_id' => $campaign->id,
                'date' => $date->toDateString(),
                'placement' => $placement,
                'is_preliminary' => true,
                ...$attributes,
            ]);
        } else {
            AdMetricDaily::query()->updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'date' => $date->toDateString(),
                    'placement' => $placement,
                    'ad_set_id' => null,
                    'ad_id' => null,
                    'is_preliminary' => false,
                ],
                $attributes,
            );
        }

        return 1;
    }

    /**
     * @param  list<array<string, mixed>>|mixed  $actions
     */
    private function extractActionValue(mixed $actions, string $needle): float
    {
        if (! is_array($actions)) {
            return 0;
        }

        $total = 0.0;

        foreach ($actions as $action) {
            $type = (string) ($action['action_type'] ?? '');

            if (str_contains($type, $needle)) {
                $total += (float) ($action['value'] ?? 0);
            }
        }

        return $total;
    }

    private function normalizePlacement(mixed $publisher, mixed $position): string
    {
        $publisherLabel = match (strtolower((string) $publisher)) {
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'messenger' => 'Messenger',
            'audience_network' => 'Audience Network',
            default => ucfirst((string) ($publisher ?: 'unknown')),
        };

        $positionLabel = match (strtolower((string) $position)) {
            'feed' => 'Feed',
            'story', 'stories' => 'Stories',
            'reels' => 'Reels',
            default => ucfirst((string) ($position ?: '')),
        };

        return trim($publisherLabel.' '.$positionLabel) ?: 'unknown';
    }
}
