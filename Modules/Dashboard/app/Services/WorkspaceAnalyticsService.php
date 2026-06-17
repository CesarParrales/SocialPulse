<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Dashboard\Support\MetricComparison;
use Modules\Dashboard\Support\OrganicMetricResolver;
use Modules\Dashboard\Support\OrganicPostPresenter;
use Modules\Ingestion\Models\AccountMetricDaily;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\OrganicMetricDaily;
use Modules\Ingestion\Models\OrganicPost;

class WorkspaceAnalyticsService
{
    public function __construct(
        private readonly WorkspaceMetricsAggregator $metrics,
    ) {}

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    public function build(Collection $assets, DashboardPeriod $period): array
    {
        $assetIds = $assets->pluck('id');

        if ($assetIds->isEmpty()) {
            return $this->emptyAnalytics($period);
        }

        $campaignIds = AdCampaign::query()->whereIn('asset_id', $assetIds)->pluck('id');

        $currentOrganic = $this->metrics->organicMetrics($assetIds, $period->start, $period->end);
        $previousOrganic = $this->metrics->organicMetrics($assetIds, $period->previousStart, $period->previousEnd);

        $currentPaid = $this->metrics->paidMetrics($campaignIds, $period->start, $period->end);
        $previousPaid = $this->metrics->paidMetrics($campaignIds, $period->previousStart, $period->previousEnd);

        $comparable = $this->hasHistoricalData($assetIds, $campaignIds, $period->previousStart);

        $followerGrowth = $this->metrics->followerGrowth($assetIds, $period->start, $period->end);

        return [
            'period' => $period->toFilters(),
            'comparable' => $comparable,
            'kpis' => [
                'reach' => MetricComparison::compare(
                    $currentOrganic['reach'] + $currentPaid['reach'],
                    $previousOrganic['reach'] + $previousPaid['reach'],
                    $comparable,
                ),
                'reach_organic' => $currentOrganic['reach'],
                'reach_paid' => $currentPaid['reach'],
                'impressions' => MetricComparison::compare(
                    $currentOrganic['impressions'] + $currentPaid['impressions'],
                    $previousOrganic['impressions'] + $previousPaid['impressions'],
                    $comparable,
                ),
                'engagement_rate' => MetricComparison::compare(
                    $currentOrganic['engagement_rate'],
                    $previousOrganic['engagement_rate'],
                    $comparable,
                ),
                'spend' => MetricComparison::compare(
                    $currentPaid['spend'],
                    $previousPaid['spend'],
                    $comparable,
                ),
                'follower_growth' => MetricComparison::compare(
                    $followerGrowth,
                    $this->metrics->followerGrowth($assetIds, $period->previousStart, $period->previousEnd),
                    $comparable,
                ),
                'posts_published' => MetricComparison::compare(
                    $currentOrganic['posts_count'],
                    $previousOrganic['posts_count'],
                    $comparable,
                ),
            ],
            'trends' => [
                'daily_reach' => $this->dailyReachSeries($assetIds, $campaignIds, $period),
                'daily_spend' => $this->dailySpendSeries($campaignIds, $period),
                'daily_community' => $this->dailyCommunitySeries($assetIds, $period),
            ],
            'channel_breakdown' => $this->channelBreakdown($assets, $period),
            'content_breakdown' => $this->metrics->contentTypeBreakdown($assetIds, $period->start, $period->end),
            'top_posts' => [
                'by_reach' => $this->topPosts($assetIds, $period, 'reach'),
                'by_engagement' => $this->topPosts($assetIds, $period, 'engagement'),
                'by_interactions' => $this->topPosts($assetIds, $period, 'interactions'),
            ],
        ];
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @param  Collection<int, int>  $campaignIds
     */
    private function hasHistoricalData(Collection $assetIds, Collection $campaignIds, Carbon $before): bool
    {
        $hasOrganic = OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->where('published_at', '<', $before)
            ->exists();

        if ($hasOrganic) {
            return true;
        }

        if ($campaignIds->isEmpty()) {
            return AccountMetricDaily::query()
                ->whereIn('asset_id', $assetIds)
                ->where('date', '<', $before->toDateString())
                ->exists()
                || OrganicMetricDaily::query()
                    ->whereIn('asset_id', $assetIds)
                    ->where('date', '<', $before->toDateString())
                    ->exists();
        }

        return AdMetricDaily::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('date', '<', $before->toDateString())
            ->exists();
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @param  Collection<int, int>  $campaignIds
     * @return list<array{date: string, organic: float, paid: float}>
     */
    private function dailyReachSeries(Collection $assetIds, Collection $campaignIds, DashboardPeriod $period): array
    {
        $series = $this->dateSeries($period);

        $organicByDay = OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('published_at', [$period->start, $period->end])
            ->get(['published_at', 'raw_metrics'])
            ->groupBy(fn (OrganicPost $post) => $post->published_at?->toDateString())
            ->map(fn (Collection $posts) => $posts->sum(fn (OrganicPost $post) => OrganicMetricResolver::reach($post->raw_metrics ?? [])));

        $paidByDay = collect();

        if ($campaignIds->isNotEmpty()) {
            $paidByDay = AdMetricDaily::query()
                ->selectRaw('date, SUM(reach) as total_reach')
                ->whereIn('campaign_id', $campaignIds)
                ->whereBetween('date', [$period->start->toDateString(), $period->end->toDateString()])
                ->groupBy('date')
                ->pluck('total_reach', 'date');
        }

        return collect($series)->map(fn (string $date) => [
            'date' => $date,
            'organic' => (float) ($organicByDay[$date] ?? 0),
            'paid' => (float) ($paidByDay[$date] ?? 0),
        ])->all();
    }

    /**
     * @param  Collection<int, int>  $campaignIds
     * @return list<array{date: string, spend: float}>
     */
    private function dailySpendSeries(Collection $campaignIds, DashboardPeriod $period): array
    {
        $series = $this->dateSeries($period);

        $spendByDay = collect();

        if ($campaignIds->isNotEmpty()) {
            $spendByDay = AdMetricDaily::query()
                ->selectRaw('date, SUM(spend) as total_spend')
                ->whereIn('campaign_id', $campaignIds)
                ->whereBetween('date', [$period->start->toDateString(), $period->end->toDateString()])
                ->groupBy('date')
                ->pluck('total_spend', 'date');
        }

        return collect($series)->map(fn (string $date) => [
            'date' => $date,
            'spend' => round((float) ($spendByDay[$date] ?? 0), 2),
        ])->all();
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @return list<array{date: string, total: int}>
     */
    private function dailyCommunitySeries(Collection $assetIds, DashboardPeriod $period): array
    {
        return collect($this->dateSeries($period))->map(fn (string $date) => [
            'date' => $date,
            'total' => $this->communityTotalOnDate($assetIds, Carbon::parse($date)),
        ])->all();
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return list<array{channel: string, reach: float, impressions: float}>
     */
    private function channelBreakdown(Collection $assets, DashboardPeriod $period): array
    {
        return $assets->groupBy(fn (ConnectedAsset $asset) => match ($asset->asset_type) {
            AssetType::FacebookPage => 'Facebook',
            AssetType::InstagramAccount => 'Instagram',
            AssetType::TikTokAccount => 'TikTok',
            AssetType::LinkedInPage => 'LinkedIn',
            AssetType::YouTubeChannel => 'YouTube',
            AssetType::MetaAds => 'Meta Ads',
            AssetType::GoogleAds => 'Google Ads',
            default => 'Otro',
        })->map(function (Collection $group, string $channel) use ($period) {
            $assetIds = $group->pluck('id');
            $organic = $this->metrics->organicMetrics($assetIds, $period->start, $period->end);

            $campaignIds = AdCampaign::query()->whereIn('asset_id', $assetIds)->pluck('id');
            $paid = $campaignIds->isNotEmpty()
                ? $this->metrics->paidMetrics($campaignIds, $period->start, $period->end)
                : ['reach' => 0.0, 'impressions' => 0.0];

            $isPaidChannel = in_array($channel, ['Meta Ads', 'Google Ads'], true);

            return [
                'channel' => $channel,
                'reach' => $isPaidChannel ? $paid['reach'] : $organic['reach'],
                'impressions' => $isPaidChannel ? $paid['impressions'] : $organic['impressions'],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @return list<array<string, mixed>>
     */
    private function topPosts(Collection $assetIds, DashboardPeriod $period, string $sortBy): array
    {
        return OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('published_at', [$period->start, $period->end])
            ->with('asset:id,name,asset_type')
            ->get()
            ->sortByDesc(function (OrganicPost $post) use ($sortBy) {
                $metrics = $post->raw_metrics ?? [];

                return match ($sortBy) {
                    'engagement' => OrganicMetricResolver::engagement($metrics),
                    'interactions' => OrganicMetricResolver::interactions($metrics),
                    default => OrganicMetricResolver::reach($metrics),
                };
            })
            ->take(5)
            ->values()
            ->map(fn (OrganicPost $post) => OrganicPostPresenter::present($post))
            ->all();
    }

    /**
     * @return list<string>
     */
    private function dateSeries(DashboardPeriod $period): array
    {
        return collect(CarbonPeriod::create($period->start->copy()->startOfDay(), $period->end->copy()->startOfDay()))
            ->map(fn (Carbon $date) => $date->toDateString())
            ->all();
    }

    /**
     * @param  Collection<int, int>  $assetIds
     */
    private function communityTotalOnDate(Collection $assetIds, Carbon $date): int
    {
        $total = 0;

        foreach ($assetIds as $assetId) {
            $followers = AccountMetricDaily::query()
                ->where('asset_id', $assetId)
                ->where('date', '<=', $date->toDateString())
                ->orderByDesc('date')
                ->value('followers');

            $fans = OrganicMetricDaily::query()
                ->where('asset_id', $assetId)
                ->where('metric_type', 'fan_count')
                ->where('date', '<=', $date->toDateString())
                ->orderByDesc('date')
                ->value('value');

            $total += (int) ($followers ?? $fans ?? 0);
        }

        return $total;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyAnalytics(DashboardPeriod $period): array
    {
        $zero = MetricComparison::compare(0, 0, false);

        return [
            'period' => $period->toFilters(),
            'comparable' => false,
            'kpis' => [
                'reach' => $zero,
                'reach_organic' => 0,
                'reach_paid' => 0,
                'impressions' => $zero,
                'engagement_rate' => $zero,
                'spend' => $zero,
                'follower_growth' => $zero,
                'posts_published' => $zero,
            ],
            'trends' => [
                'daily_reach' => [],
                'daily_spend' => [],
                'daily_community' => [],
            ],
            'channel_breakdown' => [],
            'content_breakdown' => [],
            'top_posts' => [
                'by_reach' => [],
                'by_engagement' => [],
                'by_interactions' => [],
            ],
        ];
    }
}
