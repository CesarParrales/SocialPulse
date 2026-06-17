import PrimaryButton from '@/Components/PrimaryButton';
import Card from '@/Components/UI/Card';
import EmptyState from '@/Components/UI/EmptyState';
import { useTranslation } from '@/lib/i18n';
import { Link } from '@inertiajs/react';

export type OverviewAssetSnapshot = {
    id: number;
    name: string;
    asset_type: string;
    platform: string | null;
    reach: number;
    organic_reach: number;
    paid_reach: number;
    impressions: number;
    posts_count: number;
    spend: number;
    engagement_rate: number;
    community_size: number;
    is_organic: boolean;
    is_paid: boolean;
    has_data: boolean;
};

export type PerformanceSnapshot = {
    period: { days: number; from: string; to: string };
    totals: {
        reach: number;
        organic_reach: number;
        paid_reach: number;
        impressions: number;
        posts_count: number;
        spend: number;
        engagement_rate: number;
        community_total: number;
    };
    assets: OverviewAssetSnapshot[];
    last_ingestion_at: string | null;
};

const assetTypeStyles: Record<string, string> = {
    fb_page: 'border-blue-200 bg-blue-50 text-blue-900',
    ig_account: 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-900',
    meta_ads: 'border-orange-200 bg-orange-50 text-orange-900',
    google_ads: 'border-emerald-200 bg-emerald-50 text-emerald-900',
};

function formatNumber(value: number): string {
    return value.toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function formatCurrency(value: number): string {
    return `$${value.toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
}

function formatPercent(value: number): string {
    return `${value.toFixed(1)}%`;
}

export default function WorkspacePerformanceSnapshot({
    workspaceId,
    snapshot,
}: {
    workspaceId: number;
    snapshot: PerformanceSnapshot;
}) {
    const { t } = useTranslation();
    const { totals, assets, period, last_ingestion_at } = snapshot;
    const hasAssets = assets.length > 0;
    const hasAnyData =
        hasAssets &&
        (totals.reach > 0 ||
            totals.posts_count > 0 ||
            totals.spend > 0 ||
            assets.some((asset) => asset.has_data));

    const typeLabel = (assetType: string) => {
        const key = `asset_scope.types.${assetType}`;
        const translated = t(key);

        return translated === key ? assetType : translated;
    };

    return (
        <Card>
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 className="text-base font-semibold text-sp-ink">
                        {t('workspaces.performance_title')}
                    </h3>
                    <p className="mt-1 text-sm text-sp-muted">
                        {t('workspaces.performance_description', {
                            days: String(period.days),
                            from: period.from,
                            to: period.to,
                        })}
                    </p>
                    {last_ingestion_at && (
                        <p className="mt-2 text-xs text-sp-muted">
                            {t('workspaces.performance_last_ingestion', {
                                date: new Date(
                                    last_ingestion_at,
                                ).toLocaleString(),
                            })}
                        </p>
                    )}
                </div>
                <Link
                    href={route('workspaces.dashboard', [
                        workspaceId,
                        { period: '7d' },
                    ])}
                >
                    <PrimaryButton type="button">
                        {t('workspaces.open_dashboard')}
                    </PrimaryButton>
                </Link>
            </div>

            {!hasAssets ? (
                <div className="mt-6">
                    <EmptyState
                        title={t('workspaces.performance_no_assets_title')}
                        description={t(
                            'workspaces.performance_no_assets_description',
                        )}
                        action={{
                            label: t('workspaces.manage_connections'),
                            href: route(
                                'workspaces.connections.index',
                                workspaceId,
                            ),
                        }}
                    />
                </div>
            ) : (
                <>
                    <div className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <MetricTile
                            label={t('dashboard.reach_total')}
                            value={formatNumber(totals.reach)}
                            hint={t('dashboard.reach_hint', {
                                organic: formatNumber(totals.organic_reach),
                                paid: formatNumber(totals.paid_reach),
                            })}
                        />
                        <MetricTile
                            label={t('dashboard.posts_published')}
                            value={formatNumber(totals.posts_count)}
                        />
                        <MetricTile
                            label={t('dashboard.spend')}
                            value={formatCurrency(totals.spend)}
                        />
                        <MetricTile
                            label={t('workspaces.performance_community')}
                            value={formatNumber(totals.community_total)}
                        />
                    </div>

                    {!hasAnyData && (
                        <p className="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            {t('workspaces.performance_waiting_data')}
                        </p>
                    )}

                    <div className="mt-6 space-y-3">
                        {assets.map((asset) => (
                            <div
                                key={asset.id}
                                className="rounded-xl border border-sp-border bg-sp-surface/40 p-4"
                            >
                                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="truncate font-medium text-sp-ink">
                                                {asset.name}
                                            </p>
                                            <span
                                                className={`rounded-md border px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide ${
                                                    assetTypeStyles[
                                                        asset.asset_type
                                                    ] ??
                                                    'border-sp-border bg-sp-surface text-sp-muted'
                                                }`}
                                            >
                                                {typeLabel(asset.asset_type)}
                                            </span>
                                        </div>
                                        {!asset.has_data && (
                                            <p className="mt-1 text-xs text-sp-muted">
                                                {t(
                                                    'workspaces.performance_asset_pending',
                                                )}
                                            </p>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:max-w-2xl lg:flex-1">
                                        <MiniMetric
                                            label={t('dashboard.reach')}
                                            value={formatNumber(asset.reach)}
                                        />
                                        {asset.is_organic && (
                                            <MiniMetric
                                                label={t('dashboard.posts')}
                                                value={formatNumber(
                                                    asset.posts_count,
                                                )}
                                            />
                                        )}
                                        {asset.is_paid && (
                                            <MiniMetric
                                                label={t('dashboard.spend')}
                                                value={formatCurrency(
                                                    asset.spend,
                                                )}
                                            />
                                        )}
                                        {asset.is_organic && (
                                            <MiniMetric
                                                label={t(
                                                    'dashboard.engagement_rate',
                                                )}
                                                value={formatPercent(
                                                    asset.engagement_rate,
                                                )}
                                            />
                                        )}
                                        {asset.community_size > 0 && (
                                            <MiniMetric
                                                label={t(
                                                    'workspaces.performance_community',
                                                )}
                                                value={formatNumber(
                                                    asset.community_size,
                                                )}
                                            />
                                        )}
                                    </div>

                                    <Link
                                        href={route('workspaces.dashboard', [
                                            workspaceId,
                                            {
                                                period: '7d',
                                                asset_id: asset.id,
                                            },
                                        ])}
                                        className="inline-flex shrink-0 items-center justify-center rounded-lg border border-sp-border px-4 py-2 text-sm font-medium text-sp-ink transition hover:border-sp-primary/40 hover:bg-sp-surface"
                                    >
                                        {t('workspaces.performance_view_asset')}
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                </>
            )}
        </Card>
    );
}

function MetricTile({
    label,
    value,
    hint,
}: {
    label: string;
    value: string;
    hint?: string;
}) {
    return (
        <div className="rounded-xl border border-sp-border bg-sp-surface/60 p-4">
            <p className="text-xs font-medium uppercase tracking-wide text-sp-muted">
                {label}
            </p>
            <p className="mt-1 text-2xl font-semibold tabular-nums text-sp-ink">
                {value}
            </p>
            {hint && (
                <p className="mt-1 text-xs text-sp-muted">{hint}</p>
            )}
        </div>
    );
}

function MiniMetric({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-[11px] font-medium uppercase tracking-wide text-sp-muted">
                {label}
            </p>
            <p className="mt-0.5 text-sm font-semibold tabular-nums text-sp-ink">
                {value}
            </p>
        </div>
    );
}
