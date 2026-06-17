<?php

namespace Modules\Ingestion\Google;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GooglePaidAdsClient
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function searchCampaignMetrics(
        string $customerId,
        string $accessToken,
        Carbon $date,
        string $developerToken,
    ): array {
        if ($developerToken === '') {
            throw new RuntimeException('Missing Google Ads developer token.');
        }

        $customerId = str_replace('-', '', $customerId);
        $url = 'https://googleads.googleapis.com/v18/customers/'.$customerId.'/googleAds:searchStream';

        $query = sprintf(
            "SELECT campaign.id, campaign.name, campaign.status, metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions, metrics.conversions_value, metrics.ctr, metrics.average_cpc, segments.date FROM campaign WHERE segments.date = '%s'",
            $date->toDateString(),
        );

        $response = Http::timeout(60)
            ->withToken($accessToken)
            ->withHeaders([
                'developer-token' => $developerToken,
            ])
            ->post($url, ['query' => $query]);

        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new RuntimeException('Google Ads API error: '.$message);
        }

        $rows = [];

        foreach ($response->json() ?? [] as $batch) {
            foreach ($batch['results'] ?? [] as $result) {
                $rows[] = $result;
            }
        }

        return $rows;
    }
}
