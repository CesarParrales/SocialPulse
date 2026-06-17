import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import EmptyState from '@/Components/UI/EmptyState';
import FlashAlert from '@/Components/UI/FlashAlert';
import PageHeader from '@/Components/UI/PageHeader';
import StatusBadge from '@/Components/UI/StatusBadge';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import { teamRoleLabel } from '@/lib/teamRoles';
import { useTranslation } from '@/lib/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { PageProps } from '@/types';

interface TeamMember {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

interface PendingInvitation {
    id: number;
    email: string;
    role: string;
    expires_at: string;
}

export default function Index({
    agency,
    members,
    invitations,
    invitableRoles,
    clientInviteHint,
}: PageProps<{
    agency: { id: number; name: string };
    members: TeamMember[];
    invitations: PendingInvitation[];
    invitableRoles: Array<{ value: string; label: string }>;
    clientInviteHint?: string;
}>) {
    const { t } = useTranslation();
    const { flash, locale } = usePage().props;

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        role: invitableRoles.find((r) => r.value === 'operator')?.value
            ?? invitableRoles[0]?.value
            ?? 'operator',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('team.invitations.store'), {
            onSuccess: () => reset('email'),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('team.title')} />

            <div className="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <PageHeader
                    title={t('team.title_with_agency', { name: agency.name })}
                    description={t('team.description')}
                />

                {flash.success && <FlashAlert message={flash.success} />}

                <div className="sp-card p-6">
                    <h3 className="mb-4 text-base font-semibold text-sp-ink">
                        {t('team.active_members')}
                        <span className="ml-2 text-sm font-normal text-sp-muted">
                            ({members.length})
                        </span>
                    </h3>
                    {members.length === 0 ? (
                        <EmptyState
                            title={t('team.empty_members')}
                            description={t('team.empty_members_hint')}
                        />
                    ) : (
                        <ul className="divide-y divide-sp-border">
                            {members.map((member) => (
                                <li
                                    key={member.id}
                                    className="flex flex-wrap items-center justify-between gap-3 py-3"
                                >
                                    <div>
                                        <p className="font-medium text-sp-ink">
                                            {member.name}
                                        </p>
                                        <p className="text-sm text-sp-muted">
                                            {member.email}
                                        </p>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {member.roles.map((role) => (
                                            <StatusBadge
                                                key={role}
                                                status={role}
                                                label={teamRoleLabel(role, t)}
                                            />
                                        ))}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className="sp-card p-6">
                    <h3 className="mb-4 text-base font-semibold text-sp-ink">
                        {t('team.invite')}
                    </h3>
                    <CalloutBanner title={t('team.invite_hint_title')}>
                        {t('team.invite_hint')}
                    </CalloutBanner>
                    <form onSubmit={submit} className="mt-4 space-y-4">
                        <div>
                            <InputLabel
                                htmlFor="email"
                                value={t('common.email')}
                            />
                            <TextInput
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                placeholder="operador@agencia.com"
                                required
                            />
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
                                {invitableRoles.map((role) => (
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
                            {data.role === 'client_readonly' &&
                                clientInviteHint && (
                                    <p className="mt-2 text-xs text-sp-muted">
                                        {clientInviteHint}
                                    </p>
                                )}
                        </div>
                        <PrimaryButton disabled={processing}>
                            {t('team.send_invitation')}
                        </PrimaryButton>
                    </form>
                </div>

                {invitations.length > 0 && (
                    <div className="sp-card p-6">
                        <h3 className="mb-4 text-base font-semibold text-sp-ink">
                            {t('team.pending_invitations')}
                            <span className="ml-2 text-sm font-normal text-sp-muted">
                                ({invitations.length})
                            </span>
                        </h3>
                        <ul className="divide-y divide-sp-border">
                            {invitations.map((invitation) => (
                                <li
                                    key={invitation.id}
                                    className="flex flex-wrap items-center justify-between gap-3 py-3"
                                >
                                    <div>
                                        <p className="font-medium text-sp-ink">
                                            {invitation.email}
                                        </p>
                                        <p className="text-sm text-sp-muted">
                                            {teamRoleLabel(invitation.role, t)}{' '}
                                            · {t('common.expires')}{' '}
                                            {new Date(
                                                invitation.expires_at,
                                            ).toLocaleDateString(
                                                locale === 'en' ? 'en' : 'es',
                                            )}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            router.delete(
                                                route(
                                                    'team.invitations.destroy',
                                                    invitation.id,
                                                ),
                                            )
                                        }
                                        className="text-sm text-red-600 hover:underline"
                                    >
                                        {t('common.cancel')}
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
