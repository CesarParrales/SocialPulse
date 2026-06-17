<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Collection;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Support\PlatformCatalog;
use Modules\Dashboard\Services\WorkspaceAnalyticsService;
use Modules\Dashboard\Services\WorkspaceDashboardService;
use Modules\Dashboard\Services\WorkspaceMetricsAggregator;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Dashboard\Support\OrganicMetricResolver;
use Modules\Dashboard\Support\OrganicPostPresenter;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Models\Workspace;

class ReportChannelInsightsService
{
    /** @var list<string> */
    private const POST_FORMATS = ['feed', 'video'];

    /** @var list<string> */
    private const REEL_FORMATS = ['reel'];

    public function __construct(
        private readonly WorkspaceAnalyticsService $analytics,
        private readonly WorkspaceDashboardService $dashboard,
        private readonly WorkspaceMetricsAggregator $metricsAggregator,
    ) {}

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return list<array<string, mixed>>
     */
    public function build(Workspace $workspace, Collection $assets, DashboardPeriod $period): array
    {
        if ($assets->isEmpty()) {
            return [];
        }

        $sections = [];

        foreach ($this->organicChannels() as $channelKey => $assetType) {
            $channelAssets = $assets->where('asset_type', $assetType)->values();

            if ($channelAssets->isEmpty()) {
                continue;
            }

            $analytics = $this->analytics->build($channelAssets, $period);
            $profile = PlatformCatalog::forAssetType($assetType);

            $sections[] = [
                'key' => $channelKey,
                'label' => $profile['label'] ?? $channelKey,
                'kind' => 'organic',
                'kpis' => $analytics['kpis'],
                'content_breakdown' => $analytics['content_breakdown'],
                'top_posts' => $this->topContent($channelAssets, $period, self::POST_FORMATS, 3),
                'top_reels' => $this->topContent($channelAssets, $period, self::REEL_FORMATS, 3),
                'supplemental' => $this->metricsAggregator->organicSupplementalMetrics(
                    $channelAssets->pluck('id'),
                    $period->start,
                    $period->end,
                ),
            ];
        }

        foreach ($this->paidChannels() as $channelKey => $assetType) {
            $channelAssets = $assets->where('asset_type', $assetType)->values();

            if ($channelAssets->isEmpty()) {
                continue;
            }

            $paidSummary = $this->dashboard->build($workspace, $period, $channelAssets)['paidSummary'];

            $profile = PlatformCatalog::forAssetType($assetType);

            $sections[] = [
                'key' => $channelKey,
                'label' => $profile['label'] ?? $channelKey,
                'kind' => 'paid',
                'paid_summary' => $paidSummary,
            ];
        }

        return $sections;
    }

    /**
     * @return array<string, AssetType>
     */
    private function organicChannels(): array
    {
        return [
            'facebook' => AssetType::FacebookPage,
            'instagram' => AssetType::InstagramAccount,
            'tiktok' => AssetType::TikTokAccount,
            'linkedin' => AssetType::LinkedInPage,
            'youtube' => AssetType::YouTubeChannel,
        ];
    }

    /**
     * @return array<string, AssetType>
     */
    private function paidChannels(): array
    {
        return [
            'meta_ads' => AssetType::MetaAds,
            'google_ads' => AssetType::GoogleAds,
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @param  list<string>  $formats
     * @return list<array<string, mixed>>
     */
    private function topContent(Collection $assets, DashboardPeriod $period, array $formats, int $limit): array
    {
        return OrganicPost::query()
            ->whereIn('asset_id', $assets->pluck('id'))
            ->whereIn('post_type', $formats)
            ->whereBetween('published_at', [$period->start, $period->end])
            ->with('asset:id,name,asset_type')
            ->get()
            ->sortByDesc(fn (OrganicPost $post) => OrganicMetricResolver::reach($post->raw_metrics ?? []))
            ->take($limit)
            ->values()
            ->map(fn (OrganicPost $post) => OrganicPostPresenter::present($post))
            ->all();
    }
}
