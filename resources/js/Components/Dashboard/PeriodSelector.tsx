import { router } from '@inertiajs/react';
import { useTranslation } from '@/lib/i18n';
import { FormEvent, useState } from 'react';

type PeriodOption = { value: string; label: string };

export default function PeriodSelector({
    workspaceId,
    routeName = 'workspaces.dashboard',
    routeParams,
    periodOptions,
    filters,
    selectedAssetId,
}: {
    workspaceId?: number;
    routeName?: string;
    routeParams?: Record<string, string | number>;
    periodOptions: PeriodOption[];
    filters: { period: string; from: string; to: string };
    selectedAssetId?: number | null;
}) {
    const { t } = useTranslation();
    const [customFrom, setCustomFrom] = useState(filters.from);
    const [customTo, setCustomTo] = useState(filters.to);

    const scopeParams =
        selectedAssetId != null ? { asset_id: selectedAssetId } : {};

    const targetUrl = () =>
        routeParams !== undefined
            ? route(routeName, routeParams)
            : route(routeName, workspaceId as number);

    const applyPreset = (period: string) => {
        router.get(
            targetUrl(),
            period === 'custom'
                ? { period, from: customFrom, to: customTo, ...scopeParams }
                : { period, ...scopeParams },
            { preserveState: true, replace: true },
        );
    };

    const submitCustom = (event: FormEvent) => {
        event.preventDefault();
        applyPreset('custom');
    };

    return (
        <div className="sp-card flex flex-col gap-4 p-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p className="text-sm font-medium text-sp-ink">
                    {t('dashboard.period')}
                </p>
                <div className="mt-2 flex flex-wrap gap-2">
                    {periodOptions.map((option) => (
                        <button
                            key={option.value}
                            type="button"
                            onClick={() => {
                                if (option.value === 'custom') {
                                    router.get(
                                        targetUrl(),
                                        {
                                            period: 'custom',
                                            from: customFrom,
                                            to: customTo,
                                            ...scopeParams,
                                        },
                                        {
                                            preserveState: true,
                                            replace: true,
                                        },
                                    );
                                } else {
                                    applyPreset(option.value);
                                }
                            }}
                            className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                                filters.period === option.value
                                    ? 'bg-sp-primary text-white shadow-sm'
                                    : 'bg-sp-surface text-sp-muted hover:text-sp-ink'
                            }`}
                        >
                            {option.label}
                        </button>
                    ))}
                </div>
            </div>

            {filters.period === 'custom' && (
                <form
                    onSubmit={submitCustom}
                    className="flex flex-wrap items-end gap-2"
                >
                    <label className="text-sm text-sp-muted">
                        {t('common.from')}
                        <input
                            type="date"
                            value={customFrom}
                            onChange={(e) => setCustomFrom(e.target.value)}
                            className="sp-input mt-1 block text-sm"
                        />
                    </label>
                    <label className="text-sm text-sp-muted">
                        {t('common.to')}
                        <input
                            type="date"
                            value={customTo}
                            onChange={(e) => setCustomTo(e.target.value)}
                            className="sp-input mt-1 block text-sm"
                        />
                    </label>
                    <button
                        type="submit"
                        className="rounded-lg bg-sp-primary px-3 py-2 text-sm font-medium text-white hover:bg-sp-primary-hover"
                    >
                        {t('common.apply')}
                    </button>
                </form>
            )}

            <p className="text-xs text-sp-muted">
                {filters.from} — {filters.to}
            </p>
        </div>
    );
}
