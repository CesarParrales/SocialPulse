import { useTranslation } from '@/lib/i18n';
import { ContentPost } from '@/types/content';

const postTypeStyles: Record<string, string> = {
    feed: 'bg-sp-surface text-sp-ink',
    reel: 'bg-fuchsia-600 text-white',
    video: 'bg-violet-600 text-white',
    story: 'bg-amber-500 text-white',
};

const assetTypeStyles: Record<string, string> = {
    fb_page: 'text-blue-700',
    ig_account: 'text-fuchsia-700',
};

function formatMetric(value: number | undefined): string {
    if (value === undefined || Number.isNaN(value)) {
        return '—';
    }

    return value.toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function postTypeLabel(
    postType: string,
    t: (key: string) => string,
): string {
    const key = `dashboard.post_types.${postType}`;
    const translated = t(key);

    return translated === key ? postType : translated;
}

function assetTypeLabel(
    assetType: string | undefined | null,
    t: (key: string) => string,
): string | null {
    if (!assetType) {
        return null;
    }

    const key = `asset_scope.types.${assetType}`;
    const translated = t(key);

    return translated === key ? assetType : translated;
}

export default function ContentPostCard({
    post,
    highlightMetric,
    highlightLabel,
    compact = false,
}: {
    post: ContentPost;
    highlightMetric?: string;
    highlightLabel?: string;
    compact?: boolean;
}) {
    const { t } = useTranslation();
    const metrics = post.metrics ?? {};
    const Wrapper = post.permalink_url ? 'a' : 'div';
    const wrapperProps = post.permalink_url
        ? {
              href: post.permalink_url,
              target: '_blank',
              rel: 'noopener noreferrer',
          }
        : {};

    return (
        <Wrapper
            {...wrapperProps}
            className={`group flex h-full flex-col overflow-hidden rounded-xl border border-sp-border bg-sp-surface transition hover:border-sp-primary/30 hover:shadow-md ${
                post.permalink_url ? 'cursor-pointer' : ''
            }`}
        >
            <div
                className={`relative overflow-hidden bg-sp-border/40 ${
                    compact ? 'aspect-square' : 'aspect-[4/5]'
                }`}
            >
                {post.thumbnail_url ? (
                    <img
                        src={post.thumbnail_url}
                        alt=""
                        className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                        loading="lazy"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-sp-border/60 to-sp-surface px-4 text-center text-sm text-sp-muted">
                        {post.content_preview?.slice(0, 120) ??
                            t('dashboard.content_no_preview')}
                    </div>
                )}

                <div className="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-2">
                    <span
                        className={`rounded-md px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide ${
                            postTypeStyles[post.post_type] ??
                            'bg-sp-ink/80 text-white'
                        }`}
                    >
                        {postTypeLabel(post.post_type, t)}
                    </span>
                    {post.asset_type && (
                        <span
                            className={`rounded-md bg-white/90 px-2 py-0.5 text-[11px] font-medium ${
                                assetTypeStyles[post.asset_type] ??
                                'text-sp-muted'
                            }`}
                        >
                            {assetTypeLabel(post.asset_type, t)}
                        </span>
                    )}
                </div>

                <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 via-black/35 to-transparent p-3 pt-10">
                    <div className="flex flex-wrap gap-x-3 gap-y-1 text-xs font-medium text-white/95">
                        {metrics.reach !== undefined && (
                            <span>
                                {t('dashboard.reach')}{' '}
                                {formatMetric(metrics.reach)}
                            </span>
                        )}
                        {metrics.likes !== undefined && (
                            <span>
                                {t('dashboard.likes')}{' '}
                                {formatMetric(metrics.likes)}
                            </span>
                        )}
                        {metrics.comments !== undefined && (
                            <span>
                                {t('dashboard.comments')}{' '}
                                {formatMetric(metrics.comments)}
                            </span>
                        )}
                        {metrics.reactions !== undefined &&
                            metrics.likes === undefined && (
                                <span>
                                    {t('dashboard.reactions')}{' '}
                                    {formatMetric(metrics.reactions)}
                                </span>
                            )}
                    </div>
                </div>
            </div>

            <div className={`flex flex-1 flex-col ${compact ? 'p-3' : 'p-4'}`}>
                <p className="truncate text-sm font-medium text-sp-ink">
                    {post.asset_name ?? t('dashboard.unknown_asset')}
                </p>
                <p
                    className={`mt-1 text-sm text-sp-muted ${
                        compact ? 'line-clamp-1' : 'line-clamp-2'
                    }`}
                >
                    {post.content_preview ?? t('dashboard.content_no_caption')}
                </p>
                <div className="mt-auto flex items-center justify-between gap-2 pt-3 text-xs text-sp-muted">
                    <span>
                        {post.published_at
                            ? new Date(post.published_at).toLocaleDateString(
                                  undefined,
                                  {
                                      day: 'numeric',
                                      month: 'short',
                                      year: 'numeric',
                                  },
                              )
                            : '—'}
                    </span>
                    {highlightMetric && (
                        <span className="font-semibold text-sp-primary">
                            {highlightLabel}:{' '}
                            {formatMetric(metrics[highlightMetric])}
                        </span>
                    )}
                </div>
            </div>
        </Wrapper>
    );
}
