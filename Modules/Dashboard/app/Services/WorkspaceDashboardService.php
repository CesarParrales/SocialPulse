<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Collection;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Dashboard\Support\OrganicPostPresenter;
use Modules\Ingestion\Models\AccountMetricDaily;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\IngestionLog;
use Modules\Ingestion\Models\OrganicMetricDaily;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Models\StorySnapshot;
use Modules\Workspaces\Models\Workspace;

class WorkspaceDashboardService
{
    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    public function build(Workspace $workspace, DashboardPeriod $period, Collection $assets): array
    {
        $assetIds = $assets->pluck('id');

        $recentPosts = OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->with('asset:id,name,asset_type')
            ->orderByDesc('published_at')
            ->limit(10)
            ->get(['id', 'asset_id', 'platform_post_id', 'post_type', 'published_at', 'content_preview', 'thumbnail_url', 'raw_metrics', 'captured_at']);

        $activeStories = StorySnapshot::query()
            ->whereIn('asset_id', $assetIds)
            ->where('is_expired', false)
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('asset:id,name')
            ->orderByDesc('captured_at')
            ->limit(10)
            ->get();

        $accountMetrics = $this->latestAccountMetrics($assets);
        $ingestionHealth = $this->latestIngestionLogs($assetIds);
        $paidSummary = $this->paidSummary($assetIds, $period);

        return [
            'summary' => [
                'connected_assets' => $assets->count(),
                'organic_posts' => OrganicPost::query()->whereIn('asset_id', $assetIds)->count(),
                'stories_captured' => StorySnapshot::query()->whereIn('asset_id', $assetIds)->count(),
                'ad_campaigns' => AdCampaign::query()->whereIn('asset_id', $assetIds)->count(),
                'paid_spend_7d' => $paidSummary['spend'],
                'last_ingestion_at' => IngestionLog::query()
                    ->whereIn('asset_id', $assetIds)
                    ->max('executed_at'),
            ],
            'recentPosts' => $recentPosts
                ->map(fn (OrganicPost $post) => OrganicPostPresenter::present($post))
                ->all(),
            'activeStories' => $activeStories
                ->map(fn (StorySnapshot $story) => OrganicPostPresenter::presentStory($story))
                ->all(),
            'accountMetrics' => $accountMetrics,
            'ingestionHealth' => $ingestionHealth,
            'paidSummary' => $paidSummary,
        ];
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @return array{spend: float, impressions: int, top_campaigns: list<array<string, mixed>>}
     */
    private function paidSummary(Collection $assetIds, DashboardPeriod $period): array
    {
        if ($assetIds->isEmpty()) {
            return [
                'spend' => 0.0,
                'impressions' => 0,
                'top_campaigns' => [],
            ];
        }

        $campaignIds = AdCampaign::query()
            ->whereIn('asset_id', $assetIds)
            ->pluck('id');

        if ($campaignIds->isEmpty()) {
            return [
                'spend' => 0.0,
                'impressions' => 0,
                'top_campaigns' => [],
            ];
        }

        $since = $period->start->toDateString();
        $until = $period->end->toDateString();

        $metricsQuery = AdMetricDaily::query()
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween('date', [$since, $until]);

        $spend7d = (float) (clone $metricsQuery)->sum('spend');
        $impressions7d = (int) (clone $metricsQuery)->sum('impressions');

        $topRows = AdMetricDaily::query()
            ->selectRaw('campaign_id, SUM(spend) as total_spend, SUM(impressions) as total_impressions')
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween('date', [$since, $until])
            ->groupBy('campaign_id')
            ->orderByDesc('total_spend')
            ->limit(5)
            ->get();

        $campaigns = AdCampaign::query()
            ->with('asset:id,name')
            ->whereIn('id', $topRows->pluck('campaign_id'))
            ->get()
            ->keyBy('id');

        $topCampaigns = $topRows
            ->map(function ($row) use ($campaigns) {
                $campaign = $campaigns->get($row->campaign_id);

                return [
                    'campaign_name' => $campaign?->name,
                    'asset_name' => $campaign?->asset?->name,
                    'spend' => (float) $row->total_spend,
                    'impressions' => (int) $row->total_impressions,
                ];
            })
            ->all();

        return [
            'spend' => round($spend7d, 2),
            'impressions' => $impressions7d,
            'top_campaigns' => $topCampaigns,
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return list<array<string, mixed>>
     */
    private function latestAccountMetrics(Collection $assets): array
    {
        return $assets->map(function (ConnectedAsset $asset): array {
            $followers = AccountMetricDaily::query()
                ->where('asset_id', $asset->id)
                ->orderByDesc('date')
                ->value('followers');

            $fanCount = OrganicMetricDaily::query()
                ->where('asset_id', $asset->id)
                ->where('metric_type', 'fan_count')
                ->orderByDesc('date')
                ->value('value');

            return [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'asset_type' => $asset->asset_type->value,
                'followers' => $followers,
                'fan_count' => $fanCount !== null ? (float) $fanCount : null,
            ];
        })->all();
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @return list<array<string, mixed>>
     */
    private function latestIngestionLogs(Collection $assetIds): array
    {
        return IngestionLog::query()
            ->whereIn('asset_id', $assetIds)
            ->with('asset:id,name')
            ->orderByDesc('executed_at')
            ->limit(15)
            ->get()
            ->map(fn (IngestionLog $log) => [
                'id' => $log->id,
                'asset_name' => $log->asset?->name,
                'job_type' => $log->job_type->value,
                'status' => $log->status->value,
                'records_ingested' => $log->records_ingested,
                'error_message' => $log->error_message,
                'executed_at' => $log->executed_at?->toIso8601String(),
                'duration_ms' => $log->duration_ms,
            ])
            ->all();
    }
}
