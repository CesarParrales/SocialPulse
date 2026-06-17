import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import EmptyState from '@/Components/UI/EmptyState';
import OnboardingChecklist, {
    OnboardingStep,
} from '@/Components/UI/OnboardingChecklist';
import PageHeader from '@/Components/UI/PageHeader';
import { useTranslation } from '@/lib/i18n';
import { Head, Link } from '@inertiajs/react';
import { useMemo } from 'react';
import { PageProps } from '@/types';

type WorkspaceSummary = {
    id: number;
    name: string;
    industry_category: string | null;
    timezone: string;
    agency?: { id: number; name: string };
};

type OnboardingPayload = {
    complete: boolean;
    steps: Array<{ key: string; done: boolean; href: string | null }>;
};

export default function Dashboard({
    workspaces,
    canCreateWorkspace,
    onboarding,
}: PageProps<{
    workspaces: WorkspaceSummary[];
    canCreateWorkspace: boolean;
    onboarding: OnboardingPayload;
}>) {
    const { t } = useTranslation();

    const onboardingSteps: OnboardingStep[] = useMemo(
        () =>
            onboarding.steps.map((step) => ({
                key: step.key,
                done: step.done,
                href: step.href ?? undefined,
                label: t(`onboarding.steps.${step.key}.label`),
                description: t(`onboarding.steps.${step.key}.description`),
            })),
        [onboarding.steps, t],
    );

    return (
        <AuthenticatedLayout>
            <Head title={t('home.title')} />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <PageHeader
                    title={t('home.welcome')}
                    description={t('home.tagline')}
                />

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        {!onboarding.complete && (
                            <OnboardingChecklist
                                title={t('home.onboarding_title')}
                                completeMessage={t('home.onboarding_complete')}
                                steps={onboardingSteps}
                            />
                        )}

                        <div>
                            <div className="mb-4 flex items-center justify-between">
                                <h2 className="text-base font-semibold text-sp-ink">
                                    {t('home.recent_workspaces')}
                                </h2>
                                <Link
                                    href={route('workspaces.index')}
                                    className="sp-link text-sm"
                                >
                                    {t('home.view_all_workspaces')}
                                </Link>
                            </div>

                            {workspaces.length === 0 ? (
                                <EmptyState
                                    title={t('home.no_workspaces')}
                                    description={t('home.no_workspaces_hint')}
                                    action={
                                        canCreateWorkspace
                                            ? {
                                                  label: t(
                                                      'home.create_workspace',
                                                  ),
                                                  href: route(
                                                      'workspaces.create',
                                                  ),
                                              }
                                            : undefined
                                    }
                                />
                            ) : (
                                <ul className="grid gap-3 sm:grid-cols-2">
                                    {workspaces.map((workspace) => (
                                        <li key={workspace.id}>
                                            <Link
                                                href={route(
                                                    'workspaces.dashboard',
                                                    workspace.id,
                                                )}
                                                className="sp-card group block p-5 transition hover:-translate-y-0.5 hover:shadow-sp-lg"
                                            >
                                                <p className="font-semibold text-sp-ink group-hover:text-sp-primary">
                                                    {workspace.name}
                                                </p>
                                                {workspace.agency && (
                                                    <p className="mt-1 text-sm text-sp-muted">
                                                        {workspace.agency.name}
                                                    </p>
                                                )}
                                                {workspace.industry_category && (
                                                    <p className="mt-2 text-xs text-sp-muted">
                                                        {
                                                            workspace.industry_category
                                                        }
                                                    </p>
                                                )}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </div>

                    <div className="sp-card p-6">
                        <h3 className="text-lg font-semibold text-sp-ink">
                            {t('home.next_steps')}
                        </h3>
                        <ul className="mt-3 space-y-2 text-sm text-sp-muted">
                            <li>{t('home.step_1')}</li>
                            <li>{t('home.step_2')}</li>
                            <li>{t('home.step_3')}</li>
                        </ul>
                        {canCreateWorkspace && (
                            <Link
                                href={route('workspaces.create')}
                                className="mt-6 inline-flex items-center rounded-lg bg-sp-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-sp-primary/90"
                            >
                                {t('home.create_workspace')}
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
