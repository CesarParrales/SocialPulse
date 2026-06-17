import PrimaryButton from '@/Components/PrimaryButton';
import EmptyState from '@/Components/UI/EmptyState';
import PageHeader from '@/Components/UI/PageHeader';
import SearchInput from '@/Components/UI/SearchInput';
import { useTranslation } from '@/lib/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { PageProps } from '@/types';

type WorkspaceRow = {
    id: number;
    name: string;
    industry_category: string | null;
    timezone: string;
    connected_assets_count: number;
    agency?: { id: number; name: string };
};

export default function Index({
    workspaces,
    canCreate,
    isClientView = false,
}: PageProps<{
    workspaces: WorkspaceRow[];
    canCreate: boolean;
    isClientView?: boolean;
}>) {
    const { t } = useTranslation();
    const [query, setQuery] = useState('');

    const filtered = useMemo(() => {
        const term = query.trim().toLowerCase();
        if (!term) {
            return workspaces;
        }
        return workspaces.filter(
            (workspace) =>
                workspace.name.toLowerCase().includes(term) ||
                workspace.agency?.name.toLowerCase().includes(term) ||
                workspace.industry_category?.toLowerCase().includes(term),
        );
    }, [query, workspaces]);

    return (
        <AuthenticatedLayout>
            <Head title={t('workspaces.title')} />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <PageHeader
                    title={t('workspaces.title')}
                    description={
                        isClientView
                            ? t('workspaces.client_description')
                            : t('workspaces.description')
                    }
                    actions={
                        canCreate ? (
                            <Link href={route('workspaces.create')}>
                                <PrimaryButton>{t('workspaces.new')}</PrimaryButton>
                            </Link>
                        ) : undefined
                    }
                />

                {workspaces.length > 0 && (
                    <SearchInput
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder={t('workspaces.search_placeholder')}
                        wrapperClassName="mb-8 max-w-md"
                        aria-label={t('workspaces.search_placeholder')}
                    />
                )}

                {workspaces.length === 0 ? (
                    <EmptyState
                        title={t('workspaces.empty')}
                        description={t('workspaces.empty_hint')}
                        action={
                            canCreate
                                ? {
                                      label: t('workspaces.new'),
                                      href: route('workspaces.create'),
                                  }
                                : undefined
                        }
                    />
                ) : filtered.length === 0 ? (
                    <div className="sp-card p-8 text-center text-sm text-sp-muted">
                        {t('workspaces.no_search_results', { query })}
                    </div>
                ) : (
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {filtered.map((workspace) => (
                            <li key={workspace.id}>
                                <Link
                                    href={
                                        isClientView
                                            ? route(
                                                  'workspaces.dashboard',
                                                  workspace.id,
                                              )
                                            : route(
                                                  'workspaces.show',
                                                  workspace.id,
                                              )
                                    }
                                    className="sp-card group flex h-full flex-col p-5 transition hover:-translate-y-0.5 hover:shadow-sp-lg"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <p className="font-semibold text-sp-ink group-hover:text-sp-primary">
                                            {workspace.name}
                                        </p>
                                        {workspace.industry_category && (
                                            <span className="shrink-0 rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-sp-primary">
                                                {workspace.industry_category}
                                            </span>
                                        )}
                                    </div>
                                    <p className="mt-2 text-sm text-sp-muted">
                                        {workspace.agency?.name ?? '—'}
                                    </p>
                                    <div className="mt-auto flex flex-wrap items-center gap-2 pt-4 text-xs text-sp-muted">
                                        <span>{workspace.timezone}</span>
                                        <span aria-hidden>·</span>
                                        <span
                                            className={
                                                workspace.connected_assets_count > 0
                                                    ? 'text-emerald-700'
                                                    : 'text-amber-700'
                                            }
                                        >
                                            {t('workspaces.connected_assets', {
                                                count: String(
                                                    workspace.connected_assets_count,
                                                ),
                                            })}
                                        </span>
                                    </div>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
