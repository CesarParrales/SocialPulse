import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import FlashAlert from '@/Components/UI/FlashAlert';
import StatusBadge from '@/Components/UI/StatusBadge';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { useTranslation } from '@/lib/i18n';
import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useMemo } from 'react';
import { PageProps, Workspace } from '@/types';

type ReportDetail = {
    id: number;
    name: string;
    title: string;
    period_start: string;
    period_end: string;
    status: string;
    status_label: string;
    error_message: string | null;
    generated_at: string | null;
    created_at: string | null;
    download_ready: boolean;
    preview_url: string | null;
    appendix_download_url: string | null;
    appendix_excel_download_url: string | null;
    config: {
        primary_color: string;
        secondary_color?: string;
        sections: Record<string, boolean>;
        metrics?: string[];
    };
};

export default function Show({
    workspace,
    report,
}: PageProps<{
    workspace: Pick<Workspace, 'id' | 'name'>;
    report: ReportDetail;
}>) {
    const { t } = useTranslation();
    const { locale, flash } = usePage().props;
    const isProcessing =
        report.status === 'pending' || report.status === 'generating';

    useEffect(() => {
        if (!isProcessing) {
            return;
        }

        const interval = window.setInterval(() => {
            router.reload({ only: ['report'] });
        }, 3000);

        return () => window.clearInterval(interval);
    }, [isProcessing]);

    const enabledSections = useMemo(
        () =>
            Object.entries(report.config.sections ?? {})
                .filter(([, enabled]) => enabled)
                .map(([key]) => t(`reports.section_labels.${key}`))
                .join(', ') || '—',
        [report.config.sections, t],
    );

    const enabledMetrics = useMemo(
        () =>
            (report.config.metrics ?? [])
                .map((key) => t(`reports.metric_labels.${key}`))
                .join(', ') || '—',
        [report.config.metrics, t],
    );

    return (
        <WorkspaceLayout
            headTitle={report.name}
            title={report.name}
            description={report.title}
            workspace={workspace}
            active="reports"
            actions={
                <Link
                    href={route('workspaces.reports.index', workspace.id)}
                    className="sp-link text-sm"
                >
                    {t('reports.all_reports')}
                </Link>
            }
        >
                <div className="space-y-6">
                    {flash.success && <FlashAlert message={flash.success} />}

                    <div className="sp-card overflow-hidden p-0">
                        <div
                            className="h-1.5"
                            style={{
                                background: `linear-gradient(90deg, ${report.config.primary_color}, ${report.config.secondary_color ?? report.config.primary_color})`,
                            }}
                        />
                        <div className="p-6">
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p className="text-sm text-sp-muted">
                                        {workspace.name} · {report.period_start}{' '}
                                        — {report.period_end}
                                    </p>
                                </div>
                                <StatusBadge
                                    status={report.status}
                                    label={report.status_label}
                                />
                            </div>

                            {isProcessing && (
                                <div className="mt-4">
                                    <FlashAlert
                                        message={t('reports.generating')}
                                    />
                                </div>
                            )}

                            {report.status === 'error' &&
                                report.error_message && (
                                    <div className="mt-4">
                                        <FlashAlert
                                            message={report.error_message}
                                            variant="error"
                                        />
                                    </div>
                                )}

                            {report.download_ready && (
                                <div className="mt-6 flex flex-wrap gap-3">
                                    <a
                                        href={route(
                                            'workspaces.reports.download',
                                            [workspace.id, report.id],
                                        )}
                                    >
                                        <PrimaryButton type="button">
                                            {t('reports.download_pdf')}
                                        </PrimaryButton>
                                    </a>
                                    {report.appendix_download_url && (
                                        <a href={report.appendix_download_url}>
                                            <SecondaryButton type="button">
                                                {t('reports.download_appendix_csv')}
                                            </SecondaryButton>
                                        </a>
                                    )}
                                    {report.appendix_excel_download_url && (
                                        <a href={report.appendix_excel_download_url}>
                                            <SecondaryButton type="button">
                                                {t('reports.download_appendix_excel')}
                                            </SecondaryButton>
                                        </a>
                                    )}
                                    {report.generated_at && (
                                        <p className="self-center text-xs text-sp-muted">
                                            {t('reports.generated_at')}:{' '}
                                            {new Date(
                                                report.generated_at,
                                            ).toLocaleString(
                                                locale === 'en' ? 'en' : 'es',
                                            )}
                                        </p>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>

                    {report.preview_url ? (
                        <div className="sp-card overflow-hidden">
                            <div className="border-b border-sp-border px-4 py-3">
                                <h3 className="text-sm font-semibold text-sp-ink">
                                    {t('reports.preview_title')}
                                </h3>
                            </div>
                            <iframe
                                title={report.title}
                                src={report.preview_url}
                                className="h-[min(70vh,720px)] w-full bg-sp-surface"
                            />
                        </div>
                    ) : (
                        !isProcessing &&
                        report.status !== 'error' && (
                            <div className="sp-card p-6 text-center text-sm text-sp-muted">
                                {t('reports.preview_unavailable')}
                            </div>
                        )
                    )}

                    <div className="sp-card p-4 text-sm text-sp-muted">
                        <p className="font-semibold text-sp-ink">
                            {t('common.configuration')}
                        </p>
                        <ul className="mt-3 space-y-2">
                            <li className="flex items-center gap-2">
                                <span
                                    className="inline-block h-4 w-4 rounded border border-sp-border"
                                    style={{
                                        backgroundColor:
                                            report.config.primary_color,
                                    }}
                                />
                                {t('reports.primary_color_value', {
                                    color: report.config.primary_color,
                                })}
                            </li>
                            {report.config.secondary_color && (
                                <li className="flex items-center gap-2">
                                    <span
                                        className="inline-block h-4 w-4 rounded border border-sp-border"
                                        style={{
                                            backgroundColor:
                                                report.config.secondary_color,
                                        }}
                                    />
                                    {t('reports.secondary_color_value', {
                                        color: report.config.secondary_color,
                                    })}
                                </li>
                            )}
                            <li>
                                {t('reports.sections_value', {
                                    sections: enabledSections,
                                })}
                            </li>
                            <li>
                                {t('reports.metrics_value', {
                                    metrics: enabledMetrics,
                                })}
                            </li>
                        </ul>
                    </div>
                </div>
        </WorkspaceLayout>
    );
}
