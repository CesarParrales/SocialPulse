import {
    DASHBOARD_KPI_KEYS,
    DashboardKpiKey,
} from '@/lib/dashboardKpis';
import { useTranslation } from '@/lib/i18n';
import { router } from '@inertiajs/react';
import { useEffect, useId, useRef, useState } from 'react';

const labelKeys: Record<DashboardKpiKey, string> = {
    reach: 'dashboard.reach_total',
    impressions: 'dashboard.impressions',
    engagement_rate: 'dashboard.engagement_rate',
    spend: 'dashboard.spend',
    follower_growth: 'dashboard.follower_growth',
    posts_published: 'dashboard.posts_published',
};

export default function KpiVisibilitySelector({
    workspaceId,
    visibleKpis,
    canCustomize,
}: {
    workspaceId: number;
    visibleKpis: DashboardKpiKey[];
    canCustomize: boolean;
}) {
    const { t } = useTranslation();
    const panelId = useId();
    const [open, setOpen] = useState(false);
    const rootRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!open) {
            return;
        }

        const handlePointerDown = (event: MouseEvent) => {
            if (
                rootRef.current &&
                !rootRef.current.contains(event.target as Node)
            ) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', handlePointerDown);

        return () => document.removeEventListener('mousedown', handlePointerDown);
    }, [open]);

    if (!canCustomize) {
        return null;
    }

    const persist = (next: DashboardKpiKey[]) => {
        router.put(
            route('workspaces.dashboard.kpi-preferences', workspaceId),
            { visible_kpis: next },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['kpiPreferences'],
            },
        );
    };

    const toggle = (key: DashboardKpiKey) => {
        const selected = new Set(visibleKpis);

        if (selected.has(key)) {
            if (selected.size <= 1) {
                return;
            }

            selected.delete(key);
        } else {
            selected.add(key);
        }

        persist(DASHBOARD_KPI_KEYS.filter((item) => selected.has(item)));
    };

    const reset = () => {
        persist([...DASHBOARD_KPI_KEYS]);
    };

    return (
        <div ref={rootRef} className="relative">
            <button
                type="button"
                aria-expanded={open}
                aria-controls={panelId}
                onClick={() => setOpen((value) => !value)}
                className="inline-flex items-center gap-2 rounded-lg border border-sp-border bg-sp-surface px-3 py-2 text-sm font-medium text-sp-ink transition hover:border-sp-primary/40"
            >
                {t('dashboard.kpi_customize')}
                <span className="rounded-full bg-sp-primary/10 px-2 py-0.5 text-xs font-semibold text-sp-primary">
                    {visibleKpis.length}/{DASHBOARD_KPI_KEYS.length}
                </span>
            </button>

            {open && (
                <div
                    id={panelId}
                    className="absolute right-0 z-20 mt-2 w-72 rounded-xl border border-sp-border bg-white p-4 shadow-lg"
                >
                    <p className="text-sm font-medium text-sp-ink">
                        {t('dashboard.kpi_customize_title')}
                    </p>
                    <p className="mt-1 text-xs text-sp-muted">
                        {t('dashboard.kpi_customize_hint')}
                    </p>

                    <ul className="mt-4 space-y-2">
                        {DASHBOARD_KPI_KEYS.map((key) => {
                            const checked = visibleKpis.includes(key);

                            return (
                                <li key={key}>
                                    <label className="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-sp-surface">
                                        <input
                                            type="checkbox"
                                            checked={checked}
                                            onChange={() => toggle(key)}
                                            className="rounded border-sp-border text-sp-primary focus:ring-sp-primary"
                                        />
                                        <span className="text-sm text-sp-ink">
                                            {t(labelKeys[key])}
                                        </span>
                                    </label>
                                </li>
                            );
                        })}
                    </ul>

                    <button
                        type="button"
                        onClick={reset}
                        className="mt-4 text-sm font-medium text-sp-primary hover:underline"
                    >
                        {t('dashboard.kpi_reset')}
                    </button>
                </div>
            )}
        </div>
    );
}
