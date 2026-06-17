<?php

namespace Modules\Ingestion\Meta;

use Carbon\Carbon;

class MetaPaidAdsClient extends MetaGraphApiClient
{
    public static function make(): self
    {
        return new self(self::apiVersion());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchCampaignInsights(
        string $adAccountId,
        string $accessToken,
        Carbon $since,
        Carbon $until,
    ): array {
        $accountId = $this->normalizeAdAccountId($adAccountId);

        $response = $this->get("{$accountId}/insights", [
            'level' => 'campaign',
            'fields' => implode(',', [
                'campaign_id',
                'campaign_name',
                'spend',
                'reach',
                'impressions',
                'clicks',
                'ctr',
                'cpm',
                'cpc',
                'actions',
                'purchase_roas',
            ]),
            'breakdowns' => 'publisher_platform,platform_position',
            'time_range' => json_encode([
                'since' => $since->toDateString(),
                'until' => $until->toDateString(),
            ]),
            'limit' => 500,
        ], $accessToken);

        return $response['data'] ?? [];
    }

    private function normalizeAdAccountId(string $adAccountId): string
    {
        return str_starts_with($adAccountId, 'act_') ? $adAccountId : 'act_'.$adAccountId;
    }
}
