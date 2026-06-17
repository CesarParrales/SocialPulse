import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import FlashAlert from '@/Components/UI/FlashAlert';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import { useTranslation } from '@/lib/i18n';
import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function UpdateProfileInformation({
    mustVerifyEmail,
    className = '',
}: {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}) {
    const { t } = useTranslation();
    const user = usePage().props.auth.user!;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-semibold text-sp-ink">
                    {t('profile.info_title')}
                </h2>

                <p className="mt-1 text-sm text-sp-muted">
                    {t('profile.info_description')}
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <div>
                    <InputLabel htmlFor="name" value={t('common.name')} />

                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        isFocused
                        autoComplete="name"
                    />

                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor="email" value={t('common.email')} />

                    <TextInput
                        id="email"
                        type="email"
                        className="mt-1 block w-full"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoComplete="username"
                    />

                    <InputError className="mt-2" message={errors.email} />
                </div>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <CalloutBanner
                        title={t('profile.email_unverified')}
                        variant="warning"
                    >
                        <Link
                            href={route('verification.send')}
                            method="post"
                            as="button"
                            className="font-medium underline hover:no-underline"
                        >
                            {t('profile.resend_verification')}
                        </Link>
                    </CalloutBanner>
                )}

                {recentlySuccessful && (
                    <FlashAlert message={t('profile.saved')} />
                )}

                <div className="flex justify-end">
                    <PrimaryButton disabled={processing}>
                        {t('common.save')}
                    </PrimaryButton>
                </div>
            </form>
        </section>
    );
}
