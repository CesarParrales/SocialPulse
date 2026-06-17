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
import { FormEvent } from 'react';
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

export default function Agency({
    activeTab,
    agency,
    integrations,
    integrationCredentials,
    oauthRedirects,
}: PageProps<{
    activeTab: 'general' | 'integrations';
    agency: {
        id: number;
        name: string;
        plan: string;
        billing_email: string | null;
        default_locale: string;
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
}>) {
    const { t } = useTranslation();
    const { flash, localeOptions } = usePage().props;

    const { data, setData, put, processing, errors } = useForm({
        name: agency.name,
        billing_email: agency.billing_email ?? '',
        default_locale: agency.default_locale,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        put(route('settings.agency.update'));
    };

    const planName = t(`platform.plans.${agency.plan}`, {}, agency.plan);

    const tabs = [
        {
            key: 'general',
            label: t('settings.tab_general'),
            href: route('settings.agency.edit'),
            active: activeTab === 'general',
        },
        {
            key: 'integrations',
            label: t('settings.tab_integrations'),
            href: route('settings.agency.edit', { tab: 'integrations' }),
            active: activeTab === 'integrations',
        },
    ];

    return (
        <SettingsLayout
            headTitle={t('settings.title')}
            title={t('settings.title')}
            description={t('settings.agency_description')}
            actions={
                <Link href={route('settings.index')} className="sp-link text-sm">
                    ← {t('settings.back_to_hub')}
                </Link>
            }
            subNav={<SettingsSubNav tabs={tabs} />}
        >
            {flash.success && (
                <FlashAlert message={flash.success} className="mb-6" />
            )}

            {activeTab === 'integrations' ? (
                <Card>
                    <IntegrationsSetupPanel
                        scope="agency"
                        integrations={integrations}
                        integrationCredentials={integrationCredentials}
                        submitRoute={route('settings.agency.integrations.update')}
                        oauthRedirects={oauthRedirects}
                        agencyName={agency.name}
                    />
                </Card>
            ) : (
                <Card className="max-w-2xl">
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 className="text-base font-semibold text-sp-ink">
                            {t('settings.agency')}
                        </h3>
                        <StatusBadge
                            status="ready"
                            label={t('settings.plan_label', {
                                plan: planName,
                            })}
                        />
                    </div>
                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <InputLabel
                                htmlFor="name"
                                value={t('settings.name')}
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

                        <div>
                            <InputLabel
                                htmlFor="default_locale"
                                value={t('settings.default_locale')}
                            />
                            <select
                                id="default_locale"
                                className="sp-input mt-1 block w-full"
                                value={data.default_locale}
                                onChange={(e) =>
                                    setData('default_locale', e.target.value)
                                }
                            >
                                {localeOptions.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                message={errors.default_locale}
                                className="mt-2"
                            />
                        </div>

                        <PrimaryButton disabled={processing}>
                            {t('settings.save')}
                        </PrimaryButton>
                    </form>
                </Card>
            )}
        </SettingsLayout>
    );
}
