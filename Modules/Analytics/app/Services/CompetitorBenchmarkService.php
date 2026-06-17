<?php

namespace Modules\Analytics\Services;

use Illuminate\Support\Collection;
use Modules\Analytics\Models\CompetitorAccount;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Services\WorkspaceAnalyticsService;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Workspaces\Models\Workspace;

class CompetitorBenchmarkService
{
    public function __construct(
        private readonly WorkspaceAnalyticsService $analytics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildOverview(Workspace $workspace, Collection $assets): array
    {
        $competitors = CompetitorAccount::query()
            ->where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $period = DashboardPeriod::fromPreset('30d');
        $clientMetrics = $this->clientMetrics($assets, $period);

        return [
            'client' => $clientMetrics,
            'competitors' => $competitors->map->toBenchmarkRow()->values()->all(),
            'comparison_rows' => $this->comparisonRows($clientMetrics, $competitors),
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    public function clientMetrics(Collection $assets, DashboardPeriod $period): array
    {
        if ($assets->isEmpty()) {
            return [
                'label' => 'Tu marca',
                'followers_count' => null,
                'avg_reach' => null,
                'avg_engagement_rate' => null,
                'source' => 'ingested',
            ];
        }

        $analytics = $this->analytics->build($assets, $period);
        $postsCount = (int) ($analytics['kpis']['posts_published']['current'] ?? 0);
        $totalReach = (float) ($analytics['kpis']['reach_organic'] ?? $analytics['kpis']['reach']['current'] ?? 0);

        return [
            'label' => 'Tu marca',
            'followers_count' => null,
            'avg_reach' => $postsCount > 0 ? round($totalReach / $postsCount, 2) : null,
            'avg_engagement_rate' => isset($analytics['kpis']['engagement_rate']['current'])
                ? (float) $analytics['kpis']['engagement_rate']['current']
                : null,
            'total_reach' => $totalReach,
            'posts_count' => $postsCount,
            'source' => 'ingested',
        ];
    }

    /**
     * @param  Collection<int, CompetitorAccount>  $competitors
     * @return list<array<string, mixed>>
     */
    private function comparisonRows(array $client, Collection $competitors): array
    {
        $rows = [];

        foreach ($competitors as $competitor) {
            $rows[] = [
                'name' => $competitor->name,
                'platform' => $competitor->platform,
                'followers_count' => $competitor->followers_count,
                'avg_reach' => $competitor->avg_reach,
                'avg_engagement_rate' => $competitor->avg_engagement_rate,
                'data_source_note' => $competitor->data_source_note,
                'source' => 'manual',
                'vs_client' => [
                    'followers_pct' => $this->deltaPct($client['followers_count'], $competitor->followers_count),
                    'reach_pct' => $this->deltaPct($client['avg_reach'], $competitor->avg_reach),
                    'engagement_pct' => $this->deltaPct($client['avg_engagement_rate'], $competitor->avg_engagement_rate),
                ],
            ];
        }

        return $rows;
    }

    private function deltaPct(mixed $clientValue, mixed $competitorValue): ?float
    {
        if (! is_numeric($clientValue) || ! is_numeric($competitorValue) || (float) $competitorValue == 0.0) {
            return null;
        }

        return round((((float) $clientValue - (float) $competitorValue) / (float) $competitorValue) * 100, 1);
    }
}
