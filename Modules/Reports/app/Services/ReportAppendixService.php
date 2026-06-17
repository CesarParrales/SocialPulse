<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Support\PlatformCatalog;
use Modules\Dashboard\Services\WorkspaceMetricsAggregator;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Dashboard\Support\MetricComparison;
use Modules\Dashboard\Support\OrganicPostPresenter;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Reports\Support\MetaMetricLabels;

class ReportAppendixService
{
    public function __construct(
        private readonly WorkspaceMetricsAggregator $metrics,
    ) {}

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @param  array<string, mixed>  $analytics
     * @return array<string, mixed>
     */
    public function build(Collection $assets, DashboardPeriod $period, array $analytics): array
    {
        $comparable = (bool) ($analytics['comparable'] ?? false);

        return [
            'title' => 'Anexo tabular',
            'summary' => $this->summaryTable($analytics),
            'assets' => $this->assetTables($assets, $period, $comparable),
            'posts' => $this->postsTable($assets, $period),
            'campaigns' => $this->campaignsTable($assets, $period),
            'daily_reach' => $this->dailyReachTable($analytics),
        ];
    }

    /**
     * @param  array<string, mixed>  $analytics
     * @return array{columns: list<string>, rows: list<array<string, mixed>>}
     */
    private function summaryTable(array $analytics): array
    {
        $labels = [
            'reach' => 'Alcance total',
            'impressions' => 'Impresiones totales',
            'engagement_rate' => 'Engagement rate orgánico',
            'spend' => 'Inversión pagada',
            'follower_growth' => 'Crecimiento de comunidad',
            'posts_published' => 'Publicaciones orgánicas',
        ];

        $rows = [];

        foreach ($labels as $key => $label) {
            $metric = $analytics['kpis'][$key] ?? null;

            if (! is_array($metric)) {
                continue;
            }

            $rows[] = $this->comparisonRow($label, $metric, $this->formatForKey($key));
        }

        return [
            'columns' => ['Métrica', 'Actual', 'Anterior', 'Variación'],
            'rows' => $rows,
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return list<array<string, mixed>>
     */
    private function assetTables(Collection $assets, DashboardPeriod $period, bool $comparable): array
    {
        $tables = [];

        foreach ($assets as $asset) {
            $assetIds = collect([$asset->id]);
            $campaignIds = AdCampaign::query()->where('asset_id', $asset->id)->pluck('id');
            $profile = PlatformCatalog::forAssetType($asset->asset_type);
            $channelLabel = $profile['label'] ?? $asset->asset_type->value;

            $rows = [];

            if ($asset->asset_type->isPaid()) {
                $current = $this->metrics->paidMetrics($campaignIds, $period->start, $period->end);
                $previous = $this->metrics->paidMetrics($campaignIds, $period->previousStart, $period->previousEnd);

                foreach (MetaMetricLabels::kpiLabelsForAssetType($asset->asset_type) as $key => $meta) {
                    $rows[] = $this->comparisonRow(
                        $meta['label'],
                        MetricComparison::compare($current[$key] ?? 0, $previous[$key] ?? 0, $comparable),
                        $meta['format'],
                    );
                }
            } else {
                $current = $this->metrics->organicMetrics($assetIds, $period->start, $period->end);
                $previous = $this->metrics->organicMetrics($assetIds, $period->previousStart, $period->previousEnd);
                $currentSupplemental = $this->metrics->organicSupplementalMetrics($assetIds, $period->start, $period->end);
                $previousSupplemental = $this->metrics->organicSupplementalMetrics($assetIds, $period->previousStart, $period->previousEnd);

                foreach (MetaMetricLabels::kpiLabelsForAssetType($asset->asset_type) as $key => $meta) {
                    $currentValue = match ($key) {
                        'posts_published' => $current['posts_count'],
                        'engagement_rate' => $current['engagement_rate'],
                        default => $current[$key] ?? 0,
                    };
                    $previousValue = match ($key) {
                        'posts_published' => $previous['posts_count'],
                        'engagement_rate' => $previous['engagement_rate'],
                        default => $previous[$key] ?? 0,
                    };

                    $rows[] = $this->comparisonRow(
                        $meta['label'],
                        MetricComparison::compare($currentValue, $previousValue, $comparable),
                        $meta['format'],
                    );
                }

                foreach (MetaMetricLabels::supplementalLabelsForAssetType($asset->asset_type) as $key => $meta) {
                    $rows[] = $this->comparisonRow(
                        $meta['label'],
                        MetricComparison::compare(
                            $currentSupplemental[$key] ?? 0,
                            $previousSupplemental[$key] ?? 0,
                            $comparable,
                        ),
                        $meta['format'],
                    );
                }
            }

            $tables[] = [
                'asset_name' => $asset->name,
                'channel' => $channelLabel,
                'columns' => ['Métrica', 'Actual', 'Anterior', 'Variación'],
                'rows' => $rows,
            ];
        }

        return $tables;
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array{columns: list<string>, rows: list<array<string, mixed>>}
     */
    private function postsTable(Collection $assets, DashboardPeriod $period): array
    {
        if ($assets->isEmpty()) {
            return ['columns' => array_values(MetaMetricLabels::postColumnLabels()), 'rows' => []];
        }

        $organicAssets = $assets->filter(fn (ConnectedAsset $asset) => ! $asset->asset_type->isPaid());

        $rows = OrganicPost::query()
            ->whereIn('asset_id', $organicAssets->pluck('id'))
            ->whereBetween('published_at', [$period->start, $period->end])
            ->with('asset:id,name,asset_type')
            ->get()
            ->sortByDesc(fn (OrganicPost $post) => (float) ($post->raw_metrics['reach'] ?? 0))
            ->take(50)
            ->values()
            ->map(function (OrganicPost $post) {
                $presented = OrganicPostPresenter::present($post);
                $metrics = $presented['metrics'] ?? [];
                $profile = PlatformCatalog::forAssetType($post->asset->asset_type);

                return [
                    'channel' => $profile['label'] ?? $post->asset->asset_type->value,
                    'asset' => $post->asset->name,
                    'type' => ucfirst((string) $presented['post_type']),
                    'published_at' => isset($presented['published_at'])
                        ? Carbon::parse($presented['published_at'])->format('d/m/Y')
                        : '—',
                    'reach' => (float) ($metrics['reach'] ?? 0),
                    'impressions' => (float) ($metrics['impressions'] ?? 0),
                    'interactions' => (float) ($metrics['interactions'] ?? 0),
                    'link_clicks' => (float) ($metrics['clicks'] ?? 0),
                    'video_views' => (float) (($metrics['video_views'] ?? 0) + ($metrics['plays'] ?? 0)),
                    'preview' => Str::limit((string) ($presented['content_preview'] ?? 'Post'), 80),
                ];
            })
            ->all();

        return [
            'columns' => array_values(MetaMetricLabels::postColumnLabels()),
            'column_keys' => array_keys(MetaMetricLabels::postColumnLabels()),
            'rows' => $rows,
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return array{columns: list<string>, rows: list<array<string, mixed>>}
     */
    private function campaignsTable(Collection $assets, DashboardPeriod $period): array
    {
        $paidAssets = $assets->filter(fn (ConnectedAsset $asset) => $asset->asset_type->isPaid());

        if ($paidAssets->isEmpty()) {
            return [
                'columns' => ['Campaña', 'Activo', 'Canal', 'Inversión', 'Impresiones', 'Alcance', 'Clics'],
                'rows' => [],
            ];
        }

        $campaignIds = AdCampaign::query()
            ->whereIn('asset_id', $paidAssets->pluck('id'))
            ->pluck('id');

        if ($campaignIds->isEmpty()) {
            return [
                'columns' => ['Campaña', 'Activo', 'Canal', 'Inversión', 'Impresiones', 'Alcance', 'Clics'],
                'rows' => [],
            ];
        }

        $aggregates = AdMetricDaily::query()
            ->selectRaw('campaign_id, SUM(spend) as total_spend, SUM(impressions) as total_impressions, SUM(reach) as total_reach, SUM(clicks) as total_clicks')
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween('date', [$period->start->toDateString(), $period->end->toDateString()])
            ->groupBy('campaign_id')
            ->orderByDesc('total_spend')
            ->get();

        $campaigns = AdCampaign::query()
            ->with('asset:id,name,asset_type')
            ->whereIn('id', $aggregates->pluck('campaign_id'))
            ->get()
            ->keyBy('id');

        $rows = $aggregates->map(function ($row) use ($campaigns) {
            $campaign = $campaigns->get($row->campaign_id);
            $profile = $campaign?->asset
                ? PlatformCatalog::forAssetType($campaign->asset->asset_type)
                : null;

            return [
                'campaign' => $campaign?->name ?? '—',
                'asset' => $campaign?->asset?->name ?? '—',
                'channel' => $profile['label'] ?? 'Paid',
                'spend' => round((float) $row->total_spend, 2),
                'impressions' => (int) $row->total_impressions,
                'reach' => (int) $row->total_reach,
                'clicks' => (int) $row->total_clicks,
            ];
        })->all();

        return [
            'columns' => ['Campaña', 'Activo', 'Canal', 'Inversión', 'Impresiones', 'Alcance', 'Clics'],
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $analytics
     * @return array{columns: list<string>, rows: list<array<string, mixed>>}
     */
    private function dailyReachTable(array $analytics): array
    {
        $series = $analytics['trends']['daily_reach'] ?? [];

        return [
            'columns' => ['Fecha', 'Alcance orgánico', 'Alcance pagado', 'Total'],
            'rows' => collect($series)->map(fn (array $row) => [
                'date' => $row['date'] ?? '—',
                'organic' => (float) ($row['organic'] ?? 0),
                'paid' => (float) ($row['paid'] ?? 0),
                'total' => (float) (($row['organic'] ?? 0) + ($row['paid'] ?? 0)),
            ])->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $metric
     * @return array<string, mixed>
     */
    private function comparisonRow(string $label, array $metric, string $format): array
    {
        return [
            'label' => $label,
            'current' => $this->formatValue((float) ($metric['current'] ?? 0), $format),
            'previous' => $this->formatValue((float) ($metric['previous'] ?? 0), $format),
            'change' => $this->formatChange($metric),
        ];
    }

    /**
     * @param  array<string, mixed>  $metric
     */
    private function formatChange(array $metric): string
    {
        if (! ($metric['comparable'] ?? false)) {
            return 'Sin histórico';
        }

        $change = $metric['change_pct'] ?? null;

        if ($change === null) {
            return '—';
        }

        $sign = $change >= 0 ? '+' : '';

        return $sign.number_format((float) $change, 1).'%';
    }

    private function formatValue(float $value, string $format): string
    {
        return match ($format) {
            'currency' => '$'.number_format($value, 2),
            'percent' => number_format($value, 2).'%',
            default => number_format($value, 0, '.', ','),
        };
    }

    private function formatForKey(string $key): string
    {
        return match ($key) {
            'engagement_rate' => 'percent',
            'spend' => 'currency',
            default => 'number',
        };
    }
}
