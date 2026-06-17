import { router } from '@inertiajs/react';
import { useTranslation } from '@/lib/i18n';

export type WorkspaceAssetOption = {
    id: number;
    name: string;
    asset_type: string;
    platform: string | null;
};

export type AssetScopeConfig = {
    assets: WorkspaceAssetOption[];
    selected_asset_id: number | null;
    route: string;
    routeParams?: Record<string, string | number>;
    preserveQuery?: Record<string, string | number | null | undefined>;
};

const typeStyles: Record<string, string> = {
    fb_page: 'border-blue-200 bg-blue-50 text-blue-900',
    ig_account: 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-900',
    meta_ads: 'border-orange-200 bg-orange-50 text-orange-900',
    google_ads: 'border-emerald-200 bg-emerald-50 text-emerald-900',
};

function typeLabel(
    assetType: string,
    t: (key: string, params?: Record<string, string>) => string,
): string {
    const key = `asset_scope.types.${assetType}`;
    const translated = t(key);

    return translated === key ? assetType : translated;
}

function navigateScope(
    workspaceId: number | undefined,
    routeName: string,
    assetId: number | null,
    preserveQuery?: AssetScopeConfig['preserveQuery'],
    routeParams?: AssetScopeConfig['routeParams'],
): void {
    const query: Record<string, string | number> = {};

    if (preserveQuery) {
        for (const [key, value] of Object.entries(preserveQuery)) {
            if (value !== null && value !== undefined && value !== '') {
                query[key] = value;
            }
        }
    }

    if (assetId !== null) {
        query.asset_id = assetId;
    } else {
        delete query.asset_id;
    }

    const href =
        routeParams !== undefined
            ? route(routeName, routeParams)
            : route(routeName, workspaceId as number);

    router.get(href, query, {
        preserveState: true,
        replace: true,
    });
}

export default function AssetScopeBar({
    workspaceId,
    assetScope,
}: {
    workspaceId?: number;
    assetScope: AssetScopeConfig;
}) {
    const { t } = useTranslation();
    const {
        assets,
        selected_asset_id,
        route: routeName,
        preserveQuery,
        routeParams,
    } = assetScope;

    if (assets.length === 0) {
        return null;
    }

    const showAllOption = assets.length > 1;

    return (
        <div className="sp-card p-4">
            <p className="text-sm font-medium text-sp-ink">
                {t('asset_scope.label')}
            </p>
            <div className="mt-3 flex flex-wrap gap-2">
                {showAllOption && (
                    <button
                        type="button"
                        onClick={() =>
                            navigateScope(
                                workspaceId,
                                routeName,
                                null,
                                preserveQuery,
                                routeParams,
                            )
                        }
                        className={`rounded-lg border px-3 py-2 text-left text-sm font-medium transition ${
                            selected_asset_id === null
                                ? 'border-sp-primary bg-sp-primary text-white shadow-sm'
                                : 'border-sp-border bg-sp-surface text-sp-muted hover:border-sp-primary/40 hover:text-sp-ink'
                        }`}
                    >
                        {t('asset_scope.all_assets')}
                        <span className="mt-0.5 block text-xs font-normal opacity-80">
                            {t('asset_scope.all_assets_hint', {
                                count: String(assets.length),
                            })}
                        </span>
                    </button>
                )}

                {assets.map((asset) => {
                    const isSelected = selected_asset_id === asset.id;
                    const inactiveStyle =
                        typeStyles[asset.asset_type] ??
                        'border-sp-border bg-sp-surface text-sp-muted';

                    return (
                        <button
                            key={asset.id}
                            type="button"
                            onClick={() =>
                                navigateScope(
                                    workspaceId,
                                    routeName,
                                    asset.id,
                                    preserveQuery,
                                    routeParams,
                                )
                            }
                            className={`max-w-xs rounded-lg border px-3 py-2 text-left text-sm font-medium transition ${
                                isSelected
                                    ? 'border-sp-primary bg-sp-primary text-white shadow-sm'
                                    : `${inactiveStyle} hover:border-sp-primary/40`
                            }`}
                        >
                            <span className="block truncate">{asset.name}</span>
                            <span
                                className={`mt-0.5 block text-xs font-normal ${
                                    isSelected ? 'opacity-90' : 'opacity-80'
                                }`}
                            >
                                {typeLabel(asset.asset_type, t)}
                            </span>
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
