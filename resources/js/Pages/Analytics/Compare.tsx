import EmptyState from '@/Components/UI/EmptyState';
import StatusBadge from '@/Components/UI/StatusBadge';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { AssetScopeConfig } from '@/Components/Dashboard/AssetScopeBar';
import { useTranslation } from '@/lib/i18n';
import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { PageProps, Workspace } from '@/types';

type ComparisonRow = {
    metric: string;
    label: string;
    format: 'number' | 'currency' | 'percent';
    left: number;
    right: number;
    delta: {
        change_pct: number | null;
        direction: string;
        comparable: boolean;
    };
};

type ComparisonData =
    | {
          mode: 'side_by_side';
          left_label: string;
          right_label: string;
          rows: ComparisonRow[];
      }
    | {
          mode: 'multi_column';
          columns: Array<{
              key: string;
              label: string;
              reach: number;
              impressions: number;
              posts: number;
          }>;
      };

function formatMetric(value: number, format: ComparisonRow['format']): string {
    if (format === 'currency') {
        return `$${value.toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
    }
    if (format === 'percent') {
        return `${value.toFixed(1)}%`;
    }
    return value.toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function comparisonHasNoData(comparison: ComparisonData): boolean {
    if (comparison.mode === 'side_by_side') {
        return comparison.rows.every((row) => row.left === 0 && row.right === 0);
    }

    return (
        comparison.columns.length === 0 ||
        comparison.columns.every(
            (col) =>
                col.reach === 0 && col.impressions === 0 && col.posts === 0,
        )
    );
}

export default function Compare({
    workspace,
    filters,
    comparisonTypes,
    periodOptions,
    comparison,
    hasConnectedAssets,
    assetScope,
}: PageProps<{
    workspace: Pick<Workspace, 'id' | 'name'>;
    filters: {
        type: string;
        period: string;
        left_start: string;
        left_end: string;
        right_start: string;
        right_end: string;
        asset_id: number | null;
    };
    comparisonTypes: Array<{ value: string; label: string }>;
    periodOptions: Array<{ value: string; label: string }>;
    comparison: ComparisonData;
    hasConnectedAssets: boolean;
    assetScope: AssetScopeConfig;
}>) {
    const { t } = useTranslation();
    const [leftStart, setLeftStart] = useState(filters.left_start);
    const [leftEnd, setLeftEnd] = useState(filters.left_end);
    const [rightStart, setRightStart] = useState(filters.right_start);
    const [rightEnd, setRightEnd] = useState(filters.right_end);

    const applyFilters = (extra: Record<string, string | number | null> = {}) => {
        router.get(
            route('workspaces.compare', workspace.id),
            {
                type: filters.type,
                period: filters.period,
                left_start: leftStart,
                left_end: leftEnd,
                right_start: rightStart,
                right_end: rightEnd,
                ...(filters.asset_id != null
                    ? { asset_id: filters.asset_id }
                    : {}),
                ...extra,
            },
            { preserveState: true, replace: true },
        );
    };

    const submitCustomPeriods = (event: FormEvent) => {
        event.preventDefault();
        applyFilters({ type: 'period_vs_period' });
    };

    const noComparisonData =
        hasConnectedAssets && comparisonHasNoData(comparison);

    return (
        <WorkspaceLayout
            headTitle={`${t('compare.title')} — ${workspace.name}`}
            title={t('compare.title')}
            description={t('compare.description', { name: workspace.name })}
            workspace={workspace}
            active="compare"
            assetScope={{
                ...assetScope,
                preserveQuery: {
                    type: filters.type,
                    period: filters.period,
                    left_start: leftStart,
                    left_end: leftEnd,
                    right_start: rightStart,
                    right_end: rightEnd,
                },
            }}
        >
                <div className="space-y-6">
                    {!hasConnectedAssets ? (
                        <EmptyState
                            title={t('compare.no_connections_title')}
                            description={t('compare.no_connections_description')}
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
                    <div className="sp-card p-4">
                        <p className="text-sm font-medium text-sp-ink">
                            {t('compare.type_label')}
                        </p>
                        <div className="mt-2 flex flex-wrap gap-2">
                            {comparisonTypes.map((option) => (
                                <button
                                    key={option.value}
                                    type="button"
                                    onClick={() =>
                                        applyFilters({ type: option.value })
                                    }
                                    className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                                        filters.type === option.value
                                            ? 'bg-sp-primary text-white'
                                            : 'bg-sp-surface text-sp-muted hover:text-sp-ink'
                                    }`}
                                >
                                    {option.label}
                                </button>
                            ))}
                        </div>

                        {[
                            'organic_vs_paid',
                            'facebook_vs_instagram',
                            'content_types',
                        ].includes(filters.type) && (
                            <div className="mt-4">
                                <p className="text-sm text-sp-muted">
                                    {t('dashboard.period')}
                                </p>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {periodOptions.map((option) => (
                                        <button
                                            key={option.value}
                                            type="button"
                                            onClick={() =>
                                                applyFilters({
                                                    period: option.value,
                                                })
                                            }
                                            className={`rounded-md px-3 py-1.5 text-sm ${
                                                filters.period === option.value
                                                    ? 'bg-sp-primary text-white'
                                                    : 'bg-sp-surface text-sp-muted'
                                            }`}
                                        >
                                            {option.label}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {filters.type === 'period_vs_period' && (
                            <form
                                onSubmit={submitCustomPeriods}
                                className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
                            >
                                <label className="text-sm text-sp-muted">
                                    {t('compare.period_a_from')}
                                    <input
                                        type="date"
                                        value={leftStart}
                                        onChange={(e) =>
                                            setLeftStart(e.target.value)
                                        }
                                        className="sp-input mt-1 block w-full text-sm"
                                    />
                                </label>
                                <label className="text-sm text-sp-muted">
                                    {t('compare.period_a_to')}
                                    <input
                                        type="date"
                                        value={leftEnd}
                                        onChange={(e) =>
                                            setLeftEnd(e.target.value)
                                        }
                                        className="sp-input mt-1 block w-full text-sm"
                                    />
                                </label>
                                <label className="text-sm text-sp-muted">
                                    {t('compare.period_b_from')}
                                    <input
                                        type="date"
                                        value={rightStart}
                                        onChange={(e) =>
                                            setRightStart(e.target.value)
                                        }
                                        className="sp-input mt-1 block w-full text-sm"
                                    />
                                </label>
                                <label className="text-sm text-sp-muted">
                                    {t('compare.period_b_to')}
                                    <input
                                        type="date"
                                        value={rightEnd}
                                        onChange={(e) =>
                                            setRightEnd(e.target.value)
                                        }
                                        className="sp-input mt-1 block w-full text-sm"
                                    />
                                </label>
                                <div className="sm:col-span-2 lg:col-span-4">
                                    <button
                                        type="submit"
                                        className="rounded-lg bg-sp-primary px-4 py-2 text-sm font-medium text-white hover:bg-sp-primary-hover"
                                    >
                                        {t('compare.compare_periods')}
                                    </button>
                                </div>
                            </form>
                        )}
                    </div>

                    {noComparisonData ? (
                        <EmptyState
                            title={t('compare.no_data_title')}
                            description={t('compare.no_data_description')}
                        />
                    ) : comparison.mode === 'side_by_side' ? (
                        <div className="sp-card overflow-hidden">
                            <table className="min-w-full divide-y divide-sp-border text-sm">
                                <thead className="bg-sp-surface">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium text-sp-muted">
                                            {t('common.metric')}
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-sp-muted">
                                            {comparison.left_label}
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-sp-muted">
                                            {comparison.right_label}
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-sp-muted">
                                            {t('common.variation')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sp-border">
                                    {comparison.rows.map((row) => (
                                        <tr key={row.metric}>
                                            <td className="px-4 py-3 font-medium text-sp-ink">
                                                {row.label}
                                            </td>
                                            <td className="px-4 py-3">
                                                {formatMetric(
                                                    row.left,
                                                    row.format,
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                {formatMetric(
                                                    row.right,
                                                    row.format,
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                {!row.delta.comparable ? (
                                                    <StatusBadge
                                                        status="pending"
                                                        label={t(
                                                            'compare.no_history',
                                                        )}
                                                    />
                                                ) : row.delta.change_pct !==
                                                  null ? (
                                                    <StatusBadge
                                                        status={
                                                            row.delta
                                                                .direction ===
                                                            'up'
                                                                ? 'active'
                                                                : row.delta
                                                                        .direction ===
                                                                    'down'
                                                                  ? 'failed'
                                                                  : 'processing'
                                                        }
                                                        label={`${
                                                            row.delta
                                                                .direction ===
                                                            'up'
                                                                ? '↑'
                                                                : row.delta
                                                                        .direction ===
                                                                    'down'
                                                                  ? '↓'
                                                                  : '→'
                                                        } ${Math.abs(
                                                            row.delta
                                                                .change_pct,
                                                        ).toFixed(1)}%`}
                                                    />
                                                ) : (
                                                    '—'
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="sp-card overflow-hidden">
                            <table className="min-w-full divide-y divide-sp-border text-sm">
                                <thead className="bg-sp-surface">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium text-sp-muted">
                                            {t('common.type')}
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-sp-muted">
                                            {t('dashboard.reach')}
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-sp-muted">
                                            {t('dashboard.impressions')}
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-sp-muted">
                                            {t('dashboard.posts')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sp-border">
                                    {comparison.columns.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={4}
                                                className="px-4 py-6 text-center text-sp-muted"
                                            >
                                                {t('compare.no_content')}
                                            </td>
                                        </tr>
                                    ) : (
                                        comparison.columns.map((col) => (
                                            <tr key={col.key}>
                                                <td className="px-4 py-3 font-medium">
                                                    {col.label}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {col.reach.toLocaleString()}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {col.impressions.toLocaleString()}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {col.posts}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    )}
                        </>
                    )}
                </div>
        </WorkspaceLayout>
    );
}
