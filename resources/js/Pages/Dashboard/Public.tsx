import KpiCard from '@/Components/Dashboard/KpiCard';
import PeriodSelector from '@/Components/Dashboard/PeriodSelector';
import TopContentPanel from '@/Components/Dashboard/TopContentPanel';
import RecentContentFeed from '@/Components/Dashboard/RecentContentFeed';
import ActiveStoriesPanel from '@/Components/Dashboard/ActiveStoriesPanel';
import TrendCharts from '@/Components/Dashboard/TrendCharts';
import EmptyState from '@/Components/UI/EmptyState';
import PublicDashboardLayout from '@/Components/Templates/PublicDashboardLayout';
import { AssetScopeConfig } from '@/Components/Dashboard/AssetScopeBar';
import {
    DashboardKpiKey,
    isDashboardKpiKey,
} from '@/lib/dashboardKpis';
import { useTranslation } from '@/lib/i18n';
import { ActiveStory, ContentPost, TopPostsByMetric } from '@/types/content';
import { PageProps } from '@/types';
import { useMemo } from 'react';

type KpiComparison = {
    current: number;
    previous: number;
    change_pct: number | null;
    direction: 'up' | 'down' | 'flat';
    comparable: boolean;
};

type Analytics = {
    period: { period: string; from: string; to: string };
    comparable: boolean;
    kpis: {
        reach: KpiComparison;
        reach_organic: number;
        reach_paid: number;
        impressions: KpiComparison;
        engagement_rate: KpiComparison;
        spend: KpiComparison;
        follower_growth: KpiComparison;
        posts_published: KpiComparison;
    };
    trends: {
        daily_reach: Array<{ date: string; organic: number; paid: number }>;
        daily_spend: Array<{ date: string; spend: number }>;
        daily_community: Array<{ date: string; total: number }>;
    };
    channel_breakdown: Array<{
        channel: string;
        reach: number;
        impressions: number;
    }>;
    content_breakdown: Array<{ type: string; reach: number; posts: number }>;
    top_posts: TopPostsByMetric;
};

export default function PublicDashboard({
    shareToken,
    workspace,
    periodOptions,
    analytics,
    summary,
    assetScope,
    recentPosts,
    activeStories,
    kpiPreferences,
}: PageProps<{
    shareToken: string;
    workspace: Pick<{ name: string; timezone: string }, 'name' | 'timezone'>;
    periodOptions: Array<{ value: string; label: string }>;
    analytics: Analytics;
    summary: { connected_assets: number };
    assetScope: AssetScopeConfig;
    recentPosts: ContentPost[];
    activeStories: ActiveStory[];
    kpiPreferences: {
        visible_kpis: string[];
        can_customize: boolean;
    };
}>) {
    const { t } = useTranslation();
    const { kpis, trends, channel_breakdown, content_breakdown, top_posts } =
        analytics;
    const visibleKpis = useMemo(
        () =>
            kpiPreferences.visible_kpis.filter(isDashboardKpiKey) as DashboardKpiKey[],
        [kpiPreferences.visible_kpis],
    );

    const kpiCards = useMemo(
        () =>
            [
                {
                    key: 'reach' as const,
                    label: t('dashboard.reach_total'),
                    value: kpis.reach.current,
                    comparison: kpis.reach,
                    hint: t('dashboard.reach_hint', {
                        organic: kpis.reach_organic.toLocaleString(),
                        paid: kpis.reach_paid.toLocaleString(),
                    }),
                },
                {
                    key: 'impressions' as const,
                    label: t('dashboard.impressions'),
                    value: kpis.impressions.current,
                    comparison: kpis.impressions,
                },
                {
                    key: 'engagement_rate' as const,
                    label: t('dashboard.engagement_rate'),
                    value: kpis.engagement_rate.current,
                    comparison: kpis.engagement_rate,
                    format: 'percent' as const,
                },
                {
                    key: 'spend' as const,
                    label: t('dashboard.spend'),
                    value: kpis.spend.current,
                    comparison: kpis.spend,
                    format: 'currency' as const,
                },
                {
                    key: 'follower_growth' as const,
                    label: t('dashboard.follower_growth'),
                    value: kpis.follower_growth.current,
                    comparison: kpis.follower_growth,
                },
                {
                    key: 'posts_published' as const,
                    label: t('dashboard.posts_published'),
                    value: kpis.posts_published.current,
                    comparison: kpis.posts_published,
                },
            ].filter((card) => visibleKpis.includes(card.key)),
        [kpis, t, visibleKpis],
    );

    const hasConnectedAssets = summary.connected_assets > 0;
    const routeParams = { token: shareToken };

    return (
        <PublicDashboardLayout
            headTitle={`${t('dashboard.title')} — ${workspace.name}`}
            title={t('dashboard.title')}
            description={t('public_dashboard.description', {
                name: workspace.name,
            })}
            workspaceName={workspace.name}
            assetScope={{
                ...assetScope,
                preserveQuery: {
                    period: analytics.period.period,
                    from: analytics.period.from,
                    to: analytics.period.to,
                },
            }}
        >
            <div className="space-y-6">
                {!hasConnectedAssets ? (
                    <EmptyState
                        title={t('public_dashboard.no_data_title')}
                        description={t('public_dashboard.no_data_description')}
                    />
                ) : (
                    <>
                        <PeriodSelector
                            routeName="public.dashboard"
                            routeParams={routeParams}
                            periodOptions={periodOptions}
                            filters={analytics.period}
                            selectedAssetId={assetScope.selected_asset_id}
                        />

                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-[repeat(auto-fit,minmax(11rem,1fr))]">
                            {kpiCards.map((card) => (
                                <KpiCard
                                    key={card.key}
                                    label={card.label}
                                    value={card.value}
                                    comparison={card.comparison}
                                    format={card.format}
                                    hint={card.hint}
                                />
                            ))}
                        </div>

                        <TrendCharts
                            dailyReach={trends.daily_reach}
                            dailySpend={trends.daily_spend}
                            dailyCommunity={trends.daily_community}
                        />

                        <RecentContentFeed posts={recentPosts} />

                        <TopContentPanel topPosts={top_posts} />

                        <ActiveStoriesPanel stories={activeStories} />

                        <div className="grid gap-6 lg:grid-cols-2">
                            <BreakdownTable
                                title={t('dashboard.channel_breakdown')}
                                rows={channel_breakdown.map((row) => ({
                                    label: row.channel,
                                    col1: row.reach.toLocaleString(),
                                    col2: row.impressions.toLocaleString(),
                                }))}
                                col1Label={t('dashboard.reach')}
                                col2Label={t('dashboard.impressions')}
                                channelLabel={t('dashboard.channel')}
                                emptyLabel={t('common.no_data')}
                            />
                            <BreakdownTable
                                title={t('dashboard.content_breakdown')}
                                rows={content_breakdown.map((row) => ({
                                    label: row.type,
                                    col1: row.reach.toLocaleString(),
                                    col2: String(row.posts),
                                }))}
                                col1Label={t('dashboard.reach')}
                                col2Label={t('dashboard.posts')}
                                channelLabel={t('dashboard.channel')}
                                emptyLabel={t('common.no_data')}
                            />
                        </div>
                    </>
                )}
            </div>
        </PublicDashboardLayout>
    );
}

function BreakdownTable({
    title,
    rows,
    col1Label,
    col2Label,
    channelLabel,
    emptyLabel,
}: {
    title: string;
    rows: Array<{ label: string; col1: string; col2: string }>;
    col1Label: string;
    col2Label: string;
    channelLabel: string;
    emptyLabel: string;
}) {
    return (
        <div className="sp-card relative overflow-hidden p-5">
            <h3 className="text-base font-semibold text-sp-ink">{title}</h3>
            {rows.length === 0 ? (
                <p className="mt-4 text-sm text-sp-muted">{emptyLabel}</p>
            ) : (
                <table className="mt-4 min-w-full divide-y divide-sp-border text-sm">
                    <thead>
                        <tr>
                            <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                {channelLabel}
                            </th>
                            <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                {col1Label}
                            </th>
                            <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                {col2Label}
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-sp-border">
                        {rows.map((row) => (
                            <tr key={row.label}>
                                <td className="px-3 py-2">{row.label}</td>
                                <td className="px-3 py-2">{row.col1}</td>
                                <td className="px-3 py-2">{row.col2}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
}
