import ContentPostCard from '@/Components/Dashboard/ContentPostCard';
import { useTranslation } from '@/lib/i18n';
import { ContentPost } from '@/types/content';

export default function RecentContentFeed({
    posts,
}: {
    posts: ContentPost[];
}) {
    const { t } = useTranslation();

    return (
        <div className="sp-card p-5">
            <div>
                <h3 className="text-base font-semibold text-sp-ink">
                    {t('dashboard.recent_content_title')}
                </h3>
                <p className="mt-1 text-sm text-sp-muted">
                    {t('dashboard.recent_content_description')}
                </p>
            </div>

            {posts.length === 0 ? (
                <p className="mt-6 text-sm text-sp-muted">
                    {t('dashboard.recent_content_empty')}
                </p>
            ) : (
                <div className="mt-6 flex gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    {posts.map((post) => (
                        <div
                            key={post.id}
                            className="w-56 shrink-0 sm:w-64"
                        >
                            <ContentPostCard post={post} compact />
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
