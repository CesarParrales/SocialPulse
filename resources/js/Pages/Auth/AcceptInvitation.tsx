import AuthFormHeader from '@/Components/UI/AuthFormHeader';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useTranslation } from '@/lib/i18n';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const roleLabels: Record<string, string> = {
    agency_admin: 'workspaces.member_role_agency_admin',
    operator: 'workspaces.member_role_operator',
    client_readonly: 'workspaces.member_role_client',
};

export default function AcceptInvitation({
    email,
    agencyName,
    role,
    token,
}: {
    email: string;
    agencyName: string;
    role: string;
    token: string;
}) {
    const { t } = useTranslation();

    const roleLabel = t(
        roleLabels[role] ?? 'workspaces.member_role_operator',
    );

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('invitations.store', token), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title={t('auth.invitation_title')} />

            <AuthFormHeader title={t('auth.invitation_title')} />

            <p className="mb-6 text-sm text-sp-muted">
                {t('auth.invitation_intro', {
                    agency: agencyName,
                    role: roleLabel,
                })}
            </p>

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value={t('common.email')} />
                    <TextInput
                        id="email"
                        type="email"
                        className="mt-1 block w-full bg-sp-surface"
                        value={email}
                        disabled
                    />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="name" value={t('common.name')} />
                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        autoFocus
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value={t('auth.password')} />
                    <TextInput
                        id="password"
                        type="password"
                        className="mt-1 block w-full"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value={t('auth.password_confirm')}
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        className="mt-1 block w-full"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                    />
                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="mt-6 flex items-center justify-end">
                    <PrimaryButton disabled={processing}>
                        {t('auth.create_account')}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
