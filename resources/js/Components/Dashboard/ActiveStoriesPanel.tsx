import { useTranslation } from '@/lib/i18n';
import { ActiveStory } from '@/types/content';

function formatMetric(value: number | null | undefined): string {
    if (value === null || value === undefined) {
        return '—';
    }

    return value.toLocaleString();
}

export default function ActiveStoriesPanel({
    stories,
}: {
    stories: ActiveStory[];
}) {
    const { t } = useTranslation();

    return (
        <div className="sp-card p-5">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 className="text-base font-semibold text-sp-ink">
                        {t('dashboard.active_stories_title')}
                    </h3>
                    <p className="mt-1 text-sm text-sp-muted">
                        {t('dashboard.active_stories_description')}
                    </p>
                </div>
                <span className="inline-flex w-fit rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800 ring-1 ring-amber-200">
                    {t('dashboard.active_stories_badge')}
                </span>
            </div>

            {stories.length === 0 ? (
                <p className="mt-6 text-sm text-sp-muted">
                    {t('dashboard.active_stories_empty')}
                </p>
            ) : (
                <div className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {stories.map((story) => (
                        <div
                            key={story.id}
                            className="rounded-xl border border-sp-border bg-gradient-to-br from-amber-50/80 to-sp-surface p-4"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-medium text-sp-ink">
                                        {story.asset_name ??
                                            t('dashboard.unknown_asset')}
                                    </p>
                                    <p className="mt-1 text-xs text-sp-muted">
                                        {story.captured_at
                                            ? t(
                                                  'dashboard.story_captured_at',
                                                  {
                                                      date: new Date(
                                                          story.captured_at,
                                                      ).toLocaleString(),
                                                  },
                                              )
                                            : '—'}
                                    </p>
                                </div>
                                <span className="rounded-md bg-amber-500 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                    {t('dashboard.post_types.story')}
                                </span>
                            </div>

                            <dl className="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                                <div className="rounded-lg bg-white/80 p-2 ring-1 ring-sp-border/60">
                                    <dt className="text-sp-muted">
                                        {t('dashboard.reach')}
                                    </dt>
                                    <dd className="mt-1 text-sm font-semibold tabular-nums text-sp-ink">
                                        {formatMetric(story.reach)}
                                    </dd>
                                </div>
                                <div className="rounded-lg bg-white/80 p-2 ring-1 ring-sp-border/60">
                                    <dt className="text-sp-muted">
                                        {t('dashboard.impressions')}
                                    </dt>
                                    <dd className="mt-1 text-sm font-semibold tabular-nums text-sp-ink">
                                        {formatMetric(story.impressions)}
                                    </dd>
                                </div>
                                <div className="rounded-lg bg-white/80 p-2 ring-1 ring-sp-border/60">
                                    <dt className="text-sp-muted">
                                        {t('dashboard.replies')}
                                    </dt>
                                    <dd className="mt-1 text-sm font-semibold tabular-nums text-sp-ink">
                                        {formatMetric(story.replies)}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
