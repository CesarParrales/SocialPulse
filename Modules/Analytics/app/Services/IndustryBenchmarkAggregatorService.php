<?php

namespace Modules\Analytics\Services;

use Modules\Analytics\Enums\CommunitySizeBand;
use Modules\Analytics\Models\BenchmarkSnapshot;
use Modules\Analytics\Models\IndustryBenchmarkSegment;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Services\WorkspaceMetricsAggregator;
use Modules\Workspaces\Models\Workspace;

class IndustryBenchmarkAggregatorService
{
    public function __construct(
        private readonly WorkspaceMetricsAggregator $metrics,
    ) {}

    public function aggregate(): int
    {
        $snapshots = BenchmarkSnapshot::query()
            ->whereNull('asset_id')
            ->where('calculated_at', '>=', now()->subDays(14))
            ->orderByDesc('calculated_at')
            ->get()
            ->unique('workspace_id');

        if ($snapshots->isEmpty()) {
            return 0;
        }

        $workspaceIds = $snapshots->pluck('workspace_id');
        $workspaces = Workspace::query()
            ->whereIn('id', $workspaceIds)
            ->whereNotNull('industry_category')
            ->get()
            ->keyBy('id');

        $grouped = [];

        foreach ($snapshots as $snapshot) {
            $workspace = $workspaces->get($snapshot->workspace_id);

            if ($workspace === null) {
                continue;
            }

            $assetIds = ConnectedAsset::query()
                ->whereHas('connection', fn ($query) => $query->where('workspace_id', $workspace->id))
                ->where('is_active', true)
                ->pluck('id');

            $followers = $this->metrics->currentCommunityTotal($assetIds);
            $band = CommunitySizeBand::fromFollowerCount($followers);
            $region = $workspace->region ?? 'global';
            $key = implode('|', [$workspace->industry_category, $band->value, $region]);

            $grouped[$key]['industry_category'] = $workspace->industry_category;
            $grouped[$key]['community_size_band'] = $band->value;
            $grouped[$key]['region'] = $region;
            $grouped[$key]['engagement_rates'][] = (float) $snapshot->engagement_rate_avg;
            $grouped[$key]['reach_avgs'][] = (float) $snapshot->reach_avg;
            if ($snapshot->cpm_avg !== null) {
                $grouped[$key]['cpm_avgs'][] = (float) $snapshot->cpm_avg;
            }
        }

        $calculatedAt = now();
        $segmentsWritten = 0;

        foreach ($grouped as $segment) {
            IndustryBenchmarkSegment::query()->updateOrCreate(
                [
                    'industry_category' => $segment['industry_category'],
                    'community_size_band' => $segment['community_size_band'],
                    'region' => $segment['region'],
                ],
                [
                    'sample_size' => count($segment['engagement_rates']),
                    'engagement_rate_avg' => round(collect($segment['engagement_rates'])->avg(), 4),
                    'reach_avg' => round(collect($segment['reach_avgs'])->avg(), 4),
                    'cpm_avg' => isset($segment['cpm_avgs'])
                        ? round(collect($segment['cpm_avgs'])->avg(), 4)
                        : null,
                    'calculated_at' => $calculatedAt,
                ],
            );

            $segmentsWritten++;
        }

        return $segmentsWritten;
    }
}
