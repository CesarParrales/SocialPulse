import IntegrationsSetupPanel from '@/Components/Settings/IntegrationsSetupPanel';
import SettingsSubNav from '@/Components/Settings/SettingsSubNav';
import { OAuthRedirectRow } from '@/Components/Settings/OAuthRedirectList';
import { IntegrationCredentialsPayload } from '@/Components/Settings/IntegrationCredentialsForm';
import SettingsLayout from '@/Components/Templates/SettingsLayout';
import Card from '@/Components/UI/Card';
import EmptyState from '@/Components/UI/EmptyState';
import FlashAlert from '@/Components/UI/FlashAlert';
import PrimaryButton from '@/Components/PrimaryButton';
import StatusBadge from '@/Components/UI/StatusBadge';
import { useTranslation } from '@/lib/i18n';
import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

type IntegrationStatus = {
    configured: boolean;
    system_user_configured?: boolean;
    api_version?: string | null;
    scopes: string[];
    developer_token_configured?: boolean;
    oauth_source?: 'agency' | 'platform' | 'env';
    system_user_source?: 'agency' | 'platform' | 'env';
    source?: 'agency' | 'platform' | 'env';
};

type AgencyRow = {
    id: number;
    name: string;
    plan: string;
    plan_label: string;
    billing_email: string | null;
    workspaces_count: number;
    users_count: number;
    created_at: string | null;
};

export default function Platform({
    activeTab,
    stats,
    integrations,
    integrationCredentials,
    oauthRedirects,
    envImport,
    agencies,
}: PageProps<{
    activeTab: 'overview' | 'integrations' | 'agencies';
    stats: {
        agencies: number;
        workspaces: number;
        users: number;
        connections: number;
    };
    integrations: {
        meta: IntegrationStatus;
        google: IntegrationStatus;
        tiktok?: IntegrationStatus;
        linkedin?: IntegrationStatus;
        youtube?: IntegrationStatus;
    };
    integrationCredentials: IntegrationCredentialsPayload;
    oauthRedirects: OAuthRedirectRow[];
    envImport?: {
        canImport: boolean;
        pendingCount: number;
        importRoute: string;
    };
    agencies: AgencyRow[];
}>) {
    const { t } = useTranslation();
    const { flash } = usePage().props;

    const tabs = [
        {
            key: 'overview',
            label: t('settings.tab_overview'),
            href: route('settings.platform.index'),
            active: activeTab === 'overview',
        },
        {
            key: 'integrations',
            label: t('settings.tab_integrations'),
            href: route('settings.platform.index', { tab: 'integrations' }),
            active: activeTab === 'integrations',
        },
        {
            key: 'agencies',
            label: t('platform.agencies'),
            href: route('settings.platform.index', { tab: 'agencies' }),
            active: activeTab === 'agencies',
        },
    ];

    return (
        <SettingsLayout
            headTitle={t('platform.title')}
            title={t('platform.title')}
            description={t('platform.description')}
            subNav={<SettingsSubNav tabs={tabs} />}
        >
            {flash.success && (
                <FlashAlert message={flash.success} className="mb-6" />
            )}

            {activeTab === 'integrations' && (
                <Card>
                    <IntegrationsSetupPanel
                        scope="platform"
                        integrations={integrations}
                        integrationCredentials={integrationCredentials}
                        submitRoute={route(
                            'settings.platform.integrations.update',
                        )}
                        oauthRedirects={oauthRedirects}
                        envImport={envImport}
                    />
                </Card>
            )}

            {activeTab === 'overview' && (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        label={t('platform.stats_agencies')}
                        value={stats.agencies}
                    />
                    <StatCard
                        label={t('platform.stats_workspaces')}
                        value={stats.workspaces}
                    />
                    <StatCard
                        label={t('platform.stats_users')}
                        value={stats.users}
                    />
                    <StatCard
                        label={t('platform.stats_connections')}
                        value={stats.connections}
                    />
                </div>
            )}

            {activeTab === 'agencies' && (
                <Card>
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 className="text-base font-semibold text-sp-ink">
                                {t('platform.agencies')}
                            </h3>
                            <p className="mt-1 text-sm text-sp-muted">
                                {t('platform.agencies_hint')}
                            </p>
                        </div>
                        <Link href={route('settings.platform.agencies.create')}>
                            <PrimaryButton type="button">
                                {t('platform.create_agency')}
                            </PrimaryButton>
                        </Link>
                    </div>
                    {agencies.length === 0 ? (
                        <EmptyState
                            title={t('platform.empty_agencies')}
                            description={t('platform.empty_agencies_hint')}
                            action={{
                                label: t('platform.create_agency'),
                                href: route(
                                    'settings.platform.agencies.create',
                                ),
                            }}
                        />
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-sp-border text-sm">
                                <thead>
                                    <tr>
                                        <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                            {t('common.name')}
                                        </th>
                                        <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                            {t('platform.plan')}
                                        </th>
                                        <th className="px-3 py-2 text-left font-medium text-sp-muted">
                                            {t('platform.table_workspaces_users')}
                                        </th>
                                        <th className="px-3 py-2 text-right font-medium text-sp-muted">
                                            {t('platform.table_actions')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sp-border">
                                    {agencies.map((agency) => (
                                        <tr key={agency.id}>
                                            <td className="px-3 py-3">
                                                <p className="font-medium text-sp-ink">
                                                    {agency.name}
                                                </p>
                                                {agency.billing_email && (
                                                    <p className="text-xs text-sp-muted">
                                                        {agency.billing_email}
                                                    </p>
                                                )}
                                            </td>
                                            <td className="px-3 py-3">
                                                <StatusBadge
                                                    status="ready"
                                                    label={agency.plan_label}
                                                />
                                            </td>
                                            <td className="px-3 py-3 text-sp-muted">
                                                {t('platform.workspaces_count', {
                                                    count: String(
                                                        agency.workspaces_count,
                                                    ),
                                                })}{' '}
                                                ·{' '}
                                                {t('platform.users_count', {
                                                    count: String(
                                                        agency.users_count,
                                                    ),
                                                })}
                                            </td>
                                            <td className="px-3 py-3 text-right">
                                                <div className="flex flex-wrap items-center justify-end gap-3">
                                                    <Link
                                                        href={route(
                                                            'settings.platform.agencies.edit',
                                                            {
                                                                agency: agency.id,
                                                                tab: 'integrations',
                                                            },
                                                        )}
                                                        className="sp-link text-sm"
                                                    >
                                                        {t(
                                                            'platform.manage_integrations',
                                                        )}
                                                    </Link>
                                                    <Link
                                                        href={route(
                                                            'settings.platform.agencies.edit',
                                                            agency.id,
                                                        )}
                                                        className="sp-link text-sm"
                                                    >
                                                        {t('platform.edit_agency')}{' '}
                                                        →
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </Card>
            )}
        </SettingsLayout>
    );
}

function StatCard({ label, value }: { label: string; value: number }) {
    return (
        <div className="sp-card p-4">
            <p className="text-xs font-medium uppercase tracking-wider text-sp-muted">
                {label}
            </p>
            <p className="mt-2 text-2xl font-semibold tabular-nums text-sp-ink">
                {value.toLocaleString()}
            </p>
        </div>
    );
}
