import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthFormHeader from '@/Components/UI/AuthFormHeader';
import FlashAlert from '@/Components/UI/FlashAlert';
import { useTranslation } from '@/lib/i18n';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title={t('auth.login')} />

            <AuthFormHeader
                title={t('auth.login')}
                description={t('auth.login_subtitle')}
            />

            {status && <FlashAlert message={status} className="mb-4" />}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value={t('auth.email')} />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value={t('auth.password')} />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4 block">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData(
                                    'remember',
                                    (e.target.checked || false) as false,
                                )
                            }
                        />
                        <span className="ms-2 text-sm text-sp-muted">
                            {t('auth.remember')}
                        </span>
                    </label>
                </div>

                <div className="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="text-sm text-sp-muted underline hover:text-sp-ink"
                        >
                            {t('auth.forgot_password')}
                        </Link>
                    )}

                    <PrimaryButton
                        className="sm:ms-auto"
                        disabled={processing}
                    >
                        {t('auth.login')}
                    </PrimaryButton>
                </div>

                <p className="mt-6 text-center text-sm text-sp-muted">
                    {t('auth.no_account')}{' '}
                    <Link
                        href={route('register')}
                        className="font-medium text-sp-primary hover:underline"
                    >
                        {t('auth.register')}
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
