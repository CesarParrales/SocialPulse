import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import SettingsLayout from '@/Components/Templates/SettingsLayout';
import CalloutBanner from '@/Components/UI/CalloutBanner';
import Card from '@/Components/UI/Card';
import FlashAlert from '@/Components/UI/FlashAlert';
import { useTranslation } from '@/lib/i18n';
import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';
import { PageProps } from '@/types';

type PlanOption = { value: string; label: string };

export default function PlatformCreateAgency({
    planOptions,
}: PageProps<{
    planOptions: PlanOption[];
}>) {
    const { t } = useTranslation();
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        plan: planOptions[1]?.value ?? 'agency',
        billing_email: '',
        admin_email: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('settings.platform.agencies.store'));
    };

    return (
        <SettingsLayout
            headTitle={t('platform.create_agency')}
            title={t('platform.create_agency')}
            description={t('platform.create_agency_description')}
            actions={
                <Link
                    href={route('settings.platform.index')}
                    className="sp-link text-sm"
                >
                    ← {t('platform.back_to_platform')}
                </Link>
            }
        >
            {flash.success && (
                <FlashAlert message={flash.success} className="mb-6" />
            )}

            <Card className="max-w-2xl">
                <CalloutBanner title={t('platform.create_agency_hint_title')}>
                    {t('platform.create_agency_hint')}
                </CalloutBanner>

                <form onSubmit={submit} className="mt-6 space-y-4">
                    <div>
                        <InputLabel htmlFor="name" value={t('common.name')} />
                        <TextInput
                            id="name"
                            className="mt-1 block w-full"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            autoFocus
                            placeholder={t('platform.create_name_placeholder')}
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="plan" value={t('platform.plan')} />
                        <select
                            id="plan"
                            className="sp-input mt-1 block w-full"
                            value={data.plan}
                            onChange={(e) => setData('plan', e.target.value)}
                        >
                            {planOptions.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.plan} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="billing_email"
                            value={t('settings.billing_email')}
                        />
                        <TextInput
                            id="billing_email"
                            type="email"
                            className="mt-1 block w-full"
                            value={data.billing_email}
                            onChange={(e) =>
                                setData('billing_email', e.target.value)
                            }
                        />
                        <InputError
                            message={errors.billing_email}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="admin_email"
                            value={t('platform.admin_email')}
                        />
                        <TextInput
                            id="admin_email"
                            type="email"
                            className="mt-1 block w-full"
                            value={data.admin_email}
                            onChange={(e) =>
                                setData('admin_email', e.target.value)
                            }
                            placeholder="admin@agencia.com"
                        />
                        <p className="mt-1 text-xs text-sp-muted">
                            {t('platform.admin_email_help')}
                        </p>
                        <InputError
                            message={errors.admin_email}
                            className="mt-2"
                        />
                    </div>

                    <PrimaryButton disabled={processing}>
                        {t('common.create')}
                    </PrimaryButton>
                </form>
            </Card>
        </SettingsLayout>
    );
}
