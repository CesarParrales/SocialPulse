import { useTranslation } from '@/lib/i18n';

export type OAuthRedirectRow = {
    platform: string;
    label: string;
    uri: string;
};

export default function OAuthRedirectList({
    redirects,
}: {
    redirects: OAuthRedirectRow[];
}) {
    const { t } = useTranslation();

    const copyUri = async (uri: string) => {
        try {
            await navigator.clipboard.writeText(uri);
        } catch {
            // Clipboard may be unavailable; user can still select manually.
        }
    };

    return (
        <div className="rounded-lg border border-sp-border bg-sp-surface/60 p-4">
            <h4 className="text-sm font-semibold text-sp-ink">
                {t('settings.redirect_uris_title')}
            </h4>
            <p className="mt-1 text-sm text-sp-muted">
                {t('settings.redirect_uris_hint')}
            </p>
            <ul className="mt-4 space-y-3">
                {redirects.map((row) => (
                    <li key={row.platform}>
                        <p className="text-xs font-medium text-sp-muted">
                            {row.label}
                        </p>
                        <div className="mt-1 flex flex-wrap items-center gap-2">
                            <code className="block min-w-0 flex-1 break-all rounded-md bg-white px-2 py-1.5 text-xs text-sp-ink ring-1 ring-sp-border">
                                {row.uri}
                            </code>
                            <button
                                type="button"
                                onClick={() => copyUri(row.uri)}
                                className="shrink-0 rounded-md px-2.5 py-1.5 text-xs font-medium text-sp-primary hover:bg-white hover:ring-1 hover:ring-sp-border"
                            >
                                {t('settings.redirect_copy')}
                            </button>
                        </div>
                    </li>
                ))}
            </ul>
        </div>
    );
}
