import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { FormEventHandler, useMemo, useState } from 'react';
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
    token_expires_at: string | null;
    assets: ConnectionAsset[];
}

interface DiscoverableAsset {
    type: string;
    id: string;
    name: string;
    selected: boolean;
    metadata?: Record<string, unknown>;
}

interface AssetFormRow {
    type: string;
    id: string;
    name: string;
    selected: boolean;
}

export default function Index({
    workspace,
    connections,
    discoveredAssets,
    canManage,
    metaConfigured,
    googleConfigured,
}: PageProps<{
    workspace: { id: number; name: string };
    connections: Connection[];
    discoveredAssets: { meta?: DiscoverableAsset[] };
    canManage: boolean;
    metaConfigured: boolean;
    googleConfigured: boolean;
}>) {
    const { flash } = usePage().props;

    const metaAssets = discoveredAssets.meta ?? [];
    const metaConnection = connections.find((c) => c.platform === 'meta');

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
    const [processing, setProcessing] = useState(false);

    const toggleAsset = (index: number) => {
        setAssets((current) =>
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

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-sm text-gray-500">
                            <Link
                                href={route('workspaces.show', workspace.id)}
                                className="hover:underline"
                            >
                                {workspace.name}
                            </Link>
                        </p>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Conexiones
                        </h2>
                    </div>
                </div>
            }
        >
            <Head title={`Conexiones — ${workspace.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="rounded-md bg-green-50 p-4 text-sm text-green-800">
                            {flash.success}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-medium text-gray-900">
                            Plataformas
                        </h3>
                        <div className="flex flex-wrap gap-3">
                            {canManage && metaConfigured && (
                                <a
                                    href={route(
                                        'workspaces.connections.meta.redirect',
                                        workspace.id,
                                    )}
                                >
                                    <PrimaryButton type="button">
                                        Conectar Meta
                                    </PrimaryButton>
                                </a>
                            )}
                            {canManage && googleConfigured && (
                                <a
                                    href={route(
                                        'workspaces.connections.google.redirect',
                                        workspace.id,
                                    )}
                                >
                                    <PrimaryButton type="button">
                                        Conectar Google Ads
                                    </PrimaryButton>
                                </a>
                            )}
                            {!metaConfigured && !googleConfigured && (
                                <p className="text-sm text-gray-500">
                                    Configura META_APP_ID y GOOGLE_ADS_CLIENT_ID
                                    en el archivo .env para habilitar OAuth.
                                </p>
                            )}
                        </div>

                        <ul className="mt-6 divide-y divide-gray-200">
                            {connections.map((connection) => (
                                <li
                                    key={connection.id}
                                    className="flex items-center justify-between py-3"
                                >
                                    <div>
                                        <p className="font-medium capitalize text-gray-900">
                                            {connection.platform}
                                        </p>
                                        <p className="text-sm text-gray-500">
                                            Estado: {connection.status} ·{' '}
                                            {connection.assets.length} activos
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
                                            Desconectar
                                        </button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </div>

                    {metaConnection && metaAssets.length > 0 && canManage && (
                        <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="mb-4 text-lg font-medium text-gray-900">
                                Activos disponibles (Meta)
                            </h3>
                            <form onSubmit={submitAssets} className="space-y-4">
                                <ul className="divide-y divide-gray-200">
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
                                                className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <div>
                                                <p className="font-medium text-gray-900">
                                                    {asset.name}
                                                </p>
                                                <p className="text-xs text-gray-500">
                                                    {asset.type} · {asset.id}
                                                </p>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                                <PrimaryButton disabled={processing}>
                                    Guardar activos
                                </PrimaryButton>
                            </form>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
