<?php

namespace Modules\Analytics\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Analytics\Models\BenchmarkSnapshot;
use Modules\Analytics\Support\BenchmarkRating;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Services\WorkspaceMetricsAggregator;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Models\Workspace;

class WorkspaceBenchmarkService
{
    private const BASELINE_DAYS = 90;

    private const CURRENT_DAYS = 30;

    public function __construct(
        private readonly WorkspaceMetricsAggregator $metrics,
        private readonly IndustryBenchmarkResolver $industryBenchmarks,
    ) {}

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    public function build(Workspace $workspace, Collection $assets): array
    {
        $assetIds = $assets->pluck('id');
        $campaignIds = AdCampaign::query()->whereIn('asset_id', $assetIds)->pluck('id');

        $baselineEnd = now()->subDay()->endOfDay();
        $baselineStart = now()->subDays(self::BASELINE_DAYS)->startOfDay();
        $currentEnd = now()->endOfDay();
        $currentStart = now()->subDays(self::CURRENT_DAYS - 1)->startOfDay();

        $baselineOrganic = $this->metrics->organicMetrics($assetIds, $baselineStart, $baselineEnd);
        $currentOrganic = $this->metrics->organicMetrics($assetIds, $currentStart, $currentEnd);

        $baselineReachPerPost = $this->averageReachPerPost($assetIds, $baselineStart, $baselineEnd);
        $currentReachPerPost = $this->averageReachPerPost($assetIds, $currentStart, $currentEnd);

        $baselineCpm = $this->averageCpm($campaignIds, $baselineStart, $baselineEnd);
        $currentCpm = $this->averageCpm($campaignIds, $currentStart, $currentEnd);

        $hasBaseline = $this->hasBaselineData($assetIds, $campaignIds, $baselineStart);
        $industry = $this->industryBenchmarks->resolve($workspace, $assetIds);

        $result = [
            'baseline_window' => [
                'days' => self::BASELINE_DAYS,
                'from' => $baselineStart->toDateString(),
                'to' => $baselineEnd->toDateString(),
            ],
            'current_window' => [
                'days' => self::CURRENT_DAYS,
                'from' => $currentStart->toDateString(),
                'to' => $currentEnd->toDateString(),
            ],
            'has_baseline' => $hasBaseline,
            'industry_benchmark_available' => (bool) ($industry['available'] ?? false),
            'industry_sample_size' => (int) ($industry['sample_size'] ?? 0),
            'industry_segment' => $industry['segment'] ?? null,
            'metrics' => [
                'engagement_rate' => $this->metricWithIndustry(
                    BenchmarkRating::rate(
                        $currentOrganic['engagement_rate'],
                        $baselineOrganic['engagement_rate'],
                        higherIsBetter: true,
                    ),
                    $industry,
                    'engagement_rate',
                    $currentOrganic['engagement_rate'],
                    true,
                    'percent',
                    __('app.benchmarks.metrics.engagement_rate'),
                ),
                'reach_per_post' => $this->metricWithIndustry(
                    BenchmarkRating::rate(
                        $currentReachPerPost,
                        $baselineReachPerPost,
                        higherIsBetter: true,
                    ),
                    $industry,
                    'reach_per_post',
                    $currentReachPerPost,
                    true,
                    'number',
                    __('app.benchmarks.metrics.reach_per_post'),
                ),
                'cpm' => $this->metricWithIndustry(
                    BenchmarkRating::rate(
                        $currentCpm,
                        $baselineCpm,
                        higherIsBetter: false,
                    ),
                    $industry,
                    'cpm',
                    $currentCpm,
                    false,
                    'currency',
                    __('app.benchmarks.metrics.cpm'),
                ),
            ],
        ];

        if ($hasBaseline) {
            $this->storeSnapshot($workspace, $assetIds, $baselineStart, $baselineEnd, $baselineOrganic, $baselineReachPerPost, $baselineCpm);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>|null  $industry
     * @param  array<string, mixed>  $internal
     * @return array<string, mixed>
     */
    private function metricWithIndustry(
        array $internal,
        ?array $industry,
        string $metricKey,
        float $current,
        bool $higherIsBetter,
        string $unit,
        string $name,
    ): array {
        $metric = array_merge($internal, ['unit' => $unit, 'name' => $name]);

        if (
            ($industry['available'] ?? false)
            && isset($industry['metrics'][$metricKey])
        ) {
            $metric['industry'] = BenchmarkRating::rate(
                $current,
                (float) $industry['metrics'][$metricKey],
                higherIsBetter: $higherIsBetter,
            );
        }

        return $metric;
    }

    /**
     * @param  Collection<int, int>  $assetIds
     */
    private function averageReachPerPost(Collection $assetIds, Carbon $start, Carbon $end): float
    {
        if ($assetIds->isEmpty()) {
            return 0.0;
        }

        $posts = OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('published_at', [$start, $end])
            ->get(['raw_metrics']);

        if ($posts->isEmpty()) {
            return 0.0;
        }

        $totalReach = $posts->sum(fn (OrganicPost $post) => (float) ($post->raw_metrics['reach'] ?? 0));

        return round($totalReach / $posts->count(), 2);
    }

    /**
     * @param  Collection<int, int>  $campaignIds
     */
    private function averageCpm(Collection $campaignIds, Carbon $start, Carbon $end): float
    {
        if ($campaignIds->isEmpty()) {
            return 0.0;
        }

        $query = AdMetricDaily::query()
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        $spend = (float) (clone $query)->sum('spend');
        $impressions = (float) (clone $query)->sum('impressions');

        if ($impressions <= 0) {
            return 0.0;
        }

        return round(($spend / $impressions) * 1000, 4);
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @param  Collection<int, int>  $campaignIds
     */
    private function hasBaselineData(Collection $assetIds, Collection $campaignIds, Carbon $baselineStart): bool
    {
        $hasPosts = OrganicPost::query()
            ->whereIn('asset_id', $assetIds)
            ->where('published_at', '<', now()->subDays(7))
            ->exists();

        if ($hasPosts) {
            return true;
        }

        if ($campaignIds->isEmpty()) {
            return false;
        }

        return AdMetricDaily::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('date', '>=', $baselineStart->toDateString())
            ->exists();
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @param  array{engagement_rate: float}  $baselineOrganic
     */
    private function storeSnapshot(
        Workspace $workspace,
        Collection $assetIds,
        Carbon $baselineStart,
        Carbon $baselineEnd,
        array $baselineOrganic,
        float $baselineReachPerPost,
        float $baselineCpm,
    ): void {
        $today = now()->toDateString();

        $exists = BenchmarkSnapshot::query()
            ->where('workspace_id', $workspace->id)
            ->whereNull('asset_id')
            ->whereDate('calculated_at', $today)
            ->exists();

        if ($exists) {
            return;
        }

        BenchmarkSnapshot::query()->create([
            'workspace_id' => $workspace->id,
            'asset_id' => null,
            'period_start' => $baselineStart->toDateString(),
            'period_end' => $baselineEnd->toDateString(),
            'engagement_rate_avg' => $baselineOrganic['engagement_rate'],
            'reach_avg' => $baselineReachPerPost,
            'cpm_avg' => $baselineCpm > 0 ? $baselineCpm : null,
            'calculated_at' => now(),
        ]);
    }
}
