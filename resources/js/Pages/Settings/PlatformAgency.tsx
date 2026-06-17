import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import IntegrationsSetupPanel from '@/Components/Settings/IntegrationsSetupPanel';
import SettingsSubNav from '@/Components/Settings/SettingsSubNav';
import { OAuthRedirectRow } from '@/Components/Settings/OAuthRedirectList';
import { IntegrationCredentialsPayload } from '@/Components/Settings/IntegrationCredentialsForm';
import SettingsLayout from '@/Components/Templates/SettingsLayout';
import Card from '@/Components/UI/Card';
import FlashAlert from '@/Components/UI/FlashAlert';
import StatusBadge from '@/Components/UI/StatusBadge';
import { useTranslation } from '@/lib/i18n';
import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent, ReactNode } from 'react';
import { PageProps } from '@/types';

type PlanOption = { value: string; label: string };

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

export default function PlatformAgency({
    activeTab,
    agency,
    integrations,
    integrationCredentials,
    oauthRedirects,
    envImport,
    planOptions,
}: PageProps<{
    activeTab: 'general' | 'integrations';
    agency: {
        id: number;
        name: string;
        plan: string;
        billing_email: string | null;
        workspaces_count: number;
        users_count: number;
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
    planOptions: PlanOption[];
}>) {
    const { t } = useTranslation();
    const { flash } = usePage().props;

    const { data, setData, put, processing, errors } = useForm({
        name: agency.name,
        plan: agency.plan,
        billing_email: agency.billing_email ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        put(route('settings.platform.agencies.update', agency.id));
    };

    const currentPlanLabel =
        planOptions.find((option) => option.value === agency.plan)?.label ??
        agency.plan;

    const tabs = [
        {
            key: 'general',
            label: t('settings.tab_general'),
            href: route('settings.platform.agencies.edit', agency.id),
            active: activeTab === 'general',
        },
        {
            key: 'integrations',
            label: t('settings.tab_integrations'),
            href: route('settings.platform.agencies.edit', {
                agency: agency.id,
                tab: 'integrations',
            }),
            active: activeTab === 'integrations',
        },
    ];

    return (
        <SettingsLayout
            headTitle={t('platform.edit_agency')}
            title={t('platform.edit_agency')}
            description={t('platform.edit_agency_description', {
                name: agency.name,
            })}
            actions={
                <Link
                    href={route('settings.platform.index', {
                        tab: 'agencies',
                    })}
                    className="sp-link text-sm"
                >
                    ← {t('platform.back_to_platform')}
                </Link>
            }
            subNav={<SettingsSubNav tabs={tabs} />}
        >
            {flash.success && (
                <FlashAlert message={flash.success} className="mb-6" />
            )}

            <div className="mb-6 grid gap-3 sm:grid-cols-3 lg:max-w-3xl">
                <StatPill
                    label={t('platform.plan')}
                    value={
                        <StatusBadge status="ready" label={currentPlanLabel} />
                    }
                />
                <StatPill
                    label={t('platform.stats_workspaces')}
                    value={String(agency.workspaces_count)}
                />
                <StatPill
                    label={t('platform.stats_users')}
                    value={String(agency.users_count)}
                />
            </div>

            {activeTab === 'integrations' ? (
                <Card>
                    <IntegrationsSetupPanel
                        scope="agency"
                        integrations={integrations}
                        integrationCredentials={integrationCredentials}
                        submitRoute={route(
                            'settings.platform.agencies.integrations.update',
                            agency.id,
                        )}
                        oauthRedirects={oauthRedirects}
                        agencyName={agency.name}
                        envImport={envImport}
                    />
                </Card>
            ) : (
                <Card className="max-w-2xl">
                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <InputLabel
                                htmlFor="name"
                                value={t('common.name')}
                            />
                            <TextInput
                                id="name"
                                className="mt-1 block w-full"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                            />
                            <InputError message={errors.name} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="plan"
                                value={t('platform.plan')}
                            />
                            <select
                                id="plan"
                                className="sp-input mt-1 block w-full"
                                value={data.plan}
                                onChange={(e) => setData('plan', e.target.value)}
                            >
                                {planOptions.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.plan} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="billing_email"
                                value={t('settings.billing_email')}
                            />
                            <TextInput
                                id="billing_email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.billing_email}
                                onChange={(e) =>
                                    setData('billing_email', e.target.value)
                                }
                            />
                            <InputError
                                message={errors.billing_email}
                                className="mt-2"
                            />
                        </div>

                        <PrimaryButton disabled={processing}>
                            {t('common.save')}
                        </PrimaryButton>
                    </form>
                </Card>
            )}
        </SettingsLayout>
    );
}

function StatPill({
    label,
    value,
}: {
    label: string;
    value: ReactNode;
}) {
    return (
        <div className="sp-card flex flex-col gap-1 p-4">
            <span className="text-xs font-medium uppercase tracking-wider text-sp-muted">
                {label}
            </span>
            <span className="text-lg font-semibold text-sp-ink">{value}</span>
        </div>
    );
}
