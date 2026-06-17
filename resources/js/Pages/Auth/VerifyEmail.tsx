import PrimaryButton from '@/Components/PrimaryButton';
import AuthFormHeader from '@/Components/UI/AuthFormHeader';
import FlashAlert from '@/Components/UI/FlashAlert';
import { useTranslation } from '@/lib/i18n';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useTranslation();
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title={t('auth.verify_title')} />

            <AuthFormHeader
                title={t('auth.verify_title')}
                description={t('auth.verify_description')}
            />

            {status === 'verification-link-sent' && (
                <FlashAlert
                    message={t('auth.verify_sent')}
                    className="mb-4"
                />
            )}

            <form onSubmit={submit}>
                <div className="mt-2 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <PrimaryButton disabled={processing}>
                        {t('auth.verify_resend')}
                    </PrimaryButton>

                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="text-sm text-sp-muted underline hover:text-sp-ink"
                    >
                        {t('auth.logout')}
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
