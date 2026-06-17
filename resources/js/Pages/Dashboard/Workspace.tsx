import KpiCard from '@/Components/Dashboard/KpiCard';
import KpiVisibilitySelector from '@/Components/Dashboard/KpiVisibilitySelector';
import PeriodSelector from '@/Components/Dashboard/PeriodSelector';
import TopContentPanel from '@/Components/Dashboard/TopContentPanel';
import RecentContentFeed from '@/Components/Dashboard/RecentContentFeed';
import ActiveStoriesPanel from '@/Components/Dashboard/ActiveStoriesPanel';
import TrendCharts from '@/Components/Dashboard/TrendCharts';
import Card from '@/Components/UI/Card';
import EmptyState from '@/Components/UI/EmptyState';
import StatusBadge from '@/Components/UI/StatusBadge';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { AssetScopeConfig } from '@/Components/Dashboard/AssetScopeBar';
import {
    DashboardKpiKey,
    isDashboardKpiKey,
} from '@/lib/dashboardKpis';
import { useTranslation } from '@/lib/i18n';
import { ActiveStory, ContentPost, TopPostsByMetric } from '@/types/content';
import { PageProps, Workspace } from '@/types';
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

type IngestionHealthRow = {
    id: number;
    asset_name: string | null;
    job_type: string;
    status: string;
    records_ingested: number;
    executed_at: string | null;
};

export default function WorkspaceDashboard({
    workspace,
    periodOptions,
    analytics,
    ingestionHealth,
    summary,
    assetScope,
    recentPosts,
    activeStories,
    kpiPreferences,
}: PageProps<{
    workspace: Pick<Workspace, 'id' | 'name' | 'timezone'>;
    periodOptions: Array<{ value: string; label: string }>;
    analytics: Analytics;
    ingestionHealth: IngestionHealthRow[];
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

    return (
        <WorkspaceLayout
            headTitle={`${t('dashboard.title')} — ${workspace.name}`}
            title={t('dashboard.title')}
            description={t('dashboard.description', { name: workspace.name })}
            workspace={workspace}
            active="dashboard"
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
                            title={t('dashboard.no_connections_title')}
                            description={t('dashboard.no_connections_description')}
                            action={{
                                label: t('dashboard.connect_accounts'),
                                href: route(
                                    'workspaces.connections.index',
                                    workspace.id,
                                ),
                            }}
                        />
                    ) : (
                        <>
                    <PeriodSelector
                        workspaceId={workspace.id}
                        periodOptions={periodOptions}
                        filters={analytics.period}
                        selectedAssetId={assetScope.selected_asset_id}
                    />

                    <div className="flex justify-end">
                        <KpiVisibilitySelector
                            workspaceId={workspace.id}
                            visibleKpis={visibleKpis}
                            canCustomize={kpiPreferences.can_customize}
                        />
                    </div>

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

                    <Card>
                        <h3 className="text-base font-semibold text-sp-ink">
                            {t('dashboard.ingestion_health')}
                        </h3>
                        {ingestionHealth.length === 0 ? (
                            <p className="mt-4 text-sm text-sp-muted">
                                {t('dashboard.no_ingestion')}
                            </p>
                        ) : (
                            <div className="mt-4 overflow-x-auto">
                                <table className="min-w-full divide-y divide-sp-border text-sm">
                                    <thead>
                                        <tr>
                                            <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                                {t('dashboard.asset')}
                                            </th>
                                            <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                                {t('dashboard.job')}
                                            </th>
                                            <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                                {t('dashboard.status')}
                                            </th>
                                            <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                                {t('dashboard.executed')}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-sp-border">
                                        {ingestionHealth.map((row) => (
                                            <tr key={row.id}>
                                                <td className="px-3 py-2">
                                                    {row.asset_name ?? '—'}
                                                </td>
                                                <td className="px-3 py-2 text-sp-muted">
                                                    {row.job_type}
                                                </td>
                                                <td className="px-3 py-2">
                                                    <StatusBadge status={row.status} />
                                                </td>
                                                <td className="px-3 py-2 text-sp-muted">
                                                    {row.executed_at
                                                        ? new Date(
                                                              row.executed_at,
                                                          ).toLocaleString()
                                                        : '—'}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </Card>
                        </>
                    )}
                </div>
        </WorkspaceLayout>
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
