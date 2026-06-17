import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Card from '@/Components/UI/Card';
import EmptyState from '@/Components/UI/EmptyState';
import FlashAlert from '@/Components/UI/FlashAlert';
import OnboardingChecklist, {
    OnboardingStep,
} from '@/Components/UI/OnboardingChecklist';
import StatusBadge from '@/Components/UI/StatusBadge';
import WorkspacePerformanceSnapshot, {
    PerformanceSnapshot,
} from '@/Components/Workspaces/WorkspacePerformanceSnapshot';
import WorkspaceLayout from '@/Components/Templates/WorkspaceLayout';
import { useTranslation } from '@/lib/i18n';
import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useMemo } from 'react';
import { PageProps, Workspace } from '@/types';

type SetupPayload = {
    complete: boolean;
    steps: Array<{ key: string; done: boolean; href: string | null }>;
};

export default function Show({
    workspace,
    canAssignMembers,
    assignableMembers,
    memberRoles,
    stats,
    setup,
    performanceSnapshot,
}: PageProps<{
    workspace: Workspace;
    canAssignMembers: boolean;
    assignableMembers: Array<{ id: number; name: string; email: string }>;
    memberRoles: Array<{ value: string; label: string }>;
    stats: {
        connections_count: number;
        connected_assets_count: number;
        members_count: number;
    };
    setup: SetupPayload;
    performanceSnapshot: PerformanceSnapshot;
}>) {
    const { t } = useTranslation();
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        role: memberRoles[0]?.value ?? 'operator',
        invite: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('workspaces.members.store', workspace.id), {
            onSuccess: () => reset('email'),
        });
    };

    const roleLabel = (role: string) =>
        memberRoles.find((item) => item.value === role)?.label ?? role;

    const setupSteps: OnboardingStep[] = useMemo(
        () =>
            setup.steps.map((step) => ({
                key: step.key,
                done: step.done,
                href: step.href ?? undefined,
                label: t(`workspaces.setup_steps.${step.key}.label`),
                description: t(`workspaces.setup_steps.${step.key}.description`),
            })),
        [setup.steps, t],
    );

    return (
        <WorkspaceLayout
            headTitle={workspace.name}
            title={workspace.name}
            description={workspace.agency?.name ?? undefined}
            workspace={workspace}
            active="overview"
        >
                <div className="space-y-6">
                    {flash.success && <FlashAlert message={flash.success} />}

                    <div className="grid gap-4 sm:grid-cols-3">
                        <StatCard
                            label={t('workspaces.stat_connections')}
                            value={stats.connections_count}
                        />
                        <StatCard
                            label={t('workspaces.stat_assets')}
                            value={stats.connected_assets_count}
                            highlight={stats.connected_assets_count === 0}
                        />
                        <StatCard
                            label={t('workspaces.stat_members')}
                            value={stats.members_count}
                        />
                    </div>

                    {!setup.complete && (
                        <OnboardingChecklist
                            title={t('workspaces.setup_title')}
                            completeMessage={t('workspaces.setup_complete')}
                            steps={setupSteps}
                        />
                    )}

                    <WorkspacePerformanceSnapshot
                        workspaceId={workspace.id}
                        snapshot={performanceSnapshot}
                    />

                    <Card>
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt className="text-sm text-sp-muted">
                                    {t('common.agency')}
                                </dt>
                                <dd className="font-medium text-sp-ink">
                                    {workspace.agency?.name}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm text-sp-muted">
                                    {t('common.timezone')}
                                </dt>
                                <dd className="font-medium text-sp-ink">
                                    {workspace.timezone}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm text-sp-muted">
                                    {t('common.industry')}
                                </dt>
                                <dd className="font-medium text-sp-ink">
                                    {workspace.industry_category ?? '—'}
                                </dd>
                            </div>
                            {workspace.region && (
                                <div>
                                    <dt className="text-sm text-sp-muted">
                                        {t('common.region')}
                                    </dt>
                                    <dd className="font-medium text-sp-ink">
                                        {workspace.region}
                                    </dd>
                                </div>
                            )}
                        </dl>
                        <div className="mt-6 flex flex-wrap gap-3">
                            <Link
                                href={route(
                                    'workspaces.connections.index',
                                    workspace.id,
                                )}
                                className="inline-flex items-center rounded-lg border border-sp-border px-4 py-2 text-sm font-medium text-sp-ink transition hover:bg-sp-surface"
                            >
                                {t('workspaces.manage_connections')}
                            </Link>
                        </div>
                    </Card>

                    <Card>
                        <h3 className="mb-4 text-base font-semibold text-sp-ink">
                            {t('workspaces.assigned_members')}
                        </h3>
                        {workspace.members && workspace.members.length > 0 ? (
                            <ul className="divide-y divide-sp-border">
                                {workspace.members.map((member) => (
                                    <li
                                        key={member.id}
                                        className="flex items-center justify-between py-3"
                                    >
                                        <div>
                                            <p className="font-medium text-sp-ink">
                                                {member.name}
                                            </p>
                                            <p className="text-sm text-sp-muted">
                                                {member.email}
                                            </p>
                                        </div>
                                        <StatusBadge
                                            status={member.pivot.role}
                                            label={roleLabel(member.pivot.role)}
                                        />
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <EmptyState
                                title={t('workspaces.no_members')}
                                description={t('workspaces.no_members_hint')}
                            />
                        )}
                    </Card>

                    {canAssignMembers && (
                        <Card>
                            <h3 className="mb-4 text-base font-semibold text-sp-ink">
                                {t('workspaces.assign_member')}
                            </h3>
                            <form onSubmit={submit} className="space-y-4">
                                <div>
                                    <InputLabel
                                        htmlFor="email"
                                        value={t('workspaces.member_email')}
                                    />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        list="assignable-members"
                                        className="mt-1 block w-full"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        placeholder="cliente@empresa.com"
                                        required
                                    />
                                    {assignableMembers.length > 0 && (
                                        <datalist id="assignable-members">
                                            {assignableMembers.map((member) => (
                                                <option
                                                    key={member.id}
                                                    value={member.email}
                                                >
                                                    {member.name}
                                                </option>
                                            ))}
                                        </datalist>
                                    )}
                                    <p className="mt-2 text-xs text-sp-muted">
                                        {t('workspaces.member_email_hint')}
                                    </p>
                                    <InputError
                                        message={errors.email}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="role"
                                        value={t('common.role')}
                                    />
                                    <select
                                        id="role"
                                        className="sp-input mt-1 block w-full"
                                        value={data.role}
                                        onChange={(e) =>
                                            setData('role', e.target.value)
                                        }
                                    >
                                        {memberRoles.map((role) => (
                                            <option
                                                key={role.value}
                                                value={role.value}
                                            >
                                                {role.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.role}
                                        className="mt-2"
                                    />
                                </div>

                                {data.role === 'client_readonly' && (
                                    <label className="flex items-start gap-3 rounded-lg border border-sp-border bg-sp-surface/50 p-4">
                                        <input
                                            type="checkbox"
                                            checked={data.invite}
                                            onChange={(e) =>
                                                setData(
                                                    'invite',
                                                    e.target.checked,
                                                )
                                            }
                                            className="mt-0.5 rounded border-sp-border text-sp-primary focus:ring-sp-primary"
                                        />
                                        <span className="text-sm text-sp-muted">
                                            {t(
                                                'workspaces.invite_client_if_missing',
                                            )}
                                        </span>
                                    </label>
                                )}

                                <PrimaryButton disabled={processing}>
                                    {t('workspaces.assign')}
                                </PrimaryButton>
                            </form>
                        </Card>
                    )}
                </div>
        </WorkspaceLayout>
    );
}

function StatCard({
    label,
    value,
    highlight = false,
}: {
    label: string;
    value: number;
    highlight?: boolean;
}) {
    return (
        <div className="sp-card p-4">
            <p className="text-xs font-medium uppercase tracking-wide text-sp-muted">
                {label}
            </p>
            <p
                className={
                    'mt-1 text-2xl font-semibold ' +
                    (highlight ? 'text-amber-700' : 'text-sp-ink')
                }
            >
                {value}
            </p>
        </div>
    );
}
