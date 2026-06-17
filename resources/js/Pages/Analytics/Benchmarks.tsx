import CalloutBanner from '@/Components/UI/CalloutBanner';
import EmptyState from '@/Components/UI/EmptyState';
import StatusBadge from '@/Components/UI/StatusBadge';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { AssetScopeConfig } from '@/Components/Dashboard/AssetScopeBar';
import { useTranslation } from '@/lib/i18n';
import { Link } from '@inertiajs/react';
import { PageProps, Workspace } from '@/types';

type BenchmarkMetric = {
    name: string;
    unit: 'percent' | 'number' | 'currency';
    status: 'good' | 'normal' | 'poor' | 'insufficient';
    label: string;
    ratio_pct: number | null;
    current: number;
    baseline: number;
    industry?: {
        status: BenchmarkMetric['status'];
        label: string;
        ratio_pct: number | null;
        baseline: number;
    };
};

type BenchmarksData = {
    baseline_window: { days: number; from: string; to: string };
    current_window: { days: number; from: string; to: string };
    has_baseline: boolean;
    industry_benchmark_available: boolean;
    industry_sample_size: number;
    industry_segment?: {
        industry: string;
        community_size_label: string;
        region: string;
    } | null;
    metrics: {
        engagement_rate: BenchmarkMetric;
        reach_per_post: BenchmarkMetric;
        cpm: BenchmarkMetric;
    };
};

const statusStyles: Record<
    BenchmarkMetric['status'],
    { ring: string; bg: string; dot: string }
> = {
    good: {
        ring: 'ring-green-200',
        bg: 'bg-green-50',
        dot: 'bg-green-500',
    },
    normal: {
        ring: 'ring-amber-200',
        bg: 'bg-amber-50',
        dot: 'bg-amber-500',
    },
    poor: {
        ring: 'ring-red-200',
        bg: 'bg-red-50',
        dot: 'bg-red-500',
    },
    insufficient: {
        ring: 'ring-gray-200',
        bg: 'bg-gray-50',
        dot: 'bg-gray-400',
    },
};

function formatValue(value: number, unit: BenchmarkMetric['unit']): string {
    if (unit === 'percent') {
        return `${value.toFixed(2)}%`;
    }
    if (unit === 'currency') {
        return `$${value.toFixed(2)}`;
    }
    return value.toLocaleString(undefined, { maximumFractionDigits: 0 });
}

const statusBadge: Record<BenchmarkMetric['status'], string> = {
    good: 'active',
    normal: 'pending',
    poor: 'failed',
    insufficient: 'processing',
};

function MetricCard({ metric }: { metric: BenchmarkMetric }) {
    const { t } = useTranslation();
    const styles = statusStyles[metric.status];

    return (
        <div
            className={`rounded-xl p-6 shadow-sm ring-1 ${styles.ring} ${styles.bg}`}
        >
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-medium text-sp-ink">
                        {metric.name}
                    </p>
                    <p className="mt-2 text-3xl font-semibold tabular-nums text-sp-ink">
                        {formatValue(metric.current, metric.unit)}
                    </p>
                    <p className="mt-1 text-sm text-sp-muted">
                        {t('benchmarks.avg_90d')}:{' '}
                        {formatValue(metric.baseline, metric.unit)}
                    </p>
                </div>
                <StatusBadge
                    status={statusBadge[metric.status]}
                    label={metric.label}
                />
            </div>
            <p className="mt-4 text-sm font-medium text-sp-ink">
                {metric.ratio_pct !== null && (
                    <span className="font-normal text-sp-muted">
                        {t('benchmarks.vs_avg', {
                            pct: String(metric.ratio_pct),
                        })}
                    </span>
                )}
            </p>
            {metric.industry && (
                <p className="mt-2 text-xs text-sp-muted">
                    {t('benchmarks.industry_label')}: {metric.industry.label}
                    {metric.industry.ratio_pct !== null && (
                        <span>
                            {' '}
                            (
                            {t('benchmarks.industry_segment', {
                                pct: String(metric.industry.ratio_pct),
                                value: formatValue(
                                    metric.industry.baseline,
                                    metric.unit,
                                ),
                            })}
                            )
                        </span>
                    )}
                </p>
            )}
        </div>
    );
}

export default function Benchmarks({
    workspace,
    benchmarks,
    hasConnectedAssets,
    assetScope,
}: PageProps<{
    workspace: Pick<Workspace, 'id' | 'name' | 'industry_category'>;
    benchmarks: BenchmarksData;
    hasConnectedAssets: boolean;
    assetScope: AssetScopeConfig;
}>) {
    const { t } = useTranslation();

    return (
        <WorkspaceLayout
            headTitle={`${t('benchmarks.title')} — ${workspace.name}`}
            title={t('benchmarks.title')}
            description={t('benchmarks.description', { name: workspace.name })}
            workspace={workspace}
            active="benchmarks"
            assetScope={assetScope}
        >
                <div className="space-y-6">
                    {!hasConnectedAssets ? (
                        <EmptyState
                            title={t('benchmarks.no_connections_title')}
                            description={t('benchmarks.no_connections_description')}
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
                    <div className="sp-card p-6">
                        <h3 className="text-base font-semibold text-sp-ink">
                            {t('benchmarks.internal')}
                        </h3>
                        <p className="mt-2 text-sm text-sp-muted">
                            {t('benchmarks.internal_body', {
                                current_days: String(benchmarks.current_window.days),
                                current_from: benchmarks.current_window.from,
                                current_to: benchmarks.current_window.to,
                                baseline_days: String(benchmarks.baseline_window.days),
                                baseline_from: benchmarks.baseline_window.from,
                                baseline_to: benchmarks.baseline_window.to,
                            })}
                        </p>
                        {!benchmarks.has_baseline && (
                            <div className="mt-3">
                                <CalloutBanner
                                    title={t('benchmarks.no_baseline_title')}
                                    variant="warning"
                                >
                                    {t('benchmarks.no_baseline')}
                                </CalloutBanner>
                            </div>
                        )}
                    </div>

                    <div className="grid gap-4 md:grid-cols-3">
                        <MetricCard metric={benchmarks.metrics.engagement_rate} />
                        <MetricCard metric={benchmarks.metrics.reach_per_post} />
                        <MetricCard metric={benchmarks.metrics.cpm} />
                    </div>

                    <div className="rounded-xl border border-dashed border-sp-border bg-sp-surface p-6">
                        <h3 className="text-sm font-semibold text-sp-ink">
                            {t('benchmarks.industry')}
                        </h3>
                        <p className="mt-2 text-sm text-sp-muted">
                            {benchmarks.industry_benchmark_available ? (
                                <>
                                    {t('benchmarks.industry_available', {
                                        count: String(benchmarks.industry_sample_size),
                                    })}
                                    {benchmarks.industry_segment && (
                                        <span className="mt-2 block text-xs">
                                            {benchmarks.industry_segment.industry} ·{' '}
                                            {benchmarks.industry_segment.community_size_label}{' '}
                                            · {benchmarks.industry_segment.region}
                                        </span>
                                    )}
                                </>
                            ) : (
                                <>
                                    {t('benchmarks.industry_unavailable')}
                                    {!workspace.industry_category && (
                                        <Link
                                            href={route(
                                                'settings.workspace.edit',
                                                workspace.id,
                                            )}
                                            className="mt-2 inline-block text-sm font-medium text-sp-primary hover:underline"
                                        >
                                            {t(
                                                'benchmarks.configure_segmentation',
                                            )}{' '}
                                            →
                                        </Link>
                                    )}
                                    {benchmarks.industry_sample_size > 0 && (
                                        <span className="mt-1 block">
                                            {t('benchmarks.industry_sample', {
                                                count: String(
                                                    benchmarks.industry_sample_size,
                                                ),
                                            })}
                                        </span>
                                    )}
                                </>
                            )}
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-4 text-xs text-sp-muted">
                        <span className="flex items-center gap-1">
                            <span className="h-2 w-2 rounded-full bg-emerald-500" />
                            {t('benchmarks.status_good')}
                        </span>
                        <span className="flex items-center gap-1">
                            <span className="h-2 w-2 rounded-full bg-amber-500" />
                            {t('benchmarks.status_normal')}
                        </span>
                        <span className="flex items-center gap-1">
                            <span className="h-2 w-2 rounded-full bg-red-500" />
                            {t('benchmarks.status_poor')}
                        </span>
                    </div>
                        </>
                    )}
                </div>
        </WorkspaceLayout>
    );
}
