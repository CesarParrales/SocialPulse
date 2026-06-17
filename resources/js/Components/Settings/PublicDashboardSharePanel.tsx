import SecondaryButton from '@/Components/SecondaryButton';
import PrimaryButton from '@/Components/PrimaryButton';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import { useTranslation } from '@/lib/i18n';
import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

export default function PublicDashboardSharePanel({
    workspaceId,
    enabled,
    url,
    enabledAt,
}: {
    workspaceId: number;
    enabled: boolean;
    url: string | null;
    enabledAt: string | null;
}) {
    const { t } = useTranslation();
    const [copied, setCopied] = useState(false);

    const copyLink = async () => {
        if (!url) {
            return;
        }

        try {
            await navigator.clipboard.writeText(url);
            setCopied(true);
            window.setTimeout(() => setCopied(false), 2000);
        } catch {
            setCopied(false);
        }
    };

    const submitAction = (
        event: FormEvent,
        routeName: 'workspaces.public-dashboard.enable' | 'workspaces.public-dashboard.disable' | 'workspaces.public-dashboard.regenerate',
    ) => {
        event.preventDefault();
        router.post(route(routeName, workspaceId));
    };

    return (
        <section className="space-y-4 border-t border-sp-border pt-6">
            <div>
                <h3 className="text-sm font-semibold text-sp-ink">
                    {t('public_dashboard.share_title')}
                </h3>
                <p className="mt-1 text-xs text-sp-muted">
                    {t('public_dashboard.share_hint')}
                </p>
            </div>

            <CalloutBanner
                title={t('public_dashboard.share_notice_title')}
                variant="info"
            >
                {t('public_dashboard.share_notice')}
            </CalloutBanner>

            {enabled && url ? (
                <div className="space-y-4">
                    <div>
                        <p className="text-xs font-medium text-sp-muted">
                            {t('public_dashboard.share_url')}
                        </p>
                        <div className="mt-2 flex flex-wrap gap-2">
                            <input
                                type="text"
                                readOnly
                                value={url}
                                className="sp-input min-w-0 flex-1 text-sm"
                                aria-label={t('public_dashboard.share_url')}
                            />
                            <SecondaryButton type="button" onClick={copyLink}>
                                {copied
                                    ? t('public_dashboard.copied')
                                    : t('public_dashboard.copy_link')}
                            </SecondaryButton>
                        </div>
                        {enabledAt && (
                            <p className="mt-2 text-xs text-sp-muted">
                                {t('public_dashboard.enabled_at', {
                                    date: new Date(enabledAt).toLocaleString(),
                                })}
                            </p>
                        )}
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <form onSubmit={(e) => submitAction(e, 'workspaces.public-dashboard.regenerate')}>
                            <SecondaryButton type="submit">
                                {t('public_dashboard.regenerate_link')}
                            </SecondaryButton>
                        </form>
                        <form onSubmit={(e) => submitAction(e, 'workspaces.public-dashboard.disable')}>
                            <SecondaryButton type="submit">
                                {t('public_dashboard.disable_link')}
                            </SecondaryButton>
                        </form>
                    </div>
                </div>
            ) : (
                <form onSubmit={(e) => submitAction(e, 'workspaces.public-dashboard.enable')}>
                    <PrimaryButton type="submit">
                        {t('public_dashboard.enable_link')}
                    </PrimaryButton>
                </form>
            )}
        </section>
    );
}
