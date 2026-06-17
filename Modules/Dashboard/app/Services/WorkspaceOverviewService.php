<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Workspaces\Models\Workspace;

class WorkspaceOverviewService
{
    private const PERIOD_DAYS = 7;

    public function __construct(
        private readonly WorkspaceMetricsAggregator $metrics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Workspace $workspace): array
    {
        $assets = ConnectedAsset::query()
            ->whereHas('connection', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->where('is_active', true)
            ->with('connection:id,platform')
            ->orderBy('name')
            ->get(['id', 'connection_id', 'asset_type', 'name']);

        $start = now()->subDays(self::PERIOD_DAYS - 1)->startOfDay();
        $end = now()->endOfDay();

        if ($assets->isEmpty()) {
            return $this->emptySnapshot($start, $end);
        }

        $assetIds = $assets->pluck('id');
        $campaignIds = AdCampaign::query()->whereIn('asset_id', $assetIds)->pluck('id');
        $totals = $this->metrics->snapshot($assetIds, $campaignIds, $start, $end);

        return [
            'period' => [
                'days' => self::PERIOD_DAYS,
                'from' => $start->toDateString(),
                'to' => $end->toDateString(),
            ],
            'totals' => $this->formatTotals($totals, $assetIds),
            'assets' => $assets
                ->map(fn (ConnectedAsset $asset) => $this->presentAsset($asset, $start, $end))
                ->values()
                ->all(),
            'last_ingestion_at' => IngestionLog::query()
                ->whereIn('asset_id', $assetIds)
                ->max('executed_at'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentAsset(ConnectedAsset $asset, Carbon $start, Carbon $end): array
    {
        $assetIds = collect([$asset->id]);
        $campaignIds = AdCampaign::query()->where('asset_id', $asset->id)->pluck('id');
        $snapshot = $this->metrics->snapshot($assetIds, $campaignIds, $start, $end);
        $isOrganic = $this->isOrganicAsset($asset->asset_type);
        $isPaid = $this->isPaidAsset($asset->asset_type);

        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'asset_type' => $asset->asset_type->value,
            'platform' => $asset->connection?->platform->value,
            'reach' => $snapshot['reach'],
            'organic_reach' => $snapshot['organic_reach'],
            'paid_reach' => $snapshot['paid_reach'],
            'impressions' => $snapshot['impressions'],
            'posts_count' => $snapshot['posts_count'],
            'spend' => $snapshot['spend'],
            'engagement_rate' => $snapshot['engagement_rate'],
            'community_size' => $this->metrics->currentCommunityTotal($assetIds),
            'is_organic' => $isOrganic,
            'is_paid' => $isPaid,
            'has_data' => $this->assetHasData($snapshot, $isOrganic, $isPaid),
        ];
    }

    /**
     * @param  array<string, float|int>  $snapshot
     */
    private function assetHasData(array $snapshot, bool $isOrganic, bool $isPaid): bool
    {
        if ($isPaid && ($snapshot['spend'] > 0 || $snapshot['paid_reach'] > 0)) {
            return true;
        }

        if ($isOrganic && ($snapshot['posts_count'] > 0 || $snapshot['organic_reach'] > 0)) {
            return true;
        }

        return $snapshot['reach'] > 0 || $snapshot['impressions'] > 0;
    }

    private function isOrganicAsset(AssetType $type): bool
    {
        return in_array($type, [AssetType::FacebookPage, AssetType::InstagramAccount, AssetType::TikTokAccount, AssetType::LinkedInPage, AssetType::YouTubeChannel], true);
    }

    private function isPaidAsset(AssetType $type): bool
    {
        return in_array($type, [AssetType::MetaAds, AssetType::GoogleAds], true);
    }

    /**
     * @param  array<string, float|int>  $totals
     * @param  Collection<int, int>  $assetIds
     * @return array<string, float|int>
     */
    private function formatTotals(array $totals, Collection $assetIds): array
    {
        return [
            'reach' => $totals['reach'],
            'organic_reach' => $totals['organic_reach'],
            'paid_reach' => $totals['paid_reach'],
            'impressions' => $totals['impressions'],
            'posts_count' => $totals['posts_count'],
            'spend' => round($totals['spend'], 2),
            'engagement_rate' => $totals['engagement_rate'],
            'community_total' => $this->metrics->currentCommunityTotal($assetIds),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptySnapshot(Carbon $start, Carbon $end): array
    {
        return [
            'period' => [
                'days' => self::PERIOD_DAYS,
                'from' => $start->toDateString(),
                'to' => $end->toDateString(),
            ],
            'totals' => [
                'reach' => 0,
                'organic_reach' => 0,
                'paid_reach' => 0,
                'impressions' => 0,
                'posts_count' => 0,
                'spend' => 0,
                'engagement_rate' => 0,
                'community_total' => 0,
            ],
            'assets' => [],
            'last_ingestion_at' => null,
        ];
    }
}
