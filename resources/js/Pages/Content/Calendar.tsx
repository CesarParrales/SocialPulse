import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import Card from '@/Components/UI/Card';
import FlashAlert from '@/Components/UI/FlashAlert';
import StatusBadge from '@/Components/UI/StatusBadge';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { useTranslation } from '@/lib/i18n';
import { router, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { PageProps } from '@/types';

type CalendarEntry = {
    id: number;
    title: string;
    scheduled_at: string | null;
    scheduled_date: string | null;
    channel: string;
    channel_label: string;
    content_type: string;
    content_type_label: string;
    draft_id: number | null;
    draft_status: string | null;
    draft_status_label: string | null;
};

type DraftRow = {
    id: number;
    calendar_entry_id: number | null;
    title: string;
    caption: string | null;
    channel: string;
    channel_label: string;
    content_type: string;
    content_type_label: string;
    status: string;
    status_label: string;
    review_notes: string | null;
    scheduled_at: string | null;
    scheduled_date: string | null;
    is_editable: boolean;
    can_publish: boolean;
    media_url: string | null;
    platform_post_id: string | null;
    published_to_platform_at: string | null;
    publish_error: string | null;
    platform_permalink: string | null;
};

type Option = { value: string; label: string };

function shiftMonth(month: string, delta: number): string {
    const [year, mon] = month.split('-').map(Number);
    const date = new Date(year, mon - 1 + delta, 1);

    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
}

export default function Calendar({
    workspace,
    month,
    canManage,
    canReview,
    calendarEntries,
    drafts,
    channelOptions,
    typeOptions,
}: PageProps<{
    workspace: { id: number; name: string };
    month: string;
    canManage: boolean;
    canReview: boolean;
    calendarEntries: CalendarEntry[];
    drafts: DraftRow[];
    channelOptions: Option[];
    typeOptions: Option[];
}>) {
    const { t } = useTranslation();
    const { flash } = usePage().props;

    const entryForm = useForm({
        title: '',
        scheduled_at: `${month}-01`,
        channel: 'instagram',
        content_type: 'feed',
    });

    const draftForm = useForm({
        title: '',
        caption: '',
        channel: 'instagram',
        content_type: 'feed',
        scheduled_at: '',
        media_url: '',
        calendar_entry_id: null as number | null,
    });

    const entriesByDate = useMemo(() => {
        const map = new Map<string, CalendarEntry[]>();

        for (const entry of calendarEntries) {
            const key = entry.scheduled_date ?? 'unscheduled';

            if (!map.has(key)) {
                map.set(key, []);
            }

            map.get(key)!.push(entry);
        }

        return [...map.entries()].sort(([a], [b]) => a.localeCompare(b));
    }, [calendarEntries]);

    const pendingDrafts = drafts.filter((d) => d.status === 'pending_review');

    const submitEntry = (event: FormEvent) => {
        event.preventDefault();
        entryForm.post(route('workspaces.content.entries.store', workspace.id), {
            onSuccess: () => entryForm.reset('title'),
        });
    };

    const submitDraft = (event: FormEvent) => {
        event.preventDefault();
        draftForm.post(route('workspaces.content.drafts.store', workspace.id), {
            onSuccess: () =>
                draftForm.reset('title', 'caption', 'scheduled_at', 'media_url', 'calendar_entry_id'),
        });
    };

    const attachDraftToEntry = (entry: CalendarEntry) => {
        draftForm.setData({
            ...draftForm.data,
            title: entry.title,
            channel: entry.channel,
            content_type: entry.content_type,
            scheduled_at: entry.scheduled_date ?? '',
            calendar_entry_id: entry.id,
        });
    };

    const reviewDraft = (draftId: number, action: 'approve' | 'reject', notes = '') => {
        router.post(
            route('workspaces.content.drafts.review', [workspace.id, draftId]),
            { action, review_notes: notes },
        );
    };

    return (
        <WorkspaceLayout
            headTitle={t('content.title')}
            title={t('content.title')}
            description={t('content.description', { name: workspace.name })}
            workspace={workspace}
            active="content"
        >
            <div className="space-y-6">
                {flash.success && <FlashAlert message={flash.success} />}

                <CalloutBanner
                    title={t('content.hint_title')}
                    variant="info"
                >
                    {canManage
                        ? t('content.manage_hint')
                        : t('content.client_hint')}
                </CalloutBanner>

                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                        <SecondaryButton
                            type="button"
                            onClick={() =>
                                router.get(route('workspaces.content.index', workspace.id), {
                                    month: shiftMonth(month, -1),
                                })
                            }
                        >
                            ←
                        </SecondaryButton>
                        <span className="text-sm font-semibold text-sp-ink">{month}</span>
                        <SecondaryButton
                            type="button"
                            onClick={() =>
                                router.get(route('workspaces.content.index', workspace.id), {
                                    month: shiftMonth(month, 1),
                                })
                            }
                        >
                            →
                        </SecondaryButton>
                    </div>
                </div>

                {canManage && (
                    <Card>
                        <h3 className="text-sm font-semibold text-sp-ink">
                            {t('content.new_entry')}
                        </h3>
                        <form onSubmit={submitEntry} className="mt-4 grid gap-4 lg:grid-cols-2">
                            <div>
                                <InputLabel htmlFor="entry-title" value={t('common.title')} />
                                <TextInput
                                    id="entry-title"
                                    className="mt-1 block w-full"
                                    value={entryForm.data.title}
                                    onChange={(e) => entryForm.setData('title', e.target.value)}
                                    required
                                />
                                <InputError message={entryForm.errors.title} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="entry-date" value={t('content.scheduled_date')} />
                                <TextInput
                                    id="entry-date"
                                    type="date"
                                    className="mt-1 block w-full"
                                    value={entryForm.data.scheduled_at}
                                    onChange={(e) =>
                                        entryForm.setData('scheduled_at', e.target.value)
                                    }
                                    required
                                />
                            </div>
                            <div>
                                <InputLabel htmlFor="entry-channel" value={t('content.channel')} />
                                <select
                                    id="entry-channel"
                                    className="sp-input mt-1 block w-full"
                                    value={entryForm.data.channel}
                                    onChange={(e) =>
                                        entryForm.setData('channel', e.target.value)
                                    }
                                >
                                    {channelOptions.map((option) => (
                                        <option key={option.value} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <InputLabel htmlFor="entry-type" value={t('content.content_type')} />
                                <select
                                    id="entry-type"
                                    className="sp-input mt-1 block w-full"
                                    value={entryForm.data.content_type}
                                    onChange={(e) =>
                                        entryForm.setData('content_type', e.target.value)
                                    }
                                >
                                    {typeOptions.map((option) => (
                                        <option key={option.value} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="lg:col-span-2">
                                <PrimaryButton disabled={entryForm.processing}>
                                    {t('content.add_entry')}
                                </PrimaryButton>
                            </div>
                        </form>
                    </Card>
                )}

                <Card>
                    <h3 className="text-sm font-semibold text-sp-ink">
                        {t('content.calendar_month')}
                    </h3>
                    {entriesByDate.length === 0 ? (
                        <p className="mt-4 text-sm text-sp-muted">{t('content.empty_calendar')}</p>
                    ) : (
                        <div className="mt-4 space-y-4">
                            {entriesByDate.map(([date, entries]) => (
                                <div key={date}>
                                    <p className="text-xs font-semibold uppercase tracking-wide text-sp-muted">
                                        {date}
                                    </p>
                                    <ul className="mt-2 space-y-2">
                                        {entries.map((entry) => (
                                            <li
                                                key={entry.id}
                                                className="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-sp-border px-3 py-2"
                                            >
                                                <div>
                                                    <p className="font-medium text-sp-ink">
                                                        {entry.title}
                                                    </p>
                                                    <p className="text-xs text-sp-muted">
                                                        {entry.channel_label} ·{' '}
                                                        {entry.content_type_label}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    {entry.draft_status ? (
                                                        <StatusBadge
                                                            status={entry.draft_status}
                                                            label={entry.draft_status_label ?? undefined}
                                                        />
                                                    ) : canManage ? (
                                                        <SecondaryButton
                                                            type="button"
                                                            onClick={() => attachDraftToEntry(entry)}
                                                        >
                                                            {t('content.create_draft')}
                                                        </SecondaryButton>
                                                    ) : null}
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ))}
                        </div>
                    )}
                </Card>

                {canManage && (
                    <Card>
                        <h3 className="text-sm font-semibold text-sp-ink">
                            {t('content.new_draft')}
                        </h3>
                        <form onSubmit={submitDraft} className="mt-4 space-y-4">
                            <div className="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <InputLabel htmlFor="draft-title" value={t('common.title')} />
                                    <TextInput
                                        id="draft-title"
                                        className="mt-1 block w-full"
                                        value={draftForm.data.title}
                                        onChange={(e) =>
                                            draftForm.setData('title', e.target.value)
                                        }
                                        required
                                    />
                                </div>
                                <div>
                                    <InputLabel
                                        htmlFor="draft-scheduled"
                                        value={t('content.scheduled_date')}
                                    />
                                    <TextInput
                                        id="draft-scheduled"
                                        type="date"
                                        className="mt-1 block w-full"
                                        value={draftForm.data.scheduled_at}
                                        onChange={(e) =>
                                            draftForm.setData('scheduled_at', e.target.value)
                                        }
                                    />
                                </div>
                            </div>
                            <div>
                                <InputLabel htmlFor="draft-caption" value={t('content.caption')} />
                                <textarea
                                    id="draft-caption"
                                    className="sp-input mt-1 block min-h-28 w-full"
                                    value={draftForm.data.caption}
                                    onChange={(e) =>
                                        draftForm.setData('caption', e.target.value)
                                    }
                                />
                            </div>
                            <div>
                                <InputLabel htmlFor="draft-media" value={t('content.media_url')} />
                                <TextInput
                                    id="draft-media"
                                    type="url"
                                    className="mt-1 block w-full"
                                    value={draftForm.data.media_url}
                                    onChange={(e) =>
                                        draftForm.setData('media_url', e.target.value)
                                    }
                                    placeholder="https://..."
                                />
                                <p className="mt-1 text-xs text-sp-muted">
                                    {t('content.media_url_hint')}
                                </p>
                            </div>
                            <PrimaryButton disabled={draftForm.processing}>
                                {t('content.save_draft')}
                            </PrimaryButton>
                        </form>
                    </Card>
                )}

                <Card>
                    <h3 className="text-sm font-semibold text-sp-ink">
                        {t('content.drafts_title')}
                    </h3>
                    {drafts.length === 0 ? (
                        <p className="mt-4 text-sm text-sp-muted">{t('content.empty_drafts')}</p>
                    ) : (
                        <div className="mt-4 space-y-4">
                            {drafts.map((draft) => (
                                <div
                                    key={draft.id}
                                    className="rounded-lg border border-sp-border p-4"
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p className="font-medium text-sp-ink">{draft.title}</p>
                                            <p className="mt-1 text-xs text-sp-muted">
                                                {draft.channel_label} · {draft.content_type_label}
                                                {draft.scheduled_date
                                                    ? ` · ${draft.scheduled_date}`
                                                    : ''}
                                            </p>
                                        </div>
                                        <StatusBadge
                                            status={draft.status}
                                            label={draft.status_label}
                                        />
                                    </div>
                                    {draft.caption && (
                                        <p className="mt-3 whitespace-pre-wrap text-sm text-sp-ink">
                                            {draft.caption}
                                        </p>
                                    )}
                                    {draft.review_notes && (
                                        <p className="mt-2 text-sm text-amber-800">
                                            {t('content.review_notes')}: {draft.review_notes}
                                        </p>
                                    )}
                                    {draft.publish_error && (
                                        <p className="mt-2 text-sm text-red-700">
                                            {t('content.publish_error')}: {draft.publish_error}
                                        </p>
                                    )}
                                    {draft.platform_permalink && (
                                        <p className="mt-2 text-sm">
                                            <a
                                                href={draft.platform_permalink}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="sp-link"
                                            >
                                                {t('content.view_published')}
                                            </a>
                                        </p>
                                    )}
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        {canManage && draft.can_publish && (
                                            <PrimaryButton
                                                type="button"
                                                onClick={() =>
                                                    router.post(
                                                        route(
                                                            'workspaces.content.drafts.publish',
                                                            [workspace.id, draft.id],
                                                        ),
                                                    )
                                                }
                                            >
                                                {t('content.publish_meta')}
                                            </PrimaryButton>
                                        )}
                                        {canManage && draft.is_editable && (
                                            <SecondaryButton
                                                type="button"
                                                onClick={() =>
                                                    router.post(
                                                        route(
                                                            'workspaces.content.drafts.submit',
                                                            [workspace.id, draft.id],
                                                        ),
                                                    )
                                                }
                                            >
                                                {t('content.submit_review')}
                                            </SecondaryButton>
                                        )}
                                        {canReview && draft.status === 'pending_review' && (
                                            <>
                                                <PrimaryButton
                                                    type="button"
                                                    onClick={() =>
                                                        reviewDraft(draft.id, 'approve')
                                                    }
                                                >
                                                    {t('content.approve')}
                                                </PrimaryButton>
                                                <SecondaryButton
                                                    type="button"
                                                    onClick={() => {
                                                        const notes = window.prompt(
                                                            t('content.reject_prompt'),
                                                        );

                                                        if (notes) {
                                                            reviewDraft(
                                                                draft.id,
                                                                'reject',
                                                                notes,
                                                            );
                                                        }
                                                    }}
                                                >
                                                    {t('content.reject')}
                                                </SecondaryButton>
                                            </>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </Card>

                {canReview && pendingDrafts.length > 0 && (
                    <CalloutBanner title={t('content.pending_banner_title')} variant="info">
                        {t('content.pending_banner_body', {
                            count: String(pendingDrafts.length),
                        })}
                    </CalloutBanner>
                )}
            </div>
        </WorkspaceLayout>
    );
}
