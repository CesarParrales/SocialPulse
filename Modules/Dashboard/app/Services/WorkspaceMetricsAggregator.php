<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Dashboard\Support\OrganicMetricResolver;
use Modules\Ingestion\Models\AccountMetricDaily;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\OrganicMetricDaily;
use Modules\Ingestion\Models\OrganicPost;

class WorkspaceMetricsAggregator
{
    /**
     * @param  Collection<int, int>  $assetIds
     * @return array{reach: float, impressions: float, engagement_rate: float, posts_count: int, spend: float}
     */
    public function snapshot(
        Collection $assetIds,
        Collection $campaignIds,
        Carbon $start,
        Carbon $end,
    ): array {
        $organic = $this->organicMetrics($assetIds, $start, $end);
        $paid = $this->paidMetrics($campaignIds, $start, $end);

        return [
            'reach' => $organic['reach'] + $paid['reach'],
            'impressions' => $organic['impressions'] + $paid['impressions'],
            'engagement_rate' => $organic['engagement_rate'],
            'posts_count' => $organic['posts_count'],
            'spend' => $paid['spend'],
            'organic_reach' => $organic['reach'],
            'paid_reach' => $paid['reach'],
            'follower_growth' => $this->followerGrowth($assetIds, $start, $end),
        ];
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @return array{reach: float, impressions: float, engagement_rate: float, posts_count: int}
     */
    public function organicMetrics(Collection $assetIds, Carbon $start, Carbon $end): array
    {
        if ($assetIds->isEmpty()) {
            return [
                'reach' => 0.0,
                'impressions' => 0.0,
                'engagement_rate' => 0.0,
                'posts_count' => 0,
            ];
        }

        $posts = OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('published_at', [$start, $end])
            ->get(['raw_metrics']);

        $reach = 0.0;
        $impressions = 0.0;
        $engagementSum = 0.0;
        $reachForRate = 0.0;

        foreach ($posts as $post) {
            $metrics = $post->raw_metrics ?? [];
            $postReach = OrganicMetricResolver::reach($metrics);
            $reach += $postReach;
            $impressions += OrganicMetricResolver::impressions($metrics);
            $engagement = OrganicMetricResolver::engagement($metrics);

            if ($postReach > 0) {
                $engagementSum += $engagement;
                $reachForRate += $postReach;
            }
        }

        return [
            'reach' => $reach,
            'impressions' => $impressions,
            'engagement_rate' => $reachForRate > 0 ? round(($engagementSum / $reachForRate) * 100, 2) : 0.0,
            'posts_count' => $posts->count(),
        ];
    }

    /**
     * @param  Collection<int, int>  $campaignIds
     * @return array{reach: float, impressions: float, spend: float, clicks: float}
     */
    public function paidMetrics(Collection $campaignIds, Carbon $start, Carbon $end): array
    {
        if ($campaignIds->isEmpty()) {
            return ['reach' => 0.0, 'impressions' => 0.0, 'spend' => 0.0, 'clicks' => 0.0];
        }

        $query = AdMetricDaily::query()
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        return [
            'reach' => (float) (clone $query)->sum('reach'),
            'impressions' => (float) (clone $query)->sum('impressions'),
            'spend' => (float) (clone $query)->sum('spend'),
            'clicks' => (float) (clone $query)->sum('clicks'),
        ];
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @return array<string, float>
     */
    public function organicSupplementalMetrics(Collection $assetIds, Carbon $start, Carbon $end): array
    {
        if ($assetIds->isEmpty()) {
            return $this->emptySupplementalMetrics();
        }

        $totals = $this->emptySupplementalMetrics();

        $posts = OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('published_at', [$start, $end])
            ->get(['raw_metrics']);

        foreach ($posts as $post) {
            $metrics = $post->raw_metrics ?? [];

            $totals['link_clicks'] += (float) ($metrics['clicks'] ?? $metrics['link_clicks'] ?? 0);
            $totals['video_views'] += (float) ($metrics['video_views'] ?? $metrics['plays'] ?? $metrics['views'] ?? 0);
            $totals['reactions'] += (float) (is_numeric($metrics['reactions'] ?? null) ? $metrics['reactions'] : 0);
            $totals['comments'] += (float) ($metrics['comments'] ?? 0);
            $totals['shares'] += (float) ($metrics['shares'] ?? 0);
            $totals['likes'] += (float) ($metrics['likes'] ?? 0);
            $totals['saved'] += (float) ($metrics['saved'] ?? 0);
            $totals['profile_views'] += (float) ($metrics['profile_visits'] ?? 0);
        }

        $accountProfileViews = (float) AccountMetricDaily::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('profile_views');

        $totals['profile_views'] += $accountProfileViews;

        return $totals;
    }

    /**
     * @return array<string, float>
     */
    private function emptySupplementalMetrics(): array
    {
        return [
            'link_clicks' => 0.0,
            'video_views' => 0.0,
            'reactions' => 0.0,
            'comments' => 0.0,
            'shares' => 0.0,
            'likes' => 0.0,
            'saved' => 0.0,
            'profile_views' => 0.0,
        ];
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @return list<array{type: string, reach: float, impressions: float, posts: int}>
     */
    public function contentTypeBreakdown(Collection $assetIds, Carbon $start, Carbon $end): array
    {
        if ($assetIds->isEmpty()) {
            return [];
        }

        return OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('published_at', [$start, $end])
            ->get(['post_type', 'raw_metrics'])
            ->groupBy('post_type')
            ->map(fn (Collection $posts, string $type) => [
                'type' => $type,
                'reach' => $posts->sum(fn (OrganicPost $post) => OrganicMetricResolver::reach($post->raw_metrics ?? [])),
                'impressions' => $posts->sum(fn (OrganicPost $post) => OrganicMetricResolver::impressions($post->raw_metrics ?? [])),
                'posts' => $posts->count(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, int>  $assetIds
     */
    public function followerGrowth(Collection $assetIds, Carbon $start, Carbon $end): float
    {
        return (float) ($this->communityTotalOnDate($assetIds, $end) - $this->communityTotalOnDate($assetIds, $start));
    }

    public function currentCommunityTotal(Collection $assetIds): int
    {
        return $this->communityTotalOnDate($assetIds, now());
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
}
