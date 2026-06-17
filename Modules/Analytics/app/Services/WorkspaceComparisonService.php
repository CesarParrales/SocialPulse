<?php

namespace Modules\Analytics\Services;

use Illuminate\Support\Collection;
use Modules\Analytics\Enums\ComparisonType;
use Modules\Analytics\Support\ComparisonContext;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Services\WorkspaceMetricsAggregator;
use Modules\Dashboard\Support\MetricComparison;
use Modules\Ingestion\Models\AdCampaign;

class WorkspaceComparisonService
{
    public function __construct(
        private readonly WorkspaceMetricsAggregator $metrics,
    ) {}

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    public function build(Collection $assets, ComparisonContext $context): array
    {
        return match ($context->type) {
            ComparisonType::OrganicVsPaid => $this->organicVsPaid($assets, $context),
            ComparisonType::FacebookVsInstagram => $this->facebookVsInstagram($assets, $context),
            ComparisonType::ContentTypes => $this->contentTypes($assets, $context),
            default => $this->periodComparison($assets, $context),
        };
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    private function periodComparison(Collection $assets, ComparisonContext $context): array
    {
        $assetIds = $assets->pluck('id');
        $campaignIds = $this->campaignIds($assetIds);

        $left = $this->metrics->snapshot($assetIds, $campaignIds, $context->leftStart, $context->leftEnd);
        $right = $this->metrics->snapshot($assetIds, $campaignIds, $context->rightStart, $context->rightEnd);

        return [
            'mode' => 'side_by_side',
            'left_label' => $context->leftLabel,
            'right_label' => $context->rightLabel,
            'rows' => $this->comparisonRows($left, $right, true),
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    private function organicVsPaid(Collection $assets, ComparisonContext $context): array
    {
        $assetIds = $assets->pluck('id');
        $campaignIds = $this->campaignIds($assetIds);

        $organic = $this->metrics->organicMetrics($assetIds, $context->leftStart, $context->leftEnd);
        $paid = $this->metrics->paidMetrics($campaignIds, $context->leftStart, $context->leftEnd);

        $left = [
            'reach' => $organic['reach'],
            'impressions' => $organic['impressions'],
            'engagement_rate' => $organic['engagement_rate'],
            'posts_count' => $organic['posts_count'],
            'spend' => 0.0,
            'follower_growth' => $this->metrics->followerGrowth($assetIds, $context->leftStart, $context->leftEnd),
        ];

        $right = [
            'reach' => $paid['reach'],
            'impressions' => $paid['impressions'],
            'engagement_rate' => 0.0,
            'posts_count' => 0,
            'spend' => $paid['spend'],
            'follower_growth' => 0.0,
        ];

        return [
            'mode' => 'side_by_side',
            'left_label' => 'Orgánico',
            'right_label' => 'Pagado',
            'rows' => $this->comparisonRows($left, $right, true),
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    private function facebookVsInstagram(Collection $assets, ComparisonContext $context): array
    {
        $facebookIds = $assets->where('asset_type', AssetType::FacebookPage)->pluck('id');
        $instagramIds = $assets->where('asset_type', AssetType::InstagramAccount)->pluck('id');

        $left = $this->metrics->organicMetrics($facebookIds, $context->leftStart, $context->leftEnd);
        $right = $this->metrics->organicMetrics($instagramIds, $context->leftStart, $context->leftEnd);

        $left['spend'] = 0.0;
        $left['follower_growth'] = $this->metrics->followerGrowth($facebookIds, $context->leftStart, $context->leftEnd);
        $right['spend'] = 0.0;
        $right['follower_growth'] = $this->metrics->followerGrowth($instagramIds, $context->leftStart, $context->leftEnd);

        return [
            'mode' => 'side_by_side',
            'left_label' => 'Facebook',
            'right_label' => 'Instagram',
            'rows' => $this->comparisonRows($left, $right, true),
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array<string, mixed>
     */
    private function contentTypes(Collection $assets, ComparisonContext $context): array
    {
        $breakdown = $this->metrics->contentTypeBreakdown(
            $assets->pluck('id'),
            $context->leftStart,
            $context->leftEnd,
        );

        return [
            'mode' => 'multi_column',
            'columns' => collect($breakdown)->map(fn (array $row) => [
                'key' => $row['type'],
                'label' => ucfirst($row['type']),
                'reach' => $row['reach'],
                'impressions' => $row['impressions'],
                'posts' => $row['posts'],
            ])->all(),
        ];
    }

    /**
     * @param  array<string, float|int>  $left
     * @param  array<string, float|int>  $right
     * @return list<array<string, mixed>>
     */
    private function comparisonRows(array $left, array $right, bool $comparable): array
    {
        $definitions = [
            'reach' => ['label' => __('app.compare.metrics.reach'), 'format' => 'number'],
            'impressions' => ['label' => __('app.compare.metrics.impressions'), 'format' => 'number'],
            'engagement_rate' => ['label' => __('app.compare.metrics.engagement_rate'), 'format' => 'percent'],
            'spend' => ['label' => __('app.compare.metrics.spend'), 'format' => 'currency'],
            'posts_count' => ['label' => __('app.compare.metrics.posts_count'), 'format' => 'number'],
            'follower_growth' => ['label' => __('app.compare.metrics.follower_growth'), 'format' => 'number'],
        ];

        $rows = [];

        foreach ($definitions as $key => $meta) {
            $leftValue = (float) ($left[$key] ?? 0);
            $rightValue = (float) ($right[$key] ?? 0);

            if ($leftValue === 0.0 && $rightValue === 0.0 && in_array($key, ['spend'], true)) {
                continue;
            }

            $rows[] = [
                'metric' => $key,
                'label' => $meta['label'],
                'format' => $meta['format'],
                'left' => $leftValue,
                'right' => $rightValue,
                'delta' => MetricComparison::compare($leftValue, $rightValue, $comparable),
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, int>  $assetIds
     * @return Collection<int, int>
     */
    private function campaignIds(Collection $assetIds): Collection
    {
        return AdCampaign::query()->whereIn('asset_id', $assetIds)->pluck('id');
    }
}
