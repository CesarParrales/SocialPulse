import PageHeader from '@/Components/UI/PageHeader';
import EmptyState from '@/Components/UI/EmptyState';
import FlashAlert from '@/Components/UI/FlashAlert';
import StatusBadge from '@/Components/UI/StatusBadge';
import { useTranslation } from '@/lib/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

interface NotificationItem {
    id: string;
    type: string;
    title: string;
    message: string;
    action_url: string | null;
    action_label: string;
    read_at: string | null;
    created_at: string;
}

interface PaginatedNotifications {
    data: NotificationItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const typeStatus: Record<string, string> = {
    ingestion_failed: 'failed',
    token_refresh_failed: 'error',
    token_expiring: 'pending',
};

const typeLabelKeys: Record<string, string> = {
    ingestion_failed: 'notifications.type_ingestion',
    token_refresh_failed: 'notifications.type_token',
    token_expiring: 'notifications.type_expiring',
};

export default function Index({
    notifications,
    unread_count,
}: PageProps<{
    notifications: PaginatedNotifications;
    unread_count: number;
}>) {
    const { t } = useTranslation();
    const { flash, locale } = usePage().props;

    const markAllRead = () => {
        router.post(route('notifications.read-all'));
    };

    const markRead = (id: string) => {
        router.patch(route('notifications.read', id), {}, { preserveScroll: true });
    };

    const formatDate = (iso: string) =>
        new Date(iso).toLocaleString(locale === 'en' ? 'en-US' : 'es-ES', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });

    return (
        <AuthenticatedLayout>
            <Head title={t('notifications.title')} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <PageHeader
                    title={t('notifications.title')}
                    description={
                        unread_count > 0
                            ? t('notifications.description_unread', {
                                  count: String(unread_count),
                              })
                            : t('notifications.description')
                    }
                    actions={
                        unread_count > 0 ? (
                            <button
                                type="button"
                                onClick={markAllRead}
                                className="sp-link text-sm"
                            >
                                {t('notifications.mark_all_read')}
                            </button>
                        ) : undefined
                    }
                />

                {flash.success && <FlashAlert message={flash.success} />}

                {notifications.data.length === 0 ? (
                    <EmptyState
                        title={t('notifications.empty')}
                        description={t('notifications.empty_hint')}
                    />
                ) : (
                    <ul className="space-y-3" aria-live="polite">
                        {notifications.data.map((notification) => {
                            const isUnread = notification.read_at === null;
                            const typeKey = typeLabelKeys[notification.type];
                            const typeLabel = typeKey
                                ? t(typeKey)
                                : notification.type;

                            return (
                                <li
                                    key={notification.id}
                                    className={
                                        'sp-card p-4 transition ' +
                                        (isUnread
                                            ? 'border-l-4 border-l-sp-primary bg-sp-primary/5'
                                            : 'opacity-90')
                                    }
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <StatusBadge
                                                    status={
                                                        typeStatus[
                                                            notification.type
                                                        ] ?? 'processing'
                                                    }
                                                    label={typeLabel}
                                                />
                                                <h3 className="font-semibold text-sp-ink">
                                                    {notification.title}
                                                </h3>
                                                {isUnread && (
                                                    <span className="rounded-full bg-sp-primary/10 px-2 py-0.5 text-xs font-medium text-sp-primary">
                                                        {t(
                                                            'notifications.unread',
                                                        )}
                                                    </span>
                                                )}
                                            </div>
                                            <p className="mt-2 text-sm leading-relaxed text-sp-muted">
                                                {notification.message}
                                            </p>
                                            <p className="mt-2 text-xs text-sp-muted">
                                                <time
                                                    dateTime={
                                                        notification.created_at
                                                    }
                                                >
                                                    {formatDate(
                                                        notification.created_at,
                                                    )}
                                                </time>
                                            </p>
                                            <div className="mt-3 flex flex-wrap gap-3">
                                                {notification.action_url && (
                                                    <Link
                                                        href={
                                                            notification.action_url
                                                        }
                                                        className="sp-link text-sm font-medium"
                                                        onClick={() => {
                                                            if (isUnread) {
                                                                markRead(
                                                                    notification.id,
                                                                );
                                                            }
                                                        }}
                                                    >
                                                        {notification.action_label}{' '}
                                                        →
                                                    </Link>
                                                )}
                                                {isUnread && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            markRead(
                                                                notification.id,
                                                            )
                                                        }
                                                        className="text-sm text-sp-muted hover:text-sp-ink"
                                                    >
                                                        {t(
                                                            'notifications.mark_read',
                                                        )}
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            );
                        })}
                    </ul>
                )}

                {notifications.links.length > 3 && (
                    <nav
                        className="flex flex-wrap justify-center gap-2"
                        aria-label={t('notifications.pagination')}
                    >
                        {notifications.links.map((link, index) =>
                            link.url ? (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={
                                        'rounded-lg px-3 py-1.5 text-sm transition ' +
                                        (link.active
                                            ? 'bg-sp-primary text-white'
                                            : 'text-sp-muted hover:bg-sp-surface')
                                    }
                                    preserveScroll
                                    aria-current={
                                        link.active ? 'page' : undefined
                                    }
                                >
                                    {link.label}
                                </Link>
                            ) : (
                                <span
                                    key={index}
                                    className="px-3 py-1.5 text-sm text-sp-muted"
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ),
                        )}
                    </nav>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
