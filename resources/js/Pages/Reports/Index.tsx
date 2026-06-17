import PrimaryButton from '@/Components/PrimaryButton';
import EmptyState from '@/Components/UI/EmptyState';
import StatusBadge from '@/Components/UI/StatusBadge';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { useTranslation } from '@/lib/i18n';
import { Link } from '@inertiajs/react';
import { PageProps, Workspace } from '@/types';

type ReportListItem = {
    id: number;
    name: string;
    period_start: string;
    period_end: string;
    status: string;
    status_label: string;
    generated_at: string | null;
    created_at: string | null;
};

export default function Index({
    workspace,
    reports,
    hasConnectedAssets,
}: PageProps<{
    workspace: Pick<Workspace, 'id' | 'name'>;
    reports: ReportListItem[];
    hasConnectedAssets: boolean;
}>) {
    const { t } = useTranslation();

    return (
        <WorkspaceLayout
            headTitle={`${t('reports.title')} — ${workspace.name}`}
            title={t('reports.title')}
            description={t('reports.description', { name: workspace.name })}
            workspace={workspace}
            active="reports"
            actions={
                <Link href={route('workspaces.reports.create', workspace.id)}>
                    <PrimaryButton>{t('reports.new')}</PrimaryButton>
                </Link>
            }
        >
                <div className="sp-card overflow-hidden">
                    {!hasConnectedAssets ? (
                        <div className="p-6">
                            <EmptyState
                                title={t('reports.no_connections_title')}
                                description={t('reports.no_connections_description')}
                                action={{
                                    label: t('dashboard.connect_accounts'),
                                    href: route(
                                        'workspaces.connections.index',
                                        workspace.id,
                                    ),
                                }}
                            />
                        </div>
                    ) : reports.length === 0 ? (
                        <div className="p-6">
                            <EmptyState
                                title={t('reports.empty')}
                                description={t('reports.empty_hint')}
                                action={{
                                    label: t('reports.new'),
                                    href: route(
                                        'workspaces.reports.create',
                                        workspace.id,
                                    ),
                                }}
                            />
                        </div>
                    ) : (
                        <table className="min-w-full divide-y divide-sp-border">
                            <thead className="bg-sp-surface">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-sp-muted">
                                        {t('common.name')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-sp-muted">
                                        {t('dashboard.period')}
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-sp-muted">
                                        {t('dashboard.status')}
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-sp-muted">
                                        —
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-sp-border bg-white">
                                {reports.map((report) => (
                                    <tr key={report.id} className="hover:bg-sp-surface/50">
                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-sp-ink">
                                            {report.name}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-sp-muted">
                                            {report.period_start} — {report.period_end}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4">
                                            <StatusBadge
                                                status={report.status}
                                                label={report.status_label}
                                            />
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <Link
                                                href={route('workspaces.reports.show', [workspace.id, report.id])}
                                                className="sp-link"
                                            >
                                                →
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
        </WorkspaceLayout>
    );
}
