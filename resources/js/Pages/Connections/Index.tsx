import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import EmptyState from '@/Components/UI/EmptyState';
import FlashAlert from '@/Components/UI/FlashAlert';
import StatusBadge from '@/Components/UI/StatusBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { useTranslation } from '@/lib/i18n';
import { Link, router, usePage } from '@inertiajs/react';
import { FormEventHandler, ReactNode, useMemo, useState } from 'react';
import { PageProps } from '@/types';

interface ConnectionAsset {
    id: number;
    asset_type: string;
    platform_asset_id: string;
    name: string;
    is_active: boolean;
}

interface Connection {
    id: number;
    platform: string;
    status: string;
    auth_mode?: string;
    token_expires_at: string | null;
    assets: ConnectionAsset[];
}

interface PlatformCatalogEntry {
    key: string;
    label: string;
    status: 'available' | 'planned';
    phase: string;
    channels: Array<{
        key: string;
        label: string;
        capabilities: string[];
    }>;
}

function capabilityLabel(capability: string): string {
    const labels: Record<string, string> = {
        analytics_organic: 'Analytics orgánico',
        analytics_paid: 'Analytics pagado',
        stories_capture: 'Stories',
        content_publish: 'Publicación',
        competitor_tracking: 'Competidores',
    };

    return labels[capability] ?? capability;
}

interface DiscoverableAsset {
    type: string;
    id: string;
    name: string;
    selected: boolean;
    metadata?: Record<string, unknown>;
}

function tokenExpiryLabel(
    expiresAt: string | null,
    t: (key: string, params?: Record<string, string>) => string,
): string | null {
    if (!expiresAt) {
        return null;
    }

    const expiry = new Date(expiresAt);
    const now = new Date();

    if (expiry <= now) {
        return t('connections.token_expired');
    }

    return t('connections.token_expires', {
        date: expiry.toLocaleDateString(),
    });
}

function PlatformConnectCard({
    title,
    hint,
    configured,
    canManage,
    source,
    children,
}: {
    title: string;
    hint: string;
    configured: boolean;
    canManage: boolean;
    source?: 'agency' | 'platform' | 'env';
    children: ReactNode;
}) {
    const { t } = useTranslation();

    const sourceLabel =
        source === 'agency'
            ? t('settings.integrations_source_agency')
            : source === 'platform'
              ? t('settings.integrations_source_platform')
              : source === 'env'
                ? t('settings.integrations_source_env')
                : null;

    return (
        <div className="rounded-lg border border-sp-border p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                    <h4 className="font-medium text-sp-ink">{title}</h4>
                    <p className="mt-1 text-xs text-sp-muted">{hint}</p>
                    {configured && sourceLabel && (
                        <p className="mt-1 text-xs text-sp-muted">
                            {t('connections.credentials_source', {
                                source: sourceLabel,
                            })}
                        </p>
                    )}
                </div>
                <StatusBadge
                    status={configured ? 'active' : 'pending'}
                    label={
                        configured
                            ? t('settings.configured')
                            : t('settings.not_configured')
                    }
                />
            </div>
            <div className="mt-4">
                {canManage ? (
                    children
                ) : (
                    <p className="text-xs text-sp-muted">
                        {t('connections.readonly_hint')}
                    </p>
                )}
            </div>
        </div>
    );
}

export default function Index({
    workspace,
    connections,
    discoveredAssets,
    canManage,
    canViewSettings,
    metaConfigured,
    metaSystemUserConfigured,
    googleConfigured,
    tiktokConfigured = false,
    linkedInConfigured = false,
    youTubeConfigured = false,
    integrations,
    platformCatalog = [],
}: PageProps<{
    workspace: { id: number; name: string };
    connections: Connection[];
    discoveredAssets: { meta?: DiscoverableAsset[]; tiktok?: DiscoverableAsset[]; linkedin?: DiscoverableAsset[]; youtube?: DiscoverableAsset[] };
    canManage: boolean;
    canViewSettings: boolean;
    metaConfigured: boolean;
    metaSystemUserConfigured: boolean;
    googleConfigured: boolean;
    tiktokConfigured?: boolean;
    linkedInConfigured?: boolean;
    youTubeConfigured?: boolean;
    integrations?: {
        meta: {
            oauth_source?: 'agency' | 'platform' | 'env';
            system_user_source?: 'agency' | 'platform' | 'env';
        };
        google: {
            source?: 'agency' | 'platform' | 'env';
        };
        tiktok?: {
            source?: 'agency' | 'platform' | 'env';
        };
        linkedin?: {
            source?: 'agency' | 'platform' | 'env';
        };
        youtube?: {
            source?: 'agency' | 'platform' | 'env';
        };
    };
    platformCatalog?: PlatformCatalogEntry[];
}>) {
    const { t } = useTranslation();
    const { flash, errors } = usePage().props;

    const metaAssets = discoveredAssets.meta ?? [];
    const tiktokAssets = discoveredAssets.tiktok ?? [];
    const linkedInAssets = discoveredAssets.linkedin ?? [];
    const youTubeAssets = discoveredAssets.youtube ?? [];
    const metaConnection = connections.find((c) => c.platform === 'meta');
    const tiktokConnection = connections.find((c) => c.platform === 'tiktok');
    const linkedInConnection = connections.find((c) => c.platform === 'linkedin');
    const youTubeConnection = connections.find((c) => c.platform === 'youtube');
    const hasOAuthConfigured =
        metaConfigured ||
        metaSystemUserConfigured ||
        googleConfigured ||
        tiktokConfigured ||
        linkedInConfigured ||
        youTubeConfigured;
    const plannedPlatforms = platformCatalog.filter(
        (platform) => platform.status === 'planned',
    );

    const initialAssets = useMemo(
        () =>
            metaAssets.map((asset) => ({
                type: asset.type,
                id: asset.id,
                name: asset.name,
                selected: asset.selected,
            })),
        [metaAssets],
    );

    const [assets, setAssets] = useState(initialAssets);
    const initialTiktokAssets = useMemo(
        () =>
            tiktokAssets.map((asset) => ({
                type: asset.type,
                id: asset.id,
                name: asset.name,
                selected: asset.selected,
            })),
        [tiktokAssets],
    );
    const [tiktokAssetSelection, setTiktokAssetSelection] =
        useState(initialTiktokAssets);
    const initialLinkedInAssets = useMemo(
        () =>
            linkedInAssets.map((asset) => ({
                type: asset.type,
                id: asset.id,
                name: asset.name,
                selected: asset.selected,
            })),
        [linkedInAssets],
    );
    const [linkedInAssetSelection, setLinkedInAssetSelection] =
        useState(initialLinkedInAssets);
    const initialYouTubeAssets = useMemo(
        () =>
            youTubeAssets.map((asset) => ({
                type: asset.type,
                id: asset.id,
                name: asset.name,
                selected: asset.selected,
            })),
        [youTubeAssets],
    );
    const [youTubeAssetSelection, setYouTubeAssetSelection] =
        useState(initialYouTubeAssets);
    const [processing, setProcessing] = useState(false);
    const [tiktokProcessing, setTiktokProcessing] = useState(false);
    const [linkedInProcessing, setLinkedInProcessing] = useState(false);
    const [youTubeProcessing, setYouTubeProcessing] = useState(false);

    const toggleAsset = (index: number) => {
        setAssets((current) =>
            current.map((asset, i) =>
                i === index
                    ? { ...asset, selected: !asset.selected }
                    : asset,
            ),
        );
    };

    const toggleTiktokAsset = (index: number) => {
        setTiktokAssetSelection((current) =>
            current.map((asset, i) =>
                i === index
                    ? { ...asset, selected: !asset.selected }
                    : asset,
            ),
        );
    };

    const toggleLinkedInAsset = (index: number) => {
        setLinkedInAssetSelection((current) =>
            current.map((asset, i) =>
                i === index
                    ? { ...asset, selected: !asset.selected }
                    : asset,
            ),
        );
    };

    const toggleYouTubeAsset = (index: number) => {
        setYouTubeAssetSelection((current) =>
            current.map((asset, i) =>
                i === index
                    ? { ...asset, selected: !asset.selected }
                    : asset,
            ),
        );
    };

    const submitAssets: FormEventHandler = (e) => {
        e.preventDefault();
        if (!metaConnection) return;

        setProcessing(true);

        router.post(
            route('workspaces.connections.assets.sync', [
                workspace.id,
                metaConnection.id,
            ]),
            {
                assets: assets.map((asset) => ({
                    ...asset,
                    metadata: (metaAssets.find(
                        (m) => m.id === asset.id && m.type === asset.type,
                    )?.metadata ?? {}) as Record<string, string | number | boolean | null>,
                })),
            },
            {
                onFinish: () => setProcessing(false),
            },
        );
    };

    const submitTiktokAssets: FormEventHandler = (e) => {
        e.preventDefault();
        if (!tiktokConnection) return;

        setTiktokProcessing(true);

        router.post(
            route('workspaces.connections.assets.sync', [
                workspace.id,
                tiktokConnection.id,
            ]),
            {
                assets: tiktokAssetSelection.map((asset) => ({
                    ...asset,
                    metadata: (tiktokAssets.find(
                        (m) => m.id === asset.id && m.type === asset.type,
                    )?.metadata ?? {}) as Record<
                        string,
                        string | number | boolean | null
                    >,
                })),
            },
            {
                onFinish: () => setTiktokProcessing(false),
            },
        );
    };

    const submitLinkedInAssets: FormEventHandler = (e) => {
        e.preventDefault();
        if (!linkedInConnection) return;

        setLinkedInProcessing(true);

        router.post(
            route('workspaces.connections.assets.sync', [
                workspace.id,
                linkedInConnection.id,
            ]),
            {
                assets: linkedInAssetSelection.map((asset) => ({
                    ...asset,
                    metadata: (linkedInAssets.find(
                        (m) => m.id === asset.id && m.type === asset.type,
                    )?.metadata ?? {}) as Record<
                        string,
                        string | number | boolean | null
                    >,
                })),
            },
            {
                onFinish: () => setLinkedInProcessing(false),
            },
        );
    };

    const submitYouTubeAssets: FormEventHandler = (e) => {
        e.preventDefault();
        if (!youTubeConnection) return;

        setYouTubeProcessing(true);

        router.post(
            route('workspaces.connections.assets.sync', [
                workspace.id,
                youTubeConnection.id,
            ]),
            {
                assets: youTubeAssetSelection.map((asset) => ({
                    ...asset,
                    metadata: (youTubeAssets.find(
                        (m) => m.id === asset.id && m.type === asset.type,
                    )?.metadata ?? {}) as Record<
                        string,
                        string | number | boolean | null
                    >,
                })),
            },
            {
                onFinish: () => setYouTubeProcessing(false),
            },
        );
    };

    const sessionError = (() => {
        if (typeof errors !== 'object' || errors === null) {
            return null;
        }
        const bag = errors as Record<string, string>;
        for (const key of ['oauth', 'meta', 'google', 'email']) {
            if (bag[key]) {
                return String(bag[key]);
            }
        }
        return null;
    })();

    return (
        <WorkspaceLayout
            headTitle={`${t('connections.title')} — ${workspace.name}`}
            title={t('connections.title')}
            description={t('connections.description', {
                name: workspace.name,
            })}
            workspace={workspace}
            active="connections"
        >
                <div className="space-y-6">
                    {flash.success && <FlashAlert message={flash.success} />}
                    {sessionError && (
                        <FlashAlert message={sessionError} variant="error" />
                    )}

                    <CalloutBanner
                        title={t('connections.stories_banner_title')}
                        variant="warning"
                    >
                        {t('connections.stories_banner_body')}
                    </CalloutBanner>

                    {!canManage && (
                        <CalloutBanner
                            title={t('connections.readonly_title')}
                            variant="info"
                        >
                            {t('connections.readonly_body')}
                        </CalloutBanner>
                    )}

                    {canManage && !hasOAuthConfigured && (
                        <CalloutBanner
                            title={t('connections.setup_title')}
                            variant="warning"
                        >
                            {t('connections.setup_hint')}
                            {canViewSettings && (
                                <>
                                    {' '}
                                    <Link
                                        href={route('settings.agency.edit', {
                                            tab: 'integrations',
                                        })}
                                        className="font-medium underline hover:no-underline"
                                    >
                                        {t('connections.setup_settings_link')}
                                    </Link>
                                </>
                            )}
                        </CalloutBanner>
                    )}

                    <div className="sp-card p-6">
                        <h3 className="mb-4 text-base font-semibold text-sp-ink">
                            {t('connections.platforms')}
                        </h3>

                        <div className="space-y-4">
                            <PlatformConnectCard
                                title={t('connections.connect_meta')}
                                hint={t('connections.meta_oauth_hint')}
                                configured={metaConfigured}
                                canManage={canManage}
                                source={integrations?.meta.oauth_source}
                            >
                                {metaConfigured ? (
                                    <a
                                        href={route(
                                            'workspaces.connections.meta.redirect',
                                            workspace.id,
                                        )}
                                    >
                                        <PrimaryButton type="button">
                                            {t('connections.connect_meta')}
                                        </PrimaryButton>
                                    </a>
                                ) : (
                                    <SecondaryButton
                                        type="button"
                                        disabled
                                        title={t(
                                            'connections.connect_unavailable',
                                        )}
                                    >
                                        {t('connections.connect_meta')}
                                    </SecondaryButton>
                                )}
                            </PlatformConnectCard>

                            <PlatformConnectCard
                                title={t('connections.connect_meta_system_user')}
                                hint={t('connections.meta_system_user_hint')}
                                configured={metaSystemUserConfigured}
                                canManage={canManage}
                                source={integrations?.meta.system_user_source}
                            >
                                {metaSystemUserConfigured ? (
                                    <PrimaryButton
                                        type="button"
                                        onClick={() =>
                                            router.post(
                                                route(
                                                    'workspaces.connections.meta.system-user',
                                                    workspace.id,
                                                ),
                                            )
                                        }
                                    >
                                        {t(
                                            'connections.connect_meta_system_user',
                                        )}
                                    </PrimaryButton>
                                ) : (
                                    <SecondaryButton
                                        type="button"
                                        disabled
                                        title={t(
                                            'connections.connect_unavailable',
                                        )}
                                    >
                                        {t(
                                            'connections.connect_meta_system_user',
                                        )}
                                    </SecondaryButton>
                                )}
                            </PlatformConnectCard>

                            <PlatformConnectCard
                                title={t('connections.connect_google')}
                                hint={t('connections.google_hint')}
                                configured={googleConfigured}
                                canManage={canManage}
                                source={integrations?.google.source}
                            >
                                {googleConfigured ? (
                                    <a
                                        href={route(
                                            'workspaces.connections.google.redirect',
                                            workspace.id,
                                        )}
                                    >
                                        <PrimaryButton type="button">
                                            {t('connections.connect_google')}
                                        </PrimaryButton>
                                    </a>
                                ) : (
                                    <SecondaryButton
                                        type="button"
                                        disabled
                                        title={t(
                                            'connections.connect_unavailable',
                                        )}
                                    >
                                        {t('connections.connect_google')}
                                    </SecondaryButton>
                                )}
                            </PlatformConnectCard>

                            <PlatformConnectCard
                                title={t('connections.connect_tiktok')}
                                hint={t('connections.tiktok_hint')}
                                configured={tiktokConfigured}
                                canManage={canManage}
                                source={integrations?.tiktok?.source}
                            >
                                {tiktokConfigured ? (
                                    <a
                                        href={route(
                                            'workspaces.connections.tiktok.redirect',
                                            workspace.id,
                                        )}
                                    >
                                        <PrimaryButton type="button">
                                            {t('connections.connect_tiktok')}
                                        </PrimaryButton>
                                    </a>
                                ) : (
                                    <SecondaryButton
                                        type="button"
                                        disabled
                                        title={t(
                                            'connections.connect_unavailable',
                                        )}
                                    >
                                        {t('connections.connect_tiktok')}
                                    </SecondaryButton>
                                )}
                            </PlatformConnectCard>

                            <PlatformConnectCard
                                title={t('connections.connect_linkedin')}
                                hint={t('connections.linkedin_hint')}
                                configured={linkedInConfigured}
                                canManage={canManage}
                                source={integrations?.linkedin?.source}
                            >
                                {linkedInConfigured ? (
                                    <a
                                        href={route(
                                            'workspaces.connections.linkedin.redirect',
                                            workspace.id,
                                        )}
                                    >
                                        <PrimaryButton type="button">
                                            {t('connections.connect_linkedin')}
                                        </PrimaryButton>
                                    </a>
                                ) : (
                                    <SecondaryButton
                                        type="button"
                                        disabled
                                        title={t(
                                            'connections.connect_unavailable',
                                        )}
                                    >
                                        {t('connections.connect_linkedin')}
                                    </SecondaryButton>
                                )}
                            </PlatformConnectCard>

                            <PlatformConnectCard
                                title={t('connections.connect_youtube')}
                                hint={t('connections.youtube_hint')}
                                configured={youTubeConfigured}
                                canManage={canManage}
                                source={integrations?.youtube?.source}
                            >
                                {youTubeConfigured ? (
                                    <a
                                        href={route(
                                            'workspaces.connections.youtube.redirect',
                                            workspace.id,
                                        )}
                                    >
                                        <PrimaryButton type="button">
                                            {t('connections.connect_youtube')}
                                        </PrimaryButton>
                                    </a>
                                ) : (
                                    <SecondaryButton
                                        type="button"
                                        disabled
                                        title={t(
                                            'connections.connect_unavailable',
                                        )}
                                    >
                                        {t('connections.connect_youtube')}
                                    </SecondaryButton>
                                )}
                            </PlatformConnectCard>
                        </div>

                        {connections.length === 0 ? (
                            hasOAuthConfigured ? (
                                <div className="mt-6">
                                    <EmptyState
                                        title={t('connections.empty')}
                                        description={t(
                                            'connections.ready_to_connect',
                                        )}
                                    />
                                </div>
                            ) : (
                                <p className="mt-6 text-center text-sm text-sp-muted">
                                    {t('connections.waiting_for_setup')}
                                </p>
                            )
                        ) : (
                            <ul className="mt-6 divide-y divide-sp-border">
                                {connections.map((connection) => {
                                    const tokenLabel = tokenExpiryLabel(
                                        connection.token_expires_at,
                                        t,
                                    );

                                    return (
                                        <li
                                            key={connection.id}
                                            className="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <p className="font-medium capitalize text-sp-ink">
                                                        {connection.platform}
                                                    </p>
                                                    <StatusBadge
                                                        status={
                                                            connection.status
                                                        }
                                                    />
                                                </div>
                                                <p className="mt-1 text-sm text-sp-muted">
                                                    {connection.platform ===
                                                        'meta' &&
                                                        connection.auth_mode && (
                                                            <>
                                                                {connection.auth_mode ===
                                                                'system_user'
                                                                    ? t(
                                                                          'connections.auth_mode_system_user',
                                                                      )
                                                                    : t(
                                                                          'connections.auth_mode_user_oauth',
                                                                      )}
                                                                {' · '}
                                                            </>
                                                        )}
                                                    {t(
                                                        'connections.assets_count',
                                                        {
                                                            count: String(
                                                                connection.assets.filter(
                                                                    (a) =>
                                                                        a.is_active,
                                                                ).length,
                                                            ),
                                                        },
                                                    )}
                                                    {tokenLabel && (
                                                        <>
                                                            {' · '}
                                                            <span
                                                                className={
                                                                    connection.token_expires_at &&
                                                                    new Date(
                                                                        connection.token_expires_at,
                                                                    ) <=
                                                                        new Date()
                                                                        ? 'text-red-600'
                                                                        : ''
                                                                }
                                                            >
                                                                {tokenLabel}
                                                            </span>
                                                        </>
                                                    )}
                                                </p>
                                            </div>
                                            {canManage && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        router.delete(
                                                            route(
                                                                'workspaces.connections.destroy',
                                                                [
                                                                    workspace.id,
                                                                    connection.id,
                                                                ],
                                                            ),
                                                        )
                                                    }
                                                    className="text-sm text-red-600 hover:underline"
                                                >
                                                    {t('common.disconnect')}
                                                </button>
                                            )}
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </div>

                    {plannedPlatforms.length > 0 && (
                        <div className="sp-card p-6">
                            <h3 className="mb-2 text-base font-semibold text-sp-ink">
                                Próximas plataformas
                            </h3>
                            <p className="mb-4 text-sm text-sp-muted">
                                Roadmap de integraciones oficiales. Solo APIs
                                autorizadas por cada plataforma.
                            </p>
                            <div className="grid gap-3 sm:grid-cols-2">
                                {plannedPlatforms.map((platform) => (
                                    <div
                                        key={platform.key}
                                        className="rounded-lg border border-dashed border-sp-border p-4 opacity-80"
                                    >
                                        <div className="flex items-center justify-between gap-2">
                                            <p className="font-medium text-sp-ink">
                                                {platform.label}
                                            </p>
                                            <StatusBadge
                                                status="pending"
                                                label="Próximamente"
                                            />
                                        </div>
                                        <ul className="mt-2 space-y-1 text-xs text-sp-muted">
                                            {platform.channels.map((channel) => (
                                                <li key={channel.key}>
                                                    {channel.label}
                                                    {channel.capabilities.length >
                                                        0 && (
                                                        <>
                                                            {' · '}
                                                            {channel.capabilities
                                                                .map(
                                                                    capabilityLabel,
                                                                )
                                                                .join(', ')}
                                                        </>
                                                    )}
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {metaConnection && metaAssets.length > 0 && canManage && (
                        <div className="sp-card p-6">
                            <h3 className="mb-4 text-base font-semibold text-sp-ink">
                                {t('connections.meta_assets')}
                            </h3>
                            <form onSubmit={submitAssets} className="space-y-4">
                                <ul className="divide-y divide-sp-border">
                                    {assets.map((asset, index) => (
                                        <li
                                            key={`${asset.type}-${asset.id}`}
                                            className="flex items-center gap-3 py-3"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={asset.selected}
                                                onChange={() =>
                                                    toggleAsset(index)
                                                }
                                                className="rounded border-sp-border text-sp-primary focus:ring-sp-primary"
                                            />
                                            <div>
                                                <p className="font-medium text-sp-ink">
                                                    {asset.name}
                                                </p>
                                                <p className="text-xs text-sp-muted">
                                                    {asset.type} · {asset.id}
                                                </p>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                                <PrimaryButton disabled={processing}>
                                    {t('connections.save_assets')}
                                </PrimaryButton>
                            </form>
                        </div>
                    )}

                    {metaConnection &&
                        metaAssets.length === 0 &&
                        canManage &&
                        connectionHasNoActiveAssets(metaConnection) && (
                            <div className="sp-card p-6">
                                <EmptyState
                                    title={t('connections.no_assets')}
                                    description={t(
                                        'connections.no_assets_hint',
                                    )}
                                />
                            </div>
                        )}

                    {tiktokConnection && tiktokAssets.length > 0 && canManage && (
                        <div className="sp-card p-6">
                            <h3 className="mb-4 text-base font-semibold text-sp-ink">
                                {t('connections.tiktok_assets')}
                            </h3>
                            <form
                                onSubmit={submitTiktokAssets}
                                className="space-y-4"
                            >
                                <ul className="divide-y divide-sp-border">
                                    {tiktokAssetSelection.map((asset, index) => (
                                        <li
                                            key={`${asset.type}-${asset.id}`}
                                            className="flex items-center gap-3 py-3"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={asset.selected}
                                                onChange={() =>
                                                    toggleTiktokAsset(index)
                                                }
                                                className="rounded border-sp-border text-sp-primary focus:ring-sp-primary"
                                            />
                                            <div>
                                                <p className="font-medium text-sp-ink">
                                                    {asset.name}
                                                </p>
                                                <p className="text-xs text-sp-muted">
                                                    {asset.type} · {asset.id}
                                                </p>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                                <PrimaryButton disabled={tiktokProcessing}>
                                    {t('connections.save_assets')}
                                </PrimaryButton>
                            </form>
                        </div>
                    )}

                    {linkedInConnection && linkedInAssets.length > 0 && canManage && (
                        <div className="sp-card p-6">
                            <h3 className="mb-4 text-base font-semibold text-sp-ink">
                                {t('connections.linkedin_assets')}
                            </h3>
                            <form
                                onSubmit={submitLinkedInAssets}
                                className="space-y-4"
                            >
                                <ul className="divide-y divide-sp-border">
                                    {linkedInAssetSelection.map((asset, index) => (
                                        <li
                                            key={`${asset.type}-${asset.id}`}
                                            className="flex items-center gap-3 py-3"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={asset.selected}
                                                onChange={() =>
                                                    toggleLinkedInAsset(index)
                                                }
                                                className="rounded border-sp-border text-sp-primary focus:ring-sp-primary"
                                            />
                                            <div>
                                                <p className="font-medium text-sp-ink">
                                                    {asset.name}
                                                </p>
                                                <p className="text-xs text-sp-muted">
                                                    {asset.type} · {asset.id}
                                                </p>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                                <PrimaryButton disabled={linkedInProcessing}>
                                    {t('connections.save_assets')}
                                </PrimaryButton>
                            </form>
                        </div>
                    )}

                    {youTubeConnection && youTubeAssets.length > 0 && canManage && (
                        <div className="sp-card p-6">
                            <h3 className="mb-4 text-base font-semibold text-sp-ink">
                                {t('connections.youtube_assets')}
                            </h3>
                            <form
                                onSubmit={submitYouTubeAssets}
                                className="space-y-4"
                            >
                                <ul className="divide-y divide-sp-border">
                                    {youTubeAssetSelection.map((asset, index) => (
                                        <li
                                            key={`${asset.type}-${asset.id}`}
                                            className="flex items-center gap-3 py-3"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={asset.selected}
                                                onChange={() =>
                                                    toggleYouTubeAsset(index)
                                                }
                                                className="rounded border-sp-border text-sp-primary focus:ring-sp-primary"
                                            />
                                            <div>
                                                <p className="font-medium text-sp-ink">
                                                    {asset.name}
                                                </p>
                                                <p className="text-xs text-sp-muted">
                                                    {asset.type} · {asset.id}
                                                </p>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                                <PrimaryButton disabled={youTubeProcessing}>
                                    {t('connections.save_assets')}
                                </PrimaryButton>
                            </form>
                        </div>
                    )}
                </div>
        </WorkspaceLayout>
    );
}

function connectionHasNoActiveAssets(connection: Connection): boolean {
    return connection.assets.filter((asset) => asset.is_active).length === 0;
}
