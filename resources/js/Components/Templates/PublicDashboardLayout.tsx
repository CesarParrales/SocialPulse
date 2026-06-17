import AssetScopeBar, {
    AssetScopeConfig,
} from '@/Components/Dashboard/AssetScopeBar';
import ApplicationLogo from '@/Components/ApplicationLogo';
import LocaleSelector from '@/Components/LocaleSelector';
import { useTranslation } from '@/lib/i18n';
import { Head } from '@inertiajs/react';
import { ReactNode } from 'react';

export default function PublicDashboardLayout({
    headTitle,
    title,
    description,
    workspaceName,
    assetScope,
    children,
}: {
    headTitle: string;
    title: string;
    description?: string;
    workspaceName: string;
    assetScope?: AssetScopeConfig;
    children: ReactNode;
}) {
    const { t } = useTranslation();

    return (
        <div className="min-h-screen bg-sp-surface">
            <Head title={headTitle} />
            <header className="border-b border-sp-border bg-white">
                <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-4">
                        <ApplicationLogo showWordmark />
                        <div className="hidden h-8 w-px bg-sp-border sm:block" />
                        <div>
                            <p className="text-xs font-medium uppercase tracking-wide text-sp-muted">
                                {t('public_dashboard.badge')}
                            </p>
                            <h1 className="text-lg font-semibold text-sp-ink">
                                {workspaceName}
                            </h1>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <LocaleSelector variant="guest" />
                        <span className="rounded-full bg-sp-surface px-3 py-1 text-xs font-medium text-sp-muted">
                            {t('public_dashboard.read_only')}
                        </span>
                    </div>
                </div>
            </header>

            <main className="mx-auto w-full min-w-0 max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="mb-6">
                    <h2 className="text-2xl font-bold text-sp-ink">{title}</h2>
                    {description && (
                        <p className="mt-1 text-sm text-sp-muted">
                            {description}
                        </p>
                    )}
                </div>

                {assetScope && (
                    <div className="mb-4">
                        <AssetScopeBar assetScope={assetScope} />
                    </div>
                )}

                {children}

                <footer className="mt-12 border-t border-sp-border pt-6 text-center text-xs text-sp-muted">
                    {t('public_dashboard.powered_by')}
                </footer>
            </main>
        </div>
    );
}
