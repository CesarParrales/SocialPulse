import IntegrationCredentialsForm, {
    IntegrationCredentialsPayload,
} from '@/Components/Settings/IntegrationCredentialsForm';
import IntegrationsInternalTabs, {
    IntegrationPlatformSection,
    IntegrationPlatformTab,
} from '@/Components/Settings/IntegrationsInternalTabs';
import OAuthRedirectList, {
    OAuthRedirectRow,
} from '@/Components/Settings/OAuthRedirectList';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import IntegrationStatusBlock from '@/Components/UI/IntegrationStatusBlock';
import PrimaryButton from '@/Components/PrimaryButton';
import { useTranslation } from '@/lib/i18n';
import { useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';

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

type IntegrationsSetupPanelProps = {
    scope: 'agency' | 'platform';
    integrations: {
        meta: IntegrationStatus;
        google: IntegrationStatus;
        tiktok?: IntegrationStatus;
        linkedin?: IntegrationStatus;
        youtube?: IntegrationStatus;
    };
    integrationCredentials: IntegrationCredentialsPayload;
    submitRoute: string;
    oauthRedirects: OAuthRedirectRow[];
    agencyName?: string;
    envImport?: {
        canImport: boolean;
        pendingCount: number;
        importRoute: string;
    };
};

const INTEGRATION_TOTAL = 6;

export default function IntegrationsSetupPanel({
    scope,
    integrations,
    integrationCredentials,
    submitRoute,
    oauthRedirects,
    agencyName,
    envImport,
}: IntegrationsSetupPanelProps) {
    const { t } = useTranslation();
    const importForm = useForm({});
    const [activeTab, setActiveTab] = useState<IntegrationPlatformTab>('overview');

    const configuredCount = [
        integrations.meta.configured,
        integrations.meta.system_user_configured,
        integrations.google.configured,
        integrations.tiktok?.configured,
        integrations.linkedin?.configured,
        integrations.youtube?.configured,
    ].filter(Boolean).length;

    const hybridHint =
        scope === 'platform'
            ? t('settings.integrations_platform_hint')
            : agencyName
              ? t('settings.integrations_agency_named_hint', {
                    name: agencyName,
                })
              : t('settings.integrations_agency_hint');

    const platformTabs = useMemo(
        () => [
            {
                key: 'overview' as const,
                label: t('settings.integrations_tab_overview'),
            },
            {
                key: 'meta' as const,
                label: t('settings.meta'),
                configured: integrations.meta.configured,
            },
            {
                key: 'google' as const,
                label: t('settings.google'),
                configured: integrations.google.configured,
            },
            {
                key: 'tiktok' as const,
                label: t('settings.tiktok'),
                configured: integrations.tiktok?.configured ?? false,
            },
            {
                key: 'linkedin' as const,
                label: t('settings.linkedin'),
                configured: integrations.linkedin?.configured ?? false,
            },
            {
                key: 'youtube' as const,
                label: t('settings.youtube'),
                configured: integrations.youtube?.configured ?? false,
            },
        ],
        [integrations, t],
    );

    const formSection: IntegrationPlatformSection | undefined =
        activeTab === 'overview' ? undefined : activeTab;

    return (
        <>
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 className="text-base font-semibold text-sp-ink">
                        {t('settings.integrations')}
                    </h3>
                    <p className="mt-1 text-sm text-sp-muted">
                        {t('settings.integrations_description')}
                    </p>
                </div>
                <span className="text-xs text-sp-muted">
                    {t('settings.integrations_status', {
                        count: String(configuredCount),
                        total: String(INTEGRATION_TOTAL),
                    })}
                </span>
            </div>

            <IntegrationsInternalTabs
                activeTab={activeTab}
                onChange={setActiveTab}
                tabs={platformTabs}
            />

            {activeTab === 'overview' && (
                <div className="space-y-6">
                    <CalloutBanner
                        title={t('settings.integrations_scope_title', {
                            scope:
                                scope === 'platform'
                                    ? t('settings.integrations_scope_platform')
                                    : t('settings.integrations_scope_agency'),
                        })}
                        variant="info"
                    >
                        {hybridHint}
                    </CalloutBanner>

                    {envImport?.canImport && envImport.pendingCount > 0 && (
                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                            <p className="text-sm text-amber-950">
                                {t('settings.integrations_import_env_hint', {
                                    count: String(envImport.pendingCount),
                                })}
                            </p>
                            <PrimaryButton
                                type="button"
                                disabled={importForm.processing}
                                onClick={() =>
                                    importForm.post(envImport.importRoute)
                                }
                            >
                                {t('settings.integrations_import_env_action')}
                            </PrimaryButton>
                        </div>
                    )}

                    <div className="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                        <IntegrationStatusBlock
                            title={t('settings.meta')}
                            configured={integrations.meta.configured}
                            scopes={integrations.meta.scopes}
                            source={integrations.meta.oauth_source}
                            extra={
                                integrations.meta.api_version
                                    ? `API ${integrations.meta.api_version}`
                                    : undefined
                            }
                            t={t}
                        />
                        <IntegrationStatusBlock
                            title={t('settings.meta_system_user')}
                            configured={
                                integrations.meta.system_user_configured ??
                                false
                            }
                            scopes={[]}
                            source={integrations.meta.system_user_source}
                            t={t}
                        />
                        <IntegrationStatusBlock
                            title={t('settings.google')}
                            configured={integrations.google.configured}
                            scopes={integrations.google.scopes}
                            source={integrations.google.source}
                            extra={
                                integrations.google.developer_token_configured
                                    ? t('settings.google_dev_token_ok')
                                    : t('settings.google_dev_token_missing')
                            }
                            t={t}
                        />
                        <IntegrationStatusBlock
                            title={t('settings.tiktok')}
                            configured={
                                integrations.tiktok?.configured ?? false
                            }
                            scopes={integrations.tiktok?.scopes ?? []}
                            source={integrations.tiktok?.source}
                            t={t}
                        />
                        <IntegrationStatusBlock
                            title={t('settings.linkedin')}
                            configured={
                                integrations.linkedin?.configured ?? false
                            }
                            scopes={integrations.linkedin?.scopes ?? []}
                            source={integrations.linkedin?.source}
                            t={t}
                        />
                        <IntegrationStatusBlock
                            title={t('settings.youtube')}
                            configured={
                                integrations.youtube?.configured ?? false
                            }
                            scopes={integrations.youtube?.scopes ?? []}
                            source={integrations.youtube?.source}
                            t={t}
                        />
                    </div>

                    <OAuthRedirectList redirects={oauthRedirects} />
                </div>
            )}

            {activeTab !== 'overview' && (
                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
                    <div>
                        {activeTab === 'meta' && (
                            <div className="space-y-4">
                                <IntegrationStatusBlock
                                    title={t('settings.meta')}
                                    configured={integrations.meta.configured}
                                    scopes={integrations.meta.scopes}
                                    source={integrations.meta.oauth_source}
                                    extra={
                                        integrations.meta.api_version
                                            ? `API ${integrations.meta.api_version}`
                                            : undefined
                                    }
                                    t={t}
                                />
                                <IntegrationStatusBlock
                                    title={t('settings.meta_system_user')}
                                    configured={
                                        integrations.meta
                                            .system_user_configured ?? false
                                    }
                                    scopes={[]}
                                    source={
                                        integrations.meta.system_user_source
                                    }
                                    t={t}
                                />
                            </div>
                        )}
                        {activeTab === 'google' && (
                            <IntegrationStatusBlock
                                title={t('settings.google')}
                                configured={integrations.google.configured}
                                scopes={integrations.google.scopes}
                                source={integrations.google.source}
                                extra={
                                    integrations.google
                                        .developer_token_configured
                                        ? t('settings.google_dev_token_ok')
                                        : t(
                                              'settings.google_dev_token_missing',
                                          )
                                }
                                t={t}
                            />
                        )}
                        {activeTab === 'tiktok' && (
                            <IntegrationStatusBlock
                                title={t('settings.tiktok')}
                                configured={
                                    integrations.tiktok?.configured ?? false
                                }
                                scopes={integrations.tiktok?.scopes ?? []}
                                source={integrations.tiktok?.source}
                                t={t}
                            />
                        )}
                        {activeTab === 'linkedin' && (
                            <IntegrationStatusBlock
                                title={t('settings.linkedin')}
                                configured={
                                    integrations.linkedin?.configured ?? false
                                }
                                scopes={integrations.linkedin?.scopes ?? []}
                                source={integrations.linkedin?.source}
                                t={t}
                            />
                        )}
                        {activeTab === 'youtube' && (
                            <IntegrationStatusBlock
                                title={t('settings.youtube')}
                                configured={
                                    integrations.youtube?.configured ?? false
                                }
                                scopes={integrations.youtube?.scopes ?? []}
                                source={integrations.youtube?.source}
                                t={t}
                            />
                        )}

                        <div className="mt-4">
                            <OAuthRedirectList
                                redirects={oauthRedirects.filter((row) =>
                                    activeTab === 'meta'
                                        ? row.platform === 'meta'
                                        : row.platform === activeTab,
                                )}
                            />
                        </div>
                    </div>

                    <div className="xl:border-l xl:border-sp-border xl:pl-6">
                        <IntegrationCredentialsForm
                            credentials={integrationCredentials}
                            submitRoute={submitRoute}
                            activeSection={formSection}
                        />
                    </div>
                </div>
            )}
        </>
    );
}
