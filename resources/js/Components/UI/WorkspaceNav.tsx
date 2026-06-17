import { Link, usePage } from '@inertiajs/react';
import { useTranslation } from '@/lib/i18n';
import { PageProps } from '@/types';

export type WorkspaceNavActive =
    | 'overview'
    | 'dashboard'
    | 'compare'
    | 'benchmarks'
    | 'competitors'
    | 'content'
    | 'reports'
    | 'connections'
    | 'settings';

type Tab = {
    key: string;
    labelKey: string;
    href: string;
    active: boolean;
    adminOnly?: boolean;
    clientHidden?: boolean;
};

export default function WorkspaceNav({
    workspaceId,
    workspaceName,
    active,
}: {
    workspaceId: number;
    workspaceName: string;
    active: WorkspaceNavActive;
}) {
    const { t } = useTranslation();
    const { auth } = usePage<PageProps>().props;
    const isClientReadonly = auth.user?.is_client_readonly === true;
    const canEditSettings =
        !isClientReadonly &&
        (auth.user?.roles.includes('agency_admin') ||
            auth.user?.roles.includes('super_admin'));

    const tabs: Tab[] = [
        {
            key: 'overview',
            labelKey: 'workspaces.overview',
            href: route('workspaces.show', workspaceId),
            active: active === 'overview',
            clientHidden: true,
        },
        {
            key: 'dashboard',
            labelKey: 'workspaces.dashboard',
            href: route('workspaces.dashboard', workspaceId),
            active: active === 'dashboard',
        },
        {
            key: 'compare',
            labelKey: 'workspaces.compare',
            href: route('workspaces.compare', workspaceId),
            active: active === 'compare',
            clientHidden: true,
        },
        {
            key: 'benchmarks',
            labelKey: 'workspaces.benchmarks',
            href: route('workspaces.benchmarks', workspaceId),
            active: active === 'benchmarks',
            clientHidden: true,
        },
        {
            key: 'competitors',
            labelKey: 'workspaces.competitors',
            href: route('workspaces.competitors.index', workspaceId),
            active: active === 'competitors',
            clientHidden: true,
        },
        {
            key: 'content',
            labelKey: 'workspaces.content',
            href: route('workspaces.content.index', workspaceId),
            active: active === 'content',
        },
        {
            key: 'reports',
            labelKey: 'workspaces.reports',
            href: route('workspaces.reports.index', workspaceId),
            active: active === 'reports',
            clientHidden: true,
        },
        {
            key: 'connections',
            labelKey: 'workspaces.connections',
            href: route('workspaces.connections.index', workspaceId),
            active: active === 'connections',
            clientHidden: true,
        },
        {
            key: 'settings',
            labelKey: 'workspaces.settings',
            href: route('settings.workspace.edit', workspaceId),
            active: active === 'settings',
            adminOnly: true,
            clientHidden: true,
        },
    ];

    const visibleTabs = tabs.filter((tab) => {
        if (isClientReadonly && tab.clientHidden) {
            return false;
        }

        return !tab.adminOnly || canEditSettings;
    });

    return (
        <div className="mb-6 min-w-0">
            <div className="mb-3 flex items-center justify-between gap-3">
                <p className="truncate text-xs font-medium uppercase tracking-wider text-sp-muted">
                    {workspaceName}
                </p>
                {!isClientReadonly && (
                    <Link
                        href={route('workspaces.index')}
                        className="shrink-0 text-xs text-sp-muted transition hover:text-sp-primary"
                    >
                        {t('workspaces.all_workspaces')}
                    </Link>
                )}
            </div>
            <nav aria-label={t('workspaces.nav_label')}>
                <div className="flex flex-wrap gap-1 rounded-xl border border-sp-border bg-white p-1 shadow-sp">
                    {visibleTabs.map((tab) => (
                        <Link
                            key={tab.key}
                            href={tab.href}
                            aria-current={tab.active ? 'page' : undefined}
                            className={
                                'rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150 ' +
                                (tab.active
                                    ? 'bg-sp-primary text-white shadow-sm'
                                    : 'text-sp-muted hover:bg-sp-surface hover:text-sp-ink')
                            }
                        >
                            {t(tab.labelKey)}
                        </Link>
                    ))}
                </div>
            </nav>
        </div>
    );
}
