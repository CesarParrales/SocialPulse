import StatusBadge from '@/Components/UI/StatusBadge';

export default function IntegrationStatusBlock({
    title,
    configured,
    scopes,
    extra,
    source,
    t,
}: {
    title: string;
    configured: boolean;
    scopes: string[];
    extra?: string;
    source?: 'agency' | 'platform' | 'env';
    t: (key: string, params?: Record<string, string>) => string;
}) {
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
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h4 className="font-medium text-sp-ink">{title}</h4>
                <div className="flex flex-wrap items-center gap-2">
                    {sourceLabel && (
                        <span className="rounded-md bg-sp-surface px-2 py-0.5 text-xs text-sp-muted">
                            {sourceLabel}
                        </span>
                    )}
                    <StatusBadge
                        status={configured ? 'active' : 'pending'}
                        label={
                            configured
                                ? t('settings.configured')
                                : t('settings.not_configured')
                        }
                    />
                </div>
            </div>
            {extra && (
                <p className="mt-2 text-xs text-sp-muted">{extra}</p>
            )}
            {scopes.length > 0 && (
                <>
                    <p className="mt-3 text-xs font-medium text-sp-muted">
                        {t('settings.scopes')}
                    </p>
                    <ul className="mt-1 flex flex-wrap gap-1.5">
                        {scopes.map((scope) => (
                            <li
                                key={scope}
                                className="rounded-md bg-sp-surface px-2 py-0.5 text-xs text-sp-muted"
                            >
                                {scope}
                            </li>
                        ))}
                    </ul>
                </>
            )}
        </div>
    );
}
