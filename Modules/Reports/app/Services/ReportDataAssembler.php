<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Analytics\Enums\ComparisonType;
use Modules\Analytics\Models\CompetitorInsight;
use Modules\Analytics\Services\CompetitorBenchmarkService;
use Modules\Analytics\Services\WorkspaceComparisonService;
use Modules\Analytics\Support\ComparisonContext;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Dashboard\Services\WorkspaceAnalyticsService;
use Modules\Dashboard\Services\WorkspaceDashboardService;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Reports\Models\Report;
use Modules\Workspaces\Models\Workspace;

class ReportDataAssembler
{
    public function __construct(
        private readonly WorkspaceDashboardService $dashboard,
        private readonly WorkspaceAnalyticsService $analytics,
        private readonly WorkspaceComparisonService $comparisons,
        private readonly ReportChannelInsightsService $channelInsights,
        private readonly ReportNarrativeService $narrative,
        private readonly ReportAppendixService $appendix,
        private readonly ReportOrganicMetaSummaryService $organicMetaSummary,
        private readonly CompetitorBenchmarkService $competitorBenchmarks,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function assemble(Report $report, Workspace $workspace): array
    {
        $period = DashboardPeriod::fromDates($report->period_start, $report->period_end);
        $config = $report->config;

        $assets = ConnectedAsset::query()
            ->whereHas('connection', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'connection_id', 'asset_type', 'name']);

        $base = $this->dashboard->build($workspace, $period, $assets);
        $analytics = $this->analytics->build($assets, $period);
        $channelSections = $this->channelInsights->build($workspace, $assets, $period);

        $comparison = null;

        if ($config['sections']['comparisons'] ?? false) {
            $comparison = $this->comparisons->build(
                $assets,
                new ComparisonContext(
                    ComparisonType::OrganicVsPaid,
                    $period->start,
                    $period->end,
                    $period->start,
                    $period->end,
                    'Orgánico',
                    'Pagado',
                ),
            );
        }

        $narrative = $this->narrative->build($analytics, $channelSections, $comparison);
        $organicMeta = ($config['sections']['overview'] ?? false)
            ? $this->organicMetaSummary->build($assets, $period, $channelSections)
            : null;

        if ($organicMeta !== null) {
            $narrative = $this->narrative->withoutCrossChannelDuplicate($narrative);
        }

        $appendix = $this->appendix->build($assets, $period, $analytics);
        $competitorInsight = CompetitorInsight::query()->where('workspace_id', $workspace->id)->first();
        $competitors = ($config['sections']['competitors'] ?? false)
            ? $this->competitorBenchmarks->buildOverview($workspace, $assets)
            : null;

        return [
            'workspace' => [
                'name' => $workspace->name,
                'timezone' => $workspace->timezone,
                'industry_category' => $workspace->industry_category,
            ],
            'report' => [
                'name' => $report->name,
                'title' => $config['title'],
                'period' => [
                    'from' => $report->period_start->toDateString(),
                    'to' => $report->period_end->toDateString(),
                    'days' => $period->days(),
                ],
                'generated_at' => now()->timezone($workspace->timezone ?? config('app.timezone'))->format('d/m/Y H:i'),
            ],
            'branding' => [
                'primary_color' => $config['primary_color'],
                'secondary_color' => $config['secondary_color'],
                'logo_data_uri' => $this->logoDataUri($config['logo_path'] ?? null),
            ],
            'sections' => $config['sections'],
            'metrics' => $config['metrics'],
            'analytics' => $analytics,
            'paid_summary' => $base['paidSummary'],
            'channel_sections' => $channelSections,
            'comparison' => $comparison,
            'narrative' => $narrative,
            'organic_meta_summary' => $organicMeta,
            'appendix' => $appendix,
            'competitors' => $competitors,
            'competitor_insight' => $competitorInsight ? [
                'text' => $competitorInsight->reportText(),
                'is_reviewed' => $competitorInsight->isReviewed(),
                'source' => $competitorInsight->isReviewed() ? 'ai_assisted_reviewed' : 'ai_assisted_draft',
            ] : null,
        ];
    }

    private function logoDataUri(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($path);
        $mime = Storage::disk('local')->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
