<?php

namespace Modules\Ingestion\Services;

use Carbon\Carbon;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Google\GooglePaidAdsClient;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Support\IngestionLogger;
use Modules\Ingestion\Support\PaidIngestionDateResolver;
use Modules\Settings\Services\IntegrationConfigResolver;
use Throwable;

class PaidGoogleIngestionService
{
    public function __construct(
        private readonly IngestionLogger $logger,
        private readonly PaidIngestionDateResolver $dateResolver,
        private readonly IntegrationConfigResolver $configResolver,
    ) {}

    public function ingestAsset(ConnectedAsset $asset, bool $preliminary = false): IngestionLog
    {
        $startedAt = microtime(true);
        $recordsIngested = 0;

        try {
            if ($asset->asset_type !== AssetType::GoogleAds) {
                throw new \InvalidArgumentException('Asset is not a Google Ads account.');
            }

            if (! $asset->is_active) {
                throw new \InvalidArgumentException('Asset is inactive.');
            }

            $accessToken = $this->dateResolver->connectionAccessToken($asset->loadMissing('connection.workspace'));
            $date = $this->dateResolver->resolve($preliminary);
            $agencyId = $asset->connection?->workspace?->agency_id;
            $developerToken = $this->configResolver->google($agencyId)['developer_token'] ?? '';

            $rows = GooglePaidAdsClient::searchCampaignMetrics(
                $asset->platform_asset_id,
                $accessToken,
                $date,
                (string) $developerToken,
            );

            $capturedAt = now();

            foreach ($rows as $row) {
                $recordsIngested += $this->storeRow($asset, $row, $date, $preliminary, $capturedAt);
            }

            return $this->logger->log(
                $asset,
                IngestionJobType::PaidGoogle,
                IngestionStatus::Success,
                $recordsIngested,
                null,
                $startedAt,
            );
        } catch (Throwable $exception) {
            return $this->logger->log(
                $asset,
                IngestionJobType::PaidGoogle,
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
    private function storeRow(
        ConnectedAsset $asset,
        array $row,
        Carbon $date,
        bool $preliminary,
        Carbon $capturedAt,
    ): int {
        $campaignData = $row['campaign'] ?? [];
        $metrics = $row['metrics'] ?? [];
        $platformCampaignId = (string) ($campaignData['id'] ?? '');

        if ($platformCampaignId === '') {
            return 0;
        }

        $campaign = AdCampaign::query()->updateOrCreate(
            [
                'asset_id' => $asset->id,
                'platform_campaign_id' => $platformCampaignId,
            ],
            [
                'name' => (string) ($campaignData['name'] ?? $platformCampaignId),
                'status' => isset($campaignData['status']) ? (string) $campaignData['status'] : null,
            ],
        );

        $spend = isset($metrics['costMicros']) ? ((float) $metrics['costMicros']) / 1_000_000 : 0;
        $conversionValue = isset($metrics['conversionsValue']) ? (float) $metrics['conversionsValue'] : null;
        $conversions = isset($metrics['conversions']) ? (float) $metrics['conversions'] : null;
        $roas = ($spend > 0 && $conversionValue > 0) ? round($conversionValue / $spend, 4) : null;

        $attributes = [
            'spend' => $spend,
            'impressions' => isset($metrics['impressions']) ? (int) $metrics['impressions'] : null,
            'clicks' => isset($metrics['clicks']) ? (int) $metrics['clicks'] : null,
            'ctr' => isset($metrics['ctr']) ? (float) $metrics['ctr'] : null,
            'cpc' => isset($metrics['averageCpc']) ? ((float) $metrics['averageCpc']) / 1_000_000 : null,
            'conversions' => $conversions,
            'conversion_value' => $conversionValue,
            'roas' => $roas,
            'captured_at' => $capturedAt,
        ];

        if ($preliminary) {
            AdMetricDaily::query()->create([
                'campaign_id' => $campaign->id,
                'date' => $date->toDateString(),
                'placement' => 'google_search',
                'is_preliminary' => true,
                ...$attributes,
            ]);
        } else {
            AdMetricDaily::query()->updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'date' => $date->toDateString(),
                    'placement' => 'google_search',
                    'ad_set_id' => null,
                    'ad_id' => null,
                    'is_preliminary' => false,
                ],
                $attributes,
            );
        }

        return 1;
    }
}
