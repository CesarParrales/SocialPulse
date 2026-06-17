<?php

namespace Modules\Analytics\Services;

use Illuminate\Support\Collection;
use Modules\Analytics\Enums\CommunitySizeBand;
use Modules\Analytics\Models\IndustryBenchmarkSegment;
use Modules\Dashboard\Services\WorkspaceMetricsAggregator;
use Modules\Workspaces\Models\Workspace;

class IndustryBenchmarkResolver
{
    public function __construct(
        private readonly WorkspaceMetricsAggregator $metrics,
    ) {}

    /**
     * @param  Collection<int, int>  $assetIds
     * @return array<string, mixed>|null
     */
    public function resolve(Workspace $workspace, Collection $assetIds): ?array
    {
        if ($workspace->industry_category === null || $workspace->industry_category === '') {
            return null;
        }

        $followers = $this->metrics->currentCommunityTotal($assetIds);
        $band = CommunitySizeBand::fromFollowerCount($followers);
        $region = $workspace->region ?? 'global';

        $segment = IndustryBenchmarkSegment::query()
            ->where('industry_category', $workspace->industry_category)
            ->where('community_size_band', $band->value)
            ->where('region', $region)
            ->first();

        if ($segment === null) {
            return [
                'available' => false,
                'sample_size' => 0,
                'segment' => [
                    'industry' => $workspace->industry_category,
                    'community_size_band' => $band->value,
                    'community_size_label' => $band->label(),
                    'region' => $region,
                ],
            ];
        }

        return [
            'available' => $segment->isRepresentative(),
            'sample_size' => $segment->sample_size,
            'segment' => [
                'industry' => $segment->industry_category,
                'community_size_band' => $segment->community_size_band,
                'community_size_label' => $band->label(),
                'region' => $segment->region,
            ],
            'metrics' => [
                'engagement_rate' => (float) $segment->engagement_rate_avg,
                'reach_per_post' => (float) $segment->reach_avg,
                'cpm' => $segment->cpm_avg !== null ? (float) $segment->cpm_avg : 0.0,
            ],
        ];
    }
}
