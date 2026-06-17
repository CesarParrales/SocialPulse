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

export default function ForgotPassword({ status }: { status?: string }) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title={t('auth.forgot_title')} />

            <AuthFormHeader
                title={t('auth.forgot_title')}
                description={t('auth.forgot_description')}
            />

            {status && <FlashAlert message={status} className="mb-4" />}

            <form onSubmit={submit}>
                <InputLabel htmlFor="email" value={t('auth.email')} />
                <TextInput
                    id="email"
                    type="email"
                    name="email"
                    value={data.email}
                    className="mt-1 block w-full"
                    isFocused={true}
                    onChange={(e) => setData('email', e.target.value)}
                />

                <InputError message={errors.email} className="mt-2" />

                <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={route('login')}
                        className="text-sm text-sp-muted underline hover:text-sp-ink"
                    >
                        {t('auth.back_to_login')}
                    </Link>
                    <PrimaryButton disabled={processing}>
                        {t('auth.send_reset_link')}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
