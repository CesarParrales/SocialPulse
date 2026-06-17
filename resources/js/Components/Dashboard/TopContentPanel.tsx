import ContentPostCard from '@/Components/Dashboard/ContentPostCard';
import { useTranslation } from '@/lib/i18n';
import { ContentPost, TopPostsByMetric } from '@/types/content';
import { useState } from 'react';

type SortKey = keyof TopPostsByMetric;

const tabs: Array<{
    key: SortKey;
    metricKey: string;
    labelKey: string;
}> = [
    { key: 'by_reach', metricKey: 'reach', labelKey: 'dashboard.reach' },
    {
        key: 'by_engagement',
        metricKey: 'engagement',
        labelKey: 'dashboard.engagement_rate',
    },
    {
        key: 'by_interactions',
        metricKey: 'interactions',
        labelKey: 'dashboard.interactions',
    },
];

export default function TopContentPanel({
    topPosts,
}: {
    topPosts: TopPostsByMetric;
}) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState<SortKey>('by_reach');
    const activeConfig = tabs.find((tab) => tab.key === activeTab) ?? tabs[0];
    const posts = topPosts[activeTab];

    return (
        <div className="sp-card p-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 className="text-base font-semibold text-sp-ink">
                        {t('dashboard.top_content_title')}
                    </h3>
                    <p className="mt-1 text-sm text-sp-muted">
                        {t('dashboard.top_content_description')}
                    </p>
                </div>
                <div
                    className="flex flex-wrap gap-1 rounded-lg bg-sp-surface p-1"
                    role="tablist"
                    aria-label={t('dashboard.top_content_title')}
                >
                    {tabs.map((tab) => (
                        <button
                            key={tab.key}
                            type="button"
                            role="tab"
                            aria-selected={activeTab === tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            className={`rounded-md px-3 py-1.5 text-sm font-medium transition ${
                                activeTab === tab.key
                                    ? 'bg-sp-primary text-white shadow-sm'
                                    : 'text-sp-muted hover:text-sp-ink'
                            }`}
                        >
                            {t(tab.labelKey)}
                        </button>
                    ))}
                </div>
            </div>

            {posts.length === 0 ? (
                <p className="mt-6 text-sm text-sp-muted">
                    {t('dashboard.top_content_empty')}
                </p>
            ) : (
                <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                    {posts.map((post: ContentPost) => (
                        <ContentPostCard
                            key={`${activeTab}-${post.id}`}
                            post={post}
                            highlightMetric={activeConfig.metricKey}
                            highlightLabel={t(activeConfig.labelKey)}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
