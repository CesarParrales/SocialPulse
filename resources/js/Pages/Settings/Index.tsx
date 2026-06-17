import SettingsHubActionCard from '@/Components/Settings/SettingsHubActionCard';
import SettingsLayout from '@/Components/Templates/SettingsLayout';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import FlashAlert from '@/Components/UI/FlashAlert';
import StatusBadge from '@/Components/UI/StatusBadge';
import { useTranslation } from '@/lib/i18n';
import { Link, usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import { PageProps } from '@/types';

type IntegrationRow = {
    key: string;
    labelKey: string;
    configured: boolean;
    href: string;
};

function HubIcon({ d }: { d: string }) {
    return (
        <svg
            className="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            strokeWidth={1.75}
            aria-hidden="true"
        >
            <path strokeLinecap="round" strokeLinejoin="round" d={d} />
        </svg>
    );
}

export default function SettingsIndex({
    canManagePlatform,
    canManageTeam,
    agency,
    integrations,
    integrationsSummary,
}: PageProps<{
    canManagePlatform: boolean;
    canManageTeam: boolean;
    agency: {
        name: string;
        plan: string;
        plan_label: string;
        billing_email: string | null;
        default_locale: string;
    };
    integrations: {
        meta: {
            configured: boolean;
            system_user_configured?: boolean;
        };
        google: { configured: boolean };
        tiktok?: { configured: boolean };
        linkedin?: { configured: boolean };
        youtube?: { configured: boolean };
    };
    integrationsSummary: { configured: number; total: number };
}>) {
    const { t } = useTranslation();
    const { flash, localeOptions } = usePage().props;

    const integrationsHref = route('settings.agency.edit', {
        tab: 'integrations',
    });

    const integrationRows: IntegrationRow[] = useMemo(
        () => [
            {
                key: 'meta',
                labelKey: 'settings.meta',
                configured: integrations.meta.configured,
                href: integrationsHref,
            },
            {
                key: 'meta_system',
                labelKey: 'settings.meta_system_user',
                configured: integrations.meta.system_user_configured ?? false,
                href: integrationsHref,
            },
            {
                key: 'google',
                labelKey: 'settings.google',
                configured: integrations.google.configured,
                href: integrationsHref,
            },
            {
                key: 'tiktok',
                labelKey: 'settings.tiktok',
                configured: integrations.tiktok?.configured ?? false,
                href: integrationsHref,
            },
            {
                key: 'linkedin',
                labelKey: 'settings.linkedin',
                configured: integrations.linkedin?.configured ?? false,
                href: integrationsHref,
            },
            {
                key: 'youtube',
                labelKey: 'settings.youtube',
                configured: integrations.youtube?.configured ?? false,
                href: integrationsHref,
            },
        ],
        [integrations, integrationsHref],
    );

    const progressPercent = Math.round(
        (integrationsSummary.configured / integrationsSummary.total) * 100,
    );
    const isComplete =
        integrationsSummary.configured === integrationsSummary.total;

    const localeLabel =
        localeOptions.find((option) => option.value === agency.default_locale)
            ?.label ?? agency.default_locale;

    return (
        <SettingsLayout
            headTitle={t('settings.title')}
            title={t('settings.hub_title')}
            description={t('settings.hub_description')}
        >
            {flash.success && (
                <FlashAlert message={flash.success} className="mb-6" />
            )}

            <div className="sp-card mb-8 flex flex-wrap items-center justify-between gap-6 p-6">
                <div className="space-y-2">
                    <p className="text-xs font-medium uppercase tracking-wider text-sp-muted">
                        {t('settings.hub_agency_context')}
                    </p>
                    <p className="text-lg font-semibold text-sp-ink">
                        {agency.name}
                    </p>
                    <p className="text-sm text-sp-muted">
                        {agency.billing_email ??
                            t('settings.hub_billing_missing')}
                    </p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    <StatusBadge status="ready" label={agency.plan_label} />
                    <span className="rounded-full bg-sp-surface px-2.5 py-0.5 text-xs font-medium text-sp-muted">
                        {localeLabel}
                    </span>
                </div>
            </div>

            {!isComplete && (
                <div className="mb-8">
                    <CalloutBanner
                        title={t('settings.hub_integrations_incomplete_title')}
                        variant="warning"
                    >
                        <p>{t('settings.hub_integrations_incomplete_body')}</p>
                        <Link
                            href={integrationsHref}
                            className="mt-3 inline-flex text-sm font-medium text-sp-primary hover:underline"
                        >
                            {t('settings.hub_integrations_cta')} →
                        </Link>
                    </CalloutBanner>
                </div>
            )}

            <section aria-labelledby="hub-actions-heading">
                <h2
                    id="hub-actions-heading"
                    className="text-sm font-semibold uppercase tracking-wider text-sp-muted"
                >
                    {t('settings.hub_actions_title')}
                </h2>
                <div className="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <SettingsHubActionCard
                        href={route('settings.agency.edit')}
                        title={t('settings.hub_action_general_title')}
                        description={t('settings.hub_action_general_description')}
                        icon={
                            <HubIcon d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        }
                        meta={
                            <p className="text-xs text-sp-muted">
                                {t('settings.hub_action_general_meta', {
                                    locale: localeLabel,
                                })}
                            </p>
                        }
                    />

                    <SettingsHubActionCard
                        href={integrationsHref}
                        title={t('settings.hub_action_integrations_title')}
                        description={t(
                            'settings.hub_action_integrations_description',
                        )}
                        emphasis={!isComplete}
                        icon={
                            <HubIcon d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        }
                        meta={
                            <StatusBadge
                                status={isComplete ? 'ready' : 'pending'}
                                label={t('settings.integrations_status', {
                                    count: String(integrationsSummary.configured),
                                    total: String(integrationsSummary.total),
                                })}
                            />
                        }
                    />

                    {canManageTeam && (
                        <SettingsHubActionCard
                            href={route('team.index')}
                            title={t('settings.hub_action_team_title')}
                            description={t('settings.hub_action_team_description')}
                            icon={
                                <HubIcon d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            }
                        />
                    )}

                    {canManagePlatform && (
                        <SettingsHubActionCard
                            href={route('settings.platform.index')}
                            title={t('settings.hub_platform_card_title')}
                            description={t(
                                'settings.hub_platform_card_description',
                            )}
                            icon={
                                <HubIcon d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            }
                        />
                    )}
                </div>
            </section>

            <section
                className="mt-10"
                aria-labelledby="hub-integrations-heading"
            >
                <div className="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2
                            id="hub-integrations-heading"
                            className="text-base font-semibold text-sp-ink"
                        >
                            {t('settings.hub_integrations_overview_title')}
                        </h2>
                        <p className="mt-1 text-sm text-sp-muted">
                            {t('settings.hub_integrations_overview_description')}
                        </p>
                    </div>
                    <Link
                        href={integrationsHref}
                        className="sp-link text-sm font-medium"
                    >
                        {t('settings.hub_manage_integrations')} →
                    </Link>
                </div>

                <div className="sp-card p-5">
                    <div className="mb-5">
                        <div className="mb-2 flex items-center justify-between text-sm">
                            <span className="font-medium text-sp-ink">
                                {t('settings.integrations_status', {
                                    count: String(integrationsSummary.configured),
                                    total: String(integrationsSummary.total),
                                })}
                            </span>
                            <span className="tabular-nums text-sp-muted">
                                {progressPercent}%
                            </span>
                        </div>
                        <div
                            className="h-2 overflow-hidden rounded-full bg-sp-surface"
                            role="progressbar"
                            aria-valuenow={integrationsSummary.configured}
                            aria-valuemin={0}
                            aria-valuemax={integrationsSummary.total}
                            aria-label={t('settings.integrations_status', {
                                count: String(integrationsSummary.configured),
                                total: String(integrationsSummary.total),
                            })}
                        >
                            <div
                                className="h-full rounded-full bg-sp-primary transition-all"
                                style={{ width: `${progressPercent}%` }}
                            />
                        </div>
                    </div>

                    <ul className="divide-y divide-sp-border">
                        {integrationRows.map((row) => (
                            <li key={row.key}>
                                <Link
                                    href={row.href}
                                    className="flex items-center justify-between gap-4 py-3 transition-colors hover:bg-sp-surface/60 -mx-2 px-2 rounded-lg"
                                >
                                    <span className="text-sm font-medium text-sp-ink">
                                        {t(row.labelKey)}
                                    </span>
                                    <StatusBadge
                                        status={
                                            row.configured ? 'ready' : 'pending'
                                        }
                                        label={
                                            row.configured
                                                ? t('settings.configured')
                                                : t('settings.not_configured')
                                        }
                                    />
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>
        </SettingsLayout>
    );
}
